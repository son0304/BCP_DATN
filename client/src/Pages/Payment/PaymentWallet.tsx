import { useState } from "react";
import { usePostData } from "../../Hooks/useApi";
import { useNotification } from "../../Components/Notification"; 
import type { Ticket } from "../../Types/tiket";

interface PaymentWalletProps {
  ticket: Ticket;
  onSuccess: () => void;
}

const PaymentWallet = ({ ticket, onSuccess }: PaymentWalletProps) => {
  const { mutate } = usePostData('payment/wallet');
  const [isLoading, setIsLoading] = useState(false);

  const handlePayment = () => {
    if (isLoading) return;
    setIsLoading(true);

    mutate(
      {
        ticket_id: ticket.id,
        total_amount: Math.round(Number(ticket.total_amount))
      },
      {
        onSuccess: (res: any) => {
          setIsLoading(false);
          onSuccess();
        },
        onError: (err: any) => {
          setIsLoading(false);
          console.error('Lỗi khi thanh toán ví:', err);
        }
      }
    );
  }

  return (
    <div className="mt-4 animate-fade-in-up">
      {/* Thông báo xác nhận nhỏ */}
      <div className="bg-emerald-50 border border-emerald-100 rounded-lg p-3 mb-4 flex items-start gap-3">
        <div className="mt-0.5 text-emerald-600">
           <i className="fa-solid fa-shield-halved"></i>
        </div>
        <div className="text-xs text-emerald-800">
           <p className="font-bold mb-0.5">Thanh toán an toàn</p>
           <p>Số tiền sẽ được trừ trực tiếp vào số dư ví của bạn. Giao dịch được xử lý ngay lập tức.</p>
        </div>
      </div>

      {/* Nút thanh toán */}
      <button
        onClick={handlePayment}
        disabled={isLoading}
        className={`
          w-full py-3.5 px-6 rounded-xl font-bold text-sm text-white shadow-lg transition-all duration-300 flex items-center justify-center gap-2
          ${isLoading
            ? 'bg-gray-400 cursor-not-allowed shadow-none'
            : 'bg-[#10B981] hover:bg-[#059669] hover:shadow-green-200 hover:-translate-y-0.5'
          }
        `}
      >
        {isLoading ? (
          <>
             <i className="fa-solid fa-circle-notch fa-spin"></i>
             Đang xử lý giao dịch...
          </>
        ) : (
          <>
             <i className="fa-solid fa-check-circle"></i>
             Xác nhận thanh toán ngay
          </>
        )}
      </button>
      
      {!isLoading && (
        <p className="text-center text-[10px] text-gray-400 mt-2 italic">
          Bằng việc xác nhận, bạn đồng ý với các điều khoản đặt sân.
        </p>
      )}
    </div>
  )
}

export default PaymentWallet;