import { useState, useMemo } from "react";
import type { User } from "../../Types/user";
import { useFetchData } from "../../Hooks/useApi";
import type { Ticket } from "../../Types/tiket";
import { Link } from "react-router-dom";

// --- TYPES (Giữ nguyên) ---
interface WalletLog {
  id: number;
  type: string;
  amount: string | number;
  before_balance: string | number;
  after_balance: string | number;
  description: string | null;
  created_at: string;
  ticket_id?: number | null;
}

interface Wallet {
  id: number;
  user_id: number;
  balance: string | number;
  status: string;
  logs: WalletLog[];
}

const BookingHistory = ({ user }: { user: User }) => {
  const [activeTab, setActiveTab] = useState<'bookings' | 'transactions'>('bookings');
  const [filterStatus, setFilterStatus] = useState("all");

  const { data: ticketData, isLoading: isLoadingTickets } = useFetchData("tickets");
  const { data: walletData, isLoading: isLoadingWallet } = useFetchData("wallet");

  const listBooking: Ticket[] = (ticketData?.data as Ticket[]) ?? [];
  const wallet: Wallet | null = (walletData?.data as Wallet) ?? null;
  const transactions: WalletLog[] = wallet?.logs || [];

  const formatCurrency = (value: string | number | undefined) => {
    if (value === undefined || value === null) return "0đ";
    return Number(value).toLocaleString("vi-VN", { maximumFractionDigits: 0 }) + "đ";
  };

  const statusConfig: Record<string, any> = {
    pending: { label: "Chờ xác nhận", color: "text-yellow-600 bg-yellow-50 border-yellow-200", icon: "fa-hourglass-half" },
    confirmed: { label: "Đã xác nhận", color: "text-blue-600 bg-blue-50 border-blue-200", icon: "fa-check" },
    completed: { label: "Hoàn thành", color: "text-emerald-600 bg-emerald-50 border-emerald-200", icon: "fa-check-double" },
    cancelled: { label: "Đã hủy", color: "text-red-600 bg-red-50 border-red-200", icon: "fa-xmark" },
    canceled: { label: "Đã hủy", color: "text-red-600 bg-red-50 border-red-200", icon: "fa-xmark" },
  };

  const transactionConfig: Record<string, any> = {
    deposit: { label: "Nạp tiền", color: "text-emerald-600 bg-emerald-50", icon: "fa-arrow-down", sign: "+" },
    refund: { label: "Hoàn tiền", color: "text-blue-600 bg-blue-50", icon: "fa-rotate-left", sign: "+" },
    payment: { label: "Thanh toán", color: "text-red-600 bg-red-50", icon: "fa-arrow-up", sign: "-" },
    withdraw: { label: "Rút tiền", color: "text-orange-600 bg-orange-50", icon: "fa-money-bill-transfer", sign: "-" },
  };

  const filteredBookings = useMemo(() => {
    let list = listBooking;
    if (filterStatus !== "all") list = list.filter((b) => b.status === filterStatus);
    return list.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
  }, [listBooking, filterStatus]);

  const sortedTransactions = useMemo(() => {
    return [...transactions].sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
  }, [transactions]);

  const bookingTabs = [
    { key: "all", label: "Tất cả" },
    { key: "pending", label: "Chờ xử lý" },
    { key: "confirmed", label: "Sắp tới" },
    { key: "completed", label: "Lịch sử" },
    { key: "cancelled", label: "Đã hủy" },
  ];

  return (
    <div className="space-y-6 font-sans">
      
      {/* === 1. WALLET CARD (Redesign) === */}
      <div className="bg-gradient-to-br from-[#10B981] to-[#059669] rounded-2xl shadow-xl p-6 text-white relative overflow-hidden group">
        <div className="absolute -top-10 -right-10 w-48 h-48 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition-all duration-700"></div>
        <div className="absolute bottom-0 left-0 w-32 h-32 bg-black/5 rounded-full blur-2xl"></div>

        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center relative z-10 gap-4">
          <div>
            <div className="flex items-center gap-2 mb-2 opacity-90">
               <div className="w-6 h-6 rounded bg-white/20 flex items-center justify-center text-xs backdrop-blur-sm">
                  <i className="fa-solid fa-wallet"></i>
               </div>
               <span className="text-xs font-bold uppercase tracking-widest">Ví cá nhân</span>
            </div>
            
            <div className="text-3xl md:text-4xl font-extrabold tracking-tight">
              {isLoadingWallet ? (
                <div className="h-9 w-32 bg-white/20 animate-pulse rounded"></div>
              ) : (
                formatCurrency(wallet?.balance)
              )}
            </div>
            
            <p className="text-[10px] md:text-xs mt-1 opacity-70 font-mono">
              ID: {wallet?.id ? String(wallet.id).padStart(6, '0') : '---'}
            </p>
          </div>

          <button className="bg-white text-[#059669] px-5 py-2 rounded-lg shadow-lg hover:shadow-xl hover:bg-green-50 active:scale-95 transition-all text-xs font-bold flex items-center gap-2 uppercase tracking-wide">
            <i className="fa-solid fa-circle-plus"></i> Nạp thêm
          </button>
        </div>
      </div>

      {/* === 2. TABS & CONTENT === */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden min-h-[500px] flex flex-col">
        
        {/* Navigation Tabs */}
        <div className="flex border-b border-gray-100 bg-gray-50/30">
          {[
            { id: 'bookings', icon: 'fa-ticket', label: 'Lịch sử đặt sân' },
            { id: 'transactions', icon: 'fa-clock-rotate-left', label: 'Lịch sử giao dịch' }
          ].map(tab => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id as any)}
              className={`flex-1 py-3.5 text-xs md:text-sm font-bold text-center transition-all relative
                ${activeTab === tab.id ? 'text-[#10B981] bg-white' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'}
              `}
            >
              <i className={`fa-solid ${tab.icon} mr-2`}></i> {tab.label}
              {activeTab === tab.id && <div className="absolute top-0 left-0 w-full h-0.5 bg-[#10B981]"></div>}
            </button>
          ))}
        </div>

        {/* --- BOOKINGS TAB --- */}
        {activeTab === 'bookings' && (
          <div className="flex-1 flex flex-col">
            {/* Filter Tabs */}
            <div className="p-3 border-b border-gray-50 flex gap-2 overflow-x-auto no-scrollbar bg-white sticky top-0 z-20">
              {bookingTabs.map((tab) => (
                <button
                  key={tab.key}
                  onClick={() => setFilterStatus(tab.key)}
                  className={`px-3 py-1.5 rounded-md text-[11px] font-bold whitespace-nowrap transition-all border
                    ${filterStatus === tab.key
                      ? "bg-green-50 text-green-700 border-green-200 shadow-sm"
                      : "bg-white text-gray-500 border-gray-100 hover:border-gray-300 hover:bg-gray-50"
                    }`}
                >
                  {tab.label}
                </button>
              ))}
            </div>

            {/* List */}
            <div className="flex-1 overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead className="bg-gray-50 text-[10px] uppercase font-bold text-gray-400">
                  <tr>
                    <th className="px-4 py-3 w-20">Mã vé</th>
                    <th className="px-4 py-3">Ngày đặt</th>
                    <th className="px-4 py-3 text-right">Tổng tiền</th>
                    <th className="px-4 py-3 text-center">Trạng thái</th>
                    <th className="px-4 py-3 w-10"></th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-50 text-xs md:text-sm text-gray-600">
                  {isLoadingTickets ? (
                    <tr><td colSpan={5} className="p-8 text-center text-gray-400 text-xs">Đang tải dữ liệu...</td></tr>
                  ) : filteredBookings.length === 0 ? (
                    <tr><td colSpan={5} className="p-12 text-center text-gray-400 italic text-xs">Không tìm thấy vé nào</td></tr>
                  ) : (
                    filteredBookings.map((ticket) => {
                      const status = statusConfig[ticket.status] || statusConfig.pending;
                      return (
                        <tr key={ticket.id} className="hover:bg-gray-50/80 transition-colors group">
                          <td className="px-4 py-3 font-mono font-bold text-gray-500 group-hover:text-[#10B981]">#{ticket.id}</td>
                          <td className="px-4 py-3">
                            <div className="font-semibold text-gray-700">{new Date(ticket.created_at).toLocaleDateString('vi-VN')}</div>
                            <div className="text-[10px] text-gray-400">{new Date(ticket.created_at).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })}</div>
                          </td>
                          <td className="px-4 py-3 text-right font-bold text-gray-800">{formatCurrency(ticket.total_amount)}</td>
                          <td className="px-4 py-3 text-center">
                            <span className={`inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold border ${status.color}`}>
                              <i className={`fa-solid ${status.icon}`}></i> {status.label}
                            </span>
                          </td>
                          <td className="px-4 py-3 text-center">
                            <Link to={`/booking/${ticket.id}`} className="w-6 h-6 rounded-full flex items-center justify-center text-gray-300 hover:text-[#10B981] hover:bg-green-50 transition-all">
                               <i className="fa-solid fa-angle-right"></i>
                            </Link>
                          </td>
                        </tr>
                      );
                    })
                  )}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* --- TRANSACTIONS TAB --- */}
        {activeTab === 'transactions' && (
          <div className="flex-1 overflow-x-auto">
            <table className="w-full text-left border-collapse">
              <thead className="bg-gray-50 text-[10px] uppercase font-bold text-gray-400">
                <tr>
                  <th className="px-4 py-3 w-32">Loại GD</th>
                  <th className="px-4 py-3 text-right w-28">Số tiền</th>
                  <th className="px-4 py-3">Nội dung</th>
                  <th className="px-4 py-3 text-right w-32">Thời gian</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-50 text-xs md:text-sm text-gray-600">
                {isLoadingWallet ? (
                  <tr><td colSpan={5} className="p-8 text-center text-gray-400 text-xs">Đang tải lịch sử...</td></tr>
                ) : sortedTransactions.length === 0 ? (
                  <tr><td colSpan={5} className="p-12 text-center text-gray-400 italic text-xs">Chưa có giao dịch nào</td></tr>
                ) : (
                  sortedTransactions.map((log) => {
                    const config = transactionConfig[log.type] || transactionConfig.payment;
                    const isPositive = log.type === 'deposit' || log.type === 'refund';

                    return (
                      <tr key={log.id} className="hover:bg-gray-50/80 transition-colors">
                        <td className="px-4 py-3">
                          <div className="flex items-center gap-2">
                            <div className={`w-6 h-6 rounded-md flex items-center justify-center text-[10px] ${config.color}`}>
                              <i className={`fa-solid ${config.icon}`}></i>
                            </div>
                            <span className="font-semibold text-gray-700 text-xs">{config.label}</span>
                          </div>
                        </td>

                        <td className={`px-4 py-3 text-right font-bold text-xs md:text-sm ${isPositive ? 'text-emerald-600' : 'text-red-500'}`}>
                          {isPositive ? '+' : '-'}{formatCurrency(log.amount)}
                        </td>

                        <td className="px-4 py-3">
                            <div className="max-w-[200px] md:max-w-xs text-xs text-gray-500 leading-snug break-words">
                                {log.description || "Không có nội dung"}
                            </div>
                            {log.ticket_id && (
                                <Link to={`/booking/${log.ticket_id}`} className="inline-block mt-1 text-[10px] font-bold text-[#10B981] hover:underline bg-green-50 px-1.5 py-0.5 rounded border border-green-100">
                                <i className="fa-solid fa-ticket mr-1"></i>Vé #{log.ticket_id}
                                </Link>
                            )}
                        </td>

                        <td className="px-4 py-3 text-right">
                          <div className="text-gray-700 font-medium text-xs">{new Date(log.created_at).toLocaleDateString('vi-VN')}</div>
                          <div className="text-gray-400 text-[10px] font-mono">{new Date(log.created_at).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })}</div>
                        </td>
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
};

export default BookingHistory;