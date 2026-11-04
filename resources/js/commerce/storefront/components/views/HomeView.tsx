// Copyright 2026 Anthropic PBC
// SPDX-License-Identifier: Apache-2.0

"use client";

import type { Order } from "web-shared";

export default function HomeView({
  travelerName,
  trips,
  tripsFailed,
  onSeeTrips,
}: {
  travelerName?: string;
  trips?: Order[] | null;
  tripsFailed?: boolean;
  onSeeTrips?: () => void;
}) {
  return (
    <div className="flex h-[calc(100vh-280px)] min-h-[300px] flex-col items-center justify-center select-none pointer-events-none">
      <p className="text-xl sm:text-2xl font-normal text-(--ink-soft)/40 tracking-wide text-center">
        Xin chào bạn!
      </p>
    </div>
  );
}
