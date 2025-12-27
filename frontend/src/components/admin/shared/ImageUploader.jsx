import React, { useRef } from 'react';
import { Upload, X, Star } from 'lucide-react';

const ImageUploader = ({ images = [], onUpload, onDelete, onSetPrimary, uploading }) => {
    const fileInputRef = useRef(null);

    const handleFileChange = (e) => {
        const files = Array.from(e.target.files);
        if (files.length > 0) {
            onUpload(files);
            e.target.value = null; 
        }
    };

    return (
        <div className="space-y-4">
            {/* Vùng Upload */}
            <div 
                className="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 cursor-pointer transition-colors"
                onClick={() => fileInputRef.current.click()}
            >
                <input 
                    type="file" 
                    multiple 
                    className="hidden" 
                    ref={fileInputRef} 
                    onChange={handleFileChange} 
                    accept="image/*"
                />
                <Upload className="mx-auto h-10 w-10 text-gray-400 mb-2" />
                <p className="text-sm text-gray-600">
                    {uploading ? 'Đang tải lên...' : 'Click để tải ảnh lên (Max 5MB)'}
                </p>
            </div>

            {/* Grid Preview Ảnh */}
            {images.length > 0 && (
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {images.map((img, idx) => (
                        <div key={img.uuid || idx} className="relative group border rounded-lg overflow-hidden aspect-square">
                            <img 
                                src={img.url || URL.createObjectURL(img.file)} 
                                alt="" 
                                className="w-full h-full object-cover" 
                            />
                            
                            {/* Overlay Actions */}
                            <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                <button 
                                    type="button"
                                    onClick={() => onSetPrimary && onSetPrimary(img)}
                                    className={`p-1.5 rounded-full ${img.is_primary ? 'bg-yellow-400 text-white' : 'bg-white text-gray-600 hover:bg-yellow-100'}`}
                                    title="Đặt làm ảnh chính"
                                >
                                    <Star size={16} fill={img.is_primary ? "currentColor" : "none"} />
                                </button>
                                <button 
                                    type="button"
                                    onClick={() => onDelete(img)}
                                    className="p-1.5 bg-white rounded-full text-red-500 hover:bg-red-50"
                                    title="Xóa ảnh"
                                >
                                    <X size={16} />
                                </button>
                            </div>

                            {/* Badge ảnh chính */}
                            {img.is_primary && (
                                <span className="absolute top-2 left-2 bg-yellow-400 text-white text-xs px-2 py-0.5 rounded font-medium shadow-sm">
                                    Main
                                </span>
                            )}
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default ImageUploader;