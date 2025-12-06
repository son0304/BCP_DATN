import React, { useEffect, useState } from 'react';
import { QRCodeSVG } from 'qrcode.react';
import { usePostData } from '../../Hooks/useApi';
import { useNotification } from '../../Components/Notification';
import type { Ticket } from '../../Types/tiket';
import axios from 'axios';

interface PaymentMomoProps {
  ticket: Ticket;
  onSuccess: () => void;
}

const PaymentMomo = ({ ticket, onSuccess }: PaymentMomoProps) => {
  const { mutate } = usePostData('payment/momo');
  const { showNotification } = useNotification();

  const [qrUrl, setQrUrl] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // --- 1. TẠO MÃ QR ---
  useEffect(() => {
    if (!ticket || ticket.status === 'confirmed') return;

    setIsLoading(true);
    setError(null);
    setQrUrl(null);

    mutate(
      {
        id: ticket.id,
        total_amount: Math.round(Number(ticket.total_amount))
      },
      {
        onSuccess: (res: any) => {
          if (res.success && res.payUrl) {
            setQrUrl(res.payUrl);
          } else {
            const msg = 'Không nhận được link thanh toán từ MoMo';
            setError(msg);
            showNotification(msg, 'error');
          }
        },
        onError: (err: any) => {
          console.error(err);
          const msg = 'Lỗi khi tạo giao dịch MoMo';
          setError(msg);
          showNotification(msg, 'error');
        },
        onSettled: () => setIsLoading(false)
      }
    );
  }, [ticket, mutate, showNotification]);

  // --- 2. POLLING (3s/lần) ---
  useEffect(() => {
    let intervalId: number;

    if (qrUrl) {
      intervalId = setInterval(async () => {
        try {
          const res = await axios.get(`http://localhost:8000/api/payment/check-status/${ticket.id}`);
          const data = res.data;
          if (data.status === 'confirmed' || data.payment_status === 'paid') {
            clearInterval(intervalId);
            showNotification('Thanh toán thành công! Cảm ơn quý khách.', 'success');
            onSuccess();
          }
        } catch (err) {
          console.error("Lỗi kiểm tra trạng thái", err);
        }
      }, 3000);
    }

    return () => {
      if (intervalId) clearInterval(intervalId);
    };
  }, [qrUrl, ticket.id, onSuccess, showNotification]);

  return (
    <div className="flex flex-col items-center bg-white rounded-xl p-4 animate-fade-in mt-4">
      
      {/* Loading State */}
      {isLoading && (
        <div className="py-8 text-center space-y-3">
          <div className="w-10 h-10 border-4 border-[#D82D8B] border-t-transparent rounded-full animate-spin mx-auto"></div>
          <p className="text-sm font-semibold text-gray-600">Đang khởi tạo giao dịch MoMo...</p>
        </div>
      )}

      {/* Error State */}
      {error && (
        <div className="w-full p-4 bg-red-50 text-red-600 rounded-lg text-xs md:text-sm text-center border border-red-100 flex flex-col items-center gap-2">
          <i className="fa-solid fa-triangle-exclamation text-xl"></i>
          <span>{error}</span>
          <button 
             onClick={() => window.location.reload()} 
             className="mt-2 text-xs font-bold underline hover:text-red-800"
          >
             Thử lại
          </button>
        </div>
      )}

      {/* QR Code Display */}
      {qrUrl && (
        <div className="w-full flex flex-col items-center">
          <div className="relative bg-[#FFF0F6] p-4 rounded-2xl border-2 border-[#D82D8B]/20 shadow-sm">
             {/* Logo MoMo góc */}
             <div className="absolute -top-3 -right-3 w-8 h-8 bg-white rounded-full p-1 shadow border border-gray-100 z-10">
                <img src="/momo.png" alt="logo" className="w-full h-full object-contain" />
             </div>

             <div className="bg-white p-2 rounded-xl">
                <QRCodeSVG
                value={qrUrl}
                size={200}
                level="H"
                imageSettings={{
                    src: "/momo.png", 
                    x: undefined,
                    y: undefined,
                    height: 36,
                    width: 36,
                    excavate: true,
                }}
                />
             </div>
          </div>
          
          <div className="mt-5 text-center space-y-2">
             <h4 className="text-[#D82D8B] font-bold text-sm uppercase tracking-wide">Quét mã để thanh toán</h4>
             <p className="text-xs text-gray-500 max-w-[250px] mx-auto">
                Sử dụng App <b>MoMo</b> hoặc ứng dụng Camera hỗ trợ QR code để quét.
             </p>
          </div>

          {/* Polling Indicator */}
          <div className="mt-6 flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-full border border-gray-200">
             <span className="relative flex h-2.5 w-2.5">
                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#D82D8B] opacity-75"></span>
                <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#D82D8B]"></span>
             </span>
             <p className="text-xs font-semibold text-gray-600">Đang chờ xác nhận...</p>
          </div>
          
          <p className="text-[10px] text-gray-400 mt-2 italic">
            Vui lòng không tắt trình duyệt.
          </p>
        </div>
      )}
    </div>
  );
};

export default PaymentMomo;