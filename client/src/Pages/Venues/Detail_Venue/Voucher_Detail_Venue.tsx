const Voucher_Detail_Venue = () => {
    return (
      <div className="flex flex-col gap-2">
        <input
          type="text"
          placeholder="Nhập mã voucher..."
          className="flex-1 px-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 outline-none"
        />
  
        <div className="flex gap-2">
          <button
            type="button"
            className="flex-1 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg transition-all"
          >
            Áp dụng
          </button>
  
          <button
            type="button"
            className="flex-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition-all"
          >
            Xóa
          </button>
        </div>
  
        {/* Hiển thị thông tin voucher mẫu */}
        <div className="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
          <p className="text-sm font-semibold text-green-800">
            <i className="fa-solid fa-check-circle mr-2" />
            Mã "SUMMER50" đã được áp dụng
          </p>
          <p className="text-xs text-green-700 mt-1">Giảm: 50%</p>
        </div>
      </div>
    );
  };
  
  export default Voucher_Detail_Venue;
  