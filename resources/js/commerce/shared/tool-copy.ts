// Copyright 2026 Anthropic PBC
// SPDX-License-Identifier: Apache-2.0

const TOOL_COPY: Record<string, string> = {
  search_products: "Đang tìm kiếm sản phẩm…",
  get_product_details: "Đang xem chi tiết thông số…",
  search_policies: "Đang kiểm tra chính sách đổi trả & bảo hành…",
  get_orders: "Đang tra cứu lịch sử đơn hàng…",
  get_order_status: "Đang kiểm tra tình trạng đơn hàng…",
  get_fulfillment_options: "Đang kiểm tra tùy chọn giao hàng…",
  get_cart: "Đang kiểm tra giỏ hàng…",
  add_to_cart: "Đang cập nhật giỏ hàng…",
  update_cart_item: "Đang cập nhật giỏ hàng…",
  remove_from_cart: "Đang cập nhật giỏ hàng…",
  checkout: "Đang chuẩn bị xác nhận đơn hàng…",
  get_preferences: "Đang đọc thông tin sở thích…",
  recall_memories: "Đang tra cứu thông tin bạn đã lưu…",
  save_memory: "Đang lưu thông tin cho lần mua sắm tới…",
  load_skill: "Đang tải…",
  web_search: "Đang tìm kiếm thông tin…",
  get_business_snapshot: "Đang tải báo cáo tổng quan kinh doanh…",
  query_metrics: "Đang truy vấn chỉ số kinh doanh…",
  search_listings: "Đang tìm kiếm danh sách hàng hóa…",
  get_listing: "Đang đọc chi tiết mặt hàng…",
  get_inventory_alerts: "Đang kiểm tra cảnh báo tồn kho…",
  get_order_issues: "Đang kiểm tra sự cố đơn hàng…",
  get_pricing_context: "Đang xem thông tin định giá…",
  get_campaign_performance: "Đang xem hiệu quả chiến dịch…",
  get_pending_changes: "Đang kiểm tra các thay đổi chờ duyệt…",
  run_analysis: "Đang phân tích chuyên sâu…",
  apply_change: "Đang áp dụng thay đổi…",
  discard_change: "Đang hủy thay đổi…",
};

const QUERY_TOOLS = new Set([
  "search_products",
  "search_policies",
  "search_listings",
  "web_search",
]);

export function describeToolCall(tool: string, input: Record<string, unknown> = {}): string {
  const copy = TOOL_COPY[tool];
  const query = typeof input.query === "string" ? input.query.trim() : "";
  if (copy && query && QUERY_TOOLS.has(tool)) {
    return `${copy.replace(/…$/, "")} · “${query}”`;
  }
  if (copy) return copy;
  if (tool.startsWith("present_")) return "Đang chuẩn bị câu trả lời…";
  if (tool.startsWith("stage_")) return "Đang lập đề xuất chờ duyệt…";
  return `${tool.replaceAll("_", " ")}…`;
}
