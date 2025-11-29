import React, { createContext, useContext, useState, useCallback, useRef, useEffect } from "react";

type NotificationType = "success" | "error";

interface Notification {
  message: string;
  type: NotificationType;
}

interface NotificationContextType {
  showNotification: (message: string, type?: NotificationType) => void;
}

const NotificationContext = createContext<NotificationContextType | null>(null);

export const useNotification = () => {
  const ctx = useContext(NotificationContext);
  if (!ctx) throw new Error("useNotification phải được dùng bên trong <NotificationProvider>");
  return ctx;
};

// Cấu hình màu sắc và icon cho từng loại
const toastConfig = {
  success: {
    base: "border-emerald-500",
    iconColor: "text-emerald-500",
    icon: "fa-circle-check",
    title: "Thành công"
  },
  error: {
    base: "border-red-500",
    iconColor: "text-red-500",
    icon: "fa-circle-xmark",
    title: "Thất bại"
  }
};

export const NotificationProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [notification, setNotification] = useState<Notification | null>(null);
  const [isVisible, setIsVisible] = useState(false); // State để kích hoạt transition Tailwind

  const showTimer = useRef<number | null>(null);
  const closeTimer = useRef<number | null>(null);

  // Hàm đóng thông báo (có animation trượt ra)
  const closeNotification = useCallback(() => {
    setIsVisible(false); // 1. Kích hoạt class ẩn (translate-x-full)

    // 2. Đợi 300ms cho animation chạy xong rồi mới gỡ khỏi DOM
    if (closeTimer.current) clearTimeout(closeTimer.current);
    closeTimer.current = setTimeout(() => {
      setNotification(null);
    }, 300);
  }, []);

  const showNotification = useCallback((message: string, type: NotificationType = "success") => {
    // Clear timer cũ
    if (showTimer.current) clearTimeout(showTimer.current);
    if (closeTimer.current) clearTimeout(closeTimer.current);

    setNotification({ message, type });

    // Delay nhỏ 10ms để React kịp render, sau đó set Visible = true để kích hoạt animation trượt vào
    setTimeout(() => {
      setIsVisible(true);
    }, 10);

    // Tự động tắt sau 3s
    showTimer.current = setTimeout(() => {
      closeNotification();
    }, 3000);
  }, [closeNotification]);

  // Cleanup khi unmount
  useEffect(() => {
    return () => {
      if (showTimer.current) clearTimeout(showTimer.current);
      if (closeTimer.current) clearTimeout(closeTimer.current);
    };
  }, []);

  return (
    <NotificationContext.Provider value={{ showNotification }}>
      {children}

      <div className="fixed top-24 right-5 z-[100] flex flex-col gap-2 pointer-events-none overflow-hidden p-2">
        {notification && (
          <div
            className={`
              pointer-events-auto flex items-start gap-3 p-4 rounded-lg  bg-white border-l-4 min-w-[300px] max-w-sm
              transform transition-all duration-300 ease-in-out
              ${isVisible
                ? "translate-x-0 opacity-100"   // Hiện: Về vị trí cũ, rõ nét
                : "translate-x-full opacity-0"  // Ẩn: Dịch sang phải, mờ đi
              }
              ${toastConfig[notification.type].base}
            `}
          >
            {/* Icon */}
            <div className={`text-xl mt-0.5 ${toastConfig[notification.type].iconColor}`}>
              <i className={`fa-solid ${toastConfig[notification.type].icon}`}></i>
            </div>

            {/* Content */}
            <div className="flex-1">
              <h4 className={`text-sm font-bold ${toastConfig[notification.type].iconColor}`}>
                {toastConfig[notification.type].title}
              </h4>
              <p className="text-sm text-gray-600 leading-snug">
                {notification.message}
              </p>
            </div>

            {/* Close Button */}
            <button
              onClick={closeNotification}
              className="text-gray-400 hover:text-gray-600 transition-colors"
            >
              <i className="fa-solid fa-xmark"></i>
            </button>
          </div>
        )}
      </div>
    </NotificationContext.Provider>
  );
};