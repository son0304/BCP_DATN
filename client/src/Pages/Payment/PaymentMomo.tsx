import React, { useEffect, useState } from 'react';
import { QRCodeSVG } from 'qrcode.react';
import { usePostData } from '../../Hooks/useApi';
import { useNotification } from '../../Components/Notification'; // Import hook thông báo
import type { Ticket } from '../../Types/tiket';
import axios from 'axios';

interface PaymentMomoProps {
  ticket: Ticket;
  onSuccess: () => void;
}

const PaymentMomo = ({ ticket, onSuccess }: PaymentMomoProps) => {
  const { mutate } = usePostData('payment/momo');
  const { showNotification } = useNotification(); // Sử dụng hook

  const [qrUrl, setQrUrl] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // --- 1. TẠO MÃ QR ---
  useEffect(() => {
    if (!ticket || ticket.status === 'confirmed') return;

    setIsLoading(true);
    setError(null);
    setQrUrl(null);

    // Gọi API tạo mã (nhớ làm tròn tiền)
    mutate(
      {
        id: ticket.id,
        total_amount: Math.round(Number(ticket.total_amount))
      },
      {
        onSuccess: (res: any) => {
          if (res.success && res.payUrl) {
            setQrUrl(res.payUrl);
            // Thông báo nhẹ để user biết đã tạo mã xong
            // showNotification('Đã tạo mã thanh toán MoMo', 'info'); 
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

  // --- 2. POLLING: KIỂM TRA TRẠNG THÁI (3s/lần) ---
  useEffect(() => {
    let intervalId: number;

    if (qrUrl) {
      intervalId = setInterval(async () => {
        try {
          // Gọi API check status
          // Lưu ý: URL này phải khớp với route bạn định nghĩa trong api.php
          const res = await axios.get(`http://localhost:8000/api/payment/check-status/${ticket.id}`);
          const data = res.data;

          // Nếu thanh toán thành công
          if (data.status === 'confirmed' || data.payment_status === 'paid') {
            clearInterval(intervalId); // Dừng kiểm tra
            showNotification('Thanh toán thành công! Cảm ơn quý khách.', 'success');

            // Gọi callback để reload trang cha
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
    <div className="flex flex-col items-center p-6 bg-white rounded-xl shadow-sm border border-gray-100 animate-fadeIn">
      <h3 className="mb-4 text-lg font-bold text-[#a50064]">Quét mã để thanh toán</h3>

      {isLoading && (
        <div className="flex items-center gap-2 text-gray-600 mb-4">
          <i className="fa-solid fa-circle-notch fa-spin text-[#a50064]"></i>
          <span>Đang kết nối với MoMo...</span>
        </div>
      )}

      {error && (
        <div className="p-3 bg-red-50 text-red-600 rounded-lg text-sm mb-4 text-center">
          <i className="fa-solid fa-circle-exclamation mr-2"></i>
          {error}
        </div>
      )}

      {qrUrl && (
        <div className="flex flex-col items-center">
          <div className="p-3 border-4 border-[#a50064] rounded-2xl bg-white shadow-inner">
            <QRCodeSVG
              value={qrUrl}
              size={220}
              level="H"
              imageSettings={{
                src: "/momo.png", 
                x: undefined,
                y: undefined,
                height: 40,
                width: 40,
                excavate: true,
              }}
            />
          </div>
          <p className="mt-4 text-sm text-[#a50064] text-center animate-pulse font-semibold flex items-center gap-2">
            <i className="fa-solid fa-spinner fa-spin"></i>
            Đang chờ xác nhận thanh toán...
          </p>
          <p className="text-xs text-gray-400 mt-2">
            Vui lòng không tắt trình duyệt cho đến khi hoàn tất.
          </p>
        </div>
      )}
    </div>
  );
};

export default PaymentMomo;