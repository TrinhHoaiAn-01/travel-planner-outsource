// Copyright 2026 Anthropic PBC
// SPDX-License-Identifier: Apache-2.0

import { formatMoney } from "web-shared";
import type { Product } from "./types";

const DESTINATION_GRADIENTS: Record<string, [string, string]> = {
  lisbon: ["#F4D9CC", "#C9D8D3"],
  kyoto: ["#E8D5D0", "#D8E0D6"],
  "mexico city": ["#F4DFC0", "#E5C8B8"],
  reykjavik: ["#CBD8DF", "#E6EAE6"],
  marrakesh: ["#EFD2B8", "#E0B9A0"],
  queenstown: ["#CFE0D8", "#DCE8E2"],
};

const GRADIENT_FALLBACKS = Object.values(DESTINATION_GRADIENTS);

function hash(text: string): number {
  let value = 0;
  for (let i = 0; i < text.length; i++) {
    value = (value * 31 + text.charCodeAt(i)) >>> 0;
  }
  return value;
}

/** The seed keeps two items in one unmapped place from sharing a gradient. */
export function destinationGradientCss(city?: string | null, seed?: string): string {
  const key = city?.trim().toLowerCase();
  const [from, to] =
    (key && DESTINATION_GRADIENTS[key]) ||
    GRADIENT_FALLBACKS[hash(`${key ?? ""}·${seed ?? "acme-travel"}`) % GRADIENT_FALLBACKS.length];
  return `linear-gradient(135deg, ${from}, ${to})`;
}

export function productCity(product: Product): string | undefined {
  return product.attributes?.city ?? product.attributes?.destination_city ?? undefined;
}

/** Neighborhood before city, so cards in a one-city flow stay distinct. */
export function productPlace(product: Product): string | undefined {
  return product.attributes?.neighborhood ?? productCity(product);
}

const PRICE_UNIT_LABELS: Record<string, string> = {
  per_night: "/ đêm",
  per_person: "/ người",
  per_traveler: "/ người",
};

export function priceUnitLabel(unit?: string | null): string | null {
  if (!unit) return null;
  return PRICE_UNIT_LABELS[unit] ?? `/ ${unit.replace(/^per[_\s]+/, "").replace(/_/g, " ")}`;
}

export function productPriceUnit(product: Product): string | null {
  return priceUnitLabel(product.attributes?.price_unit);
}

export function formatPrice(value: number): string {
  return formatMoney(value, "VND", { whole: Number.isInteger(value) });
}

const MONTHS = ["Thg 1", "Thg 2", "Thg 3", "Thg 4", "Thg 5", "Thg 6", "Thg 7", "Thg 8", "Thg 9", "Thg 10", "Thg 11", "Thg 12"];

/** "2026-10-13" → "13 Thg 10" */
export function shortDate(iso?: string): string | null {
  const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso ?? "");
  if (!match) return null;
  const month = MONTHS[Number(match[2]) - 1];
  return month ? `${Number(match[3])} ${month}` : null;
}

function quantityNoun(productId: string): "đêm" | "khách" | "vé" | null {
  if (productId.startsWith("AL-STAY-")) return "đêm";
  if (productId.startsWith("AL-EXP-")) return "khách";
  if (productId.startsWith("AL-FLT-")) return "vé";
  return null;
}

export function quantityLabel(productId: string, quantity: number): string {
  const noun = quantityNoun(productId);
  if (!noun) return `× ${quantity}`;
  return `× ${quantity} ${noun}`;
}
