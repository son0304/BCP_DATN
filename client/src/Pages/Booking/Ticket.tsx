import React from "react";
import { useParams } from "react-router-dom";
import { useFetchDataById } from "../../Hooks/useApi";
import type { ISODateTimeString } from "../../Types/common";

interface TicketData {
  id: number;
  user_id: number;
  promotion_id: number | null;
  subtotal: number | string;
  discount_amount: number | string;
  total_amount: number | string;
  status: "pending" | "confirmed" | "canceled";
  payment_status: "unpaid" | "paid" | "refunded";
  notes: string | null;
  created_at: ISODateTimeString;
  updated_at: ISODateTimeString;
}

const Ticket: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const { data, isLoading, error } = useFetchDataById<TicketData>("ticket", id!);

  const ticket = data?.data;

  if (isLoading) return <div className="text-center mt-10">Đang tải ticket...</div>;
  if (error) return <div className="text-center mt-10 text-red-500">Lỗi khi tải ticket</div>;
  if (!ticket) return <div className="text-center mt-10">Không tìm thấy ticket</div>;

  return (
    <div className="max-w-md mx-auto bg-white shadow-lg rounded-xl p-6 mt-10">
      <h2 className="text-2xl font-bold mb-4">Chi tiết Ticket #{ticket.id}</h2>

      <div className="space-y-2">
        <div className="flex justify-between">
          <span className="font-semibold">User ID:</span>
          <span>{ticket.user_id}</span>
        </div>

        <div className="flex justify-between">
          <span className="font-semibold">Promotion ID:</span>
          <span>{ticket.promotion_id ?? "Không có"}</span>
        </div>

        <div className="flex justify-between">
          <span className="font-semibold">Subtotal:</span>
          <span>{Number(ticket.subtotal).toLocaleString()} đ</span>
        </div>

        <div className="flex justify-between">
          <span className="font-semibold">Discount:</span>
          <span>{Number(ticket.discount_amount).toLocaleString()} đ</span>
        </div>

        <div className="flex justify-between">
          <span className="font-semibold">Total:</span>
          <span className="font-bold">{Number(ticket.total_amount).toLocaleString()} đ</span>
        </div>

        <div className="flex justify-between">
          <span className="font-semibold">Status:</span>
          <span
            className={`font-semibold ${ticket.status === "confirmed" ? "text-green-600" : "text-yellow-600"
              }`}
          >
            {ticket.status}
          </span>
        </div>

        <div className="flex justify-between">
          <span className="font-semibold">Payment:</span>
          <span
            className={`font-semibold ${ticket.payment_status === "paid" ? "text-green-600" : "text-red-600"
              }`}
          >
            {ticket.payment_status}
          </span>
        </div>

        {ticket.notes && (
          <div>
            <span className="font-semibold">Notes:</span>
            <p className="ml-2">{ticket.notes}</p>
          </div>
        )}

        <div className="flex justify-between text-sm text-gray-500 mt-4">
          <span>Created at:</span>
          <span>{new Date(ticket.created_at).toLocaleString()}</span>
        </div>

        <div className="flex justify-between text-sm text-gray-500">
          <span>Updated at:</span>
          <span>{new Date(ticket.updated_at).toLocaleString()}</span>
        </div>
      </div>
    </div>
  );
};

export default Ticket;

