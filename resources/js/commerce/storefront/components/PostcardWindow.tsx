// Copyright 2026 Anthropic PBC
// SPDX-License-Identifier: Apache-2.0

import { destinationGradientCss } from "../lib/format";

/**
 * Hiển thị thẻ hình ảnh tượng trưng (postcard) cho các điểm đến / thành phố.
 * Kích thước chữ được đồng bộ đều nhau cho tất cả tỉnh thành, nhỏ gọn và tinh tế chuẩn Times New Roman.
 */
export function PostcardWindow({
  city,
  title,
  className = "",
  fontSize,
}: {
  city?: string;
  title: string;
  className?: string;
  fontSize?: string;
}) {
  const label = city ?? title.split(/\s+/)[0] ?? "";
  return (
    <div
      className={`al-postcard ${className}`}
      style={{ backgroundImage: destinationGradientCss(city, title) }}
      aria-hidden
    >
      <span className="al-postcard-city" style={fontSize ? { fontSize } : undefined}>
        {label}
      </span>
    </div>
  );
}
