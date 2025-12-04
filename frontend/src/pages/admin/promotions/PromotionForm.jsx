import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import {
    ArrowLeft, Tag, Calendar, Percent, DollarSign, Save, AlertCircle
} from 'lucide-react';
import PromotionService from '@/services/PromotionService';
import './PromotionManagement.css';

const PromotionForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    const isEdit = !!uuid;

    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState(null);

    const [formData, setFormData] = useState({
        name: '',
        description: '',
        type: 'percentage',
        value: '',
        start_date: '',
        end_date: '',
        status: true
    });

    useEffect(() => {
        if (isEdit) {
            fetchPromotion();
        }
    }, [uuid]);

    const fetchPromotion = async () => {
        try {
            setLoading(true);
            const response = await PromotionService.getById(uuid);
            const promo = response.data;
            setFormData({
                name: promo.name || '',
                description: promo.description || '',
                type: promo.type || 'percentage',
                value: promo.value || '',
                start_date: promo.start_date?.split('T')[0] || '',
                end_date: promo.end_date?.split('T')[0] || '',
                status: promo.status ?? true
            });
        } catch (err) {
            setError('Không thể tải thông tin khuyến mãi');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null);

        if (!formData.name.trim()) {
            setError('Vui lòng nhập tên khuyến mãi');
            return;
        }
        if (!formData.value) {
            setError('Vui lòng nhập giá trị giảm giá');
            return;
        }
        if (!formData.start_date || !formData.end_date) {
            setError('Vui lòng chọn thời gian hiệu lực');
            return;
        }

        setSaving(true);
        try {
            if (isEdit) {
                await PromotionService.update(uuid, formData);
            } else {
                await PromotionService.create(formData);
            }
            navigate('/admin/promotions');
        } catch (err) {
            setError(err.message || 'Không thể lưu khuyến mãi');
        } finally {
            setSaving(false);
        }
    };

    if (loading) {
        return (
            <div className="loading-state">
                <div className="spinner"></div>
                <p>Đang tải...</p>
            </div>
        );
    }

    return (
        <div className="promotion-form-page">
            <div className="form-container">
                {/* Header */}
                <div className="form-header">
                    <button onClick={() => navigate('/admin/promotions')} className="btn-back">
                        <ArrowLeft size={20} />
                        Quay lại
                    </button>
                    <h1>
                        <Tag size={24} />
                        {isEdit ? 'Sửa khuyến mãi' : 'Tạo khuyến mãi mới'}
                    </h1>
                </div>

                {error && (
                    <div className="alert alert-error">
                        <AlertCircle size={20} />
                        {error}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="promotion-form">
                    <div className="form-section">
                        <h3>Thông tin cơ bản</h3>

                        <div className="form-group">
                            <label>Tên chương trình *</label>
                            <input
                                type="text"
                                name="name"
                                value={formData.name}
                                onChange={handleChange}
                                className="form-input"
                                placeholder="VD: Flash Sale Cuối Tuần"
                                required
                            />
                        </div>

                        <div className="form-group">
                            <label>Mô tả</label>
                            <textarea
                                name="description"
                                value={formData.description}
                                onChange={handleChange}
                                className="form-textarea"
                                placeholder="Mô tả chi tiết về chương trình..."
                                rows="3"
                            />
                        </div>
                    </div>

                    <div className="form-section">
                        <h3>Thiết lập giảm giá</h3>

                        <div className="form-row">
                            <div className="form-group">
                                <label>Loại giảm giá *</label>
                                <div className="radio-group">
                                    <label className={`radio-option ${formData.type === 'percentage' ? 'active' : ''}`}>
                                        <input
                                            type="radio"
                                            name="type"
                                            value="percentage"
                                            checked={formData.type === 'percentage'}
                                            onChange={handleChange}
                                        />
                                        <Percent size={18} />
                                        <span>Phần trăm</span>
                                    </label>
                                    <label className={`radio-option ${formData.type === 'fixed' ? 'active' : ''}`}>
                                        <input
                                            type="radio"
                                            name="type"
                                            value="fixed"
                                            checked={formData.type === 'fixed'}
                                            onChange={handleChange}
                                        />
                                        <DollarSign size={18} />
                                        <span>Số tiền cố định</span>
                                    </label>
                                </div>
                            </div>

                            <div className="form-group">
                                <label>Giá trị giảm *</label>
                                <div className="input-with-suffix">
                                    <input
                                        type="number"
                                        name="value"
                                        value={formData.value}
                                        onChange={handleChange}
                                        className="form-input"
                                        placeholder={formData.type === 'percentage' ? '10' : '50000'}
                                        min="0"
                                        max={formData.type === 'percentage' ? '100' : undefined}
                                        required
                                    />
                                    <span className="suffix">
                                        {formData.type === 'percentage' ? '%' : 'đ'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="form-section">
                        <h3>Thời gian hiệu lực</h3>

                        <div className="form-row">
                            <div className="form-group">
                                <label>
                                    <Calendar size={16} />
                                    Ngày bắt đầu *
                                </label>
                                <input
                                    type="date"
                                    name="start_date"
                                    value={formData.start_date}
                                    onChange={handleChange}
                                    className="form-input"
                                    required
                                />
                            </div>

                            <div className="form-group">
                                <label>
                                    <Calendar size={16} />
                                    Ngày kết thúc *
                                </label>
                                <input
                                    type="date"
                                    name="end_date"
                                    value={formData.end_date}
                                    onChange={handleChange}
                                    className="form-input"
                                    required
                                />
                            </div>
                        </div>

                        <div className="form-group">
                            <label className="checkbox-label">
                                <input
                                    type="checkbox"
                                    name="status"
                                    checked={formData.status}
                                    onChange={handleChange}
                                />
                                <span>Kích hoạt khuyến mãi</span>
                            </label>
                        </div>
                    </div>

                    <div className="form-actions">
                        <button
                            type="button"
                            onClick={() => navigate('/admin/promotions')}
                            className="btn btn-secondary"
                        >
                            Hủy bỏ
                        </button>
                        <button
                            type="submit"
                            className="btn btn-primary"
                            disabled={saving}
                        >
                            {saving ? 'Đang lưu...' : (
                                <>
                                    <Save size={18} />
                                    {isEdit ? 'Cập nhật' : 'Tạo khuyến mãi'}
                                </>
                            )}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default PromotionForm;
