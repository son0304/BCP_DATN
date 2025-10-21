// /components/BCP_Loading.tsx

import React from 'react';

const Loading: React.FC = () => {
  return (
    <div className="bg-gray-100 rounded-2xl shadow-lg overflow-hidden animate-pulse">
      {/* Ảnh giả */}
      <div className="w-full h-56 bg-gray-200"></div>
      <div className="p-5">
        {/* Các dòng text giả */}
        <div className="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
        <div className="h-6 bg-gray-300 rounded w-1/2 mb-4"></div>
        <div className="h-4 bg-gray-300 rounded w-full mb-6"></div>
        {/* Nút giả */}
        <div className="h-10 bg-gray-300 rounded-lg w-full"></div>
      </div>
    </div>
  );
};

export default Loading;