import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Save, Plus, Trash2, Image as ImageIcon } from 'lucide-react';
import ProductService from '@/services/admin/ProductService';
import CategoryService from '@/services/admin/CategoryService';
import './ProductForm.css';

const ProductForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    const isEditMode = !!uuid;

    const [formData, setFormData] = useState({
        name: '',
        category_uuid: '',
        description: '',
        price: '',
        sku: '',
        is_active: true,
        has_variants: false,
        variants: []
    });

    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState(null);

    // Initial variant state
    const [newVariant, setNewVariant] = useState({
        sku: '',
        price: '',
        stock: 0,
        attributes: [] // Array of attribute value UUIDs
    });

    useEffect(() => {
        fetchCategories();
        if (isEditMode) {
            fetchProductDetail();
        }
    }, [uuid]);

    const fetchCategories = async () => {
        try {
            const response = await CategoryService.getCategoryTree();
            if (response.success && response.data) {
                const flattenCategories = (cats, depth = 0) => {
                    return cats.reduce((acc, cat) => {
                        acc.push({ ...cat, depth });
                        if (cat.children && cat.children.length > 0) {
                            acc.push(...flattenCategories(cat.children, depth + 1));
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

    const fetchProductDetail = async () => {
        try {
            setLoading(true);
            const response = await ProductService.getProduct(uuid);
            if (response.success && response.data) {
                const product = response.data;
                setFormData({
                    name: product.name,
                    category_uuid: product.category?.uuid || '',
                    description: product.description || '',
                    price: product.price || '',
                    sku: product.sku || '',
                    is_active: product.is_active ?? true,
                    has_variants: product.has_variants ?? false,
                    variants: product.variants || []
                });
            }
        } catch (err) {
            setError('Không thể tải thông tin sản phẩm');
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
        setSubmitting(true);
        setError(null);

        try {
            const payload = { ...formData };

            // Convert price to number
            if (!payload.has_variants) {
                payload.price = parseFloat(payload.price);
                payload.variants = []; // Ensure variants is empty if has_variants is false
            } else {
                delete payload.price;
                delete payload.sku;

                // Format variants for API
                // Backend expects: variants: [{ sku, price, stock: [{warehouse_uuid, quantity}], attributes: [uuid] }]
                // Since we don't have warehouses or attributes yet, we might need to adjust.
                // Looking at ProductController:
                // required: ["sku", "price", "attributes", "stock"]
                // stock is array of {warehouse_uuid, quantity}

                // If the backend enforces this strictly, we are blocked without Warehouses and Attributes.
                // However, let's try to send what we can.
                // If the user just wants to create a simple product, they should uncheck "Has Variants".

                if (payload.variants.length === 0) {
                    throw new Error('Vui lòng thêm ít nhất một biến thể hoặc tắt chế độ "Có biến thể"');
                }

                payload.variants = payload.variants.map(v => ({
                    sku: v.sku,
                    price: parseFloat(v.price),
                    attributes: [], // We don't have attribute UUIDs yet
                    stock: [] // We don't have warehouse UUIDs yet
                }));
            }

            if (isEditMode) {
                await ProductService.updateProduct(uuid, payload);
                alert('Cập nhật sản phẩm thành công!');
            } else {
                await ProductService.createProduct(payload);
                alert('Tạo sản phẩm thành công!');
            }
            navigate('/admin/products');
        } catch (err) {
            setError(err.message || 'Có lỗi xảy ra');
        } finally {
            setSubmitting(false);
        }
    };

    if (loading) return <div className="product_form"><div className="loading-state"><div className="spinner"></div></div></div>;

    return (
        <div className="product_form">
            <div className="page-header">
                <button onClick={() => navigate('/admin/products')} className="btn-back">
                    <ArrowLeft size={20} />
                </button>
                <h1>{isEditMode ? 'Chỉnh sửa Sản phẩm' : 'Thêm Sản phẩm mới'}</h1>
            </div>

            <div className="form-container">
                {error && <div className="alert alert-danger">{error}</div>}

                <form onSubmit={handleSubmit} className="admin-form">
                    {/* Basic Info */}
                    <div className="form-section">
                        <h3>Thông tin chung</h3>
                        <div className="form-group">
                            <label>Tên sản phẩm <span className="text-danger">*</span></label>
                            <input
                                type="text"
                                name="name"
                                value={formData.name}
                                onChange={handleChange}
                                required
                                className="form-control"
                                placeholder="Nhập tên sản phẩm"
                            />
                        </div>

                        <div className="form-row">
                            <div className="form-group">
                                <label>Danh mục <span className="text-danger">*</span></label>
                                <select
                                    name="category_uuid"
                                    value={formData.category_uuid}
                                    onChange={handleChange}
                                    required
                                    className="form-control"
                                >
                                    <option value="">-- Chọn danh mục --</option>
                                    {categories.map(cat => (
                                        <option key={cat.uuid} value={cat.uuid}>
                                            {Array(cat.depth).fill('\u00A0\u00A0\u00A0').join('')}
                                            {cat.depth > 0 ? '└─ ' : ''}
                                            {cat.name}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="form-group checkbox-group" style={{ marginTop: '32px' }}>
                                <label className="checkbox-label">
                                    <div>
                                        <input
                                            type="checkbox"
                                            name="is_active"
                                            checked={formData.is_active}
                                            onChange={handleChange}
                                        />
                                    </div>
                                    <div>
                                        Đang kinh doanh
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div className="form-group">
                            <label>Mô tả</label>
                            <textarea
                                name="description"
                                value={formData.description}
                                onChange={handleChange}
                                className="form-control"
                                rows="5"
                                placeholder="Mô tả chi tiết sản phẩm..."
                            ></textarea>
                        </div>
                    </div>

                    {/* Pricing & Inventory (Simple Product) */}
                    {!formData.has_variants && (
                        <div className="form-section">
                            <h3>Giá & Kho hàng</h3>
                            <div className="form-row">
                                <div className="form-group">
                                    <label>Giá bán (VNĐ) <span className="text-danger">*</span></label>
                                    <input
                                        type="number"
                                        name="price"
                                        value={formData.price}
                                        onChange={handleChange}
                                        required={!formData.has_variants}
                                        className="form-control"
                                        min="0"
                                    />
                                </div>
                                <div className="form-group">
                                    <label>Mã SKU <span className="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="sku"
                                        value={formData.sku}
                                        onChange={handleChange}
                                        required={!formData.has_variants}
                                        className="form-control"
                                    />
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Variants Section */}
                    <div className="form-section">
                        <div className="form-group checkbox-group">
                            <label className="checkbox-label">
                                <input
                                    type="checkbox"
                                    name="has_variants"
                                    checked={formData.has_variants}
                                    onChange={handleChange}
                                />
                                <span>
                                    Sản phẩm có nhiều biến thể (Màu sắc, Kích thước...)
                                </span>
                            </label>
                        </div>

                        {formData.has_variants && (
                            <div className="variants-manager">
                                <h4>Danh sách biến thể</h4>

                                {formData.variants.length > 0 && (
                                    <table className="table table-bordered variants-table">
                                        <thead>
                                            <tr>
                                                <th>SKU</th>
                                                <th>Giá</th>
                                                <th>Tồn kho</th>
                                                <th>Thuộc tính (VD: Đỏ, XL)</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {formData.variants.map((variant, index) => (
                                                <tr key={index}>
                                                    <td>{variant.sku}</td>
                                                    <td>{parseInt(variant.price).toLocaleString()} đ</td>
                                                    <td>{variant.stock}</td>
                                                    <td>
                                                        {variant.attributes && variant.attributes.length > 0
                                                            ? variant.attributes.map(a => a.value || a).join(', ')
                                                            : <span className="text-muted">Mặc định</span>}
                                                    </td>
                                                    <td>
                                                        <button
                                                            type="button"
                                                            className="btn-icon btn-danger"
                                                            onClick={() => {
                                                                const newVariants = [...formData.variants];
                                                                newVariants.splice(index, 1);
                                                                setFormData(prev => ({ ...prev, variants: newVariants }));
                                                            }}
                                                        >
                                                            <Trash2 size={16} />
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                )}

                                <div className="add-variant-box">
                                    <h5>Thêm biến thể mới</h5>
                                    <div className="form-row">
                                        <div className="form-group">
                                            <label>SKU</label>
                                            <input
                                                type="text"
                                                className="form-control"
                                                value={newVariant.sku}
                                                onChange={e => setNewVariant({ ...newVariant, sku: e.target.value })}
                                                placeholder="VD: AO-DO-XL"
                                            />
                                        </div>
                                        <div className="form-group">
                                            <label>Giá</label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                value={newVariant.price}
                                                onChange={e => setNewVariant({ ...newVariant, price: e.target.value })}
                                                placeholder="0"
                                            />
                                        </div>
                                        <div className="form-group">
                                            <label>Tồn kho</label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                value={newVariant.stock}
                                                onChange={e => setNewVariant({ ...newVariant, stock: e.target.value })}
                                                placeholder="0"
                                            />
                                        </div>
                                    </div>
                                    {/* Note: Attribute selection is simplified for now */}
                                    <div className="form-group">
                                        <label>Tên thuộc tính (Tạm thời nhập text)</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            placeholder="VD: Màu Đỏ, Size XL (Phân cách bằng dấu phẩy)"
                                            onBlur={e => {
                                                const attrs = e.target.value.split(',').map(s => s.trim()).filter(Boolean);
                                                // In a real app, we would map these to AttributeValue UUIDs
                                                // For now, we'll just store them as strings to display, 
                                                // BUT the backend expects UUIDs for 'attributes'. 
                                                // Since we don't have Attribute management yet, we might hit an issue here.
                                                // Let's assume for now we just send basic variant info without attributes if the backend allows,
                                                // OR we need to implement Attribute selection.
                                                // Given the complexity, I'll stick to basic fields first.
                                            }}
                                        />
                                        <small className="text-muted">Chức năng chọn thuộc tính nâng cao sẽ được cập nhật sau.</small>
                                    </div>

                                    <button
                                        type="button"
                                        className="btn btn-secondary btn-sm"
                                        onClick={() => {
                                            if (!newVariant.sku || !newVariant.price) {
                                                alert('Vui lòng nhập SKU và Giá');
                                                return;
                                            }
                                            setFormData(prev => ({
                                                ...prev,
                                                variants: [...prev.variants, { ...newVariant, attributes: [] }] // Empty attributes for now
                                            }));
                                            setNewVariant({ sku: '', price: '', stock: 0, attributes: [] });
                                        }}
                                    >
                                        <Plus size={16} /> Thêm biến thể
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>

                    <div className="form-actions">
                        <button
                            type="button"
                            onClick={() => navigate('/admin/products')}
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

export default ProductForm;
