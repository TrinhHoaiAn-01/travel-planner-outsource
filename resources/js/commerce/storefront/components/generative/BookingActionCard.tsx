import { useEffect, useState } from "react";
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
  const [isPaidLocal, setIsPaidLocal] = useState(
    payload.status === "confirmed" || payload.status === "completed" || payload.is_paid === true
  );
  const [isAwaitingAdmin, setIsAwaitingAdmin] = useState(Boolean(payload.awaiting_admin));
  const [paymentMsg, setPaymentMsg] = useState<string | null>(null);
  const [itemsList, setItemsList] = useState<any[]>(payload.items || []);
  const [approvedCount, setApprovedCount] = useState<number>(payload.approved_items ?? 0);
  const [totalCount, setTotalCount] = useState<number>(payload.total_items ?? (payload.items ? payload.items.length : 1));

  useEffect(() => {
    if (payload.status === "confirmed" || payload.status === "completed" || payload.is_paid === true) {
      setIsPaidLocal(true);
      setIsAwaitingAdmin(false);
    } else if (payload.awaiting_admin) {
      setIsAwaitingAdmin(true);
    }
    if (payload.items) setItemsList(payload.items);
    if (payload.approved_items !== undefined) setApprovedCount(payload.approved_items);
    if (payload.total_items !== undefined) setTotalCount(payload.total_items);
  }, [payload]);

  // Realtime Polling kiểm tra xem Admin đã duyệt đơn chưa
  useEffect(() => {
    if (isPaidLocal) return;

    const cleanCode = (payload.booking_code || "").replace(/^#/, "");
    const pollUrl = payload.booking_id
      ? `/bookings/${payload.booking_id}/payment-status`
      : cleanCode
      ? `/bookings/status-by-code/${cleanCode}`
      : null;

    if (!pollUrl) return;

    let isSubscribed = true;
    const interval = setInterval(() => {
      fetch(pollUrl, {
        headers: { Accept: "application/json" },
        credentials: "same-origin",
      })
        .then((res) => (res.ok ? res.json() : null))
        .then((data) => {
          if (!isSubscribed) return;
          if (data) {
            if (data.total_items !== undefined) setTotalCount(data.total_items);
            if (data.approved_items !== undefined) setApprovedCount(data.approved_items);
            if (data.items && Array.isArray(data.items)) setItemsList(data.items);

            if (data.is_paid === true || data.status === "confirmed" || data.status === "completed") {
              setIsPaidLocal(true);
              setIsAwaitingAdmin(false);
              const successText = payload.is_trip_checkout
                ? `Quản trị viên đã duyệt đầy đủ tất cả ${data.total_items || totalCount} mục thanh toán! Kế hoạch chuyến đi của bạn đã được thanh toán thành công.`
                : "Quản trị viên đã duyệt và xác nhận thanh toán thành công! Phòng của bạn đã sẵn sàng.";
              setPaymentMsg(successText);
              clearInterval(interval);
            }
          }
        })
        .catch(() => {});
    }, 2000);

    return () => {
      isSubscribed = false;
      clearInterval(interval);
    };
  }, [isPaidLocal, payload.booking_id, payload.booking_code, payload.is_trip_checkout, totalCount, send]);

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
    setIsAwaitingAdmin(true);
  };

  const isConfirmed = isPaidLocal;

  return (
    <section
      className="al-reveal rounded-xl border border-gray-200 bg-white p-5 shadow-sm text-gray-800 transition-all"
      style={{ ...CARD, padding: "20px 24px" }}
    >
      {/* Header */}
      <div className="flex items-start justify-between gap-3 border-b border-gray-100 pb-3">
        <div>
          <div style={META} className="text-xs uppercase tracking-wider text-gray-400">
            {payload.is_trip_checkout ? "Travel Planner • Thanh toán chuyến đi" : "Travel Planner • Đặt phòng"}
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
                : isAwaitingAdmin
                ? "bg-amber-100 text-amber-800 border border-amber-300"
                : "bg-amber-50 text-amber-700 border border-amber-200"
            }`}
          >
            {isConfirmed
              ? "ĐÃ THANH TOÁN"
              : isAwaitingAdmin
              ? (totalCount > 1 ? `CHỜ ADMIN DUYỆT (${approvedCount}/${totalCount})` : "CHỜ ADMIN XÁC NHẬN")
              : "CHỜ THANH TOÁN"}
          </span>
          <div className="text-xs text-gray-500 mt-1 font-mono">
            {payload.booking_code?.startsWith("#") ? payload.booking_code : `#${payload.booking_code}`}
          </div>
        </div>
      </div>

      {/* Thông tin lịch lưu trú */}
      <div className="grid grid-cols-2 gap-3 py-3 border-b border-gray-100 text-sm">
        <div>
          <span className="text-xs text-gray-400 block">
            {payload.is_trip_checkout ? "Khởi hành:" : "Nhận phòng:"}
          </span>
          <span className="font-semibold text-gray-800">{payload.check_in_date}</span>
        </div>
        <div>
          <span className="text-xs text-gray-400 block">
            {payload.is_trip_checkout ? "Kết thúc:" : "Trả phòng:"}
          </span>
          <span className="font-semibold text-gray-800">{payload.check_out_date}</span>
        </div>
        <div>
          <span className="text-xs text-gray-400 block">
            {payload.is_trip_checkout ? "Số lượng dịch vụ:" : "Số đêm & phòng:"}
          </span>
          <span className="text-gray-700">
            {payload.is_trip_checkout
              ? `${payload.items_count || totalCount} mục trong kế hoạch`
              : `${payload.nights || 1} đêm • ${payload.room_count || 1} phòng`}
          </span>
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

      {/* Thông tin chuyển khoản VietQR nếu chưa thanh toán */}
      {!isConfirmed && payload.payment_instructions && (
        <div className="mt-3 rounded-lg bg-gray-50 p-3.5 border border-gray-200 text-xs">
          {isAwaitingAdmin && (
            <div className="mb-3 p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-xs font-medium">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <span className="text-amber-600 animate-pulse">⏳</span>
                  <span>
                    {totalCount > 1
                      ? `Đã ghi nhận chuyển khoản. Hệ thống đang chờ Quản trị viên (Admin) duyệt từng mục (${approvedCount}/${totalCount} mục đã duyệt)...`
                      : "Đã ghi nhận chuyển khoản. Hệ thống đang chờ Quản trị viên (Admin) duyệt giao dịch..."}
                  </span>
                </div>
                {totalCount > 1 && (
                  <span className="font-bold text-amber-800 font-mono ml-2 shrink-0">
                    {approvedCount}/{totalCount}
                  </span>
                )}
              </div>
              {totalCount > 1 && (
                <div className="w-full bg-amber-200/60 rounded-full h-1.5 mt-2 overflow-hidden">
                  <div
                    className="bg-amber-600 h-1.5 rounded-full transition-all duration-500"
                    style={{ width: `${Math.round((approvedCount / Math.max(1, totalCount)) * 100)}%` }}
                  />
                </div>
              )}
            </div>
          )}

          <div className="font-semibold text-gray-800 mb-2 flex items-center justify-between">
            <span>Thông tin chuyển khoản SePay / VietQR:</span>
            <span className="text-blue-600 font-mono font-bold">
              {payload.payment_instructions.bank_name || "Vietcombank"}
            </span>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
            {/* Cột trái: Hình ảnh mã VietQR */}
            {payload.payment_instructions.qr_code_url && (
              <div className="flex flex-col items-center justify-center p-2.5 bg-white rounded-lg border border-gray-200 shadow-2xs">
                <img
                  src={payload.payment_instructions.qr_code_url}
                  alt="Mã QR Chuyển khoản VietQR"
                  className="w-36 h-36 object-contain rounded"
                  loading="lazy"
                />
                <span className="text-[10px] text-gray-500 font-medium mt-1 text-center">
                  Quét mã VietQR bằng App Ngân hàng
                </span>
              </div>
            )}

            {/* Cột phải: Chi tiết tài khoản & Nội dung */}
            <div className="space-y-1.5 text-gray-600">
              <div className="flex justify-between items-center">
                <span>Ngân hàng:</span>
                <span className="font-semibold text-gray-800">{payload.payment_instructions.bank_name || "Vietcombank"}</span>
              </div>
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
                    className="px-1.5 py-0.5 rounded bg-gray-200 hover:bg-gray-300 text-[10px] text-gray-700 cursor-pointer"
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
                <span>Nội dung CK:</span>
                <div className="flex items-center gap-1.5 font-mono font-bold text-blue-800">
                  <span>{payload.payment_instructions.transfer_syntax}</span>
                  <button
                    type="button"
                    onClick={() => copyToClipboard(payload.payment_instructions!.transfer_syntax, "syntax")}
                    className="px-1.5 py-0.5 rounded bg-blue-100 hover:bg-blue-200 text-[10px] text-blue-700 cursor-pointer"
                  >
                    {copied === "syntax" ? "Đã chép" : "Chép"}
                  </button>
                </div>
              </div>
            </div>
          </div>

          {/* Bảng chi tiết từng mục thanh toán (Trip items) */}
          {itemsList.length > 0 && (
            <div className="mt-3.5 pt-3 border-t border-gray-200">
              <div className="font-semibold text-gray-800 mb-2 flex items-center justify-between text-xs">
                <span>Chi tiết từng mục trong kế hoạch ({itemsList.length} mục):</span>
                <span className="text-blue-600 font-mono font-bold">
                  {approvedCount}/{totalCount} mục đã duyệt
                </span>
              </div>
              <div className="max-h-52 overflow-y-auto space-y-1.5 pr-1 divide-y divide-gray-100">
                {itemsList.map((it, idx) => (
                  <div key={idx} className="pt-1.5 first:pt-0 flex items-center justify-between gap-2">
                    <div className="min-w-0 flex-1">
                      <div className="font-medium text-gray-800 truncate text-xs" title={it.title}>
                        <span className="font-mono text-gray-400 mr-1.5 text-[11px]">#{it.booking_code}</span>
                        {it.title}
                      </div>
                    </div>
                    <div className="flex items-center gap-2 shrink-0">
                      <span className="font-semibold text-gray-900 font-mono text-[11.5px]">
                        {Number(it.amount || 0).toLocaleString("vi-VN")}đ
                      </span>
                      <span
                        className={`inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium ${
                          it.status === "completed"
                            ? "bg-emerald-100 text-emerald-800 font-semibold"
                            : "bg-amber-100 text-amber-800"
                        }`}
                      >
                        {it.status === "completed" ? "✓ Đã duyệt" : "Chờ duyệt"}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
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
              className="px-2.5 py-1 text-gray-600 hover:text-gray-800 text-xs cursor-pointer"
            >
              Hủy
            </button>
            <button
              type="button"
              onClick={handleUpdateDates}
              className="px-3 py-1 bg-blue-600 text-white rounded font-medium hover:bg-blue-700 text-xs shadow-sm cursor-pointer"
            >
              Xác nhận đổi ngày
            </button>
          </div>
        </div>
      )}

      {/* Interactive Action Buttons */}
      <div className="mt-4 flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100">
        {!isConfirmed && !isAwaitingAdmin && (
          <>
            {!payload.is_trip_checkout && (
              <button
                type="button"
                onClick={() => setShowDatePicker(!showDatePicker)}
                className="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors border border-gray-200 cursor-pointer"
              >
                📅 Đổi ngày nhận phòng
              </button>
            )}
            <button
              type="button"
              disabled={isProcessingPayment}
              onClick={handleConfirmTransfer}
              className="px-3 py-1.5 text-xs font-semibold text-white rounded-lg transition-colors shadow-sm cursor-pointer bg-emerald-600 hover:bg-emerald-700"
            >
              ✓ Xác nhận đã chuyển khoản
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
            {payload.is_trip_checkout ? "Xem kế hoạch chuyến đi ↗" : `Xem đơn ${payload.booking_code?.startsWith("#") ? payload.booking_code : `#${payload.booking_code}`} ↗`}
          </a>
        )}
      </div>
    </section>
  );
}

