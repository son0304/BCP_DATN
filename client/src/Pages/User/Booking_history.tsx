import { useState, useMemo } from "react";
import type { User } from "../../Types/user";
import { useFetchData } from "../../Hooks/useApi";
import type { Ticket } from "../../Types/tiket";
import { Link } from "react-router-dom";

const BookingHistory = ({ user }: { user: User }) => {
  const [filterStatus, setFilterStatus] = useState("all");

  // --- CONFIG ---
  const statusConfig: Record<string, { label: string; class: string; icon: string }> = {
    pending: { label: "Chờ xác nhận", class: "bg-yellow-50 text-yellow-700 border border-yellow-200", icon: "fa-hourglass-half" },
    confirmed: { label: "Đã xác nhận", class: "bg-blue-50 text-blue-700 border border-blue-200", icon: "fa-check-circle" },
    completed: { label: "Hoàn thành", class: "bg-emerald-50 text-emerald-700 border border-emerald-200", icon: "fa-flag-checkered" },
    cancelled: { label: "Đã hủy", class: "bg-red-50 text-red-700 border border-red-200", icon: "fa-xmark" },
  };

  const paymentConfig: Record<string, { label: string; class: string }> = {
    unpaid: { label: "Chưa thanh toán", class: "text-gray-500 bg-gray-100" },
    paid: { label: "Đã thanh toán", class: "text-emerald-600 bg-emerald-100" },
    refunded: { label: "Đã hoàn tiền", class: "text-orange-600 bg-orange-100" },
  };

  const tabs = [
    { key: "all", label: "Tất cả" },
    { key: "pending", label: "Chờ xử lý" },
    { key: "confirmed", label: "Sắp tới" },
    { key: "completed", label: "Hoàn thành" },
    { key: "cancelled", label: "Đã hủy" },
  ];

  // --- DATA HANDLING ---
  const { data, isLoading } = useFetchData("tickets");
  const listBooking: Ticket[] = (data?.data as Ticket[]) ?? [];

  const filteredList = useMemo(() => {
    let list = listBooking;
    if (filterStatus !== "all") {
      list = list.filter((b) => b.status === filterStatus);
    }
    return list.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
  }, [listBooking, filterStatus]);

  // Thống kê nhanh
  const stats = {
    total: listBooking.length,
    spent: listBooking
      .filter(t => t.payment_status === 'paid')
      .reduce((acc, curr) => acc + Number(curr.total_amount), 0)
  };

  const formatCurrency = (value: string | number) =>
    Number(value).toLocaleString("vi-VN", { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + "₫";

  // --- RENDER ---
  return (
    <div className="min-h-screen bg-gray-50/50 font-sans p-4 md:p-8">

      {/* 1. HEADER & STATS */}
      <div className="max-w-6xl mx-auto mb-8">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
          <div>
            <h1 className="text-2xl md:text-3xl font-bold text-gray-800">Lịch sử đặt sân</h1>
            <p className="text-gray-500 mt-1">Quản lý và theo dõi các đơn đặt sân của bạn</p>
          </div>

          {/* Stats Card Mini */}
          <div className="flex gap-4">
            <div className="bg-white p-3 px-5 rounded-2xl shadow-sm border border-gray-100">
              <p className="text-xs text-gray-400 uppercase font-semibold">Tổng đơn</p>
              <p className="text-xl font-bold text-gray-800">{stats.total}</p>
            </div>
            <div className="bg-white p-3 px-5 rounded-2xl shadow-sm border border-gray-100">
              <p className="text-xs text-gray-400 uppercase font-semibold">Đã chi tiêu</p>
              <p className="text-xl font-bold text-emerald-600">{formatCurrency(stats.spent)}</p>
            </div>
          </div>
        </div>

        {/* 2. TABS FILTER */}
        <div className="flex overflow-x-auto pb-2 md:pb-0 gap-2 mb-6 scrollbar-hide">
          {tabs.map((tab) => (
            <button
              key={tab.key}
              onClick={() => setFilterStatus(tab.key)}
              className={`px-5 py-2.5 rounded-full text-sm font-semibold transition-all whitespace-nowrap ${filterStatus === tab.key
                ? "bg-gray-900 text-white shadow-lg shadow-gray-200 transform -translate-y-0.5"
                : "bg-white text-gray-600 border border-gray-200 hover:bg-gray-50"
                }`}
            >
              {tab.label}
            </button>
          ))}
        </div>

        {/* 3. CONTENT */}
        {isLoading ? (
          <div className="space-y-4">
            {[1, 2, 3].map(i => <div key={i} className="h-20 bg-gray-200 rounded-xl animate-pulse"></div>)}
          </div>
        ) : filteredList.length === 0 ? (
          <div className="text-center py-16 bg-white rounded-3xl border border-gray-100 shadow-sm">
            <div className="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
              <i className="fa-regular fa-calendar-xmark text-3xl"></i>
            </div>
            <h3 className="text-lg font-bold text-gray-800">Chưa có đơn đặt sân nào</h3>
            <p className="text-gray-500 mb-6">Hãy đặt sân ngay để trải nghiệm dịch vụ tốt nhất</p>
            <Link to="/venues" className="px-6 py-3 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 transition">
              Đặt sân ngay
            </Link>
          </div>
        ) : (
          <>
            {/* --- VIEW MOBILE (CARD) --- */}
            <div className="grid grid-cols-1 gap-4 md:hidden">
              {filteredList.map((ticket) => {
                const status = statusConfig[ticket.status] || statusConfig.pending;
                const payment = paymentConfig[ticket.payment_status] || paymentConfig.unpaid;
                return (
                  <Link to={`/booking/${ticket.id}`} key={ticket.id} className="block">
                    <div className="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 active:scale-98 transition-transform">
                      <div className="flex justify-between items-start mb-3">
                        <div>
                          <span className="text-xs font-bold text-gray-400 uppercase">#{ticket.id}</span>
                          <h4 className="font-bold text-gray-800 text-lg">{formatCurrency(ticket.total_amount)}</h4>
                        </div>
                        <span className={`px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1 ${status.class}`}>
                          <i className={`fa-solid ${status.icon}`}></i> {status.label}
                        </span>
                      </div>

                      <div className="space-y-2 text-sm text-gray-600 mb-4">
                        <div className="flex items-center gap-2">
                          <i className="fa-regular fa-calendar w-4"></i>
                          {new Date(ticket.created_at).toLocaleDateString('vi-VN')}
                        </div>
                        <div className="flex items-center gap-2">
                          <i className="fa-solid fa-wallet w-4"></i>
                          <span className={`font-medium ${ticket.payment_status === 'paid' ? 'text-emerald-600' : 'text-gray-500'}`}>
                            {payment.label}
                          </span>
                        </div>
                      </div>

                      <div className="pt-3 border-t border-gray-100 flex justify-between items-center">
                        <span className="text-xs text-gray-400 italic truncate max-w-[150px]">
                          {ticket.notes || "Không có ghi chú"}
                        </span>
                        <span className="text-emerald-600 text-sm font-bold flex items-center gap-1">
                          Chi tiết <i className="fa-solid fa-chevron-right text-xs"></i>
                        </span>
                      </div>
                    </div>
                  </Link>
                );
              })}
            </div>

            {/* --- VIEW DESKTOP (TABLE) --- */}
            <div className="hidden md:block bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-gray-50/50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold tracking-wider">
                    <th className="px-6 py-5">Mã đơn</th>
                    <th className="px-6 py-5">Ngày tạo</th>
                    <th className="px-6 py-5 text-center">Trạng thái</th>
                    <th className="px-6 py-5 text-center">Thanh toán</th>
                    <th className="px-6 py-5 text-right">Tổng tiền</th>
                    <th className="px-6 py-5 text-center">Hành động</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {filteredList.map((ticket) => {
                    const status = statusConfig[ticket.status] || statusConfig.pending;
                    const payment = paymentConfig[ticket.payment_status] || paymentConfig.unpaid;

                    return (
                      <tr key={ticket.id} className="hover:bg-gray-50/80 transition-colors group">
                        <td className="px-6 py-4">
                          <span className="font-mono font-bold text-gray-800 text-sm bg-gray-100 px-2 py-1 rounded">
                            #{ticket.id}
                          </span>
                        </td>
                        <td className="px-6 py-4">
                          <div className="flex flex-col">
                            <span className="text-sm font-medium text-gray-700">
                              {new Date(ticket.created_at).toLocaleDateString('vi-VN')}
                            </span>
                            <span className="text-xs text-gray-400">
                              {new Date(ticket.created_at).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })}
                            </span>
                          </div>
                        </td>
                        <td className="px-6 py-4 text-center">
                          <span className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold ${status.class}`}>
                            <i className={`fa-solid ${status.icon}`}></i> {status.label}
                          </span>
                        </td>
                        <td className="px-6 py-4 text-center">
                          <span className={`inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-medium ${payment.class}`}>
                            {payment.label}
                          </span>
                        </td>
                        <td className="px-6 py-4 text-right">
                          <span className="text-base font-bold text-gray-800 group-hover:text-emerald-600 transition-colors">
                            {formatCurrency(ticket.total_amount)}
                          </span>
                        </td>
                        <td className="px-6 py-4 text-center">
                          <Link
                            to={`/booking/${ticket.id}`}
                            className="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition-all mx-auto shadow-sm"
                            title="Xem chi tiết"
                          >
                            <i className="fa-solid fa-arrow-right"></i>
                          </Link>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </>
        )}
      </div>
    </div>
  );
};

export default BookingHistory;