// components/Notification.tsx
import React, { useEffect, useState } from 'react';

type NotificationProps = {
  message: string;
  type: 'success' | 'error';
  onClose?: () => void;
};

const Notification: React.FC<NotificationProps> = ({ message, type, onClose }) => {
  const [show, setShow] = useState(true);
  const [slideIn, setSlideIn] = useState(false);

  useEffect(() => {
    // Kích hoạt animation slide-in
    const timer = setTimeout(() => setSlideIn(true), 10);

    // Tự ẩn sau 3 giây
    const autoClose = setTimeout(() => {
      setSlideIn(false);
      setTimeout(() => onClose?.(), 300); // cho animation kết thúc
    }, 3000);

    return () => {
      clearTimeout(timer);
      clearTimeout(autoClose);
    };
  }, [onClose]);

  if (!show) return null;

  const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
  const icon = type === 'success' ? '✔️' : '❌';

  return (
    <div className="fixed top-5 right-5 z-999">
      <div
        className={`
          ${bgColor} text-white px-4 py-3 rounded-lg shadow-lg border border-white/20 flex items-center gap-3
          transform transition-transform duration-300
          ${slideIn ? 'translate-x-0' : 'translate-x-full'}
        `}
      >
        <span className="text-lg">{icon}</span>
        <span className="font-medium">{message}</span>
        <button
          onClick={() => {
            setSlideIn(false);
            setTimeout(() => onClose?.(), 300);
          }}
          className="ml-2 text-white hover:text-gray-200 font-bold"
        >
          ×
        </button>
      </div>
    </div>
  );
};

export default Notification;
