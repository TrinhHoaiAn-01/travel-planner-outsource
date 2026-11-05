// Copyright 2026 Anthropic PBC
// SPDX-License-Identifier: Apache-2.0

"use client";

/** Danh mục từ ngữ tiếng Việt cho chuyến đi và đơn đặt phòng. */

import { isOpen, type Order, ORDER_NOUNS, type OrderNouns, useCatalogIndex } from "web-shared";
import { PostcardWindow } from "../components/PostcardWindow";
import { fetchProducts } from "./api";
import { productCity } from "./format";

export const NOUNS: OrderNouns = {
  ...ORDER_NOUNS,
  one: "chuyến đi",
  title: "Chuyến đi",
  cardTitle: "Sắp tới",
  noneOpen: "Không có chuyến đi nào",
  openVerb: "Bắt đầu",
  closedWhen: (_order, date) => `Vào ${date}`,
  statusLabels: {
    processing: "Đang xác nhận",
    shipped: "Đã xác nhận",
    out_for_delivery: "Đã xác nhận",
    delivered: "Hoàn tất",
    cancelled: "Đã hủy",
    return_initiated: "Yêu cầu hoàn tiền",
    refunded: "Đã hoàn tiền",
  },
  filters: [
    { id: "open", label: "Sắp tới", match: isOpen },
    { id: "closed", label: "Đã qua", match: (order) => !isOpen(order) },
  ],
  handoff(order) {
    const ref = `chuyến đi #${order.order_id}`;
    if (isOpen(order)) return { label: "Hỏi trợ lý", prompt: `Trạng thái của ${ref} như thế nào?` };
    if (order.status === "delivered") return { label: "Đặt lại chuyến này", prompt: `Lên kế hoạch một chuyến đi tương tự ${ref}.` };
    return { label: "Hỏi trợ lý", prompt: `Thông tin chi tiết về ${ref} như thế nào?` };
  },
};

/** Hình thu nhỏ điểm đến của chuyến đi. */
export function TripThumb({ order }: { order: Order }) {
  const catalog = useCatalogIndex(fetchProducts);
  const product = order.items.map((item) => catalog[item.product_id]).find(Boolean);
  const city = product ? productCity(product) : undefined;
  return <PostcardWindow city={city} title={order.items[0]?.title ?? order.order_id} className="h-[42px] w-[60px] shrink-0" />;
}
