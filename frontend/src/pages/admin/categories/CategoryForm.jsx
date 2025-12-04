import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Save } from 'lucide-react';
import CategoryService from '@/services/CategoryService';
import '../products/ProductList.css'; // Reuse styles
import './CategoryForm.css';

const CategoryForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams(); // If uuid exists, it's Edit mode
    const isEditMode = !!uuid;

    const [formData, setFormData] = useState({
        name: '',
        slug: '',
        parent_id: '',
        description: '',
        is_active: true
    });

    const [categories, setCategories] = useState([]); // For parent category select
    const [loading, setLoading] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState(null);

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
                // Flatten tree for select options
                const flattenCategories = (cats, depth = 0) => {
                    return cats.reduce((acc, cat) => {
                        // Skip current category and its children if in edit mode (prevent cycle)
                        if (isEditMode && cat.uuid === uuid) return acc;

                        acc.push({ ...cat, depth });
                        if (cat.children && cat.children.length > 0) {
                            acc.push(...flattenCategories(cat.children, depth + 1));
                        }
                        return acc;
                    }, []);
                };

                const flatData = flattenCategories(response.data);
                setCategories(flatData);
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
                    name: category.name,
                    slug: category.slug,
                    parent_id: category.parent_id || '',
                    description: category.description || '',
                    is_active: category.is_active ?? true
                });
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

        // Auto-generate slug from name
        if (name === 'name' && !isEditMode) {
            const slug = value
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');

            setFormData(prev => ({ ...prev, slug }));
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        setError(null);

        try {
            if (isEditMode) {
                await CategoryService.updateCategory(uuid, formData);
                alert('Cập nhật danh mục thành công!');
            } else {
                await CategoryService.createCategory(formData);
                alert('Tạo danh mục thành công!');
            }
            navigate('/admin/categories');
        } catch (err) {
            setError(err.message || 'Có lỗi xảy ra');
        } finally {
            setSubmitting(false);
        }
    };

    if (loading) {
        return <div className="loading-state"><div className="spinner"></div></div>;
    }

    return (
        <div className="product-list">
            <div className="page-header">
                <div className="header-left">
                    <button onClick={() => navigate('/admin/categories')} className="btn-back">
                        <ArrowLeft size={20} />
                    </button>
                    <div>
                        <h1>{isEditMode ? 'Chỉnh sửa Danh mục' : 'Thêm Danh mục mới'}</h1>
                    </div>
                </div>
            </div>

            <div className="form-container" style={{ maxWidth: '800px', margin: '0 auto' }}>
                {error && <div className="alert alert-danger">{error}</div>}

                <form onSubmit={handleSubmit} className="admin-form">
                    <div className="form-group">
                        <label>Tên danh mục <span className="text-danger">*</span></label>
                        <input
                            type="text"
                            name="name"
                            value={formData.name}
                            onChange={handleChange}
                            required
                            className="form-control"
                            placeholder="Nhập tên danh mục"
                        />
                    </div>

                    <div className="form-group">
                        <label>Slug (URL) <span className="text-danger">*</span></label>
                        <input
                            type="text"
                            name="slug"
                            value={formData.slug}
                            onChange={handleChange}
                            required
                            className="form-control"
                            placeholder="ten-danh-muc"
                        />
                    </div>

                    <div className="form-group">
                        <label>Danh mục cha</label>
                        <select
                            name="parent_id"
                            value={formData.parent_id}
                            onChange={handleChange}
                            className="form-control"
                        >
                            <option value="">-- Không có (Danh mục gốc) --</option>
                            {categories.map(cat => (
                                <option key={cat.id} value={cat.id}>
                                    {Array(cat.depth).fill('\u00A0\u00A0\u00A0').join('')}
                                    {cat.depth > 0 ? '└─ ' : ''}
                                    {cat.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="form-group">
                        <label>Mô tả</label>
                        <textarea
                            name="description"
                            value={formData.description}
                            onChange={handleChange}
                            className="form-control"
                            rows="4"
                            placeholder="Mô tả về danh mục này..."
                        ></textarea>
                    </div>

                    <div className="form-group checkbox-group">
                        <label className="checkbox-label">
                            <input
                                type="checkbox"
                                name="is_active"
                                checked={formData.is_active}
                                onChange={handleChange}
                            />
                            Kích hoạt danh mục này
                        </label>
                    </div>

                    <div className="form-actions">
                        <button
                            type="button"
                            onClick={() => navigate('/admin/categories')}
                            className="btn btn-secondary"
                        >
                            Hủy bỏ
                        </button>
                        <button
                            type="submit"
                            className="btn btn-primary"
                            disabled={submitting}
                        >
                            <Save size={18} />
                            {submitting ? 'Đang lưu...' : (isEditMode ? 'Cập nhật' : 'Tạo mới')}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default CategoryForm;
