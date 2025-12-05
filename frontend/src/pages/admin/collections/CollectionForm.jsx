import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Layers, Save, AlertCircle, Image, X } from 'lucide-react';
import CollectionService from '@/services/admin/CollectionService';
import './CollectionManagement.css';

const CollectionForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    const isEdit = !!uuid;

    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState(null);

    const [formData, setFormData] = useState({
        name: '',
        slug: '',
        description: '',
        image: '',
        is_active: true
    });

    useEffect(() => {
        if (isEdit) fetchCollection();
    }, [uuid]);

    const fetchCollection = async () => {
        try {
            setLoading(true);
            const response = await CollectionService.getById(uuid);
            const data = response.data;
            setFormData({
                name: data.name || '',
                slug: data.slug || '',
                description: data.description || '',
                image: data.image || '',
                is_active: data.is_active ?? true
            });
        } catch (err) {
            setError('Không thể tải thông tin bộ sưu tập');
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

        if (name === 'name' && !isEdit) {
            setFormData(prev => ({
                ...prev,
                slug: value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')
            }));
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null);

        if (!formData.name.trim()) {
            setError('Vui lòng nhập tên bộ sưu tập');
            return;
        }

        setSaving(true);
        try {
            if (isEdit) {
                await CollectionService.update(uuid, formData);
            } else {
                await CollectionService.create(formData);
            }
            navigate('/admin/collections');
        } catch (err) {
            setError(err.message || 'Không thể lưu bộ sưu tập');
        } finally {
            setSaving(false);
        }
    };

    if (loading) {
        return <div className="loading-state"><div className="spinner"></div><p>Đang tải...</p></div>;
    }

    return (
        <div className="collection-form-page">
            <div className="form-container">
                <div className="form-header">
                    <button onClick={() => navigate('/admin/collections')} className="btn-back">
                        <ArrowLeft size={20} /> Quay lại
                    </button>
                    <h1><Layers size={24} /> {isEdit ? 'Sửa bộ sưu tập' : 'Tạo bộ sưu tập mới'}</h1>
                </div>

                {error && <div className="alert alert-error"><AlertCircle size={20} />{error}</div>}

                <form onSubmit={handleSubmit} className="collection-form">
                    <div className="form-grid">
                        <div className="form-main">
                            <div className="form-section">
                                <div className="form-group">
                                    <label>Tên bộ sưu tập *</label>
                                    <input
                                        type="text"
                                        name="name"
                                        value={formData.name}
                                        onChange={handleChange}
                                        className="form-input"
                                        placeholder="VD: Bộ sưu tập Xuân 2025"
                                        required
                                    />
                                </div>

                                <div className="form-group">
                                    <label>Slug</label>
                                    <input
                                        type="text"
                                        name="slug"
                                        value={formData.slug}
                                        onChange={handleChange}
                                        className="form-input"
                                        placeholder="bo-suu-tap-xuan-2025"
                                    />
                                </div>

                                <div className="form-group">
                                    <label>Mô tả</label>
                                    <textarea
                                        name="description"
                                        value={formData.description}
                                        onChange={handleChange}
                                        className="form-textarea"
                                        placeholder="Mô tả về bộ sưu tập..."
                                        rows="4"
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="form-sidebar">
                            <div className="form-section">
                                <h3>Hình ảnh</h3>
                                <div className="image-upload">
                                    {formData.image ? (
                                        <div className="image-preview">
                                            <img src={formData.image} alt="Preview" />
                                            <button type="button" onClick={() => setFormData(prev => ({ ...prev, image: '' }))} className="remove-btn">
                                                <X size={16} />
                                            </button>
                                        </div>
                                    ) : (
                                        <div className="upload-placeholder">
                                            <Image size={32} />
                                            <span>Chưa có hình ảnh</span>
                                        </div>
                                    )}
                                    <input
                                        type="text"
                                        name="image"
                                        value={formData.image}
                                        onChange={handleChange}
                                        className="form-input"
                                        placeholder="URL hình ảnh"
                                    />
                                </div>
                            </div>

                            <div className="form-section">
                                <h3>Trạng thái</h3>
                                <label className="checkbox-label">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        checked={formData.is_active}
                                        onChange={handleChange}
                                    />
                                    <span>Hiển thị bộ sưu tập</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div className="form-actions">
                        <button type="button" onClick={() => navigate('/admin/collections')} className="btn btn-secondary">
                            Hủy bỏ
                        </button>
                        <button type="submit" className="btn btn-primary" disabled={saving}>
                            {saving ? 'Đang lưu...' : <><Save size={18} /> {isEdit ? 'Cập nhật' : 'Tạo mới'}</>}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default CollectionForm;
