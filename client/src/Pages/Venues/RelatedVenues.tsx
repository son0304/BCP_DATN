// Fixed RelatedVenue component - proper data handling
// Fixed RelatedVenue component - proper data handling
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useFetchData } from "../../Hooks/useApi";
import type { Venue } from "../../Types/venue";
import type { Image } from "../../Types/image";

interface RelatedVenueProps {
    currentVenueId: number;
    limit?: number;
    limit?: number;
}

const RelatedVenue = ({ currentVenueId, limit = 3 }: RelatedVenueProps) => {
const RelatedVenue = ({ currentVenueId, limit = 3 }: RelatedVenueProps) => {
    const navigate = useNavigate();
    const [refreshKey, setRefreshKey] = useState(0);
    const [refreshKey, setRefreshKey] = useState(0);

    const offset = refreshKey * limit;
    const apiEndpoint = `venues?limit=${limit + 1}&offset=${offset}`;
    const apiEndpoint = `venues?limit=${limit + 1}&offset=${offset}`;

    const { data: venueData, isLoading, isError } = useFetchData<any>(apiEndpoint);

    console.log("Raw venueData:", venueData); // Debug log

    // Safe data extraction - handle all possible response structures
    const getVenuesArray = (): Venue[] => {
        try {
            if (!venueData) return [];
            
            // Try different possible data structures
            let venuesArray: any = null;
            
            if (Array.isArray(venueData)) {
                venuesArray = venueData;
            } else if (venueData.data) {
                if (Array.isArray(venueData.data)) {
                    venuesArray = venueData.data;
                } else if (venueData.data.data && Array.isArray(venueData.data.data)) {
                    venuesArray = venueData.data.data;
                }
            }
            
            // Ensure we have an array
            if (!Array.isArray(venuesArray)) {
                console.warn("venuesArray is not an array:", venuesArray);
                return [];
            }
            
            return venuesArray;
        } catch (error) {
            console.error("Error parsing venue data:", error);
            return [];
        }
    };

    const allVenues = getVenuesArray();
    
    // Filter out current venue and limit results - with safety checks
    const venues = allVenues
        .filter((v: any) => {
            if (!v || typeof v !== 'object') return false;
            if (!v.id) return false;
            return v.id !== currentVenueId;
        })
        .slice(0, limit);

    console.log("Filtered venues:", venues); // Debug log

    const handleVenueNavigation = (venueId: number) => {
        try {
            navigate(`/venues/${venueId}`);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (error) {
            console.error("Navigation error:", error);
        }
    };

    const handleRefresh = () => {
        setRefreshKey((prev) => prev + 1);
    };

    // Error state
    if (isError) {
        return (
            <div className="text-center text-red-500 text-xs py-4">
                <p>Không thể tải dữ liệu sân.</p>
            </div>
        );
    }
        try {
            navigate(`/venues/${venueId}`);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (error) {
            console.error("Navigation error:", error);
        }
    };

    const handleRefresh = () => {
        setRefreshKey((prev) => prev + 1);
    };

    // Error state
    if (isError) {
        return (
            <div className="text-center text-red-500 text-xs py-4">
                <p>Không thể tải dữ liệu sân.</p>
            </div>
        );
    }

    return (
        <div className="w-full">
            {/* Vertical list of venues */}
            <div className="space-y-4">
            {/* Vertical list of venues */}
            <div className="space-y-4">
                {isLoading ? (
                    // Loading skeletons
                    Array.from({ length: 3 }).map((_, i) => (
                        <div key={`skeleton-${i}`} className="flex gap-3 animate-pulse">
                            <div className="w-20 h-20 bg-gray-200 rounded-md flex-shrink-0"></div>
                            <div className="flex-1 space-y-2">
                                <div className="h-4 bg-gray-200 rounded w-3/4"></div>
                                <div className="h-3 bg-gray-200 rounded w-full"></div>
                                <div className="h-3 bg-gray-200 rounded w-1/2"></div>
                    // Loading skeletons
                    Array.from({ length: 3 }).map((_, i) => (
                        <div key={`skeleton-${i}`} className="flex gap-3 animate-pulse">
                            <div className="w-20 h-20 bg-gray-200 rounded-md flex-shrink-0"></div>
                            <div className="flex-1 space-y-2">
                                <div className="h-4 bg-gray-200 rounded w-3/4"></div>
                                <div className="h-3 bg-gray-200 rounded w-full"></div>
                                <div className="h-3 bg-gray-200 rounded w-1/2"></div>
                            </div>
                        </div>
                    ))
                ) : venues.length > 0 ? (
                    venues.map((venue: Venue) => {
                        if (!venue || !venue.id) return null;

                        const primaryImage = venue.images?.find((img: Image) => img?.is_primary === 1);
                        const imageUrl = primaryImage?.url || "https://via.placeholder.com/400x300?text=BCP+Sports";
                        const rating = venue.reviews_avg_rating ? Number(venue.reviews_avg_rating).toFixed(1) : "0.0";
                        const startTime = venue.start_time?.slice(0, 5) || "00:00";
                        const endTime = venue.end_time?.slice(0, 5) || "23:59";
                ) : venues.length > 0 ? (
                    venues.map((venue: Venue) => {
                        if (!venue || !venue.id) return null;

                        const primaryImage = venue.images?.find((img: Image) => img?.is_primary === 1);
                        const imageUrl = primaryImage?.url || "https://via.placeholder.com/400x300?text=BCP+Sports";
                        const rating = venue.reviews_avg_rating ? Number(venue.reviews_avg_rating).toFixed(1) : "0.0";
                        const startTime = venue.start_time?.slice(0, 5) || "00:00";
                        const endTime = venue.end_time?.slice(0, 5) || "23:59";

                        return (
                            <div
                                key={`venue-${venue.id}`}
                                key={`venue-${venue.id}`}
                                onClick={() => handleVenueNavigation(venue.id)}
                                className="flex gap-3 cursor-pointer group hover:bg-gray-50 p-2 rounded-lg transition-all duration-200"
                            >
                                {/* Venue Image */}
                                <div className="relative w-20 h-20 flex-shrink-0">
                                className="flex gap-3 cursor-pointer group hover:bg-gray-50 p-2 rounded-lg transition-all duration-200"
                            >
                                {/* Venue Image */}
                                <div className="relative w-20 h-20 flex-shrink-0">
                                    <img
                                        src={imageUrl}
                                        alt={venue.name || "Venue"}
                                        className="w-full h-full rounded-md object-cover"
                                        onError={(e) => {
                                            const target = e.target as HTMLImageElement;
                                            target.src = "https://via.placeholder.com/400x300?text=BCP+Sports";
                                        }}
                                    />
                                    {/* Rating badge */}
                                    <div className="absolute -top-1 -right-1 bg-emerald-500 text-white px-1.5 py-0.5 rounded-md flex items-center gap-0.5 shadow-sm text-[10px]">
                                        <i className="fa-solid fa-star text-yellow-300"></i>
                                        <span>{rating}</span>
                                        src={imageUrl}
                                        alt={venue.name || "Venue"}
                                        className="w-full h-full rounded-md object-cover"
                                        onError={(e) => {
                                            const target = e.target as HTMLImageElement;
                                            target.src = "https://via.placeholder.com/400x300?text=BCP+Sports";
                                        }}
                                    />
                                    {/* Rating badge */}
                                    <div className="absolute -top-1 -right-1 bg-emerald-500 text-white px-1.5 py-0.5 rounded-md flex items-center gap-0.5 shadow-sm text-[10px]">
                                        <i className="fa-solid fa-star text-yellow-300"></i>
                                        <span>{rating}</span>
                                    </div>
                                </div>

                                {/* Venue Info */}
                                <div className="flex-1 min-w-0">
                                    <h5 className="text-sm font-bold text-gray-800 group-hover:text-emerald-600 transition line-clamp-1 mb-1">
                                        {venue.name || "Tên sân"}
                                    </h5>
                                    
                                    {/* Address */}
                                    {venue.address_detail && (
                                        <div className="flex items-start gap-1 mb-1">
                                            <i className="fa-solid fa-location-dot text-emerald-500 text-[10px] mt-0.5 flex-shrink-0"></i>
                                            <p className="text-xs text-gray-600 line-clamp-2">{venue.address_detail}</p>
                                        </div>
                                    )}

                                    {/* Operating hours */}
                                    <div className="flex items-center gap-1 text-xs text-gray-500">
                                        <i className="fa-regular fa-clock text-emerald-500"></i>
                                        <span>{startTime} - {endTime}</span>
                                    </div>

                                    {/* Venue types */}
                                    {venue.venue_types && Array.isArray(venue.venue_types) && venue.venue_types.length > 0 && (
                                        <div className="flex flex-wrap gap-1 mt-1">
                                            {venue.venue_types.slice(0, 2).map((type, i) => (
                                                type && type.name ? (
                                                    <span
                                                        key={`type-${venue.id}-${i}`}
                                                        className="text-[9px] bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded-full font-medium"
                                                    >
                                                        {type.name}
                                                    </span>
                                                ) : null
                                            ))}
                                            {venue.venue_types.length > 2 && (
                                                <span className="text-[9px] text-gray-500">+{venue.venue_types.length - 2}</span>
                                            )}
                                        </div>
                                    )}

                                    {/* Venue types */}
                                    {venue.venue_types && Array.isArray(venue.venue_types) && venue.venue_types.length > 0 && (
                                        <div className="flex flex-wrap gap-1 mt-1">
                                            {venue.venue_types.slice(0, 2).map((type, i) => (
                                                type && type.name ? (
                                                    <span
                                                        key={`type-${venue.id}-${i}`}
                                                        className="text-[9px] bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded-full font-medium"
                                                    >
                                                        {type.name}
                                                    </span>
                                                ) : null
                                            ))}
                                            {venue.venue_types.length > 2 && (
                                                <span className="text-[9px] text-gray-500">+{venue.venue_types.length - 2}</span>
                                            )}
                                        </div>
                                    )}
                                </div>
                            </div>
                        );
                    })
                ) : (
                    <p className="text-center text-gray-500 text-xs italic py-4">
                        Không có sân gợi ý nào.
                    </p>
                    <p className="text-center text-gray-500 text-xs italic py-4">
                        Không có sân gợi ý nào.
                    </p>
                )}
            </div>
        </div>
    );
};

export default RelatedVenue;
export default RelatedVenue;