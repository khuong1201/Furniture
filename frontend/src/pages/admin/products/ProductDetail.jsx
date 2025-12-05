import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Edit, Package, Tag, FolderTree, Image as ImageIcon } from 'lucide-react';
import ProductService from '@/services/admin/ProductService';
import './ProductDetail.css';

const ProductDetail = () => {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const [product, setProduct] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchProductDetail();
    }, [uuid]);

    const fetchProductDetail = async () => {
        try {
            setLoading(true);
            const response = await ProductService.getProduct(uuid);

            if (response.success && response.data) {
                setProduct(response.data);
            }
        } catch (err) {
            setError(err.message || 'Không thể tải chi tiết sản phẩm');
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="product_detail">
                <div className="loading-state">
                    <div className="spinner"></div>
                    <p>Đang tải dữ liệu...</p>
                </div>
            </div>
        );
    }

    if (error || !product) {
        return (
            <div className="product_detail">
                <div className="error-state">
                    <p>{error || 'Không tìm thấy sản phẩm'}</p>
                    <button onClick={() => navigate('/admin/products')} className="btn-secondary">
                        Quay lại danh sách
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="product_detail">
            {/* Header */}
            <div className="detail-header">
                <button onClick={() => navigate('/admin/products')} className="btn-back">
                    <ArrowLeft size={20} />
                    Quay lại
                </button>
                <div className="header-info">
                    <h1>{product.name}</h1>
                    <div className="header-meta">
                        <span className={`badge ${product.is_active ? 'badge-success' : 'badge-danger'}`}>
                            {product.is_active ? 'Hoạt động' : 'Đã ẩn'}
                        </span>
                        {product.has_variants && (
                            <span className="badge badge-info">Có biến thể</span>
                        )}
                    </div>
                </div>
                <button
                    onClick={() => navigate(`/admin/products/${uuid}/edit`)}
                    className="btn btn-primary"
                >
                    <Edit size={18} />
                    Chỉnh sửa
                </button>
            </div>

            <div className="detail-grid">
                {/* Basic Info */}
                <div className="detail-card">
                    <div className="card-header">
                        <Package size={20} />
                        <h3>Thông tin cơ bản</h3>
                    </div>
                    <div className="card-body">
                        <div className="info-row">
                            <span className="label">SKU:</span>
                            <span className="value">{product.sku || '-'}</span>
                        </div>
                        <div className="info-row">
                            <span className="label">Giá:</span>
                            <span className="value">
                                {product.price
                                    ? product.price.toLocaleString('vi-VN') + ' đ'
                                    : '-'
                                }
                            </span>
                        </div>
                        <div className="info-row">
                            <span className="label">Danh mục:</span>
                            <span className="value">{product.category?.name || '-'}</span>
                        </div>
                        <div className="info-row">
                            <span className="label">Đánh giá:</span>
                            <span className="value">
                                {product.reviews_avg_rating
                                    ? `⭐ ${parseFloat(product.reviews_avg_rating).toFixed(1)}`
                                    : 'Chưa có'
                                }
                            </span>
                        </div>
                    </div>
                </div>

                {/* Description */}
                <div className="detail-card full-width">
                    <div className="card-header">
                        <Tag size={20} />
                        <h3>Mô tả</h3>
                    </div>
                    <div className="card-body">
                        <p>{product.description || 'Chưa có mô tả'}</p>
                    </div>
                </div>

                {/* Images */}
                {product.images && product.images.length > 0 && (
                    <div className="detail-card full-width">
                        <div className="card-header">
                            <ImageIcon size={20} />
                            <h3>Hình ảnh ({product.images.length})</h3>
                        </div>
                        <div className="card-body">
                            <div className="image-grid">
                                {product.images.map((image, index) => (
                                    <div key={index} className="image-item">
                                        <img src={image.image_url} alt={`${product.name} ${index + 1}`} />
                                        {image.is_primary && (
                                            <span className="badge badge-primary">Chính</span>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                )}

                {/* Variants */}
                {product.has_variants && product.variants && product.variants.length > 0 && (
                    <div className="detail-card full-width">
                        <div className="card-header">
                            <Package size={20} />
                            <h3>Biến thể ({product.variants.length})</h3>
                        </div>
                        <div className="card-body">
                            <table className="variants-table">
                                <thead>
                                    <tr>
                                        <th>SKU</th>
                                        <th>Thuộc tính</th>
                                        <th>Giá</th>
                                        <th>Tồn kho</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {product.variants.map((variant, index) => (
                                        <tr key={index}>
                                            <td>{variant.sku}</td>
                                            <td>
                                                {variant.attribute_values?.map(attr => attr.value).join(', ') || '-'}
                                            </td>
                                            <td>{variant.price?.toLocaleString('vi-VN')} đ</td>
                                            <td>
                                                {variant.stock?.reduce((sum, s) => sum + s.quantity, 0) || 0}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {/* Promotions */}
                {product.promotions && product.promotions.length > 0 && (
                    <div className="detail-card full-width">
                        <div className="card-header">
                            <Tag size={20} />
                            <h3>Khuyến mãi đang áp dụng</h3>
                        </div>
                        <div className="card-body">
                            <div className="promotions-list">
                                {product.promotions.map((promo, index) => (
                                    <div key={index} className="promo-item">
                                        <strong>{promo.name}</strong>
                                        <span className="badge badge-success">
                                            -{promo.discount_value}{promo.discount_type === 'percentage' ? '%' : 'đ'}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default ProductDetail;
