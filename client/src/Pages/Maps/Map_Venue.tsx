import React, { useState, useMemo, useEffect } from "react";
import { MapContainer, TileLayer, Marker, Popup, useMap, useMapEvents } from "react-leaflet";
import "leaflet/dist/leaflet.css";
import L from "leaflet";
import { useFetchData } from "../../Hooks/useApi"; // Đảm bảo đường dẫn đúng
import { Link } from "react-router-dom";

// --- 1. CẤU HÌNH ICON ---
const createIcon = (color: string, size: number, iconClass?: string) =>
  L.divIcon({
    className: "custom-marker",
    html: `<div style="background-color: ${color}; width: ${size}px; height: ${size}px; border-radius: 50%; border: 2.5px solid white; box-shadow: 0 3px 6px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white;">
        ${iconClass ? `<i class="${iconClass}" style="font-size: ${size / 2.5}px"></i>` : ""}
      </div>`,
    iconSize: [size, size],
    iconAnchor: [size / 2, size / 2],
    popupAnchor: [0, -size / 2],
  });

const defaultIcon = createIcon("#10B981", 26, "fa-solid fa-stadium"); // Màu xanh Emerald
const activeIcon = createIcon("#EF4444", 38, "fa-solid fa-location-dot"); // Màu đỏ khi chọn
const userIcon = L.divIcon({
  className: "user-marker",
  html: `<div style="width: 15px; height: 15px; background: #3B82F6; border: 2px solid white; border-radius: 50%; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3);"></div>`, // Icon vị trí người dùng đơn giản hơn
  iconSize: [20, 20],
  iconAnchor: [10, 10],
});

// --- 2. COMPONENT CON: LẤY VỊ TRÍ NGƯỜI DÙNG ---
const LocationMarker = ({ setMapCenter }: { setMapCenter: any }) => {
  const [position, setPosition] = useState<L.LatLng | null>(null);

  const map = useMapEvents({
    locationfound(e) {
      setPosition(e.latlng);
      setMapCenter([e.latlng.lat, e.latlng.lng]);
      map.flyTo(e.latlng, 14);
    },
    locationerror() {
      console.warn("Không thể lấy vị trí GPS");
    },
  });

  // Kích hoạt tìm vị trí ngay khi component mount
  useEffect(() => {
    map.locate();
  }, [map]);

  return position === null ? null : (
    <Marker position={position} icon={userIcon}>
      <Popup>Bạn đang ở đây</Popup>
    </Marker>
  );
};

// --- 3. COMPONENT CON: ĐIỀU KHIỂN CAMERA ---
const MapController = ({ center }: { center: [number, number] }) => {
  const map = useMap();
  useEffect(() => {
    if (center) map.flyTo(center, 15, { duration: 1.5 });
  }, [center, map]);
  return null;
};

// --- 4. COMPONENT CON: NÚT ZOOM/GPS ---
const MapControls = () => {
  const map = useMap();
  return (
    <div className="absolute bottom-8 right-8 z-[1000] flex flex-col gap-3">
      <button
        onClick={() => map.locate()}
        className="w-10 h-10 bg-white rounded-xl shadow-md flex items-center justify-center text-blue-600 hover:bg-blue-50 transition-all border border-gray-200"
        title="Vị trí của tôi"
      >
        <i className="fa-solid fa-crosshairs"></i>
      </button>
      <div className="flex flex-col rounded-xl shadow-md overflow-hidden border border-gray-200">
        <button onClick={() => map.zoomIn()} className="w-10 h-10 bg-white flex items-center justify-center text-gray-600 hover:bg-gray-50 border-b border-gray-100">
          <i className="fa-solid fa-plus"></i>
        </button>
        <button onClick={() => map.zoomOut()} className="w-10 h-10 bg-white flex items-center justify-center text-gray-600 hover:bg-gray-50">
          <i className="fa-solid fa-minus"></i>
        </button>
      </div>
    </div>
  );
};

