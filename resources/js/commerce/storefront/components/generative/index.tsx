// Copyright 2026 Anthropic PBC
// SPDX-License-Identifier: Apache-2.0

/** Maps a `ui` event's component key to a card. */

import { type GenerativeBlockProps, UnknownBlock } from "web-shared";
import type {
  BookingActionPayload,
  CheckoutPayload,
  ComparisonPayload,
  FavoritePayload,
  GuidePayload,
  ItineraryPayload,
  OrderStatusPayload,
  PlanPayload,
  ProductsPayload,
  TripActionPayload,
} from "../../lib/types";
import BoardingPass from "./BoardingPass";
import BookingActionCard from "./BookingActionCard";
import BookingStatusCard from "./BookingStatusCard";
import ComparisonSpread from "./ComparisonSpread";
import DisclosureCard from "./DisclosureCard";
import FavoriteActionCard from "./FavoriteActionCard";
import GuideCard from "./GuideCard";
import ItineraryTimeline from "./ItineraryTimeline";
import PlanChecklist from "./PlanChecklist";
import TravelCarousel from "./TravelCarousel";
import TripActionCard from "./TripActionCard";

interface ExtendedGenerativeBlockProps extends GenerativeBlockProps {
  send?: (text: string) => void;
}

export default function GenerativeBlock({ block, status, send }: ExtendedGenerativeBlockProps) {
  const partial = status !== "final";
  const payload = block.payload;
  switch (block.component) {
    case "products":
      return (
        <TravelCarousel
          payload={payload as ProductsPayload}
          partial={partial}
        />
      );
    case "disclosure":
      return partial ? null : <DisclosureCard payload={payload as Parameters<typeof DisclosureCard>[0]['payload']} />;
    case "comparison":
      return (
        <ComparisonSpread
          payload={payload as ComparisonPayload}
          partial={partial}
        />
      );
    case "plan":
      return (
        <PlanChecklist
          payload={payload as PlanPayload}
          partial={partial}
        />
      );
    case "guide":
      return <GuideCard payload={payload as GuidePayload} />;
    case "order_status":
      if (partial) return null;
      return <BookingStatusCard payload={payload as OrderStatusPayload} />;
    case "checkout":
      if (partial) return null;
      return <BoardingPass payload={payload as CheckoutPayload} />;
    case "itinerary":
      // The extension streams partial frames too, so days assemble one by one.
      return (
        <ItineraryTimeline
          payload={payload as ItineraryPayload}
          partial={partial}
        />
      );
    case "booking_action":
      return (
        <BookingActionCard
          payload={payload as BookingActionPayload}
          partial={partial}
          send={send}
        />
      );
    case "favorite_action":
      return (
        <FavoriteActionCard
          payload={payload as FavoritePayload}
          partial={partial}
        />
      );
    case "trip_action":
      return (
        <TripActionCard
          payload={payload as TripActionPayload}
          partial={partial}
        />
      );
    default:
      return partial ? null : <UnknownBlock component={block.component} />;
  }
}
