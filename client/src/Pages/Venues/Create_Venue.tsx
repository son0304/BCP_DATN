import React, { useMemo, useState } from 'react';
import { useForm, useFieldArray } from 'react-hook-form';
import type { Control, UseFormRegister, FieldErrors, UseFormWatch } from 'react-hook-form';
import Input from '../../Components/Input';
import Select from '../../Components/Select';
import Textarea from '../../Components/Textarea'; // Giả sử bạn có component này như trong code Sơn
import { useFetchData, usePostData } from '../../Hooks/useApi';
import { message } from 'antd';
import { useNavigate } from 'react-router-dom';

// --- Interfaces ---

interface District {
  id: number;
  name: string;
  code: string;
}
interface Province {
  id: number;
  name: string;
  code: string;
  districts: District[];
}
interface VenueType {
  id: number;
  name: string;
}

interface TimeSlot {
  start_time: string;
  end_time: string;
  price: string;
}
interface Court {
  name: string;
  venue_type_id: string;
  surface: string;
  is_indoor: string; 
  time_slots: TimeSlot[];
}

interface ImagePreview {
  file: File;
  url: string;
  is_primary: number;
}

interface FormData {
  name: string;
  phone: string;
  provinceId: string;
  districtId: string;
  address: string;
  start_time: string;
  end_time: string;
  description: string;
  courts: Court[];
}

// --- Component Con: Khung Giờ (Style theo Sơn - Màu Xanh) ---

interface CourtTimeSlotsProps {
  courtIndex: number;
  control: Control<FormData>;
  register: UseFormRegister<FormData>;
  errors: FieldErrors<FormData>;
  watch: UseFormWatch<FormData>;
}

const CourtTimeSlots: React.FC<CourtTimeSlotsProps> = ({ courtIndex, control, register, errors, watch }) => {
  const { fields, append, remove } = useFieldArray({
    control,
    name: `courts.${courtIndex}.time_slots`
  });

  return (
    <div className="mt-4 bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
      <h4 className="font-bold text-sm text-gray-700 mb-3 flex items-center gap-2">
        <i className="fa-solid fa-clock text-[#10B981]"></i> Thiết lập khung giờ & Giá
      </h4>
      
      <div className="space-y-3">
        {fields.map((field, slotIndex) => {
          const currentStartTime = watch(`courts.${courtIndex}.time_slots.${slotIndex}.start_time`);
          return (
            <div key={field.id} className="grid grid-cols-1 sm:grid-cols-7 gap-3 items-end p-3 bg-gray-50 rounded-md border border-gray-100 relative group">
              <div className="sm:col-span-2">
                 <Input
                  label="Bắt đầu"
                  type="time"
                  className="text-xs h-9"
                  {...register(`courts.${courtIndex}.time_slots.${slotIndex}.start_time`, { required: 'Bắt buộc' })}
                  error={errors.courts?.[courtIndex]?.time_slots?.[slotIndex]?.start_time?.message}
                />
              </div>
              <div className="sm:col-span-2">
                 <Input
                  label="Kết thúc"
                  type="time"
                  className="text-xs h-9"
                  {...register(`courts.${courtIndex}.time_slots.${slotIndex}.end_time`, { 
                    required: 'Bắt buộc',
                    validate: (val) => !val || !currentStartTime || val > currentStartTime || "Phải lớn hơn giờ bắt đầu"
                  })}
                  error={errors.courts?.[courtIndex]?.time_slots?.[slotIndex]?.end_time?.message}
                />
              </div>
              <div className="sm:col-span-2">
                 <Input
                  label="Giá (VNĐ)"
                  type="number"
                  placeholder="150000"
                  className="text-xs h-9"
                  {...register(`courts.${courtIndex}.time_slots.${slotIndex}.price`, { required: 'Bắt buộc', min: 0 })}
                  error={errors.courts?.[courtIndex]?.time_slots?.[slotIndex]?.price?.message}
                />
              </div>
              
              <div className="sm:col-span-1 flex justify-center pb-1">
                 <button
                  type="button"
                  onClick={() => remove(slotIndex)}
                  className="w-8 h-8 flex items-center justify-center bg-white border border-red-200 text-red-500 rounded-full hover:bg-red-50 transition-colors shadow-sm"
                  title="Xóa khung giờ"
                >
                  <i className="fa-solid fa-trash-can text-xs"></i>
                </button>
              </div>
            </div>
          );
        })}
      </div>

      <button
        type="button"
        onClick={() => append({ start_time: '', end_time: '', price: '0' })}
        className="mt-3 text-sm font-bold text-[#10B981] hover:text-[#059669] flex items-center gap-1 transition-colors border border-dashed border-[#10B981] px-3 py-1.5 rounded-md hover:bg-green-50"
      >
        <i className="fa-solid fa-plus-circle"></i> Thêm khung giờ
      </button>
    </div>
  );
};

