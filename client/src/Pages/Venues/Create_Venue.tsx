// src/Components/Venue/Create_Venue.tsx
import React from "react";
import { usePostData } from "../../Hooks/useApi";

const Create_Venue = () => {
  const { mutate, isPending, isSuccess, isError, error } = usePostData("venues");

  const fixedData = {
    owner_id: 1,
    name: "Sân bóng đá Phú Thọ",
    address_detail: "123 Lê Duẩn, Quận 10, TP.HCM",
    district_id: 1,
    province_id: 1,
    lat: 10.776,
    lng: 106.700,
    phone: "0901234567",
    is_active: true,
  };

  const handleCreate = () => {
    mutate(fixedData);
  };

  return (
    <div className="max-w-md mx-auto mt-10 bg-white p-6 rounded-xl shadow-md text-center">
      <h2 className="text-xl font-bold mb-4 text-gray-800">🧪 Test Tạo Venue</h2>

      <button
        onClick={handleCreate}
        disabled={isPending}
        className="bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2 rounded-lg transition"
      >
        {isPending ? "⏳ Đang gửi..." : "🚀 Gửi Request Test"}
      </button>

      {isSuccess && (
        <p className="text-green-600 font-semibold mt-4">
          ✅ Tạo sân thành công!
        </p>
      )}

      {isError && (
        <p className="text-red-500 font-medium mt-4">
          ❌ Lỗi khi tạo sân:{" "}
          {error instanceof Error ? error.message : "Không rõ lỗi"}
        </p>
      )}
    </div>
  );
};

export default Create_Venue;
