# Chatbot feature and audit — 29 October 2026

## What the assignment now means

1. A new visitor opens the assistant and sees exactly three starter buttons: Đặt phòng, Tham quan, Khám phá điểm đến.
2. A button returns up to three existing public destination records with detail links and catalogue prices. Hotel suggestions require room types; sightseeing suggestions exclude places with room types. No booking is created by a button.
3. Subsequent buttons use the latest recognized city/destination in this visitor's recent conversation. Vietnamese with and without accents is supported. Mentioning another recognized city changes the context.
4. History and recommendation cards reload from the existing chatbot database. Guest and signed-in identities are scoped server-side; another visitor's or Discord user's conversation is not used.
5. Button actions work without an LLM. Free-text questions retain the existing Python RAG integration. No matching public RAG documents produces an explicit no-data response without model generation.

The next-question feature is a rule based on the visitor's city and latest topic. It is not trained prediction, automatic booking, or automatic itinerary construction. This deliberately gives the otherwise vague assignment acceptance criteria.

## How RAG and history help

The free-text path is: Vietnamese question → domain/intent routing → E5 vector search plus BM25 keyword search → combined ranking → retrieved records added to the LLM prompt → answer. Conversation history supplies context for follow-up questions. Neither retrieval nor history trains the Qwen weights.

The existing E5 implementation already uses `query:` and `passage:` prefixes and a matching 768-dimension index, as required by the [multilingual E5 model card](https://huggingface.co/intfloat/multilingual-e5-base). The current index has **1,111 documents**. The live Laravel catalogue has **425 destinations, 79 cities and 69 room types**. These are different units: the index includes multiple public record types and is built from a SQL export, not queried live. Newly edited catalogue records require rebuilding the RAG documents/embeddings/index; button results query Laravel's current database directly.

Qwen's published model documentation includes [Vietnamese support](https://qwen.readthedocs.io/en/v2.5/). That does not establish the quality of a particular 0.5B model on this application. In this checkout, `AIService` defaults to `qwen2.5:0.5b`, but the local environment selects **Gemini**. The Qwen audit explicitly forces a local Qwen provider without editing `.env`. The ordinary browser free-text test used the configured Gemini provider. Existing domain/intent classifiers are separate from Qwen; this code does not implement Qwen fine-tuning.

## Measured retrieval quality

The current 12-case labelled benchmark contains 10 answerable cases and two expected no-answer/private cases. With follow-up rewriting enabled:

| Measure | Result |
| --- | ---: |
| Recall@5 | 0.767 |
| Recall@10 | 0.933 |
| MRR | 0.783 |
| Precision@5 against labelled IDs | 0.220 |
| Expected no-answer cases returning no documents | 2/2 |
| Mean retrieval latency | 234 ms |
| Process memory reported by benchmark | 1,082 MB |

These measure retrieval, not answer accuracy. This tiny set and its limited relevance labels do not establish general Vietnamese comprehension. The older benchmark report describes a different index size, so its figures should not be treated as a directly comparable run.

Raw results: [`retrieval_website_audit_results.json`](../ai-chatbot/data/retrieval_website_audit_results.json).

## Qwen generation observations

Four smoke cases were each run with and without retrieved context using `qwen2.5:0.5b`, temperature 0 and a 384-token output cap. Both variants use the same existing system prompt. This is not a pure pretrained-model benchmark: the prompt asks it to avoid inventing site records.

| Query | Without retrieved records | With retrieved records |
| --- | --- | --- |
| Famous beach in Đà Nẵng | Says no site evidence | Correctly names Mỹ Khê |
| Dragon-shaped bridge in Đà Nẵng | Invents “Cầu Cổ Môn” | Correctly names Cầu Rồng |
| `dia diem tham quan o Phu Quoc` | Says no data | Copies internal document metadata and describes a resort rather than giving a useful sightseeing list |
| Nonexistent “Sao Mai” city | Does not know | Asserts it is a Vietnamese city, despite retrieval returning no documents |

RAG helped two factual examples; it did not consistently produce useful, grounded answers. The Phú Quốc response passes a naive expected-name check while still being a poor answer. Do not report these phrase checks as an accuracy percentage. The no-data hallucination motivated the deterministic guard now in `web_api.py`; the raw generation audit intentionally records what the model did before that application guard.

Raw answers, route and source IDs: [`generation_website_audit_results.json`](../ai-chatbot/data/generation_website_audit_results.json). Reproduce with `ai-chatbot/.venv/Scripts/python.exe ai-chatbot/rag/evaluate_answers.py` from the repository root. This audit calls only the local Ollama model.

## Implementation and validation

`ChatController` validates requests and delegates through `ChatServiceInterface`. `ChatService` coordinates the existing HTTP bridge, `ChatHistoryService` and `ChatSuggestionService`. The latter reads the live catalogue without asking Qwen to generate SQL or URLs. The widget renders messages and record names as text and permits recommendation links only under `/destinations/`.

History uses `ai_chatbot.chat_sessions/chat_messages` with `channel_id=website`, separate `web-user:<id>` / `web-guest:<UUID>` identities, and the latest 20 messages. Signed-in histories survive browser sessions. Guest histories require the original Laravel session cookie. An unavailable chatbot database falls back to the browser session with a visible notice; up to 20 pending messages are retried on the next successful send. Account changes clear cached history from the previous identity. This does not merge existing Discord history without an explicit identity mapping.

- Laravel chatbot feature suite: **11 tests, 63 assertions pass**. Covers starter buttons, actual records/prices/links, contextual city changes without accents, empty and inactive results, ownership, history bounds, rejected inputs, AI outages and history recovery. Both databases are isolated SQLite in these tests.
- Python suite: **57 tests pass**, including the new no-evidence guard.
- Browser: starter and contextual buttons, real recommendation cards, a Vietnamese free-text answer, database history/card restoration after reload, desktop and 390-pixel mobile layouts, and detail-link navigation checked. The free-text browser test used the local configured provider, Gemini.
- Syntax and whitespace checks pass. Screenshots: [`desktop`](../output/playwright/chat-desktop.png), [`mobile`](../output/playwright/chat-mobile.png).
- The final full Laravel suite ran **399 tests: 264 passed, 135 failed/errored** (1,387 assertions). All 11 chatbot tests passed within that full run. Failures outside the chatbot include missing `ActivityLogServiceInterface` and `BookingServiceInterface` container bindings. These are separate project issues and were not changed as part of this feature. Machine-readable details are in `output/playwright/laravel-suite.xml`.

The original local widget changes were retained and extended. The shared-project-memory skill was not executed. Setup and API details are in [`ai-chatbot/WEB_CHAT.md`](../ai-chatbot/WEB_CHAT.md).
