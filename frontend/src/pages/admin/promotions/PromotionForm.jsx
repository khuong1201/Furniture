import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import {
    ArrowLeft, Tag, Calendar, Percent, DollarSign, Save, AlertCircle, Package
} from 'lucide-react';
import { usePromotion } from '@/hooks/admin/usePromotion';
import ProductSelector from '@/components/admin/promotions/ProductSelector';
import './PromotionForm.css';

const PromotionForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    const isEdit = !!uuid;
    const { getPromotion, createPromotion, updatePromotion, loading: hookLoading } = usePromotion();

    const [saving, setSaving] = useState(false);
    const [error, setError] = useState(null);
    const [showProductSelector, setShowProductSelector] = useState(false);

    // Initial State map đúng với DB
    const [formData, setFormData] = useState({
        name: '',
        description: '',
        type: 'percentage', // percentage | fixed
        value: '',
        start_date: '',
        end_date: '',
        is_active: true,
        
        min_order_value: '',      // DB: unsignedBigInteger -> nullable
        max_discount_amount: '',  // DB: unsignedBigInteger -> nullable
        quantity: '',             // DB: integer -> default 0
        limit_per_user: 1,        // DB: integer -> default 1
        product_ids: []           // Mảng ID cho bảng trung gian
    });

    useEffect(() => {
        if (isEdit) {
            loadPromotionData();
        }
    }, [uuid]);

    const loadPromotionData = async () => {
        try {
            const promo = await getPromotion(uuid);
            // Map data từ API vào form
            setFormData({
                name: promo.name || '',
                description: promo.description || '',
                type: promo.type || 'percentage',
                value: promo.value || '',
                // Format date cho input datetime-local: YYYY-MM-DDTHH:mm
                start_date: promo.start_date ? new Date(promo.start_date).toISOString().slice(0, 16) : '',
                end_date: promo.end_date ? new Date(promo.end_date).toISOString().slice(0, 16) : '',
                is_active: promo.is_active ?? true,
                min_order_value: promo.min_order_value || '',
                max_discount_amount: promo.max_discount_amount || '',
                quantity: promo.quantity || '',
                limit_per_user: promo.limit_per_user || 1,
                // Lấy mảng ID từ relation products
                product_ids: promo.products ? promo.products.map(p => p.id) : []
            });
        } catch (err) {
            // Lỗi đã được handle trong hook
        }
    };

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
    };

    const handleProductSave = (ids) => {
        setFormData(prev => ({ ...prev, product_ids: ids }));
        setShowProductSelector(false);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null);

        // Validation Client-side
        if (!formData.name.trim()) return setError('Vui lòng nhập tên chương trình');
        if (!formData.value || formData.value < 0) return setError('Giá trị giảm giá không hợp lệ');
        if (!formData.start_date || !formData.end_date) return setError('Vui lòng chọn thời gian hiệu lực');
        if (new Date(formData.end_date) <= new Date(formData.start_date)) return setError('Ngày kết thúc phải sau ngày bắt đầu');

        // Chuẩn bị payload (Parse số để gửi lên API đúng format integer/bigint)
        const payload = {
            ...formData,
            value: parseInt(formData.value) || 0,
            min_order_value: formData.min_order_value ? parseInt(formData.min_order_value) : null,
            max_discount_amount: (formData.type === 'percentage' && formData.max_discount_amount) ? parseInt(formData.max_discount_amount) : null,
            quantity: formData.quantity ? parseInt(formData.quantity) : 0,
            limit_per_user: formData.limit_per_user ? parseInt(formData.limit_per_user) : 1,
        };

        setSaving(true);
        try {
            if (isEdit) {
                await updatePromotion(uuid, payload);
            } else {
                await createPromotion(payload);
            }
            navigate('/admin/promotions');
        } catch (err) {
            setError(err.message || 'Có lỗi xảy ra, vui lòng thử lại.');
        } finally {
            setSaving(false);
        }
    };

    if (hookLoading && isEdit) {
        return <div className="loading-state"><div className="spinner"></div><p>Đang tải dữ liệu...</p></div>;
    }

    return (
        <div className="promotion-form-page">
            <div className="form-container">
                {/* Header */}
                <div className="form-header">
                    <button onClick={() => navigate('/admin/promotions')} className="btn-back">
                        <ArrowLeft size={18} /> Quay lại
                    </button>
                    <h1><Tag size={24} /> {isEdit ? 'Cập nhật chương trình' : 'Tạo chương trình mới'}</h1>
                </div>

                {error && (
                    <div className="alert alert-error m-4">
                        <AlertCircle size={20} /> {error}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="promotion-form">
                    {/* SECTION 1: THÔNG TIN CƠ BẢN */}
                    <div className="form-section">
                        <h3>Thông tin cơ bản</h3>
                        <div className="form-group">
                            <label>Tên chương trình <span className="text-red-500">*</span></label>
                            <input 
                                type="text" name="name" 
                                value={formData.name} onChange={handleChange} 
                                className="form-input" placeholder="VD: Siêu Sale 12.12" required 
                            />
                        </div>
                        <div className="form-group">
                            <label>Mô tả</label>
                            <textarea 
                                name="description" 
                                value={formData.description} onChange={handleChange} 
                                className="form-textarea" rows="2" placeholder="Mô tả ngắn gọn về chương trình..." 
                            />
                        </div>
                    </div>

                    {/* SECTION 2: THIẾT LẬP GIẢM GIÁ */}
                    <div className="form-section">
                        <h3>Thiết lập mức giảm</h3>
                        <div className="form-row">
                            <div className="form-group">
                                <label>Loại giảm giá</label>
                                <div className="radio-group">
                                    <label className={`radio-option ${formData.type === 'percentage' ? 'active' : ''}`}>
                                        <input type="radio" name="type" value="percentage" checked={formData.type === 'percentage'} onChange={handleChange} />
                                        <Percent size={16} /> <span>Phần trăm (%)</span>
                                    </label>
                                    <label className={`radio-option ${formData.type === 'fixed' ? 'active' : ''}`}>
                                        <input type="radio" name="type" value="fixed" checked={formData.type === 'fixed'} onChange={handleChange} />
                                        <DollarSign size={16} /> <span>Tiền cố định (đ)</span>
                                    </label>
                                </div>
                            </div>
                            <div className="form-group">
                                <label>Giá trị giảm <span className="text-red-500">*</span></label>
                                <div className="input-with-suffix">
                                    <input 
                                        type="number" name="value" 
                                        value={formData.value} onChange={handleChange} 
                                        className="form-input" min="0" required placeholder="Nhập giá trị..."
                                    />
                                    <span className="suffix">{formData.type === 'percentage' ? '%' : 'đ'}</span>
                                </div>
                            </div>
                        </div>

                        {/* Điều kiện bổ sung */}
                        <div className="form-row">
                            <div className="form-group">
                                <label>Đơn tối thiểu (đ)</label>
                                <input 
                                    type="number" name="min_order_value" 
                                    value={formData.min_order_value} onChange={handleChange} 
                                    className="form-input" min="0" placeholder="Bỏ trống nếu không yêu cầu" 
                                />
                            </div>
                            {formData.type === 'percentage' && (
                                <div className="form-group">
                                    <label>Giảm tối đa (đ)</label>
                                    <input 
                                        type="number" name="max_discount_amount" 
                                        value={formData.max_discount_amount} onChange={handleChange} 
                                        className="form-input" min="0" placeholder="Bỏ trống nếu không giới hạn" 
                                    />
                                </div>
                            )}
                        </div>
                    </div>

                    {/* SECTION 3: PHẠM VI ÁP DỤNG */}
                    <div className="form-section">
                        <h3>Phạm vi & Giới hạn</h3>
                        <div className="form-row">
                            <div className="form-group">
                                <label>Tổng số lượng mã</label>
                                <input 
                                    type="number" name="quantity" 
                                    value={formData.quantity} onChange={handleChange} 
                                    className="form-input" min="0" placeholder="0 = Không giới hạn" 
                                />
                            </div>
                            <div className="form-group">
                                <label>Giới hạn dùng/khách</label>
                                <input 
                                    type="number" name="limit_per_user" 
                                    value={formData.limit_per_user} onChange={handleChange} 
                                    className="form-input" min="1" 
                                />
                            </div>
                        </div>

                        <div className="form-group">
                            <label className="flex justify-between items-center mb-2 font-medium text-gray-700">
                                <span>Sản phẩm áp dụng</span>
                                <button 
                                    type="button" 
                                    onClick={() => setShowProductSelector(true)} 
                                    className="text-sm text-blue-600 font-medium flex items-center gap-1 hover:bg-blue-50 px-2 py-1 rounded transition-colors"
                                >
                                    <Package size={16}/> Chọn sản phẩm
                                </button>
                            </label>
                            
                            <div className={`p-4 border rounded-lg text-sm transition-colors ${formData.product_ids.length > 0 ? 'bg-blue-50 border-blue-200 text-blue-800' : 'bg-gray-50 text-gray-500'}`}>
                                {formData.product_ids.length > 0 
                                    ? `✅ Đang áp dụng cho ${formData.product_ids.length} sản phẩm cụ thể.` 
                                    : '🌍 Áp dụng cho toàn bộ sản phẩm trong cửa hàng.'}
                            </div>
                        </div>
                    </div>

                    {/* SECTION 4: THỜI GIAN */}
                    <div className="form-section">
                        <h3>Thời gian hiệu lực</h3>
                        <div className="form-row">
                            <div className="form-group">
                                <label><Calendar size={16} /> Bắt đầu <span className="text-red-500">*</span></label>
                                <input 
                                    type="datetime-local" name="start_date" 
                                    value={formData.start_date} onChange={handleChange} 
                                    className="form-input" required 
                                />
                            </div>
                            <div className="form-group">
                                <label><Calendar size={16} /> Kết thúc <span className="text-red-500">*</span></label>
                                <input 
                                    type="datetime-local" name="end_date" 
                                    value={formData.end_date} onChange={handleChange} 
                                    className="form-input" required 
                                />
                            </div>
                        </div>
                        <div className="form-group pt-2">
                            <label className="checkbox-label select-none">
                                <input 
                                    type="checkbox" name="is_active" 
                                    checked={formData.is_active} onChange={handleChange} 
                                />
                                <span className="font-medium text-gray-700">Kích hoạt chương trình ngay</span>
                            </label>
                        </div>
                    </div>

                    <div className="form-actions">
                        <button type="button" onClick={() => navigate('/admin/promotions')} className="btn btn-secondary">Hủy bỏ</button>
                        <button type="submit" className="btn btn-primary" disabled={saving}>
                            {saving ? 'Đang lưu...' : <><Save size={18} /> {isEdit ? 'Cập nhật' : 'Tạo mới'}</>}
                        </button>
                    </div>
                </form>
            </div>

            {/* Modal Product Selector */}
            {showProductSelector && (
                <ProductSelector 
                    selectedIds={formData.product_ids} 
                    onSave={handleProductSave} 
                    onClose={() => setShowProductSelector(false)} 
                />
            )}
        </div>
    );
};

export default PromotionForm;