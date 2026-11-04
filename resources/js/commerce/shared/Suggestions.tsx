// Copyright 2026 Anthropic PBC
// SPDX-License-Identifier: Apache-2.0

"use client";

import { useState } from "react";

export function Suggestions({
  suggestions,
  onPick,
  disabled,
  topicLabel,
}: {
  suggestions: string[];
  onPick: (text: string) => void;
  disabled?: boolean;
  topicLabel?: string;
}) {
  const [extraInfo, setExtraInfo] = useState("");

  if (!suggestions.length) return null;

  // Xác định tiêu đề Thông tin bổ sung ở thời điểm hiện tại (ví dụ: Thông tin bổ sung ở du lịch Hà Nội)
  const resolvedTopicLabel = (() => {
    if (topicLabel) return topicLabel;
    const textAll = suggestions.join(" ").toLowerCase();

    // Đếm số lượng thành phố khác nhau xuất hiện trong danh sách gợi ý
    const matchedCities = [
      textAll.includes("hà nội") || textAll.includes("hoàn kiếm") || textAll.includes("văn miếu") || textAll.includes("phố cổ"),
      textAll.includes("đà nẵng") || textAll.includes("mỹ khê") || textAll.includes("bà nà") || textAll.includes("sơn trà"),
      textAll.includes("hồ chí minh") || textAll.includes("sài gòn") || textAll.includes("bến thành"),
      textAll.includes("huế") || textAll.includes("đại nội") || textAll.includes("lăng cô") || textAll.includes("thiên mụ"),
    ].filter(Boolean);

    // Chỉ khi đúng 1 thành phố duy nhất xuất hiện thì mới đặt tên theo thành phố đó
    if (matchedCities.length === 1) {
      if (textAll.includes("hà nội") || textAll.includes("hoàn kiếm") || textAll.includes("văn miếu") || textAll.includes("phố cổ")) {
        return "Thông tin bổ sung ở du lịch Hà Nội";
      }
      if (textAll.includes("đà nẵng") || textAll.includes("mỹ khê") || textAll.includes("bà nà") || textAll.includes("sơn trà")) {
        return "Thông tin bổ sung ở du lịch Đà Nẵng";
      }
      if (textAll.includes("hồ chí minh") || textAll.includes("sài gòn") || textAll.includes("bến thành")) {
        return "Thông tin bổ sung ở du lịch TP. Hồ Chí Minh";
      }
      if (textAll.includes("huế") || textAll.includes("đại nội") || textAll.includes("lăng cô") || textAll.includes("thiên mụ")) {
        return "Thông tin bổ sung ở du lịch Huế";
      }
    }
    return "Thông tin bổ sung ở thời điểm hiện tại";
  })();

  const handleConfirm = () => {
    const text = extraInfo.trim();
    if (!text || disabled) return;
    onPick(text);
    setExtraInfo("");
  };

  const handlePickSuggestion = (suggestion: string) => {
    if (disabled) return;
    const extra = extraInfo.trim();
    if (extra) {
      onPick(`${suggestion} (Thông tin bổ sung: ${extra})`);
      setExtraInfo("");
    } else {
      onPick(suggestion);
    }
  };

  return (
    <div
      className="mt-3 flex flex-col gap-2.5 w-full"
      role="group"
      aria-label="Gợi ý khám phá tiếp theo"
    >
      {/* Danh sách button gợi ý mở rộng 100% width, thiết kế hiện đại, tỉ lệ chuyên nghiệp */}
      <div className="flex flex-col gap-2 w-full">
        {suggestions.map((suggestion, index) => (
          <button
            key={suggestion}
            type="button"
            disabled={disabled}
            onClick={() => handlePickSuggestion(suggestion)}
            className="group w-full flex items-center justify-between text-left px-5 py-3.5 rounded-2xl sm:rounded-full border border-slate-200/90 bg-white/95 hover:border-blue-500 hover:bg-blue-50/40 active:bg-blue-50/80 shadow-2xs hover:shadow-xs transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed select-none"
            style={{ animationDelay: `${index * 50}ms` }}
            title={`Bấm để chọn: ${suggestion}`}
          >
            <div className="flex items-center gap-3 min-w-0">
              <span className="flex items-center justify-center w-6 h-6 rounded-full bg-blue-50 text-blue-600 text-xs font-bold shrink-0 group-hover:scale-110 group-hover:bg-blue-100 transition-all">
                ✦
              </span>
              <span className="truncate font-semibold text-[14px] text-slate-800 group-hover:text-blue-700 transition-colors">
                {suggestion}
              </span>
            </div>
            <span className="text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all duration-200 text-sm ml-2 shrink-0">
              →
            </span>
          </button>
        ))}
      </div>

      {/* Khung Thông tin bổ sung theo Hình 1 mẫu, có nút Xác nhận bên phải nằm cùng hàng */}
      <div className="mt-2 pt-3 border-t border-slate-200/80 w-full flex flex-col gap-1.5">
        <label className="text-[12.5px] font-semibold text-slate-700 select-none">
          {resolvedTopicLabel}
        </label>
        <div className="flex items-center gap-2 w-full">
          <input
            type="text"
            value={extraInfo}
            onChange={(e) => setExtraInfo(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                handleConfirm();
              }
            }}
            disabled={disabled}
            placeholder="Ví dụ: Đi 3 ngày 2 đêm, có trẻ nhỏ, ngân sách 5 triệu, muốn gần bãi biển..."
            className="flex-1 min-w-0 h-11 px-4 rounded-xl border border-slate-200 bg-white text-[13.5px] text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition shadow-2xs"
          />
          <button
            type="button"
            disabled={disabled || !extraInfo.trim()}
            onClick={handleConfirm}
            className="shrink-0 h-11 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-[13.5px] shadow-xs hover:shadow-sm transition-all duration-150 disabled:opacity-40 disabled:cursor-not-allowed select-none flex items-center justify-center gap-1.5 whitespace-nowrap"
          >
            <span>Xác nhận</span>
          </button>
        </div>
      </div>
    </div>
  );
}
