import React, { useState, useEffect, useMemo } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useProduct } from '@/hooks/useProduct';
import { useCart } from '@/hooks/useCart';
import { useOrder } from '@/hooks/useOrder';
import { Star, Minus, Plus, ShoppingCart, MessageCircle, Store, ChevronRight, MapPin } from 'lucide-react';
import { AiOutlineLoading3Quarters, AiOutlineWarning } from "react-icons/ai";
import ProductReviews from './ProductReviews';
import styles from './ProductDetail.module.css';

const ProductDetail = () => {
  const navigate = useNavigate();
  const { id } = useParams();

  const { productDetail, loading, error, getDetail } = useProduct();
  const { addToCart, loading: cartLoading } = useCart();
  const { createOrder, loading: orderLoading } = useOrder();

  const [activeImage, setActiveImage] = useState(null);
  
  // --- STATE QUẢN LÝ BIẾN THỂ ---
  const [selectedVariant, setSelectedVariant] = useState(null);
  // Thay vì lưu riêng lẻ, ta lưu object: { "Color": "Navy Blue", "Material": "Leather" }
  const [selectedAttributes, setSelectedAttributes] = useState({}); 
  
  const [address, setAddress] = useState('');
  const [quantity, setQuantity] = useState(1);

  const isLoggedIn = () => {
    const token = localStorage.getItem('access_token');
    return !!token;
  };

  // 1. Fetch dữ liệu
  useEffect(() => {
    if (id) {
      getDetail(id);
    }
  }, [id, getDetail]);

  // 2. Set ảnh mặc định
  useEffect(() => {
    if (!productDetail?.images?.length) return;
    const primary = productDetail.images.find(img => img.is_primary === 1) || productDetail.images[0];
    setActiveImage(primary.url);
  }, [productDetail]);

  // 3. TÍNH TOÁN DANH SÁCH OPTIONS TỪ VARIANTS (Dynamic)
  // Logic: Quét toàn bộ variants để gom nhóm các thuộc tính có sẵn
  const attributeOptions = useMemo(() => {
    if (!productDetail?.variants) return {};

    const options = {};
    productDetail.variants.forEach(variant => {
      variant.attributes.forEach(attr => {
        const name = attr.attribute_name; // VD: "Color", "Material"
        const value = attr.value;         // VD: "Navy Blue", "Leather"

        if (!options[name]) {
          options[name] = new Set(); // Dùng Set để lọc trùng
        }
        options[name].add(value);
      });
    });

    // Chuyển Set thành Array để render
    const result = {};
    Object.keys(options).forEach(key => {
      result[key] = Array.from(options[key]);
    });
    
    return result;
  }, [productDetail]);

  // 4. Set mặc định attribute ban đầu (Lấy variant đầu tiên làm chuẩn)
  useEffect(() => {
    if (productDetail?.variants?.length && !selectedVariant) {
      const firstVariant = productDetail.variants[0];
      
      // Xây dựng state attributes từ variant đầu tiên
      const initialAttrs = {};
      firstVariant.attributes.forEach(attr => {
        initialAttrs[attr.attribute_name] = attr.value;
      });

      setSelectedAttributes(initialAttrs);
      setSelectedVariant(firstVariant);
    }
  }, [productDetail]);

  // 5. Tìm Variant khi người dùng thay đổi lựa chọn
  useEffect(() => {
    if (!productDetail?.variants) return;
    if (Object.keys(selectedAttributes).length === 0) return;

    // Tìm variant có TẤT CẢ attribute khớp với selectedAttributes
    const foundVariant = productDetail.variants.find(v => 
      v.attributes.every(attr => 
        selectedAttributes[attr.attribute_name] === attr.value
      )
    );

    setSelectedVariant(foundVariant || null);
    
    // Nếu tìm thấy variant và có ảnh riêng, update ảnh hiển thị
    if (foundVariant && foundVariant.image) {
        setActiveImage(foundVariant.image);
    }

  }, [selectedAttributes, productDetail]);

  // 6. Handler chọn attribute
  const handleAttributeSelect = (attributeName, value) => {
    setSelectedAttributes(prev => ({
      ...prev,
      [attributeName]: value
    }));
  };

  const handleQuantity = (type) => {
    if (type === 'dec' && quantity > 1) setQuantity(quantity - 1);
    if (type === 'inc') setQuantity(quantity + 1);
  };
  
  const handleProductAction = async (actionType) => {
    if (!isLoggedIn()) {
      alert('Bạn cần đăng nhập để tiếp tục!');
      return navigate('/customer/login');
    }

    // Validate động: Kiểm tra xem đã chọn đủ các nhóm thuộc tính chưa
    const requiredAttributes = Object.keys(attributeOptions);
    const missingAttributes = requiredAttributes.filter(key => !selectedAttributes[key]);

    if (missingAttributes.length > 0) {
      return alert(`Vui lòng chọn: ${missingAttributes.join(', ')}`);
    }

    if (!selectedVariant) {
        return alert('Phiên bản sản phẩm này hiện không khả dụng. Vui lòng chọn kết hợp khác.');
    }

    if (!address.trim()) return alert('Vui lòng nhập địa chỉ giao hàng!');

    try {
      if (actionType === 'cart') {
        await addToCart(selectedVariant.uuid, quantity);
        alert('✅ Đã thêm vào giỏ hàng thành công!');
      }

      else if (actionType === 'buy_now') {
        const isConfirmed = window.confirm(`Bạn muốn đặt hàng ngay ${quantity} sản phẩm này?`);
        if (!isConfirmed) return;

        const payload = {
          address_id: parseInt(address) || 1, 
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
      console.error('❌ ACTION FAILED:', error);
      alert(error.message || 'Có lỗi xảy ra');
    }
  };

  if (loading){
    return (
      <div className="loading-state">
        <AiOutlineLoading3Quarters className="loading-icon" />
        <span>Đang tải sản phẩm...</span>
      </div>
    )
  }
  if (!productDetail || error){
    return (
      <div className="error-state">
        <AiOutlineWarning className="error-icon" />
        <span>{error}</span>
      </div>
    )
  }

  const displayImages = productDetail.images || [];
  const rating = productDetail.rating_avg;
  const reviewsCount = productDetail.rating_count;
  const soldCount = productDetail.sold_count;
  
  // Hiển thị giá: Ưu tiên giá của Variant đang chọn, nếu không thì lấy giá gốc
  const currentPrice = selectedVariant 
    ? selectedVariant.price_formatted 
    : productDetail.price_formatted;
  
  const originalPrice = selectedVariant
    ? (selectedVariant.original_price_formatted || null)
    : productDetail.original_price_formatted;

  const isFlashSale = productDetail.flash_sale?.is_active;

  const showOriginalPrice = isFlashSale || (originalPrice && originalPrice !== currentPrice);

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
                {currentPrice}
              </span>

              {showOriginalPrice && (
                <>
                  <span className={styles['original-price']}>
                    {originalPrice}
                  </span>

                  {isFlashSale && (
                    <span className={styles['discount-badge']}>
                      -{productDetail.flash_sale.discount_percent}%
                    </span>
                  )}
                </>
              )}
            </div>  
          </div>
          
          <div className={styles['product-body']}>
            {/* Delivery */}
            <div className={styles['variant-section']}>
              <span className={styles['label']}>Delivery</span>
              <div className={styles['address-input-group']}>
                  <MapPin size={18} className={styles['map-icon']} />
                  <input 
                      type="text" 
                      className={styles['addr-input']}
                      placeholder="Enter address..."
                      value={address}
                      onChange={(e) => setAddress(e.target.value)}
                  />
              </div>
            </div>

            {/* --- RENDER THUỘC TÍNH ĐỘNG (Dynamic Attributes) --- */}
            {/* Tự động render Color, Material, Size,... dựa trên dữ liệu API */}
            {Object.entries(attributeOptions).map(([attrName, values]) => (
              <div className={styles['variant-section']} key={attrName}>
                <span className={styles['label']}>{attrName}</span>
                <div className={styles['options-row']}>
                  {values.map((val) => {
                    const isSelected = selectedAttributes[attrName] === val;
                    return (
                      <button 
                        key={val}
                        className={`${styles['option-btn']} ${isSelected ? styles['selected'] : ''}`}
                        onClick={() => handleAttributeSelect(attrName, val)}
                      >
                        {val}
                      </button>
                    )
                  })}
                </div>
              </div>
            ))}

            {/* Quantity */}
            <div className={styles['variant-section']}>
              <span className={styles['label']}>Quantity</span>
              <div className={styles['qty-control']}>
                <button onClick={() => handleQuantity('dec')}><Minus size={24}/></button>
                <input type="text" value={quantity} readOnly />
                <button onClick={() => handleQuantity('inc')}><Plus size={24}/></button>
              </div>
            </div>

            {/* Variant Stock Warning (Optional) */}
            {selectedVariant && (
                <div style={{ marginBottom: '15px', color: '#666', fontSize: '14px' }}>
                    Stock available: {selectedVariant.stock_quantity}
                </div>
            )}

            {/* Action Buttons */}
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

      {/* --- PHẦN DESCRIPTION --- */}
      <h4 className={styles['desc-title']}>Product Description</h4>
      <div className={styles['product-description-section']}>
      
        <div className={styles['desc-content']}>
          <p>{productDetail.description || "No description available."}</p>
        </div>

        {/*--- SPECIFICATIONS --- */}
        <h3 className={styles['desc-title']} >Product Specifications</h3>
        <div className={styles['specs-table']}>
            <div className={styles['spec-row']}>
                <span className={styles['spec-label']}>Brand</span>
                <span className={styles['spec-value']}>Atelier Home</span>
            </div>
            {/* Hiển thị các thuộc tính của variant đang chọn trong bảng thông số (nếu cần) */}
            {selectedVariant?.attributes?.map((attr, idx) => (
                <div className={styles['spec-row']} key={idx}>
                    <span className={styles['spec-label']}>{attr.attribute_name}</span>
                    <span className={styles['spec-value']}>{attr.value}</span>
                </div>
            ))}
        </div>

        {/* --- RATING --- */}
        <h3 className={styles['desc-title']} style={{ marginTop: '40px' }}>Product Rating</h3>
        <ProductReviews productId={productDetail.uuid} />
      </div>      
    </div>
  );
};

export default ProductDetail;