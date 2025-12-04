import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Truck, Save, AlertCircle, DollarSign, Clock } from 'lucide-react';
import ShippingService from '@/services/ShippingService';
import './ShippingManagement.css';

const ShippingForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    const isEdit = !!uuid;

    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState(null);

    const [formData, setFormData] = useState({
        name: '',
        description: '',
        base_cost: '',
        cost_per_kg: '',
        estimated_days: '',
        is_active: true
    });

    useEffect(() => { if (isEdit) fetchShipping(); }, [uuid]);

    const fetchShipping = async () => {
        try {
            setLoading(true);
            const response = await ShippingService.getById(uuid);
            const data = response.data;
            setFormData({
                name: data.name || '',
                description: data.description || '',
                base_cost: data.base_cost || '',
                cost_per_kg: data.cost_per_kg || '',
                estimated_days: data.estimated_days || '',
                is_active: data.is_active ?? true
            });
        } catch (err) {
            setError('Không thể tải thông tin');
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({ ...prev, [name]: type === 'checkbox' ? checked : value }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!formData.name.trim()) { setError('Vui lòng nhập tên'); return; }

        setSaving(true);
        try {
            if (isEdit) await ShippingService.update(uuid, formData);
            else await ShippingService.create(formData);
            navigate('/admin/shippings');
        } catch (err) {
            setError(err.message || 'Không thể lưu');
        } finally {
            setSaving(false);
        }
    };

    if (loading) return <div className="loading-state"><div className="spinner"></div></div>;

    return (
        <div className="shipping-form-page">
            <div className="form-container">
                <div className="form-header">
                    <button onClick={() => navigate('/admin/shippings')} className="btn-back">
                        <ArrowLeft size={20} /> Quay lại
                    </button>
                    <h1><Truck size={24} /> {isEdit ? 'Sửa phương thức' : 'Thêm phương thức vận chuyển'}</h1>
                </div>

                {error && <div className="alert alert-error"><AlertCircle size={20} />{error}</div>}

                <form onSubmit={handleSubmit} className="shipping-form">
                    <div className="form-section">
                        <h3>Thông tin cơ bản</h3>
                        <div className="form-group">
                            <label>Tên phương thức *</label>
                            <input type="text" name="name" value={formData.name} onChange={handleChange}
                                className="form-input" placeholder="VD: Giao hàng tiêu chuẩn" required />
                        </div>
                        <div className="form-group">
                            <label>Mô tả</label>
                            <textarea name="description" value={formData.description} onChange={handleChange}
                                className="form-textarea" placeholder="Mô tả chi tiết..." rows="3" />
                        </div>
                    </div>

                    <div className="form-section">
                        <h3>Chi phí & Thời gian</h3>
                        <div className="form-row">
                            <div className="form-group">
                                <label><DollarSign size={14} /> Phí cơ bản (VNĐ)</label>
                                <input type="number" name="base_cost" value={formData.base_cost} onChange={handleChange}
                                    className="form-input" placeholder="30000" min="0" />
                            </div>
                            <div className="form-group">
                                <label><DollarSign size={14} /> Phí theo kg (VNĐ)</label>
                                <input type="number" name="cost_per_kg" value={formData.cost_per_kg} onChange={handleChange}
                                    className="form-input" placeholder="5000" min="0" />
                            </div>
                        </div>
                        <div className="form-group">
                            <label><Clock size={14} /> Thời gian giao dự kiến</label>
                            <input type="text" name="estimated_days" value={formData.estimated_days} onChange={handleChange}
                                className="form-input" placeholder="3-5 ngày" />
                        </div>
                    </div>

                    <div className="form-section">
                        <label className="checkbox-label">
                            <input type="checkbox" name="is_active" checked={formData.is_active} onChange={handleChange} />
                            <span>Kích hoạt phương thức này</span>
                        </label>
                    </div>

                    <div className="form-actions">
                        <button type="button" onClick={() => navigate('/admin/shippings')} className="btn btn-secondary">Hủy</button>
                        <button type="submit" className="btn btn-primary" disabled={saving}>
                            {saving ? 'Đang lưu...' : <><Save size={18} /> {isEdit ? 'Cập nhật' : 'Tạo mới'}</>}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default ShippingForm;