// --- Component Chính ---

const Create_Venue = () => {
  // 1. Fetch Data
  const { data: proData } = useFetchData('provinces');
  const provinces: Province[] = (proData?.data as Province[]) || [];

  const { data: venueTypesData } = useFetchData('venue_types');
  const venueTypes: VenueType[] = (venueTypesData?.data as VenueType[]) || [];
 
  const { mutate,loading } = usePostData('venues');
  const navigate = useNavigate();

  // 2. Local State
  const [selectedVenueTypes, setSelectedVenueTypes] = useState<string[]>([]);
  const [images, setImages] = useState<ImagePreview[]>([]);

  // 3. Form Setup
  const { register, handleSubmit, watch, setValue, control, formState: { errors } } = useForm<FormData>({
    defaultValues: {
      provinceId: '',
      districtId: '',
      courts: [] 
    },
  });

  const { fields: courtFields, append: appendCourt, remove: removeCourt } = useFieldArray({
    control,
    name: 'courts'
  });

  const selectedProvinceId = watch('provinceId');
  useMemo(() => setValue('districtId', ''), [selectedProvinceId, setValue]);
  const provincesById = provinces.find(p => p.id.toString() === selectedProvinceId);

  // 4. Handlers (Logic Ảnh của Sơn)
  const handleImagesChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files) return;

    const newImages: ImagePreview[] = Array.from(files).map((file, idx) => ({
      file,
      url: URL.createObjectURL(file),
      is_primary: (images.length === 0 && idx === 0) ? 1 : 0, 
    }));
    setImages(prev => [...prev, ...newImages]);
  };

  const setMainImage = (index: number) => {
    setImages(prev =>
      prev.map((img, idx) => ({
        ...img,
        is_primary: idx === index ? 1 : 0,
      }))
    );
  };

  const onSubmit = async (data: FormData) => {
    const userStr = localStorage.getItem("user");
    const user = userStr ? JSON.parse(userStr) : null;

    if (!user) {
      message.error("Vui lòng đăng nhập.");
      return;
    }

    if (images.length === 0) {
      message.warning('Vui lòng upload ít nhất 1 ảnh!');
      return;
    }
    if (data.courts.length === 0) {
      message.warning('Vui lòng thêm ít nhất một sân!');
      return;
    }

    // FormData
    const formData = new FormData();
    formData.append('owner_id', user.id.toString());
    formData.append('name', data.name);
    formData.append('phone', data.phone);
    formData.append('province_id', data.provinceId);
    formData.append('district_id', data.districtId);
    formData.append('address_detail', data.address);
    formData.append('start_time', data.start_time);
    formData.append('end_time', data.end_time);
    formData.append('description', data.description || '');

    // Images
    images.forEach((img, idx) => formData.append('images[]', img.file));
    const mainImgIndex = images.findIndex(i => i.is_primary);
    formData.append('main_image_index', (mainImgIndex !== -1 ? mainImgIndex : 0).toString());

    // Courts - Loop append manual for Laravel
    data.courts.forEach((court, cIdx) => {
      formData.append(`courts[${cIdx}][name]`, court.name);
      formData.append(`courts[${cIdx}][venue_type_id]`, court.venue_type_id);
      if(court.surface) formData.append(`courts[${cIdx}][surface]`, court.surface);
      formData.append(`courts[${cIdx}][is_indoor]`, court.is_indoor);
      
      court.time_slots.forEach((slot, sIdx) => {
        formData.append(`courts[${cIdx}][time_slots][${sIdx}][start_time]`, slot.start_time);
        formData.append(`courts[${cIdx}][time_slots][${sIdx}][end_time]`, slot.end_time);
        formData.append(`courts[${cIdx}][time_slots][${sIdx}][price]`, slot.price.toString());
      });
    });

    try {
      await mutate(formData); 
      message.success('🎉 Đăng ký sân thành công!');
      navigate('/congratulations');
    } catch (err: any) {
      const status = err?.response?.status;
      const errData = err?.response?.data;
      if (status === 409 && errData?.alreadyRegistered) {
        navigate('/congratulations', { state: { alreadyRegistered: true } });
      } else {
        message.error(errData?.message || 'Có lỗi xảy ra, vui lòng thử lại!');
      }
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 font-sans">
      <div className="max-w-4xl w-full bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
        
        {/* --- Header Form (Style Sơn) --- */}
        <div className="bg-gray-50/50 px-6 py-5 border-b border-gray-100 flex flex-col items-center">
          <div className="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center mb-3 text-[#10B981]">
             <i className="fa-solid fa-store text-xl"></i>
          </div>
          <h1 className="text-2xl font-bold text-[#11182C]">Đăng Ký Sân Mới</h1>
          <p className="text-sm text-gray-500 mt-1">Điền thông tin để đưa sân của bạn lên hệ thống BCP Sports</p>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="p-6 md:p-8 space-y-8">
          
          {/* Section 1: Thông tin cơ bản */}
          <div>
            <h3 className="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
               <i className="fa-regular fa-id-card text-[#10B981]"></i> Thông tin chung
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
              <Input 
                label="Tên sân / Thương hiệu (*)" 
                id="name" 
                type="text" 
                placeholder="VD: Sân bóng BCP..." 
                {...register('name', { required: 'Tên thương hiệu là bắt buộc' })} 
                error={errors.name?.message} 
              />
              <Input 
                label="Số điện thoại liên hệ (*)" 
                id="phone" 
                type="tel" 
                placeholder="0912..." 
                {...register('phone', { required: 'Số điện thoại là bắt buộc' })} 
                error={errors.phone?.message} 
              />
            </div>
          </div>

          <hr className="border-gray-100" />

          {/* Section 2: Vị trí & Thời gian */}
          <div>
             <h3 className="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
               <i className="fa-solid fa-map-location-dot text-[#10B981]"></i> Địa điểm & Thời gian
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
              <Select
                id="provinceId"
                label="Tỉnh/Thành phố (*)"
                {...register('provinceId', { required: 'Vui lòng chọn tỉnh/thành' })}
                options={provinces.map(p => ({ value: p.id.toString(), label: p.name }))}
                error={errors.provinceId?.message}
              />
              <Select
                id="districtId"
                label="Quận/Huyện (*)"
                {...register('districtId', { required: 'Vui lòng chọn quận/huyện' })}
                options={provincesById?.districts.map(d => ({ value: d.id.toString(), label: d.name })) || []}
                disabled={!selectedProvinceId}
                error={errors.districtId?.message}
              />
            </div>
            
            <div className="mb-5">
               <Input 
                  label="Địa chỉ chi tiết (Số nhà, đường...) (*)" 
                  id="address" 
                  type="text" 
                  placeholder="VD: 123 Đường Nguyễn Văn A..." 
                  {...register('address', { required: 'Địa chỉ chi tiết là bắt buộc' })} 
                  error={errors.address?.message} 
               />
            </div>

            <div className="grid grid-cols-2 gap-5">
              <Input label="Giờ mở cửa (*)" id="start_time" type="time" {...register('start_time', { required: 'Bắt buộc' })} error={errors.start_time?.message} />
              <Input label="Giờ đóng cửa (*)" id="end_time" type="time" {...register('end_time', { required: 'Bắt buộc' })} error={errors.end_time?.message} />
            </div>

            {/* Checkbox Venue Types */}
            <div className="mt-5">
               <label className="text-sm font-medium text-gray-700 block mb-2">Loại hình kinh doanh</label>
               <div className="flex flex-wrap gap-3">
                  {venueTypes.map((vt) => (
                    <label key={vt.id} className={`flex items-center space-x-2 border rounded-lg px-4 py-2 cursor-pointer transition-all ${selectedVenueTypes.includes(vt.id.toString()) ? 'bg-green-50 border-[#10B981] text-[#059669]' : 'bg-white border-gray-200 hover:bg-gray-50'}`}>
                      <input
                        type="checkbox"
                        value={vt.id.toString()}
                        checked={selectedVenueTypes.includes(vt.id.toString())}
                        onChange={(e) => {
                          const val = e.target.value;
                          setSelectedVenueTypes(prev => e.target.checked ? [...prev, val] : prev.filter(v => v !== val));
                        }}
                        className="accent-[#10B981]"
                      />
                      <span className="font-medium text-sm">{vt.name}</span>
                    </label>
                  ))}
               </div>
            </div>
          </div>

          <hr className="border-gray-100" />

          {/* Section 3: Upload ảnh (Style Sơn) */}
          <div>
            <h3 className="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
               <i className="fa-regular fa-images text-[#10B981]"></i> Hình ảnh sân
            </h3>
            <div className="bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:bg-green-50/30 hover:border-[#10B981]/50 transition-colors group cursor-pointer relative">
               <div className="mb-3">
                 <i className="fa-solid fa-cloud-arrow-up text-3xl text-gray-400 group-hover:text-[#10B981] transition-colors"></i>
               </div>
               <Input label="Chọn ảnh" id="images" type="file" accept="image/*" multiple onChange={handleImagesChange} className="hidden absolute inset-0 opacity-0 cursor-pointer" />
               <p className="text-sm font-medium text-gray-600">Nhấn để tải ảnh lên</p>
               <p className="text-xs text-gray-400 mt-1">Hỗ trợ: JPG, PNG, WEBP (Tối đa 5MB/ảnh)</p>
            </div>
            
            {images.length > 0 && (
              <div className="mt-5 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                {images.map((img, idx) => (
                  <div 
                    key={idx} 
                    onClick={() => setMainImage(idx)}
                    className={`relative group rounded-lg overflow-hidden border-2 cursor-pointer transition-all aspect-video ${img.is_primary ? 'border-[#10B981] ring-2 ring-[#10B981]/20 shadow-md' : 'border-gray-200 hover:border-gray-300'}`}
                  >
                    <img src={img.url} alt={`preview-${idx}`} className="w-full h-full object-cover" />
                    <div className={`absolute bottom-0 inset-x-0 py-1.5 text-[10px] font-bold text-center transition-colors ${img.is_primary ? 'bg-[#10B981] text-white' : 'bg-black/60 text-white/80 group-hover:bg-gray-800'}`}>
                      {img.is_primary === 1 ? <><i className="fa-solid fa-circle-check mr-1"></i> Ảnh đại diện</> : "Đặt làm ảnh chính"}
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

          <hr className="border-gray-100" />

          {/* Section 4: Mô tả */}
          <div>
             <h3 className="text-sm font-bold text-gray-800 uppercase tracking-wide mb-4 flex items-center gap-2">
               <i className="fa-solid fa-pen-to-square text-[#10B981]"></i> Mô tả
            </h3>
             <Textarea 
                id="description" 
                label="Giới thiệu về sân" 
                placeholder="Nhập các thông tin tiện ích..." 
                rows={4} 
                {...register('description')} 
             />
          </div>

          <hr className="border-gray-100" />

          {/* Section 5: Danh sách Sân Con (Phần này phải có để form hoạt động) */}
          <div>
            <div className="flex justify-between items-center mb-4">
               <h3 className="text-sm font-bold text-gray-800 uppercase tracking-wide flex items-center gap-2">
                  <i className="fa-solid fa-layer-group text-[#10B981]"></i> Danh sách Sân con
               </h3>
               <button type="button" onClick={() => appendCourt({ name: "", venue_type_id: "", surface: "", is_indoor: "0", time_slots: [] })} className="text-xs bg-[#10B981] text-white px-3 py-2 rounded-lg font-bold hover:bg-[#059669]">
                  + Thêm sân mới
               </button>
            </div>

            <div className="space-y-6">
               {courtFields.map((field, index) => (
                  <div key={field.id} className="p-5 border border-gray-200 rounded-xl bg-gray-50/30 relative">
                     <button type="button" onClick={() => removeCourt(index)} className="absolute top-4 right-4 text-gray-400 hover:text-red-500">
                        <i className="fa-solid fa-xmark text-lg"></i>
                     </button>
                     
                     <h4 className="font-bold text-gray-800 mb-4 text-sm"><span className="bg-[#10B981] text-white px-2 py-0.5 rounded text-xs mr-2">#{index + 1}</span>Thông tin sân</h4>
                     
                     <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Input label="Tên sân (*)" placeholder="Sân 1..." {...register(`courts.${index}.name`, { required: 'Bắt buộc' })} error={errors.courts?.[index]?.name?.message} />
                        <Select label="Loại sân (*)" {...register(`courts.${index}.venue_type_id`, { required: 'Bắt buộc' })} options={venueTypes.filter(vt => selectedVenueTypes.length === 0 || selectedVenueTypes.includes(vt.id.toString())).map(vt => ({ value: vt.id.toString(), label: vt.name }))} error={errors.courts?.[index]?.venue_type_id?.message} />
                        <Input label="Mặt sân" placeholder="Cỏ nhân tạo..." {...register(`courts.${index}.surface`)} />
                        <Select label="Không gian" {...register(`courts.${index}.is_indoor`)} options={[{ value: '1', label: 'Trong nhà' }, { value: '0', label: 'Ngoài trời' }]} />
                     </div>
                     
                     {/* TimeSlots */}
                     <CourtTimeSlots courtIndex={index} control={control} register={register} errors={errors} watch={watch} />
                  </div>
               ))}
               {courtFields.length === 0 && <p className="text-center text-gray-400 text-sm py-4 border-2 border-dashed rounded-lg">Chưa có sân nào. Vui lòng thêm sân.</p>}
            </div>
          </div>

          {/* Submit Button */}
          <div className="pt-4 border-t border-gray-100">
            <button 
              type="submit" 
              disabled={loading}
              className={`w-full bg-[#10B981] hover:bg-[#059669] text-white text-base font-bold py-4 rounded-xl transition-all shadow-lg shadow-green-200 hover:shadow-xl hover:-translate-y-0.5 active:scale-95 flex items-center justify-center gap-2 ${loading ? 'opacity-70' : ''}`}
            >
               {loading ? <i className="fa-solid fa-spinner fa-spin"></i> : <i className="fa-solid fa-paper-plane"></i>}
               Gửi Đăng Ký
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default Create_Venue;