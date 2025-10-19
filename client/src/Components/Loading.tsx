// /components/BCP_Loading.tsx

import React from 'react';

const Loading: React.FC = () => {
  return (
    <>
      {/* Animation 'wave' (sóng vỗ) giữ nguyên */}
      <style>
        {`
          @keyframes wave {
            0%, 60%, 100% {
              transform: translateY(0);
            }
            30% {
              transform: translateY(-20px); /* Độ cao nảy lên */
            }
          }
        `}
      </style>

      {/* --- ĐỔI MÀU NỀN ---
        Đổi từ 'bg-white/70' sang 'bg-[#2d6a2d]/70' (nền xanh lá đậm, mờ)
      */}
      <div className="fixed inset-0 z-50 flex items-center justify-center bg-[#2d6a2d]/70 backdrop-blur-sm">
        
        {/* Container cho chữ BCP */}
        <div className="flex items-center justify-center space-x-2">
          
          {/* --- ĐỔI MÀU CHỮ ---
            Đổi từ 'text-[#2d6a2d]' sang 'text-orange-500'
          */}
          <span className="text-6xl font-extrabold text-orange-500 animate-[wave_1.5s_ease-in-out_infinite]">
            B
          </span>
          
          {/* --- ĐỔI MÀU CHỮ ---
            Đổi từ 'text-[#348738]' sang 'text-orange-400' (sáng hơn 1 chút)
          */}
          <span className="text-6xl font-extrabold text-orange-400 animate-[wave_1.5s_ease-in-out_infinite] [animation-delay:0.2s]">
            C
          </span>
          
          {/* --- ĐỔI MÀU CHỮ ---
            Đổi từ 'text-[#2d6a2d]' sang 'text-orange-500'
          */}
          <span className="text-6xl font-extrabold text-orange-500 animate-[wave_1.5s_ease-in-out_infinite] [animation-delay:0.4s]">
            P
          </span>

        </div>
      </div>
    </>
  );
};

export default Loading;