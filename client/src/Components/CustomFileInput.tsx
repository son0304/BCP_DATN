import React, { useState, useRef, useEffect } from 'react';

type CustomFileInputProps = React.InputHTMLAttributes<HTMLInputElement> & {
    label: string;
    onFileChange: (files: FileList | null) => void;
};

const CustomFileInput: React.FC<CustomFileInputProps> = ({ label, id, onFileChange, multiple, accept, ...props }) => {
    const [fileName, setFileName] = useState<string>('Chưa có tệp nào được chọn');
    const [previews, setPreviews] = useState<string[]>([]);

    const fileInputRef = useRef<HTMLInputElement>(null);

    const cleanupPreviews = () => {
        previews.forEach(url => URL.revokeObjectURL(url));
    };

    const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        cleanupPreviews();

        const files = event.target.files;
        if (files && files.length > 0) {
            const name = files.length > 1 ? `${files.length} tệp đã được chọn` : files[0].name;
            setFileName(name);
            onFileChange(files);

            const imageFiles = Array.from(files).filter(file => file.type.startsWith('image/'));
            const newPreviews = imageFiles.map(file => URL.createObjectURL(file));
            setPreviews(newPreviews);
        } else {
            setFileName('Chưa có tệp nào được chọn');
            onFileChange(null);
            setPreviews([]);
        }
    };

    const handleButtonClick = () => {
        fileInputRef.current?.click();
    };

    useEffect(() => {
        return () => {
            cleanupPreviews();
        };
    }, []);

    return (
        <div className="w-full">
            <label htmlFor={id} className="block text-sm font-medium text-gray-700 mb-1">
                {label}
            </label>
            <div className="mt-1 flex items-center">
                <button
                    type="button"
                    onClick={handleButtonClick}
                    className="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#348738]"
                >
                    Chọn tệp
                </button>
                <input
                    type="file"
                    id={id}
                    ref={fileInputRef}
                    multiple={multiple}
                    accept={accept}
                    className="hidden"
                    onChange={handleFileChange}
                    {...props}
                />
                <span className="ml-3 text-sm text-gray-500 truncate">{fileName}</span>
            </div>

            {previews.length > 0 && (
                <div className="mt-4 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4">
                    {previews.map((src, index) => (
                        <div key={index} className="relative aspect-square bg-gray-100 rounded-md overflow-hidden">
                            <img
                                src={src}
                                alt={`Xem trước ${index + 1}`}
                                className="w-full h-full object-cover"
                            />
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default CustomFileInput;
