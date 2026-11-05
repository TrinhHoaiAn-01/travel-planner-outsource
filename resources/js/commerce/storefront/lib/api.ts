// Copyright 2026 Anthropic PBC
// SPDX-License-Identifier: Apache-2.0

import { AgentApi } from "web-shared";
import type { Product } from "./types";

const API_URL = window.location.origin;

export const api = new AgentApi(API_URL, "/commerce-api/shopping");

export const UNREACHABLE = "Máy chủ Travel Planner hiện đang bận, xin vui lòng thử lại sau!";

export async function fetchProducts(): Promise<Product[] | null> {
  const data = await api.get<{ products: Product[] }>("/products", { limit: "100" });
  return data?.products ?? null;
}
