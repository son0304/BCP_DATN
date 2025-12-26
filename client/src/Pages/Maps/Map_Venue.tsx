import React, { useState, useMemo, useEffect } from "react";
import { MapContainer, TileLayer, Marker, Popup, useMap, useMapEvents } from "react-leaflet";
import "leaflet/dist/leaflet.css";
import L from "leaflet";
import { useFetchData } from "../../Hooks/useApi";
import { Link } from "react-router-dom";

// --- ICON CONFIG ---
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

const defaultIcon = createIcon("#10B981", 26, "fa-solid fa-stadium");
const activeIcon = createIcon("#EF4444", 38, "fa-solid fa-location-dot");
const userIcon = L.divIcon({
  className: "user-marker",
  html: `<div class="user-location-pulse"></div>`,
  iconSize: [20, 20],
  iconAnchor: [10, 10],
});

// --- COMPONENT CON: XỬ LÝ ĐỊNH VỊ ---
const LocationMarker = ({ setMapCenter }: { setMapCenter: any }) => {
  const [position, setPosition] = useState<L.LatLng | null>(null);
  
  const map = useMapEvents({
    locationfound(e) {
      setPosition(e.latlng);
      setMapCenter([e.latlng.lat, e.latlng.lng]);
      map.flyTo(e.latlng, 15); // Tự động bay tới vị trí người dùng
    },
    locationerror() {
      alert("Không thể truy cập vị trí của bạn. Hãy kiểm tra quyền GPS trên trình duyệt.");
    },
  });

  return position === null ? null : (
    <Marker position={position} icon={userIcon}>
      <Popup>Bạn đang ở đây!</Popup>
    </Marker>
  );
};

// --- COMPONENT CON: ĐIỀU KHIỂN CAMERA ---
const MapController = ({ center }: { center: [number, number] }) => {
  const map = useMap();
  useEffect(() => {
    map.flyTo(center, map.getZoom(), { duration: 1.5 });
  }, [center]);
  return null;
};

