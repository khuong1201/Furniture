import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { useProduct } from '../../hooks/useProducts';
import { Star, Minus, Plus, ShoppingCart, MessageCircle, Store, ChevronRight } from 'lucide-react';
import './ProductDetail.css';

const ProductDetail = () => {
  const { id } = useParams();
  const { productDetail, loading, error, getDetail } = useProduct();

  const [activeImage, setActiveImage] = useState('');
  const [quantity, setQuantity] = useState(1);
  

  const [selectedColor, setSelectedColor] = useState('');
  const [selectedSize, setSelectedSize] = useState('');

  useEffect(() => {
    if (id) {
      getDetail(id);
    }
  }, [id, getDetail]);

  // --- 2. CẬP NHẬT ẢNH MẶC ĐỊNH KHI CÓ DỮ LIỆU ---
  useEffect(() => {
    if (productDetail) {
      // Ưu tiên lấy ảnh từ mảng images, nếu không có thì lấy ảnh đại diện chính
      const firstImg = productDetail.images?.[0]?.path || productDetail.image;
      if (firstImg) {
        setActiveImage(getImageUrl(firstImg));
      }
    }
  }, [productDetail]);

  const getImageUrl = (path) => {
    if (!path) return 'https://placehold.co/600x400?text=No+Image';
    if (path.startsWith('http')) return path; 
    return `http://localhost:8000/storage/${path}`; 
  };

  const handleQuantity = (type) => {
    if (type === 'dec' && quantity > 1) setQuantity(quantity - 1);
    if (type === 'inc') setQuantity(quantity + 1);
  };

  // --- KIỂM TRA TRẠNG THÁI TẢI ---
  if (loading) return <div className="pd-loading">⏳ Đang tải chi tiết sản phẩm...</div>;
  if (error) return <div className="pd-error">❌ Lỗi: {error}</div>;
  if (!productDetail) return <div className="pd-error">⚠️ Không tìm thấy sản phẩm</div>;

  // --- CHUẨN BỊ DỮ LIỆU HIỂN THỊ ---
  // Nếu API chưa trả về mảng ảnh, tạo mảng tạm chứa 1 ảnh chính để không lỗi giao diện
  const displayImages = productDetail.images && productDetail.images.length > 0 
    ? productDetail.images.map(img => img.path) 
    : [productDetail.image];

  // Mock màu/size nếu DB chưa có bảng variants (để giữ giao diện đẹp)
  const colors = productDetail.colors || ["Standard"];
  const sizes = productDetail.sizes || ["Standard"];

  return (
    <div className="pd-wrapper">
      {/* Breadcrumb */}
      <div className="breadcrumb">
        <span>Product</span> <ChevronRight size={14} /> <span>{productDetail.name}</span>
      </div>

      <div className="pd-container">
        {/* --- CỘT TRÁI: HÌNH ẢNH --- */}
        <div className="pd-gallery">
          <div className="main-image">
            <img src={activeImage} alt={productDetail.name} />
          </div>
          <div className="thumbnail-list">
            {displayImages.map((img, index) => {
               const fullUrl = getImageUrl(img);
               return (
                <div 
                  key={index} 
                  className={`thumb-item ${activeImage === fullUrl ? 'active' : ''}`}
                  onMouseEnter={() => setActiveImage(fullUrl)}
                >
                  <img src={fullUrl} alt={`Thumb ${index}`} />
                </div>
               );
            })}
          </div>
        </div>

        {/* --- CỘT PHẢI: THÔNG TIN --- */}
        <div className="pd-info">
          <h1 className="product-title">{productDetail.name}</h1>
          
          <div className="product-meta">
            <span className="rating">
              {productDetail.rating || 5.0} <Star size={14} fill="#ffc107" color="#ffc107" />
            </span>
            <span className="divider">|</span>
            <span className="reviews">{productDetail.reviews_count || 0} Ratings</span>
            <span className="divider">|</span>
            <span className="sold">{productDetail.sold || 0} sold</span>
          </div>

          <div className="price-section">
            <span className="current-price">
                {Number(productDetail.price).toLocaleString()} VND
            </span>
            {/* Nếu có giá gốc thì hiển thị */}
            {productDetail.original_price && (
                <>
                    <span className="original-price">
                        {Number(productDetail.original_price).toLocaleString()} VND
                    </span>
                    <span className="discount-badge">-10%</span>
                </>
            )}
          </div>

          <div className="delivery-info">
            <span className="label">Delivery</span>
            <span className="value map-icon">📍 Enter address to see delivery options</span>
          </div>

          {/* Chọn Màu (Nếu có) */}
          <div className="variant-section">
            <span className="label">Color</span>
            <div className="options-row">
              {colors.map((color, index) => (
                <button 
                  key={color}
                  className={`option-btn ${selectedColor === color ? 'selected' : ''}`}
                  onClick={() => setSelectedColor(color)}
                >
                  {color}
                </button>
              ))}
            </div>
          </div>

          {/* Size   */}
          <div className="variant-section">
            <span className="label">Size</span>
            <div className="options-row">
              {sizes.map((size, index) => (
                <button 
                  key={size}
                  className={`option-btn ${selectedSize === size ? 'selected' : ''}`}
                  onClick={() => setSelectedSize(size)}
                >
                  {size}
                </button>
              ))}
            </div>
          </div>

          {/* Chọn Số lượng */}
          <div className="quantity-section">
            <span className="label">Quantity</span>
            <div className="qty-control">
              <button onClick={() => handleQuantity('dec')}><Minus size={24}/></button>
              <input type="text" value={quantity} readOnly />
              <button onClick={() => handleQuantity('inc')}><Plus size={24}/></button>
            </div>
          </div>

          {/* Nút hành động */}
          <div className="action-buttons">
            <button className="btn-add-cart" onClick={() => alert('Thêm vào giỏ hàng thành công!')}>
              <ShoppingCart size={20} /> Add to Cart
            </button>
            <button className="btn-buy-now">Buy Now</button>
          </div>
        </div>
      </div>

      {/* --- PHẦN SHOP INFO (Giả lập nếu API chưa có shop) --- */}
      <div className="shop-section">
        <div className="shop-info">
          <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100" alt="Shop Avatar" className="shop-avatar" />
          <div className="shop-text">
            <h4>Furniture Official Store</h4>
            <p>Active 5 minutes ago</p>
          </div>
        </div>
        <div className="shop-actions">
          <button className="btn-shop-chat"><MessageCircle size={16}/> Chat</button>
          <button className="btn-shop-view"><Store size={16}/> View</button>
        </div>
      </div>

      {/* --- PHẦN 4: PRODUCT DESCRIPTION --- */}
      <div className=''>
        <h4 className="desc-title">Product Description</h4>
        <div className="product-description-section">
        
          
          {/* Nội dung mô tả (nếu có HTML từ editor thì dùng dangerouslySetInnerHTML, nếu text thường thì hiện luôn) */}
          <div className="desc-content">
            <p>{productDetail.description || "No description available."}</p>
          </div>

          {/*--- PHẦN 5: SPECIFICATIONS (Thông số kỹ thuật) --- */}
          <h3 className="desc-title" >Product Specifications</h3>
          <div className="specs-table">
              <div className="spec-row">
                  <span className="spec-label">Brand</span>
                  <span className="spec-value">Atelier Home</span>
              </div>
              <div className="spec-row">
                  <span className="spec-label">Material</span>
                  <span className="spec-value">Premium Velvet, Solid Oak Frame</span>
              </div>
              <div className="spec-row">
                  <span className="spec-label">Dimensions</span>
                  <span className="spec-value">W: 84" x D: 36" x H: 33"</span>
              </div>
              <div className="spec-row">
                  <span className="spec-label">Warranty</span>
                  <span className="spec-value">2 years manufacturer warranty</span>
              </div>
              <div className="spec-row">
                  <span className="spec-label">Country of Origin</span>
                  <span className="spec-value">Italy</span>
              </div>
          </div>
        </div>      
      </div>
    </div>
  );
};

export default ProductDetail;