import React, { useRef } from 'react';
import { UploadCloud, X, Star } from 'lucide-react';

const ProductMediaSection = ({ mediaItems, onChange }) => {
    const fileInputRef = useRef(null);

    const handleFileSelect = (e) => {
        if (!e.target.files || e.target.files.length === 0) return;

        const files = Array.from(e.target.files);
        
        const newItems = files.map(file => ({
            id: `new-${Date.now()}-${Math.random()}`, // ID tạm
            url: URL.createObjectURL(file),           // Preview URL
            file: file,                               // File gốc để upload
            is_new: true,
            is_primary: false
        }));

        // Logic UX: Nếu chưa có ảnh nào, ảnh đầu tiên upload sẽ là primary
        if (mediaItems.length === 0 && newItems.length > 0) {
            newItems[0].is_primary = true;
        }

        onChange([...mediaItems, ...newItems]);
        
        // Reset input để cho phép chọn lại cùng 1 file nếu lỡ xóa
        e.target.value = ''; 
    };

    // Xử lý chọn ảnh chính (Chỉ 1 ảnh được là true)
    const handleSetPrimary = (targetId) => {
        const updated = mediaItems.map(item => ({
            ...item,
            is_primary: item.id === targetId // Set true cho item chọn, còn lại false
        }));
        onChange(updated);
    };

    // Xử lý xóa ảnh
    const handleRemove = (targetId) => {
        const itemToRemove = mediaItems.find(i => i.id === targetId);
        
        // Clean memory leak từ createObjectURL nếu là ảnh mới
        if (itemToRemove?.is_new && itemToRemove.url) {
            URL.revokeObjectURL(itemToRemove.url);
        }

        const remaining = mediaItems.filter(i => i.id !== targetId);

        // Logic UX: Nếu xóa mất ảnh primary, tự động đẩy quyền primary cho ảnh đầu tiên còn lại
        if (itemToRemove?.is_primary && remaining.length > 0) {
            remaining[0].is_primary = true;
        }

        onChange(remaining);
    };

    return (
        <div className="form-card">
            <div className="card-header">
                <h3 className="card-title">Media Gallery</h3>
            </div>
            <div className="card-body">
                <div className="media-layout">
                    {/* Left: Upload Button */}
                    <div className="media-upload-left">
                        <input
                            ref={fileInputRef}
                            type="file" multiple accept="image/*"
                            className="hidden" style={{ display: 'none' }}
                            onChange={handleFileSelect}
                        />
                        <div className="upload-box-vertical" onClick={() => fileInputRef.current.click()} title="Click to upload">
                            <UploadCloud className="upload-icon" size={32} />
                            <div className="mt-2">
                                <div className="text-sm font-semibold">Click to upload</div>
                                <div className="text-xs text-gray-500">Max 5MB</div>
                            </div>
                        </div>
                    </div>

                    {/* Right: Gallery Grid */}
                    <div className="media-gallery-right">
                        {mediaItems.map((item) => (
                            <div 
                                key={item.id} 
                                className={`media-item-card ${item.is_primary ? 'is-primary' : ''}`}
                            >
                                <img src={item.url} alt="product" className="media-img" />
                                
                                {item.is_new && <span className="badge-new">NEW</span>}

                                {/* Primary Button */}
                                <button
                                    type="button"
                                    onClick={() => handleSetPrimary(item.id)}
                                    className={`btn-media-action btn-star ${item.is_primary ? 'active' : ''}`}
                                    title="Set as Primary"
                                >
                                    <Star size={14} fill={item.is_primary ? "currentColor" : "none"} />
                                </button>

                                {/* Delete Button */}
                                <button
                                    type="button"
                                    onClick={() => handleRemove(item.id)}
                                    className="btn-media-action btn-delete"
                                    title="Remove image"
                                >
                                    <X size={14} />
                                </button>
                            </div>
                        ))}

                        {mediaItems.length === 0 && (
                            <div className="media-empty-state">No images uploaded yet</div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ProductMediaSection;