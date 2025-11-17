import { useState } from "react";
import type { User } from "../../Types/user";
import { useFetchData } from "../../Hooks/useApi";
import { Modal, Descriptions, List, Steps, Tag } from "antd";
import type { Ticket } from "../../Types/tiket";
import { Link } from "react-router-dom";

const BookingHistory = ({ user }: { user: User }) => {
  const [isStatus, setIsStatus] = useState("all");
  const [selectedTicket, setSelectedTicket] = useState<Ticket | null>(null);

  const statusOptions = [
    { value: "all", label: "Tất cả", tag: "text-white" },
    { value: "pending", label: "Chờ xác nhận", tag: "bg-red-100 text-red-800" },
    { value: "confirmed", label: "Đã xác nhận", tag: "bg-sky-100  text-sky-800" },
    { value: "completed", label: "Hoàn thành", tag: "bg-green-100 text-green-800" },
    { value: "cancelled", label: "Đã hủy", tag: "bg-purple-100 text-purple-800" },
  ];

  const paymentOptions = [
    { value: "unpaid", label: "Chưa thanh toán", color: "bg-gray-100 text-gray-800" },
    { value: "paid", label: "Đã thanh toán", color: "bg-green-100 text-green-800" },
    { value: "refunded", label: "Đã hoàn tiền", color: "bg-orange-100 text-orange-800" },
  ];

  const { data } = useFetchData("tickets");
  const listBooking: Ticket[] = (data?.data as Ticket[]) ?? [];
  console.log(selectedTicket);


  const bookingByStatus = listBooking
    .filter((b) => isStatus === "all" || b.status === isStatus)
    .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());

  const formatCurrency = (value: string | number) =>
    Number(value).toLocaleString("vi-VN", { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + "₫";

  return (
    <>
      <div className="p-6">
        <h2 className="text-2xl font-bold text-gray-800 mb-6 text-center">Lịch sử đặt sân của bạn</h2>

        {/* Filter Buttons */}
        <div className="flex flex-wrap gap-3 mb-6">
          {statusOptions.map((option) => (
            <button
              key={option.value}
              onClick={() => setIsStatus(option.value)}
              className={`px-4 py-2 rounded-xl text-sm font-medium border transition-all duration-200 
              ${isStatus === option.value ? "bg-[#F59E0B] text-white bg-[#F59E0B]" : "bg-white text-gray-700 border-gray-300 hover:bg-gray-100"}`}
            >
              {option.label}
            </button>
          ))}
        </div>

        {/* Booking Table */}
        <div className="overflow-x-auto">
          <table className="min-w-full border border-gray-200 divide-y divide-gray-200">
            <thead className="bg-gray-100">
              <tr>
                {["ID", "Trạng thái", "Thanh toán", "Tổng tiền", "Ghi chú", "Ngày tạo"].map((header) => (
                  <th key={header} className="px-4 py-2 text-sm font-semibold text-gray-700 text-center whitespace-nowrap">
                    {header}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {bookingByStatus.length > 0 ? (
                bookingByStatus.map((ticket) => {
                  const statusItem = statusOptions.find((s) => s.value === ticket.status);
                  const paymentItem = paymentOptions.find((p) => p.value === ticket.payment_status);
                  return (
                    <tr key={ticket.id} className="hover:bg-gray-50 text-center whitespace-nowrap">
                      <td className="px-4 py-2 text-sm text-blue-600 hover:text-blue-800 cursor-pointer font-medium align-middle" title="Xem chi tiết">
                        <Link to={`/booking/${ticket.id}`}>
                          #{ticket.id}
                        </Link>
                      </td>
                      <td className="px-4 py-2 text-sm align-middle">
                        {statusItem ? <span className={`px-2 py-1 rounded-full text-xs font-medium ${statusItem.tag}`}>{statusItem.label}</span> : ticket.status}
                      </td>
                      <td className="px-4 py-2 text-sm align-middle">
                        {paymentItem ? <span className={`px-2 py-1 rounded-full text-xs font-medium ${paymentItem.color}`}>{paymentItem.label}</span> : ticket.payment_status}
                      </td>
                      <td className="px-4 py-2 text-sm text-gray-700 align-middle">{formatCurrency(ticket.total_amount)}</td>
                      <td className="px-4 py-2 text-sm text-gray-700 align-middle">{ticket.notes || "-"}</td>
                      <td className="px-4 py-2 text-sm text-gray-700 align-middle">{new Date(ticket.created_at).toLocaleString("vi-VN")}</td>
                    </tr>
                  );
                })
              ) : (
                <tr>
                  <td colSpan={6} className="px-4 py-4 text-center text-gray-500">
                    Không có lịch sử booking nào.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>



      </div>
      {/* 
      {selectedTicket && (
       <Detail_Booking ticket={selectedTicket} onClose={() => setSelectedTicket(null)} />
      )} */}

    </>
  );
};

export default BookingHistory;
