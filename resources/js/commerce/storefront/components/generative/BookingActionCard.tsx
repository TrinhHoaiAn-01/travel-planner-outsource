import { useState } from "react";
import type { BookingActionPayload } from "../../lib/types";
import { BODY, CARD, DISPLAY, META } from "./shared";

interface BookingActionCardProps {
  payload: BookingActionPayload;
  partial?: boolean;
  send?: (text: string) => void;
}

export default function BookingActionCard({ payload, partial, send }: BookingActionCardProps) {
  const [copied, setCopied] = useState<string | null>(null);
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [newCheckIn, setNewCheckIn] = useState(payload.check_in_date || "");
  const [newCheckOut, setNewCheckOut] = useState(payload.check_out_date || "");
  const [isProcessingPayment, setIsProcessingPayment] = useState(false);
  const [isPaidLocal, setIsPaidLocal] = useState(payload.status === "confirmed" || payload.status === "completed");
  const [paymentMsg, setPaymentMsg] = useState<string | null>(null);

  const copyToClipboard = (text: string, key: string) => {
    navigator.clipboard.writeText(text);
    setCopied(key);
    setTimeout(() => setCopied(null), 2000);
  };

  const handleUpdateDates = () => {
    if (!newCheckIn) return;
    const msg = `Tôi muốn đổi ngày nhận phòng sang ${newCheckIn}${newCheckOut ? ` và trả phòng ${newCheckOut}` : ""} cho đơn ${payload.booking_code}`;
    if (send) {
      send(msg);
      setShowDatePicker(false);
    }
  };

  const handleConfirmTransfer = () => {
    if (send) {
      send(`Tôi đã hoàn tất chuyển khoản cho đơn ${payload.booking_code}, hãy kiểm tra giúp tôi.`);
    }
  };

  const isConfirmed = payload.status === "confirmed" || payload.status === "completed" || payload.is_paid === true;

  return (
    <section
      className="al-reveal rounded-xl border border-gray-200 bg-white p-5 shadow-sm text-gray-800 transition-all"
      style={{ ...CARD, padding: "20px 24px" }}
    >
      {/* Header */}
      <div className="flex items-start justify-between gap-3 border-b border-gray-100 pb-3">
        <div>
          <div style={META} className="text-xs uppercase tracking-wider text-gray-400">
            Travel Planner • Đặt phòng
          </div>
          <h3 className="mt-1 font-serif text-lg font-bold text-gray-900" style={{ fontFamily: DISPLAY }}>
            {payload.hotel_name || "Khách sạn / Resort"}
          </h3>
          {payload.room_name && (
            <p className="text-sm font-medium text-blue-600 mt-0.5">
              {payload.room_name}
            </p>
          )}
        </div>
        <div className="text-right">
          <span
            className={`inline-block px-2.5 py-1 text-xs font-semibold rounded-full ${
              isConfirmed
                ? "bg-emerald-50 text-emerald-700 border border-emerald-200"
                : "bg-amber-50 text-amber-700 border border-amber-200"
            }`}
          >
            {isConfirmed ? "ĐÃ XÁC NHẬN" : "CHỜ THANH TOÁN"}
          </span>
          <div className="text-xs text-gray-500 mt-1 font-mono">
            {payload.booking_code?.startsWith("#") ? payload.booking_code : `#${payload.booking_code}`}
          </div>
        </div>
      </div>

      {/* Thông tin lịch lưu trú */}
      <div className="grid grid-cols-2 gap-3 py-3 border-b border-gray-100 text-sm">
        <div>
          <span className="text-xs text-gray-400 block">Nhận phòng:</span>
          <span className="font-semibold text-gray-800">{payload.check_in_date}</span>
        </div>
        <div>
          <span className="text-xs text-gray-400 block">Trả phòng:</span>
          <span className="font-semibold text-gray-800">{payload.check_out_date}</span>
        </div>
        <div>
          <span className="text-xs text-gray-400 block">Số đêm & phòng:</span>
          <span className="text-gray-700">{payload.nights || 1} đêm • {payload.room_count || 1} phòng</span>
        </div>
        <div>
          <span className="text-xs text-gray-400 block">Tổng thanh toán:</span>
          <span className="font-bold text-blue-700 text-base">
            {Number(payload.total_price || 0).toLocaleString("vi-VN")} VNĐ
          </span>
        </div>
      </div>

      {/* Thông báo cập nhật / thanh toán */}
      {paymentMsg && (
        <div className="mt-3 p-2.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-lg flex items-center gap-2">
          <span>✓</span>
          <span>{paymentMsg}</span>
        </div>
      )}

      {/* Thông tin chuyển khoản Vietcombank nếu chưa thanh toán */}
      {!isConfirmed && payload.payment_instructions && (
        <div className="mt-3 rounded-lg bg-gray-50 p-3.5 border border-gray-200 text-xs">
          <div className="font-semibold text-gray-800 mb-2 flex items-center justify-between">
            <span>Thông tin chuyển khoản ngân hàng:</span>
            <span className="text-blue-600 font-mono">Vietcombank</span>
          </div>
          <div className="space-y-1.5 text-gray-600">
            <div className="flex justify-between items-center">
              <span>Chủ tài khoản:</span>
              <span className="font-semibold text-gray-800">{payload.payment_instructions.account_holder}</span>
            </div>
            <div className="flex justify-between items-center">
              <span>Số tài khoản:</span>
              <div className="flex items-center gap-1.5 font-mono font-bold text-gray-900">
                <span>{payload.payment_instructions.account_number}</span>
                <button
                  type="button"
                  onClick={() => copyToClipboard(payload.payment_instructions!.account_number, "stk")}
                  className="px-1.5 py-0.5 rounded bg-gray-200 hover:bg-gray-300 text-[10px] text-gray-700"
                >
                  {copied === "stk" ? "Đã chép" : "Chép"}
                </button>
              </div>
            </div>
            <div className="flex justify-between items-center">
              <span>Số tiền:</span>
              <span className="font-bold text-blue-600 font-mono">
                {Number(payload.payment_instructions.amount).toLocaleString("vi-VN")} VNĐ
              </span>
            </div>
            <div className="flex justify-between items-center">
              <span>Nội dung chuyển khoản:</span>
              <div className="flex items-center gap-1.5 font-mono font-bold text-blue-800">
                <span>{payload.payment_instructions.transfer_syntax}</span>
                <button
                  type="button"
                  onClick={() => copyToClipboard(payload.payment_instructions!.transfer_syntax, "syntax")}
                  className="px-1.5 py-0.5 rounded bg-blue-100 hover:bg-blue-200 text-[10px] text-blue-700"
                >
                  {copied === "syntax" ? "Đã chép" : "Chép"}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Form đổi ngày inline */}
      {showDatePicker && (
        <div className="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs space-y-2">
          <div className="font-semibold text-blue-900">Chọn khoảng ngày mới:</div>
          <div className="grid grid-cols-2 gap-2">
            <div>
              <label className="text-gray-600 block text-[11px] mb-0.5">Ngày nhận phòng:</label>
              <input
                type="date"
                value={newCheckIn}
                onChange={(e) => setNewCheckIn(e.target.value)}
                className="w-full rounded border border-gray-300 bg-white px-2 py-1 text-xs"
              />
            </div>
            <div>
              <label className="text-gray-600 block text-[11px] mb-0.5">Ngày trả phòng:</label>
              <input
                type="date"
                value={newCheckOut}
                onChange={(e) => setNewCheckOut(e.target.value)}
                className="w-full rounded border border-gray-300 bg-white px-2 py-1 text-xs"
              />
            </div>
          </div>
          <div className="flex justify-end gap-2 pt-1">
            <button
              type="button"
              onClick={() => setShowDatePicker(false)}
              className="px-2.5 py-1 text-gray-600 hover:text-gray-800 text-xs"
            >
              Hủy
            </button>
            <button
              type="button"
              onClick={handleUpdateDates}
              className="px-3 py-1 bg-blue-600 text-white rounded font-medium hover:bg-blue-700 text-xs shadow-sm"
            >
              Xác nhận đổi ngày
            </button>
          </div>
        </div>
      )}

      {/* Interactive Action Buttons */}
      <div className="mt-4 flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100">
        {!isConfirmed && (
          <>
            <button
              type="button"
              onClick={() => setShowDatePicker(!showDatePicker)}
              className="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors border border-gray-200"
            >
              📅 Đổi ngày nhận phòng
            </button>
            <button
              type="button"
              disabled={isProcessingPayment}
              onClick={handleConfirmTransfer}
              className="px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-sm disabled:opacity-50"
            >
              {isProcessingPayment ? "Đang kiểm tra..." : "✓ Xác nhận đã chuyển khoản"}
            </button>
          </>
        )}

        {payload.view_url && (
          <a
            href={payload.view_url}
            target="_blank"
            rel="noopener noreferrer"
            className="px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border border-blue-200 ml-auto"
          >
            Xem đơn {payload.booking_code?.startsWith("#") ? payload.booking_code : `#${payload.booking_code}`} ↗
          </a>
        )}
      </div>
    </section>
  );
}

