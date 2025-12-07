import React, { useState, useEffect, useMemo } from 'react';

import { useParams, useNavigate } from 'react-router-dom';
import { useProduct } from '@/hooks/useProduct';
import { useCart } from '@/hooks/useCart';
import { useOrder } from '@/hooks/useOrder';
import { useAddress } from '@/hooks/useAddress'; 

import { Star, Minus, Plus, ShoppingCart, ChevronRight, MapPin, Plus as PlusIcon } from 'lucide-react';
import { AiOutlineLoading3Quarters, AiOutlineWarning } from "react-icons/ai";

import ProductReviews from './ProductReviews';
import AddressForm from '@/pages/customer/address/AddressForm.jsx'; 
import styles from './ProductDetail.module.css';

const ProductDetail = () => {
  const navigate = useNavigate();
  const { id } = useParams();

  // --- HOOKS ---
  const { productDetail, loading, error, getDetail } = useProduct();
  const { addToCart, loading: cartLoading } = useCart();
  const { createOrder, loading: orderLoading } = useOrder();
  const { addresses, fetchAddresses } = useAddress(); // Lấy danh sách địa chỉ

  // --- STATES ---
  const [activeImage, setActiveImage] = useState(null);
  
  // State biến thể
  const [selectedVariant, setSelectedVariant] = useState(null);
  const [selectedAttributes, setSelectedAttributes] = useState({}); 
  
  // State địa chỉ & số lượng
  const [addressId, setAddressId] = useState(''); // Lưu ID địa chỉ (thay vì text)
  const [quantity, setQuantity] = useState(1);
  
  // State Modal thêm địa chỉ
  const [showAddressModal, setShowAddressModal] = useState(false);

  // --- HELPER ---
  const isLoggedIn = () => {
    const token = localStorage.getItem('access_token');
    return !!token;
  };

  // 1. Fetch dữ liệu sản phẩm
  useEffect(() => {
    if (id) getDetail(id);
  }, [id, getDetail]);

  // 2. Fetch danh sách địa chỉ nếu đã login
  useEffect(() => {
    if (isLoggedIn()) {
      fetchAddresses();
    }
  }, [fetchAddresses]);

  // 3. Tự động chọn địa chỉ mặc định
  useEffect(() => {
    if (addresses.length > 0 && !addressId) {
      // Ưu tiên địa chỉ mặc định, nếu không có thì lấy cái đầu tiên
      const defaultAddr = addresses.find(a => a.is_default) || addresses[0];
      setAddressId(defaultAddr.id || defaultAddr.uuid);
    }
  }, [addresses, addressId]);

  // 4. Set ảnh chính mặc định
  useEffect(() => {
    if (!productDetail?.images?.length) return;
    const primary = productDetail.images.find(img => img.is_primary === 1) || productDetail.images[0];
    setActiveImage(primary.url);
  }, [productDetail]);

  // 5. Tính toán các Options (Màu, Size...) từ Variants
  const attributeOptions = useMemo(() => {
    if (!productDetail?.variants) return {};

    const options = {};
    productDetail.variants.forEach(variant => {
      variant.attributes.forEach(attr => {
        const name = attr.attribute_name;
        const value = attr.value;
        if (!options[name]) options[name] = new Set();
        options[name].add(value);
      });
    });

    const result = {};
    Object.keys(options).forEach(key => {
      result[key] = Array.from(options[key]);
    });
    
    return result;
  }, [productDetail]);

  // 6. Chọn variant mặc định ban đầu
  useEffect(() => {
    if (productDetail?.variants?.length && !selectedVariant) {
      const firstVariant = productDetail.variants[0];
      const initialAttrs = {};
      firstVariant.attributes.forEach(attr => {
        initialAttrs[attr.attribute_name] = attr.value;
      });

      setSelectedAttributes(initialAttrs);
      setSelectedVariant(firstVariant);
    }
  }, [productDetail]);

  // 7. Logic tìm variant khi user thay đổi attribute
  useEffect(() => {
    if (!productDetail?.variants) return;
    if (Object.keys(selectedAttributes).length === 0) return;

    const foundVariant = productDetail.variants.find(v => 
      v.attributes.every(attr => 
        selectedAttributes[attr.attribute_name] === attr.value
      )
    );

    setSelectedVariant(foundVariant || null);
    
    if (foundVariant && foundVariant.image) {
        setActiveImage(foundVariant.image);
    }
  }, [selectedAttributes, productDetail]);

  // --- HANDLERS ---

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

  // Callback khi tạo địa chỉ thành công từ Modal
  const handleAddressCreated = async () => {
    setShowAddressModal(false); 
    await fetchAddresses(); // Load lại để cập nhật Dropdown
  };
  
  const handleProductAction = async (actionType) => {
    if (!isLoggedIn()) {
      alert('Bạn cần đăng nhập để tiếp tục!');
      return navigate('/customer/login');
    }

    // Validate Attribute
    const requiredAttributes = Object.keys(attributeOptions);
    const missingAttributes = requiredAttributes.filter(key => !selectedAttributes[key]);

    if (missingAttributes.length > 0) {
      return alert(`Vui lòng chọn: ${missingAttributes.join(', ')}`);
    }

    if (!selectedVariant) {
        return alert('Phiên bản sản phẩm này hiện không khả dụng.');
    }

    // Validate Address ID
    if (!addressId) {
        return alert('Vui lòng chọn địa chỉ giao hàng hoặc thêm mới!');
    }

    try {
      if (actionType === 'cart') {
        await addToCart(selectedVariant.uuid, quantity);
        alert('✅ Đã thêm vào giỏ hàng thành công!');
      }

      else if (actionType === 'buy_now') {
        const isConfirmed = window.confirm(`Bạn muốn đặt hàng ngay ${quantity} sản phẩm này?`);
        if (!isConfirmed) return;

        const payload = {
          address_id: parseInt(addressId), // Gửi ID địa chỉ
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

  // --- RENDER LOADING / ERROR ---
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

  // --- VARIABLES DISPLAY ---
  const displayImages = productDetail.images || [];
  const rating = productDetail.rating_avg;
  const reviewsCount = productDetail.rating_count;
  const soldCount = productDetail.sold_count;
  
  // Giá
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
              <span className={styles['current-price']}>{currentPrice}</span>
              
              {showOriginalPrice && (
                <>
                  <span className={styles['original-price']}>{originalPrice}</span>
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
            
            {/* --- SELECTION: DELIVERY ADDRESS --- */}
            <div className={styles['variant-section']}>
              <span className={styles['label']}>Delivery</span>
              <div className={styles['address-selection-row']}>
                <MapPin size={18} className={styles['map-icon']} />
                
                {/* Dropdown chọn địa chỉ */}
                <select 
                  className={styles['address-select']}
                  value={addressId}
                  onChange={(e) => setAddressId(e.target.value)}
                >
                  <option value="">-- Select an address --</option>
                  {addresses.map((addr) => (
                    <option key={addr.id || addr.uuid} value={addr.id || addr.uuid}>
                      {addr.province}, {addr.district}, {addr.ward}, {addr.string}-
                      {addr.is_default ? ' (Default)' : ''}
                    </option>
                  ))}
                </select>

                {/* Nút thêm địa chỉ nhanh */}
                <button 
                  className={styles['btn-add-address']} 
                  onClick={() => setShowAddressModal(true)}
                  title="Add new address"
                >
                  <PlusIcon size={20} />
                </button>
              </div>
              
              {addresses.length === 0 && (
                 <small style={{color: '#888', marginTop: '5px', display: 'block'}}>
                    You haven't added any address yet. Click + to add.
                 </small>
              )}
            </div>

            {/* --- SELECTION: DYNAMIC ATTRIBUTES --- */}
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

            {/* --- SELECTION: QUANTITY --- */}
            <div className={styles['variant-section']}>
              <span className={styles['label']}>Quantity</span>
              <div className={styles['qty-control']}>
                <button onClick={() => handleQuantity('dec')}><Minus size={24}/></button>
                <input type="text" value={quantity} readOnly />
                <button onClick={() => handleQuantity('inc')}><Plus size={24}/></button>
              </div>
            </div>
            
            {/* Variant Stock Warning */}
            {selectedVariant && (
                <div style={{ marginBottom: '15px', color: '#666', fontSize: '14px' }}>
                    Stock available: {selectedVariant.stock_quantity}
                </div>
            )}

            {/* --- ACTION BUTTONS --- */}
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

      {/* --- DESCRIPTION & SPECS --- */}
      <h4 className={styles['desc-title']}>Product Description</h4>
      <div className={styles['product-description-section']}>
        <div className={styles['desc-content']}>
          <p>{productDetail.description || "No description available."}</p>
        </div>

        <h3 className={styles['desc-title-tiny']} >Product Specifications</h3>
        <div className={styles['specs-table']}>
            <div className={styles['spec-row']}>
                <span className={styles['spec-label']}>Brand</span>
                <span className={styles['spec-value']}>Atelier Home</span>
            </div>
            {selectedVariant?.attributes?.map((attr, idx) => (
                <div className={styles['spec-row']} key={idx}>
                    <span className={styles['spec-label']}>{attr.attribute_name}</span>
                    <span className={styles['spec-value']}>{attr.value}</span>
                </div>
            ))}
        </div>

        <h3 className={styles['desc-title']} style={{ marginTop: '40px' }}>Product Rating</h3>
        <ProductReviews productId={productDetail.uuid} />
      </div>      

      {/* --- MODAL ADD ADDRESS --- */}
      {showAddressModal && (
        <div className={styles['modal-overlay']}>
          <div className={styles['modal-content']}>
            <button 
              className={styles['close-modal-btn']} 
              onClick={() => setShowAddressModal(false)}
            >
              &times;
            </button>
            
            <AddressForm 
              onSuccess={handleAddressCreated}
              onCancel={() => setShowAddressModal(false)}
            />
          </div>
        </div>
      )}
    </div>
  );
};

export default ProductDetail;