// --- COMPONENT CHÍNH ---
const Map_Venue: React.FC = () => {
  const [selectedVenue, setSelectedVenue] = useState<any>(null);
  const [mapCenter, setMapCenter] = useState<[number, number]>([21.0377, 105.7750]);
  const [activeCategory, setActiveCategory] = useState("Tất cả");

  const { data, isLoading, isError } = useFetchData<any>('venues');
  const allVenues = useMemo(() => data?.data || [], [data]);

  const filteredVenues = useMemo(() => {
    if (activeCategory === "Tất cả") return allVenues;
    return allVenues.filter((v: any) => v.venue_types?.some((t: any) => t.name === activeCategory));
  }, [allVenues, activeCategory]);

  const handleSelectVenue = (venue: any) => {
    setSelectedVenue(venue);
    setMapCenter([Number(venue.lat), Number(venue.lng)]);
  };

  const getVenueThumbnail = (images: any[]) => {
    if (!images || images.length === 0) return "https://via.placeholder.com/300";
    const primary = images.find(img => img.is_primary === 1);
    return primary ? primary.url : images[0].url;
  };

  if (isLoading) return <div className="h-[600px] flex items-center justify-center font-bold">Đang tải...</div>;

  return (
    <div className="w-full bg-[#F1F5F9] py-10 px-4 flex justify-center">
      <div className="w-full max-w-7xl h-[650px] bg-white rounded-[2rem] shadow-2xl flex flex-col lg:flex-row overflow-hidden border border-gray-100">

        {/* CỘT TRÁI: DANH SÁCH */}
        <div className="w-full lg:w-[38%] flex flex-col border-r bg-white z-10">
          <div className="p-6 border-b">
            <h2 className="text-2xl font-black text-gray-800 flex items-center gap-2">
              <i className="fa-solid fa-map-location-dot text-emerald-600"></i> Tìm sân gần bạn
            </h2>
            <div className="flex gap-2 mt-4 overflow-x-auto no-scrollbar">
              {['Tất cả', 'Cầu lông', 'Bóng đá', 'Pickleball'].map(tag => (
                <button
                  key={tag}
                  onClick={() => setActiveCategory(tag)}
                  className={`px-4 py-2 border text-xs font-black rounded-xl transition-all ${
                    activeCategory === tag ? "bg-emerald-600 text-white shadow-lg" : "bg-gray-50 text-gray-500 hover:bg-emerald-50"
                  }`}
                >
                  {tag}
                </button>
              ))}
            </div>
          </div>

          <div className="flex-1 overflow-y-auto p-4 space-y-4 bg-[#F8FAFC]">
            {filteredVenues.map((venue: any) => (
              <div
                key={venue.id}
                onClick={() => handleSelectVenue(venue)}
                className={`flex gap-4 p-4 bg-white rounded-2xl border cursor-pointer transition-all duration-300 ${
                  selectedVenue?.id === venue.id ? "border-emerald-500 ring-4 ring-emerald-500/10 shadow-lg scale-[1.02]" : "border-gray-100"
                }`}
              >
                <img src={getVenueThumbnail(venue.images)} className="w-24 h-24 rounded-2xl object-cover shadow-sm" alt="" />
                <div className="flex flex-col justify-between py-1 flex-1">
                  <h3 className="text-sm font-black text-gray-800">{venue.name}</h3>
                  <p className="text-[11px] text-gray-500 line-clamp-2"><i className="fa-solid fa-location-dot text-emerald-500 mr-1"></i>{venue.address_detail}</p>
                  <div className="flex gap-1 mt-2">
                    {venue.venue_types?.map((t: any) => (
                      <span key={t.id} className="text-[9px] bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-md font-bold border border-emerald-100 uppercase">{t.name}</span>
                    ))}
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* CỘT PHẢI: BẢN ĐỒ */}
        <div className="flex-1 h-full relative z-0">
          <MapContainer center={mapCenter} zoom={14} className="w-full h-full" zoomControl={false}>
            <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
            
            <MapController center={mapCenter} />
            
            {/* --- XỬ LÝ VỊ TRÍ NGƯỜI DÙNG Ở ĐÂY --- */}
            <LocationMarker setMapCenter={setMapCenter} />

            {filteredVenues.map((venue: any) => (
              <Marker
                key={venue.id}
                position={[Number(venue.lat), Number(venue.lng)]}
                icon={selectedVenue?.id === venue.id ? activeIcon : defaultIcon}
                eventHandlers={{ click: () => handleSelectVenue(venue) }}
              >
                <Popup offset={[0, -10]}>
                  <div className="w-44 p-1">
                    <img src={getVenueThumbnail(venue.images)} className="w-full h-24 object-cover rounded-lg mb-2 shadow-sm" alt="" />
                    <h3 className="text-xs font-bold">{venue.name}</h3>
                    <Link to={`/venues/${venue.id}`} className="block mt-2">
                      <button className="w-full bg-emerald-600 text-white text-[10px] font-bold py-2 rounded-lg">XEM CHI TIẾT</button>
                    </Link>
                  </div>
                </Popup>
              </Marker>
            ))}

            <MapControls />
          </MapContainer>
        </div>
      </div>
    </div>
  );
};

// --- NÚT ĐIỀU KHIỂN ---
const MapControls = () => {
  const map = useMap();
  
  const handleLocate = () => {
    map.locate(); // Kích hoạt sự kiện locationfound của Leaflet
  };

  return (
    <div className="absolute bottom-8 right-8 z-[1000] flex flex-col gap-3">
      {/* NÚT GPS */}
      <button 
        onClick={handleLocate}
        className="w-12 h-12 bg-white rounded-2xl shadow-2xl flex items-center justify-center text-blue-600 hover:bg-blue-50 transition-all active:scale-90 border border-gray-100"
      >
        <i className="fa-solid fa-location-crosshairs text-xl"></i>
      </button>

      <button onClick={() => map.zoomIn()} className="w-12 h-12 bg-white rounded-2xl shadow-xl flex items-center justify-center text-gray-600 hover:text-emerald-600"><i className="fa-solid fa-plus text-lg"></i></button>
      <button onClick={() => map.zoomOut()} className="w-12 h-12 bg-white rounded-2xl shadow-xl flex items-center justify-center text-gray-600 hover:text-emerald-600"><i className="fa-solid fa-minus text-lg"></i></button>
    </div>
  );
};

export default Map_Venue;