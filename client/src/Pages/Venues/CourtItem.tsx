import React from 'react';
import { useFieldArray, type Control, type UseFormRegister, type FieldErrors } from "react-hook-form";

interface CourtItemProps {
    index: number;
    control: Control<VenueForm>;
    register: UseFormRegister<VenueForm>;
    errors: FieldErrors<VenueForm>;
    remove: (index: number) => void;
    venueTypes: { id: number | string; name: string }[];
}


export type TimeSlot = {
    start_time: string;
    end_time: string;
    price: number;
};

export type Court = {
    name: string;
    venue_type_id: string;
    surface: string;
    is_indoor: string;
    time_slots: TimeSlot[];
};

export type VenueForm = {
    courts: Court[];
};

const CourtItem = ({ index, control, register, errors, remove, venueTypes }: CourtItemProps) => {
    // Khởi tạo FieldArray cho time_slots NẰM TRONG sân hiện tại (courts[index].time_slots)
    const { fields: slotFields, append: appendSlot, remove: removeSlot } = useFieldArray({
        control,
        name: `courts.${index}.time_slots`
    });

    return (
        <div className="bg-white p-5 rounded-2xl border border-gray-200 mb-5 shadow-sm relative transition-all hover:border-emerald-300">
            {/* Header của từng Sân */}
            <div className="flex justify-between items-center mb-4 pb-2 border-b border-gray-100">
                <h6 className="text-lg font-bold text-gray-800">
                    Sân #<span className="text-emerald-600">{index + 1}</span>
                </h6>
                <button
                    type="button"
                    onClick={() => remove(index)}
                    className="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center"
                >
                    <i className="fa-solid fa-times"></i>
                </button>
            </div>

            {/* Row 1: Tên sân & Loại sân */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5 mb-4">
                <div>
                    <label className="text-sm font-bold text-gray-700 mb-1 block">Tên sân <span className="text-red-500">*</span></label>
                    <input
                        {...register(`courts.${index}.name`, { required: "Tên sân là bắt buộc" })}
                        className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none ${errors.courts?.[index]?.name ? 'border-red-500 bg-red-50' : 'border-gray-300'}`}
                        placeholder="Ví dụ: Sân A"
                    />
                    {errors.courts?.[index]?.name && <span className="text-xs text-red-500 mt-1">{errors.courts[index]?.name?.message as string}</span>}
                </div>
                <div>
                    <label className="text-sm font-bold text-gray-700 mb-1 block">Loại sân <span className="text-red-500">*</span></label>
                    <select
                        {...register(`courts.${index}.venue_type_id`, { required: "Chọn loại sân" })}
                        className={`w-full px-3 py-2 border rounded-lg bg-white focus:ring-2 focus:ring-emerald-500 outline-none ${errors.courts?.[index]?.venue_type_id ? 'border-red-500 bg-red-50' : 'border-gray-300'}`}
                    >
                        <option value="">-- Chọn loại hình --</option>
                        {venueTypes.map(t => (
                            <option key={t.id} value={t.id}>{t.name}</option>
                        ))}
                    </select>
                </div>
            </div>

            {/* Row 2: Mặt sân & Trong nhà/Ngoài trời */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                <div>
                    <label className="text-sm font-bold text-gray-700 mb-1 block">Mặt sân</label>
                    <input
                        {...register(`courts.${index}.surface`)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none"
                        placeholder="Ví dụ: Cỏ nhân tạo, Sàn gỗ..."
                    />
                </div>
                <div>
                    <label className="text-sm font-bold text-gray-700 mb-1 block">Trong nhà / Ngoài trời</label>
                    <select
                        {...register(`courts.${index}.is_indoor`)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-emerald-500 outline-none"
                    >
                        <option value="0">Ngoài trời</option>
                        <option value="1">Trong nhà</option>
                    </select>
                </div>
            </div>

            {/* Phần Khung giờ & Giá (Nested Array) */}
            <div className="bg-gray-50 rounded-xl p-4 border border-gray-200">
                <div className="flex justify-between items-center mb-3">
                    <span className="text-sm font-bold text-gray-800 uppercase tracking-wider">Khung giờ và giá</span>
                    <button
                        type="button"
                        onClick={() => appendSlot({ start_time: "05:00", end_time: "22:00", price: 0 })}
                        className="text-xs bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-lg font-bold hover:bg-emerald-200 transition flex items-center gap-1"
                    >
                        <i className="fa-solid fa-plus"></i> Thêm khung giờ
                    </button>
                </div>

                <div className="overflow-x-auto">
                    <table className="w-full text-sm text-left border-collapse bg-white rounded-lg shadow-sm overflow-hidden">
                        <thead className="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                            <tr>
                                <th className="px-4 py-3 font-bold w-1/4">Bắt đầu</th>
                                <th className="px-4 py-3 font-bold w-1/4">Kết thúc</th>
                                <th className="px-4 py-3 font-bold w-1/3">Giá (VNĐ)</th>
                                <th className="px-4 py-3 font-bold text-center w-[50px]"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {slotFields.map((slot, sIndex) => (
                                <tr key={slot.id} className="hover:bg-gray-50 transition-colors">
                                    <td className="px-2 py-2">
                                        <input
                                            type="time"
                                            {...register(`courts.${index}.time_slots.${sIndex}.start_time`, { required: true })}
                                            className="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
                                        />
                                    </td>
                                    <td className="px-2 py-2">
                                        <input
                                            type="time"
                                            {...register(`courts.${index}.time_slots.${sIndex}.end_time`, { required: true })}
                                            className="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
                                        />
                                    </td>
                                    <td className="px-2 py-2">
                                        <input
                                            type="number"
                                            placeholder="0"
                                            {...register(`courts.${index}.time_slots.${sIndex}.price`, { required: true, min: 0 })}
                                            className="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
                                        />
                                    </td>
                                    <td className="px-2 py-2 text-center">
                                        <button
                                            type="button"
                                            onClick={() => removeSlot(sIndex)}
                                            className="text-gray-400 hover:text-red-500 transition p-1"
                                        >
                                            <i className="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            ))}
                            {slotFields.length === 0 && (
                                <tr>
                                    <td colSpan={4} className="text-center py-4 text-gray-400 italic text-xs">
                                        Chưa có khung giờ nào. Vui lòng thêm ít nhất 1 khung giờ.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
                {errors.courts?.[index]?.time_slots && (
                    <p className="text-red-500 text-xs mt-2 text-center">Vui lòng nhập đầy đủ thông tin khung giờ.</p>
                )}
            </div>
        </div>
    );
};

export default CourtItem;