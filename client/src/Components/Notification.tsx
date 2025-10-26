import React, { createContext, useContext, useState, useCallback } from "react";

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

export const NotificationProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [notification, setNotification] = useState<Notification | null>(null);

  const showNotification = useCallback((message: string, type: NotificationType = "success") => {
    setNotification({ message, type });
    setTimeout(() => setNotification(null), 3000); // tự ẩn sau 3s
  }, []);

  return (
    <NotificationContext.Provider value={{ showNotification }}>
      {children}

      {notification && (
        <div
          key={Date.now()}
          className={`fixed top-24 right-5 z-50 p-4 rounded-lg shadow-xl max-w-sm
            ${notification.type === "success" ? "bg-green-600" : "bg-red-600"} text-white
            animate-slide-in
          `}
        >
          {notification.message}
        </div>
      )}
    </NotificationContext.Provider>
  );
};
