import { useState } from "react"
import type { User } from "../../Types/user"
import { useFetchData } from "../../Hooks/useApi";
import type { Ticket } from "../../Types/tiket";

const BookingHistory = ({ user }: { user: User }) => {
    const [isStatus, setIsStatus] = useState("all");

    const statusOptions = [
        { value: "all", label: "Tất cả" },
        { value: "pending", label: "Chờ xác nhận" },
        { value: "confirmed", label: "Đã xác nhận" },
        { value: "completed", label: "Hoàn thành" },
        { value: "cancelled", label: "Đã hủy" },
    ];

    const { data } = useFetchData("tickets");
    const lisBooking: Ticket[] = (data?.data as Ticket[]) ?? [];

    const bookingByStatus = lisBooking.filter(
        (booking) => isStatus === "all" || booking.status === isStatus
    );

    return (
        <div className="p-6">
            {/* Filter Buttons */}
            <div className="flex gap-3 mb-6">
                {statusOptions.map((option) => (
                    <button
                        key={option.value}
                        onClick={() => setIsStatus(option.value)}
                        className={`px-4 py-2 rounded-xl text-sm font-medium border transition-all duration-200
                ${isStatus === option.value
                                ? "bg-yellow-500 text-white shadow-md"
                                : "bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100"
                            }`}
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
                            <th className="px-4 py-2 text-left text-sm font-semibold text-gray-700">ID</th>
                            <th className="px-4 py-2 text-left text-sm font-semibold text-gray-700">Status</th>
                            <th className="px-4 py-2 text-left text-sm font-semibold text-gray-700">Payment</th>
                            <th className="px-4 py-2 text-left text-sm font-semibold text-gray-700">Total</th>
                            <th className="px-4 py-2 text-left text-sm font-semibold text-gray-700">Notes</th>
                            <th className="px-4 py-2 text-left text-sm font-semibold text-gray-700">Created At</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200">
                        {bookingByStatus.length > 0 ? (
                            bookingByStatus.map((ticket) => (
                                <tr key={ticket.id} className="hover:bg-gray-50">
                                    <td className="px-4 py-2 text-sm text-gray-700">{ticket.id}</td>
                                    <td className="px-4 py-2 text-sm text-gray-700">{ticket.status}</td>
                                    <td className="px-4 py-2 text-sm text-gray-700">{ticket.payment_status}</td>
                                    <td className="px-4 py-2 text-sm text-gray-700">{ticket.total_amount}</td>
                                    <td className="px-4 py-2 text-sm text-gray-700">{ticket.notes}</td>
                                    <td className="px-4 py-2 text-sm text-gray-700">
                                        {new Date(ticket.created_at).toLocaleString("vi-VN", {
                                            day: "2-digit",
                                            month: "2-digit",
                                            year: "numeric",
                                            hour: "2-digit",
                                            minute: "2-digit",
                                        })}
                                    </td>
                                </tr>
                            ))
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
    );
}

export default BookingHistory
