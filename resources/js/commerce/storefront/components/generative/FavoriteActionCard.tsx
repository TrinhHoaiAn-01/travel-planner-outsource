import type { FavoritePayload } from "../../lib/types";
import { CARD, DISPLAY, META } from "./shared";

interface FavoriteActionCardProps {
  payload: FavoritePayload;
  partial?: boolean;
}

export default function FavoriteActionCard({ payload }: FavoriteActionCardProps) {
  return (
    <section
      className="al-reveal rounded-xl border border-rose-100 bg-white p-4 shadow-sm text-gray-800 transition-all"
      style={{ ...CARD, padding: "16px 20px" }}
    >
      <div className="flex items-center justify-between gap-3">
        <div className="flex items-center gap-3">
          <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-500 text-lg">
            ❤️
          </div>
          <div>
            <div style={META} className="text-[11px] uppercase tracking-wider text-rose-400 font-semibold">
              Mục yêu thích
            </div>
            <h4 className="font-serif text-base font-bold text-gray-900 mt-0.5" style={{ fontFamily: DISPLAY }}>
              {payload.destination_name}
            </h4>
            <p className="text-xs text-gray-500 mt-0.5">
              {payload.message || "Đã lưu vào danh sách điểm đến yêu thích của bạn."}
            </p>
          </div>
        </div>
        {payload.url && (
          <a
            href={payload.url}
            target="_blank"
            rel="noopener noreferrer"
            className="shrink-0 px-3 py-1.5 text-xs font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors border border-rose-200"
          >
            Xem danh sách ↗
          </a>
        )}
      </div>
    </section>
  );
}

