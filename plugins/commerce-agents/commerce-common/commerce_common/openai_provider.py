# Copyright 2026 Anthropic PBC / Gemini Migration
# SPDX-License-Identifier: Apache-2.0

"""Provider abstraction for commerce agents running on Google Gemini (OpenAI-compatible) and modern LLMs.

Provides:
- AsyncOpenAI client initialization pointing to Gemini endpoint
- Model resolution with GEMINI_MODEL (default: gemini-2.5-flash)
- Anthropic-to-OpenAI tool schema conversion
- Message transcript to Chat Completions format conversion
- Multi-step streaming turn loop with tool calling, eager UI partials, and event emission
"""

from __future__ import annotations

import asyncio
import copy
import json
import logging
import os
import time
from collections.abc import AsyncIterator, Collection, Mapping, Sequence
from typing import Any

import openai
from openai import AsyncOpenAI

from .config import BaseAgentConfig
from .execution import BaseToolExecutor
from .presentation import PresentationComponent, enrich_partial
from .streaming import AgentEvent, ToolOutcome, parse_partial_json
from .turn import (
    compact_history,
    elapsed_ms,
    outcome_events,
    round_closes_turn,
    tool_result_block,
    usage_totals,
)

logger = logging.getLogger(__name__)

DEFAULT_GEMINI_MODEL = "gemini-flash-lite-latest"
DEFAULT_GEMINI_BASE_URL = "https://generativelanguage.googleapis.com/v1beta/openai/"


def get_openai_model(default: str = DEFAULT_GEMINI_MODEL) -> str:
    """Lấy tên model từ OPENAI_MODEL hoặc GEMINI_MODEL, tự động chuẩn hóa model hợp lệ."""
    model = os.environ.get("OPENAI_MODEL", "").strip() or os.environ.get("GEMINI_MODEL", "").strip() or default
    if model in ("gemini-2.5-flash", "gemini-2.0-flash", "gemini-1.5-flash", "gemini-2.0-flash-lite"):
        return DEFAULT_GEMINI_MODEL
    if model == "gpt-4.1-mini":
        return "gpt-4o-mini"
    return model


def get_openai_client(
    api_key: str | None = None,
    timeout: float = 120.0,
) -> AsyncOpenAI:
    """Tạo AsyncOpenAI client kết nối với Google Gemini OpenAI-compatible API."""
    resolved_key = (
        api_key
        or os.environ.get("GEMINI_API_KEY", "").strip()
        or os.environ.get("OPENAI_API_KEY", "").strip()
    )
    if not resolved_key:
        raise ValueError(
            "GEMINI_API_KEY chưa được cấu hình. Vui lòng thêm GEMINI_API_KEY vào file .env."
        )
    base_url = os.environ.get("GEMINI_BASE_URL", DEFAULT_GEMINI_BASE_URL).strip()
    return AsyncOpenAI(api_key=resolved_key, base_url=base_url, timeout=timeout, max_retries=1)


def convert_anthropic_tools_to_openai(
    tools: Sequence[Mapping[str, Any]],
) -> list[dict[str, Any]]:
    """Chuyển định dạng công cụ Anthropic sang Chat Completions function tool chuẩn."""
    openai_tools: list[dict[str, Any]] = []
    for tool in tools:
        if "input_schema" not in tool:
            continue
        schema = copy.deepcopy(dict(tool["input_schema"]))
        _clean_empty_enums(schema)

        openai_tools.append({
            "type": "function",
            "function": {
                "name": str(tool["name"]),
                "description": str(tool.get("description", "")),
                "parameters": schema,
            },
        })
    return openai_tools


def _clean_empty_enums(schema: dict[str, Any]) -> None:
    """Làm sạch các enum rỗng để tránh lỗi schema."""
    if not isinstance(schema, dict):
        return
    for key, val in list(schema.items()):
        if key == "enum" and isinstance(val, list) and len(val) == 0:
            del schema[key]
        elif isinstance(val, dict):
            _clean_empty_enums(val)
        elif isinstance(val, list):
            for item in val:
                if isinstance(item, dict):
                    _clean_empty_enums(item)


