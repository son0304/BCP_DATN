// /components/CustomDatePicker.tsx

import React, { useState, useEffect } from 'react';

// Định nghĩa props cho component
interface CustomDatePickerProps {
  isOpen: boolean;
  onClose: () => void;
  onConfirm: (date: Date) => void;
  initialDate: Date | null; // Ngày đang được chọn ban đầu
}

const CustomDatePicker: React.FC<CustomDatePickerProps> = ({
  isOpen,
  onClose,
  onConfirm,
  initialDate,
}) => {
  // --- STATE ---
  // `viewDate` là tháng/năm đang xem (ví dụ: tháng 10 2025)
  const [viewDate, setViewDate] = useState(initialDate || new Date());
  // `selectedDate` là ngày được click chọn tạm thời trong modal
  const [selectedDate, setSelectedDate] = useState(initialDate);

  // --- EFFECT ---
  // Reset state khi modal được mở
  useEffect(() => {
    if (isOpen) {
      const dateToShow = initialDate || new Date();
      setViewDate(dateToShow);
      setSelectedDate(initialDate);
    }
  }, [isOpen, initialDate]);

  // --- LOGIC TẠO LỊCH ---
  const generateCalendarDays = () => {
    const year = viewDate.getFullYear();
    const month = viewDate.getMonth(); // 0 (tháng 1) - 11 (tháng 12)

    const firstDayOfMonth = new Date(year, month, 1);
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    // Lấy T2 (Mon) = 0, CN (Sun) = 6
    const startDayIndex = (firstDayOfMonth.getDay() === 0) ? 6 : firstDayOfMonth.getDay() - 1;

    const days = [];

    // 1. Thêm các ngày "padding" của tháng trước
    const prevMonthEndDate = new Date(year, month, 0).getDate();
    for (let i = startDayIndex; i > 0; i--) {
      days.push({
        date: new Date(year, month - 1, prevMonthEndDate - i + 1),
        isCurrentMonth: false,
      });
    }

    // 2. Thêm các ngày của tháng hiện tại
    for (let i = 1; i <= daysInMonth; i++) {
      days.push({
        date: new Date(year, month, i),
        isCurrentMonth: true,
      });
    }

    // 3. Thêm các ngày "padding" của tháng sau (để lấp đầy 42 ô)
    const remainingSlots = 42 - days.length;
    for (let i = 1; i <= remainingSlots; i++) {
      days.push({
        date: new Date(year, month + 1, i),
        isCurrentMonth: false,
      });
    }

    return days;
  };

  const calendarDays = generateCalendarDays();
  const today = new Date();

  // --- HANDLERS ---
  const handlePrevMonth = () => {
    setViewDate(new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1));
  };

  const handleNextMonth = () => {
    setViewDate(new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1));
  };

  const handleSelectDate = (date: Date) => {
    setSelectedDate(date);
  };

  const handleConfirm = () => {
    if (selectedDate) {
      onConfirm(selectedDate); // Gửi ngày đã chọn ra ngoài
    }
    onClose(); // Đóng modal
  };

  // --- RENDER ---
  if (!isOpen) return null;

  return (
    // Backdrop (lớp mờ)
    <div
      className="fixed inset-0 z-40 flex items-end justify-center bg-black/60 backdrop-blur-sm"
      onClick={onClose} // Đóng khi click ra ngoài
    >
      {/* Modal Content */}
      <div
        className="bg-white w-full max-w-md rounded-t-2xl p-5 shadow-lg"
        onClick={(e) => e.stopPropagation()} // Ngăn click bên trong làm đóng modal
      >
        {/* Header: Tháng/Năm và Nút điều hướng */}
        <div className="flex items-center justify-between mb-4">
          <button
            onClick={handlePrevMonth}
            className="text-gray-600 hover:text-[#2d6a2d] p-2 rounded-full"
          >
            &lt;
          </button>
          <h3 className="font-semibold text-lg text-gray-800 capitalize">
            {viewDate.toLocaleString('vi-VN', { month: 'long', year: 'numeric' })}
          </h3>
          <button
            onClick={handleNextMonth}
            className="text-gray-600 hover:text-[#2d6a2d] p-2 rounded-full"
          >
            &gt;
          </button>
        </div>

        {/* Lưới Lịch */}
        <div>
          {/* Header (T2, T3...) */}
          <div className="grid grid-cols-7 gap-1 text-center text-sm font-medium text-gray-500 mb-2">
            <span>T2</span>
            <span>T3</span>
            <span>T4</span>
            <span>T5</span>
            <span>T6</span>
            <span>T7</span>
            <span>CN</span>
          </div>

          {/* Ngày */}
          <div className="grid grid-cols-7 gap-1">
            {calendarDays.map((day, index) => {
              const isSelected = selectedDate &&
                day.date.toDateString() === selectedDate.toDateString();
                
              const isToday = day.date.toDateString() === today.toDateString();

              // Xác định class cho ngày
              let dayClass = 'h-10 w-10 flex items-center justify-center rounded-full text-sm cursor-pointer';
              
              if (!day.isCurrentMonth) {
                dayClass += ' text-gray-300'; // Ngày tháng trước/sau
              } else if (isSelected) {
                // Đã chọn (giống hình ảnh)
                dayClass += ' bg-[#348738] text-white font-bold'; 
              } else if (isToday) {
                // Hôm nay (giống hình ảnh)
                dayClass += ' text-[#348738] font-semibold border border-[#348738]';
              } else {
                // Ngày bình thường
                dayClass += ' text-gray-700 hover:bg-gray-100';
              }

              return (
                <div
                  key={index}
                  className={dayClass}
                  onClick={() => handleSelectDate(day.date)}
                >
                  {day.date.getDate()}
                </div>
              );
            })}
          </div>
        </div>

        {/* Nút Huỷ và Xác nhận */}
        <div className="flex justify-end gap-4 mt-6">
          <button
            className="px-6 py-2 rounded-lg text-gray-700 font-medium hover:bg-gray-100"
            onClick={onClose}
          >
            Huỷ
          </button>
          <button
            className="px-6 py-2 rounded-lg bg-[#348738] text-white font-medium hover:bg-[#2d6a2d]"
            onClick={handleConfirm}
          >
            Xác nhận
          </button>
        </div>
      </div>
    </div>
  );
};

export default CustomDatePicker;