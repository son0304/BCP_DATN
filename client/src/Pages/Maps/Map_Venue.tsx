import React, { useState } from "react";
import { MapContainer, TileLayer, Marker, Popup, useMap } from "react-leaflet";
import "leaflet/dist/leaflet.css";
import L from "leaflet";

// --- 1. CONFIG ICON ---
const defaultIcon = L.divIcon({
  className: "custom-pin",
  html: `<div style="background-color: #10B981; width: 24px; height: 24px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>`,
  iconSize: [24, 24],
  iconAnchor: [12, 12],
});

const activeIcon = L.divIcon({
  className: "custom-pin-active",
  html: `<div style="background-color: #EF4444; width: 36px; height: 36px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-size: 14px;"><i class="fa-solid fa-location-dot"></i></div>`,
  iconSize: [36, 36],
  iconAnchor: [18, 18],
  popupAnchor: [0, -20],
});

// --- 2. FAKE DATA ---
const FAKE_VENUES = [
  { id: 1, name: "Sân Bóng Đá Kỳ Hòa", address: "824 Sư Vạn Hạnh, Q.10", price: 300000, rating: 4.5, image: "https://images.unsplash.com/photo-1575361204480-aadea25e6e68?auto=format&fit=crop&w=300&q=80", lat: 10.7781, lng: 106.6665, type: "Bóng đá" },
  { id: 2, name: "CLB Cầu Lông 18", address: "18 Cộng Hòa, Tân Bình", price: 120000, rating: 4.8, image: "https://images.unsplash.com/photo-1626224583764-84764d622398?auto=format&fit=crop&w=300&q=80", lat: 10.8016, lng: 106.6533, type: "Cầu lông" },
  { id: 3, name: "Sân Tennis Hồ Xuân Hương", address: "2 Hồ Xuân Hương, Q.3", price: 250000, rating: 4.2, image: "https://images.unsplash.com/photo-1595435934249-5df7ed86e1c0?auto=format&fit=crop&w=300&q=80", lat: 10.7765, lng: 106.6912, type: "Tennis" },
  { id: 4, name: "Pickleball Center Saigon", address: "102 Nguyễn Du, Q.1", price: 180000, rating: 5.0, image: "https://images.unsplash.com/photo-1563206767-5b1d972b9fb1?auto=format&fit=crop&w=300&q=80", lat: 10.7745, lng: 106.6985, type: "Pickleball" },
  { id: 5, name: "Sân Bóng Rổ Hoa Lư", address: "2 Đinh Tiên Hoàng, Q.1", price: 150000, rating: 4.6, image: "https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=300&q=80", lat: 10.7892, lng: 106.7003, type: "Bóng rổ" }
];

const FlyToLocation = ({ center }: { center: [number, number] }) => {
  const map = useMap();
  map.flyTo(center, 14, { duration: 1.5 });
  return null;
};

