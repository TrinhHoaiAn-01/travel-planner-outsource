import type { TripActionPayload } from "../../lib/types";
import { CARD, DISPLAY, META } from "./shared";

interface TripActionCardProps {
  payload: TripActionPayload;
  partial?: boolean;
}

export default function TripActionCard({ payload }: TripActionCardProps) {
  return (
    <section
      className="al-reveal rounded-xl border border-indigo-100 bg-white p-4 shadow-sm text-gray-800 transition-all"
      style={{ ...CARD, padding: "16px 20px" }}
    >
      <div className="flex items-center justify-between gap-3">
        <div className="flex items-center gap-3">
          <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 text-lg">
            ✈️
          </div>
          <div>
            <div style={META} className="text-[11px] uppercase tracking-wider text-indigo-500 font-semibold">
              Chuyến đi • Ngày {payload.day_number || 1}
            </div>
            <h4 className="font-serif text-base font-bold text-gray-900 mt-0.5" style={{ fontFamily: DISPLAY }}>
              {payload.destination_name}
            </h4>
            <p className="text-xs text-gray-500 mt-0.5">
              {payload.message || `Đã thêm vào lịch trình "${payload.trip_name}".`}
            </p>
          </div>
        </div>
        {payload.url && (
          <a
            href={payload.url}
            target="_blank"
            rel="noopener noreferrer"
            className="shrink-0 px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors border border-indigo-200"
          >
            Xem chuyến đi ↗
          </a>
        )}
      </div>
    </section>
  );
}

