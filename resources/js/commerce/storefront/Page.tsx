// Copyright 2026 Anthropic PBC
// SPDX-License-Identifier: Apache-2.0

"use client";

import { useCallback, useEffect, useState } from "react";
import { type AgentEvent, OrdersView, StoreShell, type StoreView, upcoming, useAgentTurn, useResource, useSession } from "web-shared";
import Chat from "./components/Chat";
import TripPanel from "./components/TripPanel";
import HomeView from "./components/views/HomeView";
import { api, UNREACHABLE } from "./lib/api";
import { formatPrice } from "./lib/format";
import { NOUNS, TripThumb } from "./lib/orders";
import type { CartItem, CartPayload } from "./lib/types";

type View = "assistant" | "trips";

const ASSISTANT = "Trợ lý AI";

function Wordmark() {
  return (
    <a
      href="/"
      target="_top"
      className="al-display pr-1 text-[16px] sm:text-[22px] not-italic leading-none text-(--ink)"
      style={{ fontWeight: 650, fontStyle: "normal" }}
    >
      Travel Planner
    </a>
  );
}

export default function StorefrontPage() {
  const session = useSession(api);
  const [view, setView] = useState<View>("assistant");
  const [cart, setCart] = useState<CartPayload | null>(null);
  // A staged checkout owns the panel's primary action until the trip changes again.
  const [checkoutStaged, setCheckoutStaged] = useState(false);
  const [panelOpen, setPanelOpen] = useState(false);

  const onEvent = useCallback((event: AgentEvent) => {
    if (event.type === "cart_update") {
      setCart(event.data.cart as CartPayload);
      setCheckoutStaged(false);
    } else if (event.type === "ui" && event.data.component === "checkout") {
      setCheckoutStaged(true);
    }
  }, []);

  const chat = useAgentTurn(api, { ...session, unreachable: UNREACHABLE, onEvent });

  const handleRemoveItem = useCallback(
    async (item: CartItem) => {
      // 1. Cập nhật lạc quan (optimistic) trên state giỏ hàng ngay lập tức
      setCart((prev) => {
        if (!prev) return null;
        const nextItems = [...prev.items];
        const idx = nextItems.findIndex((i) => i.product_id === item.product_id);
        if (idx !== -1) {
          nextItems.splice(idx, 1);
        }
        const subtotal = nextItems.reduce((acc, cur) => acc + cur.line_total, 0);
        return {
          ...prev,
          items: nextItems,
          subtotal,
          item_count: nextItems.reduce((acc, cur) => acc + cur.quantity, 0),
        };
      });

      // 2. Gọi API DELETE để đồng bộ xóa trong Database (ItineraryItem / Booking)
      try {
        const next = await api.delete<CartPayload>("/cart", {
          product_id: item.product_id,
          title: item.title,
        });
        if (next) setCart(next);
      } catch {
        // bỏ qua nếu lỗi mạng, đã có bước chat hỏi bên dưới
      }

      // 3. Gửi tin nhắn cho AI Agent để ghi nhận vào cuộc trò chuyện
      chat.send(`Xóa ${item.title} khỏi chuyến đi của tôi.`);
    },
    [chat]
  );

  // A reply may have changed or refunded a booking, so trips re-read after each one.
  const { data: trips, failed: tripsFailed } = useResource(session.sessionId ? () => api.fetchOrders() : null, [session.sessionId, chat.completed]);

  useEffect(() => {
    if (session.sessionId) void api.fetchCart<CartPayload>().then((next) => next && setCart(next));
  }, [session.sessionId, chat.completed]);

  const views: StoreView<View>[] = [
    { id: "assistant", label: "Trợ lý", icon: "spark" },
    { id: "trips", label: "Chuyến đi", icon: "plane" },
  ];
  const shopper = session.shopper ?? { name: "Khách" };
  const count = cart?.items.length ?? 0;

  return (
    <StoreShell
      brand={<Wordmark />}
      views={views}
      view={view}
      onViewChange={setView}
      chat={chat}
      api={api}
      assistantName={ASSISTANT}
      banner={document.documentElement.dataset.signedIn !== 'true' ? <div className="border-b border-(--line) bg-(--well) px-4 py-2 text-center text-xs"><a className="font-semibold underline" href="/login" target="_top">Đăng nhập</a> để xem booking, chuyến đi và dữ liệu của bạn.</div> : undefined}
      shopper={shopper}
      bag={{ label: "Kế hoạch", count, noun: "chuyến đi", figure: count ? formatPrice(cart?.subtotal ?? 0) : null }}
      panel={<TripPanel cart={cart} checkoutStaged={checkoutStaged} onRemoveItem={handleRemoveItem} />}
      panelOpen={panelOpen}
      onPanelOpenChange={setPanelOpen}
      placeholder={view === "trips" ? "Hỏi về các chuyến đi hoặc thay đổi kế hoạch…" : "Hỏi về điểm đến, lịch trình hoặc nơi lưu trú…"}
    >
      {/* The conversation stays mounted under the other view so its cards keep their state. */}
      <div className={view === "assistant" ? "h-full" : "hidden"}>
        <Chat chat={chat} home={<HomeView travelerName={shopper.name} trips={trips} tripsFailed={tripsFailed} onSeeTrips={() => setView("trips")} />} />
      </div>
      {view === "trips" ? (
        <OrdersView
          orders={trips}
          failed={tripsFailed}
          nouns={NOUNS}
          subtitle={trips ? `${upcoming(trips).length} chuyến đi sắp tới. Hãy hỏi về bất kỳ chuyến nào hoặc lên kế hoạch mới.` : undefined}
          thumb={(order) => <TripThumb order={order} />}
        />
      ) : null}
    </StoreShell>
  );
}