// --- 5. COMPONENT CHÍNH ---
const Map_Venue: React.FC = () => {
  // State
  const [selectedVenue, setSelectedVenue] = useState<any>(null);
  const [mapCenter, setMapCenter] = useState<[number, number]>([21.0285, 105.8542]); // Mặc định Hà Nội
  const [activeCategory, setActiveCategory] = useState("Tất cả");

  // Fetch Data
  const { data: apiResponse, isLoading } = useFetchData<any>('venues');

  // --- LOGIC XỬ LÝ DỮ LIỆU QUAN TRỌNG ---
  const allVenues = useMemo(() => {
    // API Response: { success: true, data: { current_page: 1, data: [ARRAY_HERE] } }
    // Cần chọc sâu 2 lớp .data để lấy mảng sân
    return apiResponse?.data?.data || [];
  }, [apiResponse]);

  // Lọc dữ liệu
  const filteredVenues = useMemo(() => {
    if (activeCategory === "Tất cả") return allVenues;
    return allVenues.filter((v: any) =>
      v.venue_types?.some((t: any) => t.name === activeCategory)
    );
  }, [allVenues, activeCategory]);

  // Helper: Chọn sân
  const handleSelectVenue = (venue: any) => {
    if (venue.lat && venue.lng) {
      setSelectedVenue(venue);
      setMapCenter([Number(venue.lat), Number(venue.lng)]);
    }
  };

  // Helper: Lấy ảnh thumbnail
  const getVenueThumbnail = (images: any[]) => {
    if (!images || images.length === 0) return "https://via.placeholder.com/150?text=No+Image";
    const primary = images.find(img => img.is_primary === 1);
    return primary ? primary.url : images[0].url;
  };

  // Loading State
  if (isLoading) return (
    <div className="h-[600px] w-full flex items-center justify-center bg-gray-50">
      <div className="flex flex-col items-center gap-3">
        <i className="fa-solid fa-circle-notch animate-spin text-3xl text-emerald-500"></i>
        <span className="text-gray-500 font-medium text-sm">Đang tải bản đồ...</span>
      </div>
    </div>
  );

  return (
    <div className="w-full bg-[#F8FAFC] py-10 px-4 flex justify-center font-sans">
      <div className="w-full max-w-7xl h-[700px] bg-white rounded-[24px] shadow-xl border border-gray-200 flex flex-col lg:flex-row overflow-hidden">

        {/* === CỘT TRÁI: DANH SÁCH (SIDEBAR) === */}
        <div className="w-full lg:w-[400px] flex flex-col bg-white border-r border-gray-100 z-10 h-full">

          {/* Header & Filter */}
          <div className="p-5 border-b border-gray-100 bg-white shadow-sm z-10">
            <h2 className="text-xl font-black text-gray-800 flex items-center gap-2 mb-4">
              <i className="fa-solid fa-map-location-dot text-emerald-500"></i>
              Tìm sân quanh đây
            </h2>
            <div className="flex gap-2 overflow-x-auto pb-2 no-scrollbar">
              {['Tất cả', 'Cầu lông', 'Bóng đá', 'Pickleball'].map(tag => (
                <button
                  key={tag}
                  onClick={() => setActiveCategory(tag)}
                  className={`whitespace-nowrap px-4 py-2 text-[11px] font-bold uppercase tracking-wide rounded-full transition-all border ${activeCategory === tag
                    ? "bg-emerald-500 text-white border-emerald-500 shadow-md shadow-emerald-200"
                    : "bg-white text-gray-500 border-gray-200 hover:bg-gray-50"
                    }`}
                >
                  {tag}
                </button>
              ))}
            </div>
          </div>

          {/* List Items */}
          <div className="flex-1 overflow-y-auto p-3 space-y-3 bg-gray-50">
            {filteredVenues.length > 0 ? (
              filteredVenues.map((venue: any) => (
                <div
                  key={venue.id}
                  onClick={() => handleSelectVenue(venue)}
                  className={`flex gap-3 p-3 bg-white rounded-xl border cursor-pointer transition-all duration-200 hover:shadow-md ${selectedVenue?.id === venue.id
                    ? "border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/10"
                    : "border-gray-100"
                    }`}
                >
                  {/* Ảnh nhỏ */}
                  <img
                    src={getVenueThumbnail(venue.images)}
                    className="w-20 h-20 rounded-lg object-cover bg-gray-200 flex-shrink-0"
                    alt={venue.name}
                  />

                  {/* Thông tin */}
                  <div className="flex flex-col justify-center min-w-0">
                    <h3 className={`text-sm font-bold truncate ${selectedVenue?.id === venue.id ? "text-emerald-600" : "text-gray-800"}`}>
                      {venue.name}
                    </h3>
                    <p className="text-[11px] text-gray-500 mt-1 line-clamp-2 leading-relaxed">
                      <i className="fa-solid fa-location-dot text-[10px] mr-1 opacity-70"></i>
                      {venue.address_detail}
                    </p>
                    <div className="flex flex-wrap gap-1 mt-2">
                      {venue.venue_types?.slice(0, 2).map((t: any) => (
                        <span key={t.id} className="text-[9px] font-bold bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded border border-gray-200">
                          {t.name}
                        </span>
                      ))}
                    </div>
                  </div>
                </div>
              ))
            ) : (
              <div className="text-center py-10 text-gray-400 text-sm">
                Không tìm thấy sân nào.
              </div>
            )}
          </div>
        </div>

        {/* === CỘT PHẢI: BẢN ĐỒ (MAP) === */}
        <div className="flex-1 h-full relative z-0 bg-slate-100">
          <MapContainer center={mapCenter} zoom={13} className="w-full h-full outline-none" zoomControl={false}>
            <TileLayer
              attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>'
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />

            <MapController center={mapCenter} />
            <LocationMarker setMapCenter={setMapCenter} />

            {/* Render Markers */}
            {filteredVenues.map((venue: any) => {
              // Chỉ render nếu có tọa độ
              if (!venue.lat || !venue.lng) return null;

              return (
                <Marker
                  key={venue.id}
                  position={[Number(venue.lat), Number(venue.lng)]}
                  icon={selectedVenue?.id === venue.id ? activeIcon : defaultIcon}
                  eventHandlers={{
                    click: () => handleSelectVenue(venue)
                  }}
                >
                  <Popup offset={[0, -10]} className="custom-popup">
                    <div className="w-[200px] overflow-hidden">
                      <div className="relative h-28 w-full">
                        <img
                          src={getVenueThumbnail(venue.images)}
                          className="w-full h-full object-cover rounded-t-lg"
                          alt=""
                        />
                        <div className="absolute top-2 right-2 bg-white/90 backdrop-blur px-2 py-0.5 rounded text-[10px] font-bold shadow-sm">
                          <i className="fa-solid fa-star text-amber-400 mr-1"></i>
                          {Number(venue.reviews_avg_rating || 5).toFixed(1)}
                        </div>
                      </div>
                      <div className="p-3">
                        <h3 className="text-sm font-bold text-gray-800 line-clamp-1">{venue.name}</h3>
                        <p className="text-[10px] text-gray-500 mt-1 truncate">{venue.address_detail}</p>
                        <Link to={`/venues/${venue.id}`} className="block mt-3">
                          <button className="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-[10px] font-bold py-2 rounded-md transition-colors uppercase tracking-wider">
                            Đặt sân ngay
                          </button>
                        </Link>
                      </div>
                    </div>
                  </Popup>
                </Marker>
              );
            })}

            <MapControls />
          </MapContainer>
        </div>
      </div>
    </div>
  );
};

export default Map_Venue;