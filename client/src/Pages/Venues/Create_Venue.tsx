import React, { useMemo, useState } from 'react';
import Input from '../../Components/Input';
import Select from '../../Components/Select';
import Textarea from '../../Components/Textarea';
import CustomFileInput from '../../Components/CustomFileInput';
import { useFetchData } from '../../Hooks/useApi';

interface Province {
  id: number;
  name: string;
}

interface District {
  id: number;
  name: string;
}

const Create_Venue: React.FC = () => {
  const { data: provincesResponse } = useFetchData<Province[]>('provinces');
  const { data: districtsResponse } = useFetchData<District[]>('districts');

  const provinceOptions = useMemo(
    () =>
      provincesResponse?.data?.map((pro) => ({
        value: pro.id,
        label: pro.name,
      })) || [],
    [provincesResponse?.data]
  );

  const districtOptions = useMemo(
    () =>
      districtsResponse?.data?.map((dis) => ({
        value: dis.id,
        label: dis.name,
      })) || [],
    [districtsResponse?.data]
  );

  const [venueName, setVenueName] = useState('');
  const [capacity, setCapacity] = useState<number | ''>('');
  const [selectedProvince, setSelectedProvince] = useState<number | ''>('');
  const [selectedDistrict, setSelectedDistrict] = useState<number | ''>('');
  const [description, setDescription] = useState('');
  const [files, setFiles] = useState<File[]>([]);
  const [imageLinks, setImageLinks] = useState<string[]>([]);

  const handleFileChange = (fileList: FileList | null) => {
    if (fileList) {
      setFiles(Array.from(fileList));
    } else {
      setFiles([]);
    }
  };

  // Giả sử API upload file trả về link ảnh
  const uploadFiles = async (files: File[]): Promise<string[]> => {
    const uploadedLinks = await Promise.all(
      files.map(async (file) => {
        const formData = new FormData();
        formData.append('file', file);

        const response = await fetch('/api/upload', {
          method: 'POST',
          body: formData,
        });
        const data = await response.json();
        return data.url; // API trả về url ảnh
      })
    );
    return uploadedLinks;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    let links: string[] = [];
    if (files.length > 0) {
      links = await uploadFiles(files);
      setImageLinks(links);
    }

    const formData = {
      venueName,
      capacity,
      selectedProvince,
      selectedDistrict,
      description,
      images: links,
    };

    console.log(formData);
    // TODO: Gửi formData lên API
  };

  return (
    <div className="container mx-auto px-4 py-12 md:py-16 max-w-5xl">
      <form onSubmit={handleSubmit} className="flex flex-col gap-6">
        <h1 className="text-2xl font-bold">Đăng ký thương hiệu sân</h1>

        <div className="grid grid-cols-2 gap-4">
          <Input
            label="Tên thương hiệu"
            id="venue-name"
            type="text"
            placeholder="Nhập tên thương hiệu"
            value={venueName}
            onChange={(e) => setVenueName(e.target.value)}
          />
          <Input
            label="Sức chứa (số lượng người)"
            id="venue-capacity"
            type="number"
            placeholder="Nhập sức chứa"
            value={capacity}
            onChange={(e) => setCapacity(Number(e.target.value))}
          />
        </div>

        <div className="grid grid-cols-2 gap-4">
          <Select
            id="province"
            label="Tỉnh/Thành Phố"
            options={provinceOptions}
            value={selectedProvince}
            onChange={(e) => setSelectedProvince(Number(e.target.value))}
          />
          <Select
            id="district"
            label="Quận/Huyện"
            options={districtOptions}
            value={selectedDistrict}
            onChange={(e) => setSelectedDistrict(Number(e.target.value))}
          />
        </div>

        <Textarea
          id="description"
          label="Mô tả chi tiết về sân"
          placeholder="Nhập các thông tin như: số lượng sân, chất lượng mặt cỏ, tiện ích đi kèm (nước uống, wifi, bãi đỗ xe...)"
          rows={4}
          value={description}
          onChange={(e) => setDescription(e.target.value)}
        />

        <CustomFileInput
          id="venue-images"
          label="Hình ảnh sân (có xem trước)"
          onFileChange={handleFileChange}
          multiple
          accept="image/*"
        />

        <button
          type="submit"
          className="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 transition"
        >
          Đăng ký
        </button>
      </form>
    </div>
  );
};

export default Create_Venue;
