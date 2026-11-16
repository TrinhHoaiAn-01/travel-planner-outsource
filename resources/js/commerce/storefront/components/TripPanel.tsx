// Copyright 2026 Anthropic PBC
// SPDX-License-Identifier: Apache-2.0

"use client";

import { AskLink, BagPanel, CheckoutButton, plural, RemoveLink, Stepper, TotalRow, useCatalogIndex, useStoreFrame } from "web-shared";
import { fetchProducts } from "../lib/api";
import { formatPrice, productCity, productPlace, quantityLabel, shortDate } from "../lib/format";
import type { CartItem, CartPayload, Product } from "../lib/types";
import { PostcardWindow } from "./PostcardWindow";

/** Stays count nights, experiences count guests; flights get no stepper. */
function quantityNoun(item: CartItem): "đêm" | "khách" | null {
  if (item.product_id.startsWith("AL-STAY-")) return "đêm";
  if (item.product_id.startsWith("AL-EXP-")) return "khách";
  return null;
}

function quantityMessage(item: CartItem, quantity: number): string {
  const s = quantity === 1 ? "" : "s";
  return quantityNoun(item) === "đêm" ? `Đổi ${item.title} thành ${quantity} đêm.` : `Đổi ${item.title} cho ${quantity} khách.`;
}

function cancellationLine(product?: Product): { text: string; free: boolean } | null {
  if (!product?.attributes) return null;
  const refundable = /^(yes|true)$/i.test(product.attributes.refundable ?? "");
  if (refundable) {
    const until = shortDate(product.attributes.free_cancellation_until);
    return { text: until ? `Hủy miễn phí trước ${until}` : "Hủy miễn phí", free: true };
  }
  if (/^(no|false|none)$/i.test((product.attributes.refundable ?? "").trim())) {
    return { text: "Không hoàn tiền", free: false };
  }
  return null;
}

function CartRow({ item, product, onRemoveItem }: { item: CartItem; product?: Product; onRemoveItem?: (item: CartItem) => void }) {
  const { ask } = useStoreFrame();
  const noun = quantityNoun(item);
  const cancellation = cancellationLine(product);
  const nights = noun === "đêm" ? item.quantity : 0;
  return (
    <div>
      <div className="flex min-w-0 items-start gap-3">
        <PostcardWindow
          city={product ? productPlace(product) : undefined}
          title={item.title}
          className="h-[56px] w-[88px] shrink-0"
        />
        <div className="min-w-0 flex-1">
          <div className="al-display truncate text-[14px] font-semibold leading-snug text-(--ink)">
            {item.title}
          </div>
          {product ? (
            <div className="al-meta mt-0.5 truncate">
              {[product.brand, productCity(product)].filter(Boolean).join(" · ")}
            </div>
          ) : null}
          {nights > 0 ? (
            <div className="mt-1.5 flex items-center gap-1.5">
              <span aria-hidden className="flex gap-[3px]">
                {Array.from({ length: Math.min(nights, 7) }, (_, i) => (
                  <span
                    key={i}
                    className="h-2 w-3.5 rounded-[3px]"
                    style={{ background: "var(--well)", border: "1px solid var(--line)" }}
                  />
                ))}
              </span>
              <span className="text-[11px] font-semibold text-(--ink-soft)">
                {nights} đêm
              </span>
            </div>
          ) : null}
          {cancellation ? (
            <div
              className={`mt-1 text-[11px] font-semibold ${
                cancellation.free ? "text-(--accent)" : "text-(--ink-soft)"
              }`}
            >
              {cancellation.free ? "✓ " : ""}
              {cancellation.text}
            </div>
          ) : null}
        </div>
      </div>
      {/* Line totals share the trip total's right edge. */}
      <div className="mt-1.5 flex items-baseline justify-between gap-3 pl-[100px]">
        <span className="text-[12px] text-(--ink-soft)">
          {formatPrice(item.price)} {quantityLabel(item.product_id, item.quantity)}
        </span>
        <span className="al-display shrink-0 text-[15px] font-bold text-(--ink)">
          {formatPrice(item.line_total)}
        </span>
      </div>
      <div className="mt-2 flex items-center gap-2.5">
        {noun ? (
          <Stepper
            quantity={item.quantity}
            unit={noun}
            itemTitle={item.title}
            onChange={(quantity) => {
              if (quantity < 1) {
                if (onRemoveItem) onRemoveItem(item);
                else ask(`Xóa ${item.title} khỏi chuyến đi của tôi.`);
              } else {
                ask(quantityMessage(item, quantity));
              }
            }}
          />
        ) : null}
        <RemoveLink
          itemTitle={item.title}
          onClick={() => {
            if (onRemoveItem) {
              onRemoveItem(item);
            } else {
              ask(`Xóa ${item.title} khỏi chuyến đi của tôi.`);
            }
          }}
        />
      </div>
    </div>
  );
}

/** The trip beside the conversation. `productIndex` lets /showcase render it from fixtures. */
export default function TripPanel({
  cart,
  checkoutStaged = false,
  productIndex,
  onRemoveItem,
}: {
  cart: CartPayload | null;
  checkoutStaged?: boolean;
  productIndex?: Record<string, Product>;
  onRemoveItem?: (item: CartItem) => void;
}) {
  const catalog = useCatalogIndex(fetchProducts);
  const index = productIndex ?? catalog;
  const items = cart?.items ?? [];
  // A three-night stay counts as one booking.
  const count = items.length;
  return (
    <BagPanel
      title="Kế hoạch chuyến đi"
      count={count ? `${count} mục` : "Trống"}
      isEmpty={count === 0}
      empty={
        <>
          Chưa có mục nào trong kế hoạch.
          <br />
          Hãy hỏi Trợ lý AI gợi ý điểm đến phù hợp.
        </>
      }
      footer={
        <>
          <TotalRow label="Tổng tạm tính" value={formatPrice(cart?.subtotal ?? 0)} note={count ? "Đã bao gồm thuế phí; chưa trừ tiền cho đến khi bạn xác nhận đặt." : undefined} />
          <CheckoutButton staged={checkoutStaged} disabled={count === 0} prompt="Tiến hành đặt chuyến đi của tôi." />
          {count ? (
            <div className="mt-2.5 flex justify-center">
              <AskLink label="Hỏi về chuyến đi này" prompt="Xem lại chuyến đi của tôi: có điểm nào còn thiếu hoặc nên thay đổi không?" />
            </div>
          ) : null}
        </>
      }
    >
      <ul>
        {items.map((item, position) => (
          <li key={`${item.product_id}-${position}`} className={`py-3.5 first:pt-0 ${position > 0 ? "border-t border-dashed border-(--line)" : ""}`}>
            <CartRow item={item} product={index[item.product_id]} onRemoveItem={onRemoveItem} />
          </li>
        ))}
      </ul>
    </BagPanel>
  );
}
