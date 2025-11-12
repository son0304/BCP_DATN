import { useState } from "react";
import type { User } from "../../Types/user";
import { useFetchData } from "../../Hooks/useApi";
import { Modal, Descriptions, List, Steps, Tag } from "antd";
import type { Ticket } from "../../Types/tiket";

const BookingHistory = ({ user }: { user: User }) => {
  const [isStatus, setIsStatus] = useState("all");

  const statusOptions = [
    { value: "all", label: "Tất cả", color: "bg-gray-200 text-gray-800", active: "bg-gray-500 text-white" },
    { value: "pending", label: "Chờ xác nhận", color: "bg-yellow-100 text-yellow-800", active: "bg-yellow-500 text-white" },
    { value: "confirmed", label: "Đã xác nhận", color: "bg-blue-100 text-blue-800", active: "bg-blue-500 text-white" },
    { value: "completed", label: "Hoàn thành", color: "bg-green-100 text-green-800", active: "bg-green-500 text-white" },
    { value: "cancelled", label: "Đã hủy", color: "bg-red-100 text-red-800", active: "bg-red-500 text-white" },
  ];

  const paymentOptions = [
    { value: "unpaid", label: "Chưa thanh toán", color: "bg-gray-100 text-gray-800" },
    { value: "paid", label: "Đã thanh toán", color: "bg-green-100 text-green-800" },
    { value: "refunded", label: "Đã hoàn tiền", color: "bg-orange-100 text-orange-800" },
  ];

  const { data } = useFetchData("tickets");
  const lisBooking: Ticket[] = (data?.data as Ticket[]) ?? [];

  const bookingByStatus = lisBooking
    .filter((booking) => isStatus === "all" || booking.status === isStatus)
    .sort(
      (a, b) =>
        new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
    );

  const [selectedTicket, setSelectedTicket] = useState<Ticket | null>(null);

  const { Step } = Steps;

  return (
    <div className="p-6">
      <h2 className="text-2xl font-bold text-gray-800 mb-6 text-center">
        Lịch sử đặt sân của bạn
      </h2>
      {/* Filter Buttons */}
      <div className="flex flex-wrap gap-3 mb-6">
        {statusOptions.map((option) => (
          <button
            key={option.value}
            onClick={() => setIsStatus(option.value)}
            className={`px-4 py-2 rounded-xl text-sm font-medium border transition-all duration-200 
              ${isStatus === option.value
                ? option.active
                : option.color + " border-gray-200 hover:opacity-80"}`}
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
              {[
                "ID",
                "Trạng thái",
                "Thanh toán",
                "Tổng tiền",
                "Ghi chú",
                "Ngày tạo",
              ].map((header) => (
                <th
                  key={header}
                  className="px-4 py-2 text-sm font-semibold text-gray-700 text-center whitespace-nowrap"
                >
                  {header}
                </th>
              ))}
            </tr>
          </thead>

          <tbody className="divide-y divide-gray-200">
            {bookingByStatus.length > 0 ? (
              bookingByStatus.map((ticket) => {
                const statusItem =
                  statusOptions.find((opt) => opt.value === ticket.status) ?? null;
                const paymentItem =
                  paymentOptions.find((opt) => opt.value === ticket.payment_status) ?? null;

                return (
                  <tr
                    key={ticket.id}
                    className="hover:bg-gray-50 text-center whitespace-nowrap"
                  >
                    <td
                      onClick={() => setSelectedTicket(ticket)}
                      className="px-4 py-2 text-sm text-blue-600 hover:text-blue-800 cursor-pointer font-medium align-middle"
                      title="Xem chi tiết"
                    >
                      {ticket.id}
                    </td>

                    <td className="px-4 py-2 text-sm align-middle">
                      {statusItem ? (
                        <span
                          className={`px-2 py-1 rounded-full text-xs font-medium ${statusItem.color}`}
                        >
                          {statusItem.label}
                        </span>
                      ) : (
                        ticket.status
                      )}
                    </td>

                    <td className="px-4 py-2 text-sm align-middle">
                      {paymentItem ? (
                        <span
                          className={`px-2 py-1 rounded-full text-xs font-medium ${paymentItem.color}`}
                        >
                          {paymentItem.label}
                        </span>
                      ) : (
                        ticket.payment_status
                      )}
                    </td>

                    <td className="px-4 py-2 text-sm text-gray-700 align-middle">
                      {Number(ticket.total_amount.toLocaleString("vi-VN", { minimumFractionDigits: 0, maximumFractionDigits: 0 }))}₫
                    </td>

                    <td className="px-4 py-2 text-sm text-gray-700 align-middle">
                      {ticket.notes || "-"}
                    </td>

                    <td className="px-4 py-2 text-sm text-gray-700 align-middle">
                      {new Date(ticket.created_at).toLocaleString("vi-VN", {
                        day: "2-digit",
                        month: "2-digit",
                        year: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                      })}
                    </td>
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

        {/* Modal Chi tiết */}
        {selectedTicket && (
          <Modal
            open={!!selectedTicket}
            onCancel={() => setSelectedTicket(null)}
            footer={null}
            title={`Chi tiết đơn #${selectedTicket.id}`}
            width={720}
            centered
          >
            {/* Thông tin cơ bản của đơn */}
            <div className="mb-4">
              <Descriptions
                column={1}
                size="small"
                bordered
                className="text-sm text-gray-700"
              >
                <Descriptions.Item label="Trạng thái">
                  {selectedTicket.status === "cancelled" ? (
                    // 1. Nếu đã hủy, hiển thị Tag (Thẻ) màu đỏ
                    <Tag color="error">Đã hủy</Tag>
                  ) : (
                    // 2. Nếu không, hiển thị quy trình 3 bước bình thường
                    <Steps
                      size="small"
                      current={
                        // Tính toán bước hiện tại trên 3 bước
                        ["pending", "confirmed", "completed"].indexOf(selectedTicket.status)
                      }
                      status={
                        // Chỉ có 2 trạng thái: đang xử lý hoặc hoàn thành
                        selectedTicket.status === "completed"
                          ? "finish"
                          : "process"
                      }
                      responsive
                    >
                      <Step title="Chờ xác nhận" />
                      <Step title="Đã xác nhận" />
                      <Step title="Hoàn thành" />
                    </Steps>
                  )}
                </Descriptions.Item>
                <Descriptions.Item label="Thanh toán">
                  {(() => {
                    const paymentItem = paymentOptions.find(
                      (opt) => opt.value === selectedTicket.payment_status
                    );
                    return paymentItem ? (
                      <span
                        className={`px-2 py-1 rounded-full text-xs font-medium ${paymentItem.color}`}
                      >
                        {paymentItem.label}
                      </span>
                    ) : (
                      selectedTicket.payment_status
                    );
                  })()}
                </Descriptions.Item>
                <Descriptions.Item label="Giá">
                  {Number(selectedTicket.subtotal).toLocaleString("vi-VN", {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0,
                  })}₫
                </Descriptions.Item>
                <Descriptions.Item label="Giảm giá">
                  <span className="text-red-500">
                    -{Number(selectedTicket.discount_amount).toLocaleString("vi-VN", {
                      minimumFractionDigits: 0,
                      maximumFractionDigits: 0,
                    })}₫
                  </span>
                </Descriptions.Item>
                <Descriptions.Item label="Tổng">
                  {Number(selectedTicket.total_amount).toLocaleString("vi-VN", {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0,
                  })}₫
                </Descriptions.Item>
                <Descriptions.Item label="Ghi chú">
                  {selectedTicket.notes || "Không có"}
                </Descriptions.Item>
                <Descriptions.Item label="Ngày tạo">
                  {new Date(selectedTicket.created_at).toLocaleString("vi-VN")}
                </Descriptions.Item>
              </Descriptions>

            </div>

            {/* Danh sách sân đã đặt */}
            <div>
              <h3 className="font-medium mb-2 text-gray-800">Danh sách sân đã đặt</h3>

              {selectedTicket?.items && selectedTicket.items.length > 0 ? (
                <List
                  itemLayout="vertical"
                  dataSource={selectedTicket.items}
                  bordered
                  renderItem={(item) => (
                    <List.Item key={item.id}>
                      <div className="space-y-1">
                        <p><strong>Sân:</strong> {item.booking?.court?.venue?.name ?? "N/K"}</p>
                        <p><strong>Sân con:</strong> {item.booking?.court?.name ?? "N/K"}</p>
                        <p>
                          <strong>Ngày:</strong>{" "}
                          {item.booking?.date
                            ? new Date(item.booking.date).toLocaleDateString("vi-VN")
                            : "N/K"}
                        </p>
                        <p>
                          <strong>Giờ chơi:</strong>{" "}
                          {item.booking?.time_slot?.start_time?.slice(0, 5) ?? "N/K"} -{" "}
                          {item.booking?.time_slot?.end_time?.slice(0, 5) ?? "N/K"}
                        </p>
                        <p>
                          <strong>Giá:</strong>{" "}
                          {item.unit_price
                            ? Number(item.unit_price).toLocaleString("vi-VN", {
                              minimumFractionDigits: 0,
                              maximumFractionDigits: 0,
                            }) + "₫"
                            : "N/K"}
                        </p>
                      </div>
                    </List.Item>
                  )}
                />
              ) : (
                <p className="text-gray-500 text-sm mt-2">Không có sân nào trong đơn này.</p>
              )}

            </div>
          </Modal>
        )}
      </div>
    </div>
  );
};

export default BookingHistory;