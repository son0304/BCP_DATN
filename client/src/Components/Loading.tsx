import React from "react";

const Loading: React.FC = () => {
  return (
    <div className="fixed inset-0 flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm z-50">
      {/* Vòng xoay */}
      <div className="relative w-16 h-16 mb-4">
        <div className="absolute inset-0 rounded-full border-4 border-[#10B981] border-t-[#F59E0B] animate-spin"></div>
      </div>

      {/* Chữ hiển thị */}
      <p className="text-base md:text-lg font-semibold text-[#111827]">
        Đang tải dữ liệu...
      </p>
    </div>
  );
};

export default Loading;
