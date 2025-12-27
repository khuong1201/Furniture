import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Edit, Layers, Tag, Box } from 'lucide-react';
import ProductService from '@/services/admin/ProductService';
import './ProductDetail.css';
const ProductDetail = () => {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const [product, setProduct] = useState(null);

    useEffect(() => {
        ProductService.instance.getProductDetail(uuid).then(res => setProduct(res.data));
    }, [uuid]);

    if (!product) return <div className="loading-container"><Loader2 className="spinner-gold" /></div>;

    return (
        <div className="product-detail-page">
            <div className="header">
                <button onClick={() => navigate(-1)} className="btn-back"><ArrowLeft size={18} /> Back</button>
                <div className="flex-between">
                    <h1>{product.name}</h1>
                    <button onClick={() => navigate(`/admin/products/${uuid}/edit`)} className="btn-primary-gradient"><Edit size={18} /> Edit Product</button>
                </div>
            </div>

            <div className="detail-grid">
                <div className="main-info">
                    <div className="gallery-card">
                        <h3>Gallery</h3>
                        <div className="gallery-grid">
                            {product.images.map(img => <img key={img.uuid} src={img.image_url} className={img.is_primary ? 'primary' : ''} />)}
                        </div>
                    </div>
                    
                    <div className="variants-section">
                        <h3>Variants & Availability</h3>
                        <div className="matrix-display">
                            {product.variants.map(v => (
                                <div key={v.uuid} className="variant-row">
                                    <div className="info">
                                        <p className="sku">{v.sku}</p>
                                        <p className="attrs">{v.attributeValues.map(av => av.value).join(' / ')}</p>
                                    </div>
                                    <div className="stock">
                                        {v.stock.map(s => <span key={s.warehouse_uuid}>{s.warehouse_name}: <strong>{s.quantity}</strong></span>)}
                                    </div>
                                    <p className="price">{v.price.toLocaleString()} đ</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                <div className="meta-sidebar">
                    <div className="info-card">
                        <h3>Overview</h3>
                        <div className="item"><Layers size={16}/> <span>Category: {product.category?.name}</span></div>
                        <div className="item"><Tag size={16}/> <span>Brand: {product.brand?.name || 'N/A'}</span></div>
                        <div className="item"><Box size={16}/> <span>Type: {product.has_variants ? 'Variable' : 'Simple'}</span></div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ProductDetail;