// Completed React components for RelatedVenue and HorizontalVenueList
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useFetchData } from "../../Hooks/useApi";
import type { Venue } from "../../Types/venue";
import type { Image } from "../../Types/image";

interface VenuesProps {
    limit?: number;
    refreshKey: number;
    onRefresh: () => void;
    currentVenueId: number;
}

interface RelatedVenueProps {
    currentVenueId: number;
}

const HorizontalVenueList = ({ limit = 3, refreshKey, onRefresh, currentVenueId }: VenuesProps) => {
    const navigate = useNavigate();

    const offset = refreshKey * limit;
    const apiEndpoint = `venues?limit=${limit}&offset=${offset}`;

    const { data: venueData, isLoading, isError } = useFetchData<Venue[]>(apiEndpoint);

    if (isError)
        return <p className="text-center text-red-500 py-4">Đã xảy ra lỗi khi tải dữ liệu sân lân cận!</p>;

    const venues: Venue[] = ((venueData?.data as Venue[]) || []).filter(v => v.id !== currentVenueId);
    const displayedVenues = venues.slice(0, limit);

    const handleVenueNavigation = (venueId: number) => {
        onRefresh();
        navigate(`/venues/${venueId}`);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    return (
        <div className="w-full">
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                {isLoading ? (
                    Array.from({ length: limit }).map((_, i) => (
                        <div key={i} className="bg-white rounded-xl shadow-md animate-pulse border border-gray-200">
                            <div className="w-full h-24 sm:h-32 bg-gray-200"></div>
                            <div className="p-3 space-y-2">
                                <div className="h-4 bg-gray-300 w-3/4 rounded"></div>
                                <div className="h-3 bg-gray-200 w-2/3 rounded"></div>
                            </div>
                        </div>
                    ))
                ) : displayedVenues.length > 0 ? (
                    displayedVenues.map((venue) => {
                        const primaryImage = venue.images?.find((img: Image) => img.is_primary === 1);

                        return (
                            <div
                                key={venue.id}
                                className="bg-white rounded-xl border border-gray-200 overflow-hidden transition-transform duration-300 hover:-translate-y-1 hover:shadow-lg flex flex-col cursor-pointer"
                                onClick={() => handleVenueNavigation(venue.id)}
                            >
                                <div className="relative">
                                    <img
                                        src={primaryImage?.url || "https://via.placeholder.com/400x300?text=BCP+Sports"}
                                        alt={venue.name}
                                        className="w-full h-24 sm:h-32 object-cover"
                                    />
                                    <div className="absolute top-0 right-0 bg-[#10B981] text-white px-2 py-1 rounded-bl-md flex items-center gap-1 shadow-md text-xs">
                                        <i className="fa-solid fa-star text-yellow-400"></i>
                                        <span>{Number(venue.reviews_avg_rating)?.toFixed(1) || "0.0"}</span>
                                    </div>
                                    <div className="absolute bottom-0 left-0 bg-[#10B981] text-white px-2 py-1 rounded-tr-md flex items-center gap-1 shadow-md text-xs">
                                        <i className="fa-regular fa-clock text-white mr-1"></i>
                                        <span>{venue.start_time?.slice(0, 5)} - {venue.end_time?.slice(0, 5)}</span>
                                    </div>
                                </div>
                                <div className="p-3 flex-1 flex flex-col">
                                    <h3 className="text-sm font-semibold text-[#11182C] mb-1 line-clamp-1">{venue.name}</h3>
                                    <div className="flex items-start text-xs text-gray-600">
                                        <i className="fa-solid fa-location-dot text-[#10B981] mt-0.5 mr-1 flex-shrink-0"></i>
                                        <span className="line-clamp-2">{venue.address_detail}</span>
                                    </div>
                                    <div className="flex flex-wrap gap-1 mt-2">
                                        {venue.venue_types?.length ? (
                                            venue.venue_types.map((type, i) => (
                                                <span key={i} className="text-[10px] bg-[#D1FAE5] text-[#065F46] px-1.5 py-0.5 rounded-full font-medium line-clamp-1">
                                                    {type.name}
                                                </span>
                                            ))
                                        ) : (
                                            <span className="text-[11px] text-gray-500 italic">Chưa có loại hình</span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        );
                    })
                ) : (
                    <p className="col-span-full text-center text-gray-500 italic py-4">Không có sân lân cận nào được tìm thấy.</p>
                )}
            </div>
        </div>
    );
};

export default function RelatedVenue({ currentVenueId }: RelatedVenueProps) {
    const [refreshKey, setRefreshKey] = useState(0);

    const refreshVenues = () => {
        setRefreshKey((prev) => prev + 1);
    };

    return (
        <div className="w-full bg-[#F9FAFB] py-4">
            <HorizontalVenueList
                limit={3}
                refreshKey={refreshKey}
                onRefresh={refreshVenues}
                currentVenueId={currentVenueId}
                key={refreshKey}
            />
        </div>
    );
}