def convert_messages_to_chat_messages(
    instructions: str,
    messages: list[dict[str, Any]],
) -> list[dict[str, Any]]:
    """Chuyển đổi lịch sử hội thoại sang định dạng tin nhắn OpenAI Chat Completions chuẩn."""
    chat_messages: list[dict[str, Any]] = []
    if instructions:
        chat_messages.append({"role": "system", "content": instructions})

    for msg in messages:
        role = msg.get("role")
        content = msg.get("content")

        if role == "user":
            if isinstance(content, str):
                chat_messages.append({"role": "user", "content": content})
            elif isinstance(content, list):
                text_parts: list[str] = []
                tool_results: list[dict[str, Any]] = []
                for part in content:
                    if not isinstance(part, dict):
                        continue
                    ptype = part.get("type")
                    if ptype == "text":
                        text_parts.append(str(part.get("text", "")))
                    elif ptype == "tool_result":
                        tool_results.append(part)
                if text_parts:
                    chat_messages.append({"role": "user", "content": "\n".join(text_parts)})
                for tr in tool_results:
                    call_id = tr.get("tool_use_id", "")
                    result_text = str(tr.get("content", ""))
                    chat_messages.append({
                        "role": "tool",
                        "tool_call_id": call_id,
                        "content": result_text,
                    })
        elif role == "assistant":
            if isinstance(content, str):
                chat_messages.append({"role": "assistant", "content": content})
            elif isinstance(content, list):
                text_parts = []
                tool_calls = []
                for part in content:
                    if not isinstance(part, dict):
                        continue
                    ptype = part.get("type")
                    if ptype == "text":
                        text_parts.append(str(part.get("text", "")))
                    elif ptype == "tool_use":
                        call_id = part.get("id", "")
                        name = part.get("name", "")
                        inp = part.get("input", {})
                        args_str = json.dumps(inp, ensure_ascii=False) if isinstance(inp, dict) else str(inp)
                        tc_data = {
                            "id": call_id,
                            "type": "function",
                            "function": {
                                "name": name,
                                "arguments": args_str,
                            },
                        }
                        if part.get("extra_content"):
                            tc_data["extra_content"] = part["extra_content"]
                        tool_calls.append(tc_data)
                item: dict[str, Any] = {"role": "assistant"}
                if text_parts:
                    item["content"] = "\n".join(text_parts)
                else:
                    item["content"] = None
                if tool_calls:
                    item["tool_calls"] = tool_calls
                chat_messages.append(item)
        elif role == "system":
            chat_messages.append({"role": "system", "content": str(content)})

    return chat_messages


