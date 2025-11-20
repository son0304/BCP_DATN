import { useState } from "react";
import PaymentMomo from "../Payment/PaymentMomo";
import PaymentVNPay from "../Payment/PaymentVNPay";
import momo from "../../../public/momo.png";
import vnpay from "../../../public/vnpay.png";

import { useParams } from "react-router-dom";
import { useFetchDataById } from "../../Hooks/useApi";
import type { Ticket } from "../../Types/tiket";

const Ticket_Detail = () => {
  const [paymentMethod, setPaymentMethod] = useState<string>("");
  const { id } = useParams();
  const { data, isLoading, isError } = useFetchDataById<Ticket>("ticket", id || "");

  if (isLoading)
    return (
      <div className="text-center p-6 text-xl text-gray-600">
        Đang tải dữ liệu...
      </div>
    );
  if (isError)
    return (
      <div className="text-center p-6 text-xl text-red-600">
        Lỗi khi tải dữ liệu đơn đặt.
      </div>
    );

  const ticket = data?.data;

  const formatCurrency = (value: string | number) =>
    Number(value).toLocaleString("vi-VN", { minimumFractionDigits: 0, maximumFractionDigits: 0 }) +
    "₫";

  // Hàm hiển thị badge trạng thái
  const renderStatusBadge = (status?: string) => {
    switch (status) {
      case "pending":
        return { text: "Chờ xác nhận", className: "bg-yellow-100 text-yellow-800" };
      case "confirmed":
        return { text: "Đã xác nhận", className: "bg-blue-100 text-blue-800" };
      case "cancelled":
        return { text: "Đã hủy", className: "bg-red-100 text-red-800" };
      case "completed":
      default:
        return { text: "Hoàn thành", className: "bg-green-100 text-green-800" };
    }
  };

  const statusBadge = renderStatusBadge(ticket?.status);

  return (
    <div className="container max-w-[700px] mx-auto my-6 border border-gray-200 rounded-2xl shadow-lg bg-white overflow-hidden">
      {/* Header */}
      <div className="bg-[#10B981] p-4 md:p-6 text-white rounded-t-2xl">
        <div className="flex flex-col md:flex-row md:justify-between md:items-center gap-2">
          <div>
            <h1 className="text-lg md:text-2xl font-bold">Chi tiết ticket #{ticket?.id}</h1>
            <p className="text-sm opacity-90 mt-1">
              Ngày tạo:{" "}
              {ticket?.created_at
                ? new Date(ticket.created_at).toLocaleString("vi-VN")
                : "-"}
            </p>
          </div>

          <div>
            <span
              className={`inline-block px-3 py-1 rounded-full text-sm font-semibold mt-2 md:mt-0 ${statusBadge.className}`}
            >
              {statusBadge.text}
            </span>
          </div>
        </div>
      </div>

      <div className="p-4 md:p-6 space-y-4">
        {/* Thông tin khách hàng */}
        <div className="border-b border-gray-200 pb-4">
          <h3 className="text-lg font-bold text-gray-700 mb-2 border-l-4 border-[#10B981] pl-2">
            Thông tin người đặt
          </h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-2 text-gray-600 text-sm">
            <p>
              <span className="font-semibold">Họ và tên:</span> {ticket?.user?.name || "-"}
            </p>
            <p>
              <span className="font-semibold">Email:</span> {ticket?.user?.email || "-"}
            </p>
            <p>
              <span className="font-semibold">Số điện thoại:</span> {ticket?.user?.phone || "-"}
            </p>
          </div>
        </div>

        {/* Danh sách sân */}
        <div className="border-b border-gray-200 pb-4">
          <h3 className="text-lg font-bold text-gray-700 mb-2 border-l-4 border-[#F59E0B] pl-2">
            Thông tin hóa đơn
          </h3>
          <div className="space-y-3">
            {ticket?.items?.map((item) => (
              <div
                key={item.id}
                className="p-3 border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition bg-gray-50"
              >
                <p className="font-semibold text-indigo-700">{item.booking?.court?.name}</p>
                <p className="text-gray-600 text-sm">Ngày: {item.booking?.date}</p>
                <p className="text-gray-600 text-sm">
                  Khung giờ: {item.booking?.time_slot?.start_time} - {item.booking?.time_slot?.end_time}
                </p>
                <p className="text-gray-700 font-medium">Giá: {formatCurrency(item.unit_price)}</p>
              </div>
            ))}
          </div>
        </div>

        {/* Tổng tiền */}
        <div className="border-b border-gray-200 pb-4 flex justify-between items-center">
          <h3 className="text-lg font-bold text-gray-700 border-l-4 border-[#F59E0B] pl-2">Tổng</h3>
          <div className="grid grid-rows-3 text-right">
            <span className="text-gray-600 font-semibold">{formatCurrency(ticket?.subtotal ?? 0)}</span>
            <span className="text-red-500 font-semibold border-b border-gray-200">
              -{formatCurrency(ticket?.discount_amount ?? 0)}
            </span>
            <span className="text-gray-800 font-bold">{formatCurrency(ticket?.total_amount ?? 0)}</span>
          </div>
        </div>

        {/* Thanh toán */}
        {ticket?.status !== "cancelled" && (
          <div>
            <h3 className="text-lg font-bold text-gray-700 mb-3 border-l-4 border-[#10B981] pl-2">
              Phương thức thanh toán
            </h3>
            <div className="flex gap-4 justify-center mb-4">
              <div
                onClick={() => setPaymentMethod("momo")}
                className={`flex-1 border rounded-xl p-4 shadow-md cursor-pointer flex justify-center items-center transition ${
                  paymentMethod === "momo"
                    ? "bg-pink-100 border-pink-500 shadow-lg"
                    : "bg-white border-gray-200 hover:shadow-lg"
                }`}
              >
                <img src={momo} alt="MoMo" className="h-12 w-auto" />
              </div>
              <div
                onClick={() => setPaymentMethod("vnpay")}
                className={`flex-1 border rounded-xl p-4 shadow-md cursor-pointer flex justify-center items-center transition ${
                  paymentMethod === "vnpay"
                    ? "bg-blue-100 border-blue-500 shadow-lg"
                    : "bg-white border-gray-200 hover:shadow-lg"
                }`}
              >
                <img src={vnpay} alt="VNPay" className="h-12 w-auto" />
              </div>
            </div>

            {/* Payment Component */}
            {paymentMethod === "momo" && <PaymentMomo />}
            {paymentMethod === "vnpay" && <PaymentVNPay />}
          </div>
        )}

        {/* Ghi chú */}
        {ticket?.notes && (
          <div className="border-t border-gray-200 pt-3 text-gray-600 italic">
            <strong>Ghi chú:</strong> {ticket.notes}
          </div>
        )}
      </div>
    </div>
  );
};

export default Ticket_Detail;
