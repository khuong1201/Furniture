import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import {
    ArrowLeft,
    Save,
    Upload,
    Eye,
    EyeOff,
    Link,
    Layers,
    Hash,
    Info,
    CheckCircle,
    Settings
} from 'lucide-react';
import CategoryService from '@/services/CategoryService';
import './CategoryManagement.css';

const CategoryForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    const isEditMode = !!uuid;

    const [formData, setFormData] = useState({
        name: '',
        slug: '',
        parent_id: '',
        description: '',
        is_active: true,
        meta_title: '',
        meta_description: '',
        meta_keywords: ''
    });

    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(false);
    const [slugModified, setSlugModified] = useState(false);

    useEffect(() => {
        fetchCategories();
        if (isEditMode) {
            fetchCategoryDetail();
        }
    }, [uuid]);

    const fetchCategories = async () => {
        try {
            const response = await CategoryService.getCategoryTree();
            if (response.success && response.data) {
                const flattenCategories = (cats, depth = 0) => {
                    return cats.reduce((acc, cat) => {
                        if (isEditMode && cat.uuid === uuid) return acc;
                        acc.push({ ...cat, depth });
                        if (cat.all_children && cat.all_children.length > 0) {
                            acc.push(...flattenCategories(cat.all_children, depth + 1));
                        }
                        return acc;
                    }, []);
                };
                setCategories(flattenCategories(response.data));
            }
        } catch (err) {
            console.error('Error fetching categories:', err);
        }
    };

    const fetchCategoryDetail = async () => {
        try {
            setLoading(true);
            const response = await CategoryService.getCategory(uuid);
            if (response.success && response.data) {
                const category = response.data;
                setFormData({
                    name: category.name || '',
                    slug: category.slug || '',
                    parent_id: category.parent_id || '',
                    description: category.description || '',
                    is_active: category.is_active ?? true,
                    meta_title: category.meta_title || '',
                    meta_description: category.meta_description || '',
                    meta_keywords: category.meta_keywords || ''
                });
                setSlugModified(true);
            }
        } catch (err) {
            setError('Không thể tải thông tin danh mục');
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

        if (name === 'name' && !slugModified && !isEditMode) {
            const generatedSlug = generateSlug(value);
            setFormData(prev => ({ ...prev, slug: generatedSlug }));
        }
    };

    const handleSlugChange = (e) => {
        setFormData(prev => ({ ...prev, slug: generateSlug(e.target.value) }));
        setSlugModified(true);
    };

    const generateSlug = (text) => {
        return text
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        setError(null);
        setSuccess(false);

        try {
            if (isEditMode) {
                await CategoryService.updateCategory(uuid, formData);
            } else {
                await CategoryService.createCategory(formData);
            }

            setSuccess(true);
            setTimeout(() => {
                navigate('/admin/categories');
            }, 1500);
        } catch (err) {
            setError(err.response?.data?.message || err.message || 'Có lỗi xảy ra');
        } finally {
            setSubmitting(false);
        }
    };

    if (loading) {
        return (
            <div className="loading-state">
                <div className="spinner-gold"></div>
                <p>Đang tải thông tin...</p>
            </div>
        );
    }

    return (
        <div className="category-form-container">
            {/* Header */}
            <div className="form-header">
                <button onClick={() => navigate('/admin/categories')} className="back-btn">
                    <ArrowLeft size={20} />
                    Quay lại
                </button>
                <div className="header-content">
                    <h1>
                        {isEditMode ? 'Chỉnh sửa Danh mục' : 'Thêm Danh mục mới'}
                    </h1>
                    <p className="form-subtitle">
                        {isEditMode ? 'Cập nhật thông tin danh mục của bạn' : 'Tạo danh mục mới cho sản phẩm'}
                    </p>
                </div>
            </div>

            {/* Main Form */}
            <div className="form-wrapper">
                {success && (
                    <div className="success-alert">
                        <CheckCircle size={20} />
                        <span>Danh mục đã được {isEditMode ? 'cập nhật' : 'tạo'} thành công!</span>
                    </div>
                )}

                {error && (
                    <div className="error-alert">
                        <Info size={20} />
                        <span>{error}</span>
                    </div>
                )}

                <form onSubmit={handleSubmit} className="category-form">
                    <div className="form-grid">
                        {/* Left Column - Basic Info */}
                        <div className="form-column">
                            <div className="form-card">
                                <div className="card-header">
                                    <h3>
                                        <Layers size={20} />
                                        Thông tin cơ bản
                                    </h3>
                                </div>
                                <div className="card-body">
                                    <div className="form-group">
                                        <label className="form-label required">
                                            Tên danh mục
                                        </label>
                                        <input
                                            type="text"
                                            name="name"
                                            value={formData.name}
                                            onChange={handleChange}
                                            required
                                            className="form-input"
                                            placeholder="Ví dụ: Sofa phòng khách"
                                        />
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label required">
                                            <Link size={16} />
                                            Slug (URL)
                                        </label>
                                        <div className="input-with-prefix">
                                            <span className="input-prefix">/categories/</span>
                                            <input
                                                type="text"
                                                name="slug"
                                                value={formData.slug}
                                                onChange={handleSlugChange}
                                                required
                                                className="form-input"
                                                placeholder="sofa-phong-khach"
                                            />
                                        </div>
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label">
                                            <Layers size={16} />
                                            Danh mục cha
                                        </label>
                                        <select
                                            name="parent_id"
                                            value={formData.parent_id}
                                            onChange={handleChange}
                                            className="form-input"
                                        >
                                            <option value="">-- Không có (Danh mục gốc) --</option>
                                            {categories.map(cat => (
                                                <option key={cat.id} value={cat.id}>
                                                    {Array(cat.depth).fill('\u00A0\u00A0').join('')}
                                                    {cat.depth > 0 ? '├─ ' : ''}
                                                    {cat.name}
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label">
                                            <Info size={16} />
                                            Mô tả
                                        </label>
                                        <textarea
                                            name="description"
                                            value={formData.description}
                                            onChange={handleChange}
                                            className="form-textarea"
                                            rows="4"
                                            placeholder="Mô tả chi tiết về danh mục này..."
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Right Column - Settings & SEO */}
                        <div className="form-column">
                            <div className="form-card">
                                <div className="card-header">
                                    <h3>
                                        <Settings size={20} />
                                        Cài đặt & SEO
                                    </h3>
                                </div>
                                <div className="card-body">
                                    <div className="form-group">
                                        <label className="checkbox-label">
                                            <input
                                                type="checkbox"
                                                name="is_active"
                                                checked={formData.is_active}
                                                onChange={handleChange}
                                                className="checkbox-input"
                                            />
                                            <span className="checkbox-custom"></span>
                                            <span className="checkbox-text">
                                                {formData.is_active ? (
                                                    <>
                                                        <Eye size={16} />
                                                        Đang hiển thị
                                                    </>
                                                ) : (
                                                    <>
                                                        <EyeOff size={16} />
                                                        Đang ẩn
                                                    </>
                                                )}
                                            </span>
                                        </label>
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label">
                                            <Hash size={16} />
                                            Meta Title
                                        </label>
                                        <input
                                            type="text"
                                            name="meta_title"
                                            value={formData.meta_title}
                                            onChange={handleChange}
                                            className="form-input"
                                            placeholder="Tối ưu hóa SEO"
                                            maxLength="60"
                                        />
                                        <div className="input-counter">
                                            {formData.meta_title.length}/60
                                        </div>
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label">
                                            <Info size={16} />
                                            Meta Description
                                        </label>
                                        <textarea
                                            name="meta_description"
                                            value={formData.meta_description}
                                            onChange={handleChange}
                                            className="form-textarea"
                                            rows="3"
                                            placeholder="Mô tả ngắn cho SEO..."
                                            maxLength="160"
                                        />
                                        <div className="input-counter">
                                            {formData.meta_description.length}/160
                                        </div>
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label">
                                            <Hash size={16} />
                                            Meta Keywords
                                        </label>
                                        <input
                                            type="text"
                                            name="meta_keywords"
                                            value={formData.meta_keywords}
                                            onChange={handleChange}
                                            className="form-input"
                                            placeholder="Từ khóa SEO, phân cách bằng dấu phẩy"
                                        />
                                    </div>
                                </div>
                            </div>

                            {/* Form Actions */}
                            <div className="form-actions-card">
                                <div className="form-actions">
                                    <button
                                        type="button"
                                        onClick={() => navigate('/admin/categories')}
                                        className="btn btn-secondary"
                                        disabled={submitting}
                                    >
                                        Hủy bỏ
                                    </button>
                                    <button
                                        type="submit"
                                        className="btn btn-primary"
                                        disabled={submitting || success}
                                    >
                                        {submitting ? (
                                            <>
                                                <div className="spinner-small"></div>
                                                Đang xử lý...
                                            </>
                                        ) : success ? (
                                            <>
                                                <CheckCircle size={18} />
                                                Thành công!
                                            </>
                                        ) : (
                                            <>
                                                <Save size={18} />
                                                {isEditMode ? 'Cập nhật' : 'Tạo mới'}
                                            </>
                                        )}
                                    </button>
                                </div>

                                {isEditMode && (
                                    <div className="form-info">
                                        <Info size={16} />
                                        <p>Được cập nhật lần cuối: {formData.updated_at}</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default CategoryForm;