async def run_openai_stream_turn(
    *,
    client: AsyncOpenAI,
    model: str,
    instructions: str,
    messages: list[dict[str, Any]],
    tools: list[dict[str, Any]],
    executor: BaseToolExecutor,
    config: BaseAgentConfig,
    session_id: str | None,
    state: Any,
    specs: Mapping[str, PresentationComponent],
    partial_tools: Collection[str],
    eager_frames: bool = False,
    forced_tool: str | None = None,
    fallback_client: AsyncOpenAI | None = None,
    fallback_model: str | None = None,
) -> AsyncIterator[AgentEvent]:
    """Thực hiện một lượt hội thoại với stream, hỗ trợ gọi đa công cụ và cập nhật UI liên tục."""
    turn_started = time.monotonic()
    totals = usage_totals()
    stop_reason: str | None = None
    last_prompt_tokens = 0
    active_client = client
    active_model = model

    for round_index in range(config.max_tool_iterations + 1):
        force_text = round_index == config.max_tool_iterations
        if force_text:
            tool_choice: Any = "none"
        elif round_index == 0 and forced_tool:
            tool_choice = {"type": "function", "function": {"name": forced_tool}}
        else:
            tool_choice = "auto"

        chat_messages = convert_messages_to_chat_messages(instructions, messages)

        success = False
        switched_to_fallback = False
        attempt = 0
        max_attempts = 4
        while attempt < max_attempts:
            assistant_text = ""
            active_tool_calls = {}
            tool_call_order = []
            try:
                stream = await active_client.chat.completions.create(
                    model=active_model,
                    messages=chat_messages,
                    tools=tools if tools else None,
                    tool_choice=tool_choice if tools else None,
                    stream=True,
                )
                async for chunk in stream:
                    if not chunk.choices:
                        continue
                    delta = chunk.choices[0].delta
                    if delta.content:
                        assistant_text += delta.content
                        yield AgentEvent.text_delta(delta.content)
                    if delta.tool_calls:
                        for tc in delta.tool_calls:
                            target_key = None
                            if tc.id:
                                for k, v in active_tool_calls.items():
                                    if v.get("call_id") == tc.id:
                                        target_key = k
                                        break
                            if target_key is None and tc.index is not None:
                                target_key = tc.index

                            extra = getattr(tc, "extra_content", None) or {}
                            if target_key is None or target_key not in active_tool_calls:
                                target_key = len(active_tool_calls)
                                call_id = tc.id or f"call_{target_key}_{int(time.time()*1000)}"
                                active_tool_calls[target_key] = {
                                    "call_id": call_id,
                                    "name": tc.function.name if (tc.function and tc.function.name) else "",
                                    "buffer": tc.function.arguments if (tc.function and tc.function.arguments) else "",
                                    "signature": None,
                                    "extra_content": extra,
                                }
                                tool_call_order.append(target_key)
                            else:
                                if tc.id:
                                    active_tool_calls[target_key]["call_id"] = tc.id
                                if tc.function:
                                    if tc.function.name:
                                        active_tool_calls[target_key]["name"] = tc.function.name
                                    if tc.function.arguments:
                                        active_tool_calls[target_key]["buffer"] += tc.function.arguments
                                if extra:
                                    active_tool_calls[target_key]["extra_content"] = extra

                            call_entry = active_tool_calls[target_key]
                            tname = call_entry["name"]
                            if tname in partial_tools:
                                parsed = parse_partial_json(
                                    call_entry["buffer"], settle_strings=not eager_frames
                                )
                                if parsed and tname in specs:
                                    partial = enrich_partial(specs[tname], parsed, state)
                                    if partial is not None:
                                        component, payload, sig = partial
                                        sig_key = json.dumps(
                                            payload if eager_frames else sig,
                                            sort_keys=True,
                                            default=str,
                                        )
                                        if sig_key != call_entry["signature"]:
                                            call_entry["signature"] = sig_key
                                            yield AgentEvent.ui_partial(
                                                component, payload, call_entry["call_id"]
                                            )

                    if hasattr(chunk, "usage") and chunk.usage:
                        totals["input_tokens"] += chunk.usage.prompt_tokens or 0
                        totals["output_tokens"] += chunk.usage.completion_tokens or 0
                        last_prompt_tokens = chunk.usage.prompt_tokens or 0

                success = True
                break
            except (openai.RateLimitError, openai.InternalServerError, openai.APITimeoutError, openai.AuthenticationError, openai.APIConnectionError, openai.NotFoundError, openai.BadRequestError) as ai_err:
                if fallback_client and active_client != fallback_client and not switched_to_fallback:
                    switched_to_fallback = True
                    logger.warning(
                        "Primary AI model (%s) failed with %s: %s. Switching to fallback (%s)...",
                        active_model, type(ai_err).__name__, ai_err, fallback_model
                    )
                    active_client = fallback_client
                    active_model = fallback_model or DEFAULT_GEMINI_MODEL
                    yield AgentEvent.model_provider("gemini", active_model)
                    attempt = 0
                    continue

                if active_model != DEFAULT_GEMINI_MODEL and isinstance(ai_err, openai.NotFoundError):
                    logger.warning(
                        "Model %s returned 404, falling back to %s",
                        active_model, DEFAULT_GEMINI_MODEL
                    )
                    active_model = DEFAULT_GEMINI_MODEL
                    yield AgentEvent.model_provider("gemini", active_model)
                    attempt = 0
                    continue

                if attempt < 2 and isinstance(ai_err, (openai.RateLimitError, openai.InternalServerError, openai.APITimeoutError, openai.APIConnectionError)):
                    wait_s = 1.0 * (attempt + 1)
                    await asyncio.sleep(wait_s)
                    attempt += 1
                    continue

                logger.exception("AI stream failed after retries: %s", ai_err)
                yield AgentEvent.error(
                    f"Máy chủ AI ({active_model}) đang phản hồi chậm hoặc lỗi kết nối. Vui lòng thử lại sau giây lát."
                )
                return
            except Exception as e:
                if fallback_client and active_client != fallback_client and not switched_to_fallback:
                    switched_to_fallback = True
                    logger.warning(
                        "Primary AI model (%s) failed with %s: %s. Switching to fallback (%s)...",
                        active_model, type(e).__name__, e, fallback_model
                    )
                    active_client = fallback_client
                    active_model = fallback_model or DEFAULT_GEMINI_MODEL
                    yield AgentEvent.model_provider("gemini", active_model)
                    attempt = 0
                    continue

                logger.exception("AI stream failed: %s", e)
                yield AgentEvent.error(f"Đã xảy ra lỗi trong quá trình xử lý: {e}")
                return

        if not success:
            return
        # Nếu không có lệnh gọi công cụ nào hoặc đã đến giới hạn, kết thúc lượt
        if not tool_call_order or force_text:
            if assistant_text:
                messages.append({"role": "assistant", "content": assistant_text})
            stop_reason = "end_turn"
            break

        assistant_blocks: list[dict[str, Any]] = []
        if assistant_text:
            assistant_blocks.append({"type": "text", "text": assistant_text})

        for idx in tool_call_order:
            cinfo = active_tool_calls[idx]
            cid = cinfo["call_id"]
            try:
                parsed_args = json.loads(cinfo["buffer"]) if cinfo["buffer"].strip() else {}
            except Exception:
                parsed_args = {}
            cinfo["parsed_args"] = parsed_args
            tool_use_block = {
                "type": "tool_use",
                "id": cid,
                "name": cinfo["name"],
                "input": parsed_args,
            }
            if cinfo.get("extra_content"):
                tool_use_block["extra_content"] = cinfo["extra_content"]
            assistant_blocks.append(tool_use_block)

        messages.append({"role": "assistant", "content": assistant_blocks})

        for idx in tool_call_order:
            cinfo = active_tool_calls[idx]
            yield executor.tool_call_event(cinfo["name"], cinfo["call_id"], cinfo["parsed_args"])

        tool_results_list: list[tuple[str, ToolOutcome]] = []
        user_result_blocks: list[dict[str, Any]] = []

        for idx in tool_call_order:
            cinfo = active_tool_calls[idx]
            cid = cinfo["call_id"]
            outcome = await executor.execute(cinfo["name"], cinfo["parsed_args"])
            tool_results_list.append((cinfo["name"], outcome))

            for event in outcome_events(cinfo["name"], cid, outcome):
                yield event

            user_result_blocks.append(tool_result_block(cid, outcome))

        messages.append({"role": "user", "content": user_result_blocks})

        if config.close_on_presentation and round_closes_turn(
            tool_results_list, executor.ends_clean
        ):
            stop_reason = "end_turn"
            break

    cleared = compact_history(
        messages, last_prompt_tokens, config.compact_history_above_tokens, session_id or ""
    )
    yield AgentEvent.turn_complete(stop_reason, totals, elapsed_ms(turn_started), cleared)
