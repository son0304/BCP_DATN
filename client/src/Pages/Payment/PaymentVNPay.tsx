import React, { useEffect, useState } from 'react';
import { QRCodeSVG } from 'qrcode.react';
import { usePostData } from '../../Hooks/useApi';
import { useNotification } from '../../Components/Notification';
import axios from 'axios';

interface PaymentVNPayProps {
  ticket: any;
  onSuccess: () => void;
}

const PaymentVNPay = ({ ticket, onSuccess }: PaymentVNPayProps) => {
  const { mutate } = usePostData('payment/vnpay');
  const { showNotification } = useNotification();

  const [qrUrl, setQrUrl] = useState<string | null>(null);
  const [isProcessing, setIsProcessing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // --- 1. XỬ LÝ KHI VNPAY TRẢ VỀ (REDIRECT) ---
  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const vnpResponseCode = params.get('vnp_ResponseCode');

    if (vnpResponseCode) {
      const verify = async () => {
        setIsProcessing(true);
        try {
          // Gửi dữ liệu về Laravel để xác thực (Checksum)
          const res = await axios.get(`http://localhost:8000/api/payment/vnpay-callback`, {
            params: Object.fromEntries(params.entries())
          });

          if (res.data.success) {
            showNotification('Thanh toán thành công!', 'success');
            // Xóa query params trên URL cho sạch
            window.history.replaceState({}, '', window.location.pathname);
            onSuccess();
          } else {
            setError(res.data.message || 'Thanh toán không thành công.');
          }
        } catch (e) {
          setError('Lỗi hệ thống khi xác thực.');
        } finally {
          setIsProcessing(false);
        }
      };
      verify();
    } else {
      // Nếu mới vào trang, thực hiện lấy link thanh toán
      initPayment();
    }
  }, []);

  // --- 2. LẤY LINK THANH TOÁN TỪ BACKEND ---
  const initPayment = () => {
    if (ticket.status === 'confirmed') return;

    mutate({ id: ticket.id }, {
      onSuccess: (res: any) => {
        if (res.success && res.payUrl) {
          setQrUrl(res.payUrl);
        } else {
          setError(res.message || 'Không lấy được link thanh toán.');
        }
      },
      onError: () => setError('Lỗi kết nối Server.')
    });
  };

  // UI khi đang kiểm tra hóa đơn sau khi thanh toán xong
  if (isProcessing) {
    return (
      <div className="flex flex-col items-center p-8 bg-blue-50 rounded-xl">
        <div className="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-4"></div>
        <p className="text-blue-700 font-bold uppercase tracking-tight">Đang xác thực giao dịch...</p>
      </div>
    );
  }

  return (
    <div className="flex flex-col items-center bg-white p-4 animate-fade-in">
      {qrUrl ? (
        <div className="w-full space-y-6 text-center">
          {/* NÚT CHÍNH ĐỂ TEST TRÊN TRÌNH DUYỆT */}
          <a href={qrUrl} target="_blank" rel="noreferrer"
            className="block w-full bg-[#005baa] hover:bg-[#004a8a] text-white font-bold py-4 rounded-xl shadow-xl transition-all uppercase text-sm">
            Nhấn để tới cổng thanh toán VNPay
          </a>

          <div className="flex flex-col md:flex-row items-center justify-center gap-6">
            <div className="bg-white p-2 border-2 border-dashed border-blue-200 rounded-lg">
              <QRCodeSVG value={qrUrl} size={160} />
              <p className="text-[10px] text-blue-500 font-bold mt-2">QR CODE TEST</p>
            </div>

            <div className="bg-amber-50 p-4 rounded-xl border border-amber-200 text-left text-[11px] text-amber-900 leading-relaxed shadow-sm">
              <h5 className="font-bold border-b border-amber-200 mb-2 pb-1 uppercase">Thông tin thẻ Test (NCB)</h5>
              <p>• Ngân hàng: <strong>NCB</strong></p>
              <p>• Số thẻ: <strong className="text-red-600">97041985219704198526191432198</strong></p>
              <p>• Tên: <strong>NGUYEN VAN A</strong></p>
              <p>• Ngày phát hành: <strong>07/15</strong></p>
              <p>• OTP: <strong>123456</strong></p>
            </div>
          </div>

          <div className="text-[10px] text-gray-400 italic">
            * Sau khi thanh toán tại VNPay, hệ thống sẽ tự động quay lại đây.
          </div>
        </div>
      ) : (
        !error && <p className="text-gray-400 animate-pulse">Đang tải dữ liệu thanh toán...</p>
      )}

      {error && (
        <div className="w-full p-4 bg-red-50 text-red-600 rounded-lg border border-red-100 text-sm font-medium text-center">
          {error}
        </div>
      )}
    </div>
  );
};

export default PaymentVNPay;