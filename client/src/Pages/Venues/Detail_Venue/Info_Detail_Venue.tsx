import type { Venue } from '../../../Types/venue';
import { useFetchData } from '../../../Hooks/useApi';
import type { Ticket } from '../../../Types/tiket';
import type { User } from '../../../Types/user';
import Review_Venue from './Review';
import type { QueryObserverResult, RefetchOptions } from '@tanstack/react-query';
import type { ApiResponse } from '../../../Types/api';

type InfoDetailVenueProps = {
  venue: Venue,
  user: User,
  refetch: (options?: RefetchOptions) => Promise<QueryObserverResult<ApiResponse<Venue>, Error>>;
  formatPrice: (price: number) => string;
};

const Info_Detail_Venue: React.FC<InfoDetailVenueProps> = ({ venue, user, refetch, formatPrice }) => {

  const courts = venue.courts ?? [];
  const reviews = venue.reviews ?? [];
  const { data } = useFetchData('tickets')
  const tickets = data?.data as Ticket[] || [];

  return (
    <div className="lg:col-span-3 space-y-5 order-2 lg:order-1">

      {/* --- 1. GIỚI THIỆU --- */}
      <section className="bg-white rounded-lg p-5 border border-gray-100 shadow-sm">
        <div className="flex items-center gap-2 mb-3 border-b border-gray-100 pb-2">
          <i className="fa-regular fa-file-lines text-emerald-600"></i>
          <h3 className="text-base font-bold text-gray-800 uppercase tracking-wide">Giới thiệu</h3>
        </div>
        <p className="text-sm text-gray-600 leading-6 text-justify">
          {venue.description ?? 'Sân đang cập nhật mô tả chi tiết.'}
        </p>
      </section>

      

      {/* --- 3. DANH SÁCH SÂN CON --- */}
      <section className="bg-white rounded-lg p-5 border border-gray-100 shadow-sm">
        <div className="flex items-center gap-2 mb-3 border-b border-gray-100 pb-2">
          <i className="fa-solid fa-layer-group text-emerald-600"></i>
          <h3 className="text-base font-bold text-gray-800 uppercase tracking-wide">Sân con hoạt động</h3>
        </div>

        <div className="overflow-hidden rounded-lg border border-gray-100">
          <table className="w-full text-sm">
            <thead>
              <tr className="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                <th className="py-3 px-4 text-left">Tên Sân</th>
                <th className="py-3 px-4 text-center">Khung giờ mở</th>
                <th className="py-3 px-4 text-right">Giá từ</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {courts.map((c) => {
                const slots = c.time_slots ?? [];
                const openSlots = slots.filter((s: any) => s.status === 'open');
                const prices = slots.map((s: any) => Number(s.price)).filter(p => !isNaN(p));
                const minPrice = prices.length ? Math.min(...prices) : 0;

                return (
                  <tr key={c.id} className="hover:bg-emerald-50/50 transition-colors">
                    <td className="py-3 px-4 font-medium text-gray-800">{c.name}</td>
                    <td className="py-3 px-4 text-center">
                      <span className="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-emerald-100 bg-emerald-600 rounded-full">
                        {openSlots.length}
                      </span>
                    </td>
                    <td className="py-3 px-4 text-right font-bold text-amber-500">
                      {minPrice ? formatPrice(minPrice) : 'Liên hệ'}
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </section>

      {/* --- 4. ĐÁNH GIÁ --- */}
      <Review_Venue venue={venue} reviews={reviews} tickets={tickets} user={user} refetch={refetch} />
    </div>
  )
}

export default Info_Detail_Venue;