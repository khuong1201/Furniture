import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useProduct } from '@/hooks/useProduct';
import { useCart } from '@/hooks/useCart';
import { Star, Minus, Plus, ShoppingCart, MessageCircle, Store, ChevronRight, MapPin, ThumbsUp } from 'lucide-react';
import './ProductDetail.css';

const ProductDetail = () => {
  const navigate = useNavigate();
  const { id } = useParams();

  const { productDetail, loading, error, getDetail } = useProduct();
  const { addToCart, loading: cartLoading, error: cartError, message } = useCart();

  const [activeImage, setActiveImage] = useState(null);
  const [selectedVariant, setSelectedVariant] = useState(null);
  const [address, setAddress] = useState('');
  const [selectedColor, setSelectedColor] = useState('');
  const [selectedSize, setSelectedSize] = useState('');
  const [quantity, setQuantity] = useState(1);

  const isLoggedIn = () => {
    const token = localStorage.getItem('access_token');

    return !!token;
  };

  useEffect(() => {
    if (id) {
      getDetail(id);
    }
  }, [id, getDetail]);


  useEffect(() => {
    if (!productDetail?.images?.length) return;

    const primary =
      productDetail.images.find(img => img.is_primary === 1) ||
      productDetail.images[0];

    setActiveImage(primary.url);
  }, [productDetail]);

  useEffect(() => {
    if (!selectedVariant && productDetail?.variants?.length) {
      setSelectedVariant(productDetail.variants[0]);
    }
  }, [productDetail, selectedVariant]);

  useEffect(() => {
    if (!productDetail?.variants?.length) return;
    if (!selectedColor || !selectedSize) return;

    const foundVariant = productDetail.variants.find(v =>
      v.attributes.some(a => a.value_uuid === selectedColor) &&
      v.attributes.some(a => a.value_uuid === selectedSize)
    );

    if (foundVariant) {
      setSelectedVariant(foundVariant);
    }
  }, [selectedColor, selectedSize, productDetail]);

  const handleQuantity = (type) => {
    if (type === 'dec' && quantity > 1) setQuantity(quantity - 1);
    if (type === 'inc') setQuantity(quantity + 1);
  };
  
  const handleAddToCart = async (actionType) => {

    if (!isLoggedIn()) {
      alert('Bạn cần đăng nhập để tiếp tục!');
      return navigate('/customer/login');
    }
    if (!selectedColor) return alert('Please select a color!');
    if (!selectedSize) return alert('Please select a size!');
    if (!address.trim()) return alert('Please enter your delivery address!');

    try {
      const result = await addToCart(
        selectedVariant.uuid,
        quantity
      );

      console.log('✅ ADD TO CART SUCCESS:', result);
      alert('Add to cart success');
    } catch (error) {
      console.error('❌ ADD TO CART FAILED:', error);
      alert(error.message || 'Add to cart failed');
    }

  };


  if (loading) return <div className="pd-loading">⏳ Đang tải chi tiết sản phẩm...</div>;
  if (error) return <div className="pd-error">❌ Lỗi: {error}</div>;
  if (!productDetail) return <div className="pd-error">⚠️ Không tìm thấy sản phẩm</div>;


  const displayImages = productDetail.images || [];
  
  const colors = productDetail?.available_options?.find(
    opt => opt.attribute_name === "Màu sắc"
  )?.values || [];
  
  const sizes = productDetail?.available_options?.find(
    opt => opt.attribute_name === "Kích thước"
  )?.values || [];

  const rating = 4.9;
  const reviewsCount = 156;
  const soldCount = 89;

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
            <img 
              src={activeImage || displayImages?.[0]?.url} 
              alt={productDetail.name} 
            />
          </div>

          <div className="thumbnail-list">
            {displayImages.map((img, index) => (
              <div
                key={img.uuid}
                className={`thumb-item ${activeImage === img.url ? 'active' : ''}`}
                onMouseEnter={() => setActiveImage(img.url)}
              >
                <img src={img.url} alt={`Thumb ${index}`} />
              </div>
            ))}
          </div>
        </div>

        {/* --- CỘT PHẢI: THÔNG TIN --- */}
        <div className="pd-info">
          <div className='product-header'>
            <h1 className="product-title">{productDetail.name}</h1>

            <div className="product-meta">
              <span className="rating">
                {rating} <Star size={14} fill="#ffc107" color="#ffc107" />
              </span>
              <span className="divider">|</span>
              <span className="reviews">{reviewsCount} Ratings</span>
              <span className="divider">|</span>
              <span className="sold">{soldCount} sold</span>
            </div>

            <div className="price-section">
              <span className="current-price">
                {Number(selectedVariant?.price || productDetail.variants?.[0]?.price || 0).toLocaleString()} VND
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
          </div>
          
          <div className='product-body'>
            {/* delivery */}
            <div className="variant-section">
              <span className="label">Delivery</span>
              <div className="address-input-group">
                  <MapPin size={18} className="map-icon" />
                  <input 
                      type="text" 
                      className="addr-input"
                      placeholder="Enter address to see delivery options"
                      value={address}
                      onChange={(e) => setAddress(e.target.value)}
                  />
              </div>
            </div>

            {/* Chọn Màu (Nếu có) */}
            <div className="variant-section">
              <span className="label">Color</span>
              <div className="options-row">
                {colors.map((c) => (
                  <button 
                    key={c.uuid}
                    className={`option-btn ${selectedColor === c.uuid ? 'selected' : ''}`}
                    onClick={() => setSelectedColor(c.uuid)}
                  >
                    {c.value}
                  </button>
                ))}
              </div>
            </div>

            {/* Size   */}
            <div className="variant-section">
              <span className="label">Size</span>
              <div className="options-row">
                {sizes.map((s) => (
                  <button 
                    key={s.uuid}
                    className={`option-btn ${selectedSize === s.uuid ? 'selected' : ''}`}
                    onClick={() => setSelectedSize(s.uuid)}
                  >
                    {s.value}
                  </button>
                ))}
              </div>
            </div>

            {/* quantity */}
            <div className="variant-section">
              <span className="label">Quantity</span>
              <div className="qty-control">
                <button onClick={() => handleQuantity('dec')}><Minus size={24}/></button>
                <input type="text" value={quantity} readOnly />
                <button onClick={() => handleQuantity('inc')}><Plus size={24}/></button>
              </div>
            </div>

            {/* Nút hành động */}
            <div className="action-buttons">
              <button 
                className="btn-add-cart" 
                onClick={handleAddToCart}
                disabled={cartLoading}
              >
                <ShoppingCart size={20}/>
                {cartLoading ? 'Processing...' : 'Add to Cart'}
              </button>
              
              <button 
                className="btn-buy-now"
              
              >
                Buy Now
              </button>
            </div>
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

        {/* --- PHẦN 6: PRODUCT RATING (MỚI) --- */}
        <h3 className="desc-title" style={{ marginTop: '40px' }}>Product Rating</h3>
        
        <div className="rating-container">
          {/* 1. Tổng quan điểm số */}
          <div className="rating-overview">
            <div className="rating-score">
              <span className="score-num">4.9</span>
              <div className="score-stars">
                {[1, 2, 3, 4, 5].map((s) => (
                  <Star key={s} size={20} fill="#ffc107" color="#ffc107" />
                ))}
              </div>
              <span className="score-count">156 Ratings</span>
            </div>

            <div className="rating-bars">
              {[
                { star: 5, percent: '90%' },
                { star: 4, percent: '80%' },
                { star: 3, percent: '60%' },
                { star: 2, percent: '15%' },
                { star: 1, percent: '0%' },
              ].map((item) => (
                <div key={item.star} className="bar-row">
                  <span className="star-label">{item.star} <Star size={12} fill="#ffc107" color="#ffc107"/></span>
                  <div className="progress-bg">
                    <div className="progress-fill" style={{ width: item.percent }}></div>
                  </div>
                  <span className="percent-label">{item.percent}</span>
                </div>
              ))}
            </div>
          </div>

          {/* 2. Bộ lọc */}
          <div className="rating-filters">
            {['All (156)', 'With Photos (89)', '5 star (133)', '4 star (19)', '3 star (3)'].map((filter, idx) => (
              <button key={idx} className={`filter-btn ${idx === 0 ? 'active' : ''}`}>
                {filter}
              </button>
            ))}
          </div>

          {/* 3. Danh sách đánh giá (Mock Data) */}
          <div className="review-list">
            {[1, 2, 3].map((item) => (
              <div key={item} className="review-item">
                <img 
                  src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=100" 
                  alt="User" 
                  className="user-avatar" 
                />
                <div className="review-content">
                  <div className="review-header">
                     <span className="user-name">Michael C.</span>
                     <div className="user-rating">
                        {[1, 2, 3, 4, 5].map(s => <Star key={s} size={12} fill="#ffc107" color="#ffc107"/>)}
                     </div>
                  </div>
                  <span className="review-date">2024-12-15 04:30</span>
                  
                  <p className="review-text">
                    Absolutely stunning! The velvet is so soft and the construction is solid. Worth every penny.
                  </p>
                  
                  <div className="review-images">
                    <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=200" alt="Review 1" />
                    <img src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=200" alt="Review 2" />
                  </div>

                  <div className="review-actions">
                    <button className="btn-like"><ThumbsUp size={14} /> 3</button>
                  </div>
                </div>
              </div>
            ))}
          </div>
          
          <button className="btn-view-all">View All Reviews</button>
        </div>
      </div>      
    </div>
  );
};

export default ProductDetail;