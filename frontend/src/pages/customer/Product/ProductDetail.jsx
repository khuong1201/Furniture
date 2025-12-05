import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useProduct } from '@/hooks/useProduct';
import { useCart } from '@/hooks/useCart';
import { useOrder } from '@/hooks/useOrder';
import { Star, Minus, Plus, ShoppingCart, MessageCircle, Store, ChevronRight, MapPin, ThumbsUp } from 'lucide-react';
import ProductReviews from './ProductReviews';
// 1. Import styles từ module
import styles from './ProductDetail.module.css';

const ProductDetail = () => {
  const navigate = useNavigate();
  const { id } = useParams();

  const { productDetail, loading, error, getDetail } = useProduct();
  const { addToCart, loading: cartLoading, error: cartError, message } = useCart();

  const { createOrder, loading: orderLoading } = useOrder();

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
  
  const handleProductAction = async (actionType) => {

    if (!isLoggedIn()) {
      alert('Bạn cần đăng nhập để tiếp tục!');
      return navigate('/customer/login');
    }
    if (!selectedColor) return alert('Please select a color!');
    if (!selectedSize) return alert('Please select a size!');
    if (!address.trim()) return alert('Please enter your delivery address!');

    try {
      if (actionType === 'cart') {
        await addToCart(selectedVariant.uuid, quantity);
        alert('✅ Đã thêm vào giỏ hàng thành công!');
      }

      else if (actionType === 'buy_now') {
        const isConfirmed = window.confirm(`Bạn muốn đặt hàng ngay ${quantity} sản phẩm này?`);
        if (!isConfirmed) return;

        // Tạo payload chuẩn cho OrderController
        const payload = {
          address_id: parseInt(address) || 1, // Parse ID từ input
          items: [
            {
              variant_uuid: selectedVariant.uuid,
              quantity: quantity
            }
          ]
        };

        const result = await createOrder(payload);
        console.log('✅ BUY NOW SUCCESS:', result);
        alert('🎉 Đặt hàng thành công!');

        if (result?.uuid) {
            navigate(`/customer/orders/${result.uuid}`);
        } else {
            navigate('/customer/orders');
        }
      }

    } catch (error) {
      console.error('❌ ADD TO CART FAILED:', error);
      alert(error.message || 'Add to cart failed');
    }

  };


  // Sử dụng styles['class-name'] cho các trạng thái loading/error
  if (loading) return <div className={styles['pd-loading']}>⏳ Đang tải chi tiết sản phẩm...</div>;
  if (error) return <div className={styles['pd-error']}>❌ Lỗi: {error}</div>;
  if (!productDetail) return <div className={styles['pd-error']}>⚠️ Không tìm thấy sản phẩm</div>;


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
    <div className={styles['pd-wrapper']}>
      {/* Breadcrumb */}
      <div className={styles['breadcrumb']}>
        <span>Product</span> <ChevronRight size={14} /> <span>{productDetail.name}</span>
      </div>

      <div className={styles['pd-container']}>
        {/* --- CỘT TRÁI: HÌNH ẢNH --- */}
        <div className={styles['pd-gallery']}>
          <div className={styles['main-image']}>
            <img 
              src={activeImage || displayImages?.[0]?.url} 
              alt={productDetail.name} 
            />
          </div>

          <div className={styles['thumbnail-list']}>
            {displayImages.map((img, index) => (
              <div
                key={img.uuid}
                // Kết hợp class tĩnh và class động bằng Template Literals
                className={`${styles['thumb-item']} ${activeImage === img.url ? styles['active'] : ''}`}
                onMouseEnter={() => setActiveImage(img.url)}
              >
                <img src={img.url} alt={`Thumb ${index}`} />
              </div>
            ))}
          </div>
        </div>

        {/* --- CỘT PHẢI: THÔNG TIN --- */}
        <div className={styles['pd-info']}>
          <div className={styles['product-header']}>
            <h1 className={styles['product-title']}>{productDetail.name}</h1>

            <div className={styles['product-meta']}>
              <span className={styles['rating']}>
                {rating} <Star size={24} fill="#ffc107" color="#ffc107" />
              </span>
              <span className={styles['divider']}>|</span>
              <span className={styles['reviews']}>{reviewsCount} Ratings</span>
              <span className={styles['divider']}>|</span>
              <span className={styles['sold']}>{soldCount} sold</span>
            </div>

            <div className={styles['price-section']}>
              <span className={styles['current-price']}>
                {Number(selectedVariant?.price || productDetail.variants?.[0]?.price || 0).toLocaleString()} VND
              </span>
              {/* Nếu có giá gốc thì hiển thị */}
              {productDetail.original_price && (
                  <>
                      <span className={styles['original-price']}>
                          {Number(productDetail.original_price).toLocaleString()} VND
                      </span>
                      <span className={styles['discount-badge']}>-10%</span>
                  </>
              )}
          </div>  
          </div>
          
          <div className={styles['product-body']}>
            {/* delivery */}
            <div className={styles['variant-section']}>
              <span className={styles['label']}>Delivery</span>
              <div className={styles['address-input-group']}>
                  <MapPin size={18} className={styles['map-icon']} />
                  <input 
                      type="text" 
                      className={styles['addr-input']}
                      placeholder="Enter address to see delivery options"
                      value={address}
                      onChange={(e) => setAddress(e.target.value)}
                  />
              </div>
            </div>

            {/* Chọn Màu */}
            <div className={styles['variant-section']}>
              <span className={styles['label']}>Color</span>
              <div className={styles['options-row']}>
                {colors.map((c) => (
                  <button 
                    key={c.uuid}
                    className={`${styles['option-btn']} ${selectedColor === c.uuid ? styles['selected'] : ''}`}
                    onClick={() => setSelectedColor(c.uuid)}
                  >
                    {c.value}
                  </button>
                ))}
              </div>
            </div>

            {/* Size */}
            <div className={styles['variant-section']}>
              <span className={styles['label']}>Size</span>
              <div className={styles['options-row']}>
                {sizes.map((s) => (
                  <button 
                    key={s.uuid}
                    className={`${styles['option-btn']} ${selectedSize === s.uuid ? styles['selected'] : ''}`}
                    onClick={() => setSelectedSize(s.uuid)}
                  >
                    {s.value}
                  </button>
                ))}
              </div>
            </div>

            {/* Quantity */}
            <div className={styles['variant-section']}>
              <span className={styles['label']}>Quantity</span>
              <div className={styles['qty-control']}>
                <button onClick={() => handleQuantity('dec')}><Minus size={24}/></button>
                <input type="text" value={quantity} readOnly />
                <button onClick={() => handleQuantity('inc')}><Plus size={24}/></button>
              </div>
            </div>

            {/* Nút hành động */}
            <div className={styles['action-buttons']}>
              <button 
                className={styles['btn-add-cart']} 
                onClick={() => handleProductAction('cart')}
                disabled={cartLoading}
              >
                <ShoppingCart size={20}/>
                {cartLoading ? 'Processing...' : 'Add to Cart'}
              </button>
              
              <button className={styles['btn-buy-now']}
                onClick={() => handleProductAction('buy_now')}
                disabled={cartLoading || orderLoading || !selectedVariant}
              >
                {orderLoading ? 'Processing...' : 'Buy Now'}
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* --- PHẦN SHOP INFO --- */}
      <div className={styles['shop-section']}>
        <div className={styles['shop-info']}>
          <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100" alt="Shop Avatar" className={styles['shop-avatar']} />
          <div className={styles['shop-text']}>
            <h4>Furniture Official Store</h4>
            <p>Active 5 minutes ago</p>
          </div>
        </div>
        <div className={styles['shop-actions']}>
          <button className={styles['btn-shop-chat']}><MessageCircle size={16}/> Chat</button>
          <button className={styles['btn-shop-view']}><Store size={16}/> View</button>
        </div>
      </div>

      {/* --- PHẦN 4: PRODUCT DESCRIPTION --- */}
      
      <h4 className={styles['desc-title']}>Product Description</h4>
      <div className={styles['product-description-section']}>
      
        <div className={styles['desc-content']}>
          <p>{productDetail.description || "No description available."}</p>
        </div>

        {/*--- PHẦN 5: SPECIFICATIONS --- */}
        <h3 className={styles['desc-title']} >Product Specifications</h3>
        <div className={styles['specs-table']}>
            <div className={styles['spec-row']}>
                <span className={styles['spec-label']}>Brand</span>
                <span className={styles['spec-value']}>Atelier Home</span>
            </div>
            <div className={styles['spec-row']}>
                <span className={styles['spec-label']}>Material</span>
                <span className={styles['spec-value']}>Premium Velvet, Solid Oak Frame</span>
            </div>
            <div className={styles['spec-row']}>
                <span className={styles['spec-label']}>Dimensions</span>
                <span className={styles['spec-value']}>W: 84" x D: 36" x H: 33"</span>
            </div>
            <div className={styles['spec-row']}>
                <span className={styles['spec-label']}>Warranty</span>
                <span className={styles['spec-value']}>2 years manufacturer warranty</span>
            </div>
            <div className={styles['spec-row']}>
                <span className={styles['spec-label']}>Country of Origin</span>
                <span className={styles['spec-value']}>Italy</span>
            </div>
        </div>

        {/* --- PHẦN 6: PRODUCT RATING --- */}
        <h3 className={styles['desc-title']} style={{ marginTop: '40px' }}>Product Rating</h3>
        <ProductReviews productId={productDetail.uuid} />
      </div>      
    </div>
  );
};

export default ProductDetail;