// --- 3. MAIN COMPONENT ---
const Map_Venue = () => {
  const [selectedVenue, setSelectedVenue] = useState<any>(null);
  const [mapCenter, setMapCenter] = useState<[number, number]>([10.7765, 106.6912]);

  const handleSelectVenue = (venue: any) => {
    setSelectedVenue(venue);
    setMapCenter([venue.lat, venue.lng]);
  };

  return (
    // Wrapper ngoài cùng để tạo khoảng cách với Header/Footer
    <div className="w-full bg-[#F8FAFC] py-10 px-4 font-sans flex justify-center">
      
      {/* 
          CONTAINER CHÍNH:
          - max-w-7xl: Giới hạn chiều rộng (khoảng 1280px) để không bị bè ra 2 bên.
          - h-[650px]: Chiều cao cố định, vừa tầm mắt.
          - mx-auto: Căn giữa màn hình.
      */}
      <div className="w-full max-w-7xl h-[600px] md:h-[650px] bg-white rounded-3xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col lg:flex-row">
        
        {/* === LEFT: LIST (35% Width) === */}
        <div className="w-full lg:w-[35%] flex flex-col border-r border-gray-200 bg-white z-10 relative">
          
          {/* Header List */}
          <div className="p-5 border-b border-gray-100 bg-white shadow-sm z-20">
            <h2 className="text-xl font-extrabold text-gray-800 flex items-center gap-2">
              <i className="fa-solid fa-map-location-dot text-emerald-600"></i> Tìm sân gần bạn
            </h2>
            <div className="flex gap-2 mt-3 overflow-x-auto no-scrollbar pb-1">
               {['Tất cả', 'Bóng đá', 'Cầu lông', 'Tennis'].map(tag => (
                  <button key={tag} className="px-3 py-1 bg-gray-50 border border-gray-200 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 text-xs font-bold rounded-lg text-gray-600 whitespace-nowrap transition">
                     {tag}
                  </button>
               ))}
            </div>
          </div>
          
          {/* List Items (Scrollable) */}
          <div className="flex-1 overflow-y-auto p-3 space-y-3 bg-[#F9FAFB] custom-scrollbar">
            {FAKE_VENUES.map((venue) => (
              <div
                key={venue.id}
                onClick={() => handleSelectVenue(venue)}
                className={`group flex gap-3 p-3 bg-white rounded-xl border cursor-pointer transition-all duration-200 ${
                  selectedVenue?.id === venue.id 
                    ? "border-emerald-500 ring-1 ring-emerald-500 shadow-md bg-emerald-50/10" 
                    : "border-gray-100 hover:border-emerald-300 hover:shadow-sm"
                }`}
              >
                <div className="relative w-24 h-20 rounded-lg overflow-hidden flex-shrink-0">
                   <img src={venue.image} alt="venue" className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                </div>

                <div className="flex flex-col justify-between w-full py-0.5">
                  <div>
                    <h3 className="text-sm font-bold text-gray-800 line-clamp-1 group-hover:text-emerald-700">
                      {venue.name}
                    </h3>
                    <p className="text-xs text-gray-500 line-clamp-1 mt-0.5">
                      <i className="fa-solid fa-location-dot text-gray-400 mr-1"></i>{venue.address}
                    </p>
                  </div>
                  
                  <div className="flex justify-between items-end mt-1">
                     <span className="text-[10px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-bold uppercase">{venue.type}</span>
                     <span className="text-sm font-extrabold text-amber-500">{venue.price.toLocaleString()}đ</span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* === RIGHT: MAP (65% Width) === */}
        <div className="flex-1 h-full relative bg-gray-100 z-0">
          <MapContainer 
            center={mapCenter} 
            zoom={13} 
            scrollWheelZoom={true} 
            className="w-full h-full"
            zoomControl={false}
          >
            <TileLayer
              attribution='&copy; OpenStreetMap'
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            <FlyToLocation center={mapCenter} />

            {FAKE_VENUES.map((venue) => (
              <Marker 
                key={venue.id} 
                position={[venue.lat, venue.lng]} 
                icon={selectedVenue?.id === venue.id ? activeIcon : defaultIcon}
                eventHandlers={{ click: () => handleSelectVenue(venue) }}
              >
                <Popup className="custom-popup-clean" closeButton={false} offset={[0, -20]}>
                  <div className="w-48 font-sans p-1">
                    <div className="relative h-28 rounded-lg overflow-hidden mb-2">
                       <img src={venue.image} className="w-full h-full object-cover" alt="popup" />
                       <span className="absolute top-2 right-2 bg-white/90 px-1.5 rounded text-[10px] font-bold shadow flex items-center gap-1">
                          {venue.rating} <i className="fa-solid fa-star text-amber-400"></i>
                       </span>
                    </div>
                    <h3 className="text-sm font-bold text-gray-800 line-clamp-1">{venue.name}</h3>
                    <p className="text-[10px] text-gray-500 mt-0.5">{venue.address}</p>
                    <button className="w-full mt-2 bg-emerald-600 text-white text-[10px] font-bold py-1.5 rounded hover:bg-emerald-700 transition">
                       Đặt sân ngay
                    </button>
                  </div>
                </Popup>
              </Marker>
            ))}
          </MapContainer>
          
          {/* Zoom Controls Custom */}
          <div className="absolute bottom-6 right-6 z-[400] flex flex-col gap-2">
             <button className="w-9 h-9 bg-white rounded-lg shadow-md flex items-center justify-center text-gray-600 hover:text-emerald-600 transition" title="Phóng to">
                <i className="fa-solid fa-plus"></i>
             </button>
             <button className="w-9 h-9 bg-white rounded-lg shadow-md flex items-center justify-center text-gray-600 hover:text-emerald-600 transition" title="Thu nhỏ">
                <i className="fa-solid fa-minus"></i>
             </button>
          </div>
        </div>

      </div>
    </div>
  );
};

export default Map_Venue;