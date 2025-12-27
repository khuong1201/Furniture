import React, { useState, useEffect, useMemo, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useProduct } from '@/hooks/useProduct';
import { useCart } from '@/hooks/useCart';
import { useOrder } from '@/hooks/useOrder';
import { useAddress } from '@/hooks/useAddress'; 

import { Star, Minus, Plus, ShoppingCart, ChevronRight, MapPin, Plus as PlusIcon, Check, X } from 'lucide-react';
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
  const { buyNow, loading: orderLoading } = useOrder();
  const { addresses, fetchAddresses } = useAddress();

  // --- STATES ---
  const [activeImage, setActiveImage] = useState(null);
  const [selectedAttributes, setSelectedAttributes] = useState({}); 
  
  // State địa chỉ & số lượng
  const [addressId, setAddressId] = useState(null); 
  const [quantity, setQuantity] = useState(1);
  
  // State Modal Address
  const [showAddressModal, setShowAddressModal] = useState(false);
  const [modalMode, setModalMode] = useState('list'); // 'list' | 'create'

  // --- MEMOS (LOGIC TỐI ƯU) ---
  
  // 1. Check login 1 lần
  const isLoggedIn = useMemo(() => !!localStorage.getItem('access_token'), []);

  // 2. Tự động tính toán Variant dựa trên attributes đã chọn (Thay vì dùng useEffect set state)
  const selectedVariant = useMemo(() => {
    if (!productDetail?.variants || Object.keys(selectedAttributes).length === 0) return null;
    return productDetail.variants.find(v => 
      v.attributes.every(attr => selectedAttributes[attr.attribute_name] === attr.value)
    );
  }, [productDetail, selectedAttributes]);

  // 3. Tính toán danh sách Attributes có sẵn
  const attributeOptions = useMemo(() => {
    if (!productDetail?.variants) return {};
    const options = {};
    productDetail.variants.forEach(variant => {
      variant.attributes.forEach(attr => {
        const name = attr.attribute_name;
        if (!options[name]) options[name] = new Set();
        options[name].add(attr.value);
      });
    });
    const result = {};
    Object.keys(options).forEach(key => result[key] = Array.from(options[key]));
    return result;
  }, [productDetail]);

  // 4. Tìm object địa chỉ đang chọn
  const selectedAddressObj = useMemo(() => {
      if (!addressId) return null;
      return addresses.find(a => (a.id === addressId || a.uuid === addressId));
  }, [addresses, addressId]);

  // --- EFFECTS ---

  // 1. Fetch dữ liệu khi vào trang
  useEffect(() => {
    if (id) getDetail(id);
    if (isLoggedIn) fetchAddresses();
  }, [id, isLoggedIn, getDetail, fetchAddresses]);

  // 2. Tự động chọn địa chỉ mặc định (Chỉ chạy khi list address thay đổi)
  useEffect(() => {
    if (addresses.length > 0 && !addressId) {
      const defaultAddr = addresses.find(a => a.is_default) || addresses[0];
      setAddressId(defaultAddr.id || defaultAddr.uuid);
    }
  }, [addresses, addressId]);

  // 3. Khởi tạo Attribute mặc định & Ảnh chính khi Product vừa load xong
  useEffect(() => {
    if (!productDetail) return;

    // Set ảnh chính
    const primary = productDetail.images?.find(img => img.is_primary === 1) || productDetail.images?.[0];
    if (primary) setActiveImage(primary.url);

    // Set attribute mặc định của variant đầu tiên
    if (productDetail.variants?.length > 0) {
      const firstVariant = productDetail.variants[0];
      const initialAttrs = {};
      firstVariant.attributes.forEach(attr => initialAttrs[attr.attribute_name] = attr.value);
      setSelectedAttributes(initialAttrs);
    }
  }, [productDetail]);

  // 4. Tự động đổi ảnh khi chọn Variant khác
  useEffect(() => {
    if (selectedVariant?.image) {
      setActiveImage(selectedVariant.image);
    }
  }, [selectedVariant]);

  // --- HANDLERS (Dùng useCallback để tránh render lại component con) ---
  
  const handleAttributeSelect = useCallback((attributeName, value) => {
    setSelectedAttributes(prev => ({ ...prev, [attributeName]: value }));
  }, []);

  const handleQuantity = useCallback((type) => {
    setQuantity(prev => {
      if (type === 'dec') return Math.max(1, prev - 1);
      return prev + 1;
    });
  }, []);

  const handleOpenAddressModal = useCallback(() => {
      if (!isLoggedIn) return navigate('/login');
      setShowAddressModal(true);
      setModalMode(addresses.length === 0 ? 'create' : 'list');
  }, [isLoggedIn, addresses.length, navigate]);

  const handleSelectAddressInModal = useCallback((addr) => {
      setAddressId(addr.id || addr.uuid);
      setShowAddressModal(false);
  }, []);

  const handleAddressCreated = async () => {
    await fetchAddresses();
    setModalMode('list'); 
  };
  
  const handleProductAction = async (actionType) => {
    if (!isLoggedIn) {
      alert('Bạn cần đăng nhập để tiếp tục!');
      return navigate('/login');
    }

    const requiredAttributes = Object.keys(attributeOptions);
    const missingAttributes = requiredAttributes.filter(key => !selectedAttributes[key]);

    if (missingAttributes.length > 0) return alert(`Vui lòng chọn: ${missingAttributes.join(', ')}`);
    if (!selectedVariant) return alert('Phiên bản sản phẩm này hiện không khả dụng.');
    if (!addressId) return alert('Vui lòng chọn địa chỉ giao hàng!');

    try {
      if (actionType === 'cart') {
        await addToCart(selectedVariant.uuid, quantity);
        alert('✅ Đã thêm vào giỏ hàng thành công!');
      } else if (actionType === 'buy_now') {
        const isConfirmed = window.confirm(`Bạn muốn đặt hàng ngay ${quantity} sản phẩm này?`);
        if (!isConfirmed) return;

        const payload = {
          address_id: parseInt(addressId),
          variant_uuid: selectedVariant.uuid, 
          quantity: quantity,
          voucher_code: null, 
          note: 'Mua ngay'
        };

        const result = await buyNow(payload);
        const orderUuid = result?.data?.uuid || result?.uuid;
            
            if (orderUuid) {
                navigate(`/orders/${orderUuid}`);
            } else {
                navigate('/me?tab=orders');
            }
      }
    } catch (error) {
      alert(error.message || 'Có lỗi xảy ra');
    }
  };

  if (loading) return <div className="loading-state"><AiOutlineLoading3Quarters className="loading-icon" /><span>Đang tải...</span></div>;
  if (!productDetail || error) return <div className="error-state"><AiOutlineWarning className="error-icon" /><span>{error}</span></div>;

  // Variables tính toán cho UI
  const displayImages = productDetail.images || [];
  const currentPrice = selectedVariant ? selectedVariant.price_formatted : productDetail.price_formatted;
  const originalPrice = selectedVariant ? (selectedVariant.original_price_formatted || null) : productDetail.original_price_formatted;
  const isFlashSale = productDetail.flash_sale?.is_active;
  const showOriginalPrice = isFlashSale || (originalPrice && originalPrice !== currentPrice);

  return (
    <div className={styles['pd-wrapper']}>
      <div className={styles['breadcrumb']}>
        <span>Product</span> <ChevronRight size={14} /> <span>{productDetail.name}</span>
      </div>

      <div className={styles['pd-container']}>
        {/* LEFT: GALLERY */}
        <div className={styles['pd-gallery']}>
          <div className={styles['main-image']}>
            <img src={activeImage || displayImages?.[0]?.url} alt={productDetail.name} />
          </div>
          <div className={styles['thumbnail-list']}>
            {displayImages.map((img, index) => (
              <div key={img.uuid} className={`${styles['thumb-item']} ${activeImage === img.url ? styles['active'] : ''}`} onMouseEnter={() => setActiveImage(img.url)}>
                <img src={img.url} alt={`Thumb ${index}`} />
              </div>
            ))}
          </div>
        </div>

        {/* RIGHT: INFO */}
        <div className={styles['pd-info']}>
          <div className={styles['product-header']}>
            <h1 className={styles['product-title']}>{productDetail.name}</h1>
            <div className={styles['product-meta']}>
              <span className={styles['rating']}>{productDetail.rating_avg} <Star size={24} fill="#ffc107" color="#ffc107" /></span>
              <span className={styles['divider']}>|</span>
              <span className={styles['reviews']}>{productDetail.rating_count} Ratings</span>
              <span className={styles['divider']}>|</span>
              <span className={styles['sold']}>{productDetail.sold_count} sold</span>
            </div>
            <div className={styles['price-section']}>
              <span className={styles['current-price']}>{currentPrice}</span>
              {showOriginalPrice && (
                <>
                  <span className={styles['original-price']}>{originalPrice}</span>
                  {isFlashSale && <span className={styles['discount-badge']}>-{productDetail.flash_sale.discount_percent}%</span>}
                </>
              )}
            </div>  
          </div>
          
          <div className={styles['product-body']}>
            
            {/* --- SELECTION: ADDRESS (NEW UI) --- */}
            <div className={styles['variant-section']}>
              <span className={styles['label']}>Delivery</span>
              <div className={styles['address-display-box']}>
                 <div className={styles['addr-header']}>
                    <MapPin size={18} className={styles['map-icon']} />
                    <span className={styles['addr-title']}>Shipping To:</span>
                 </div>
                 
                 {selectedAddressObj ? (
                      <div className={styles['addr-details']}>
                          <p className={styles['addr-name']}>
                              {selectedAddressObj.full_name} | {selectedAddressObj.phone}
                              {selectedAddressObj.is_default && <span className={styles['default-tag']}>Default</span>}
                          </p>
                          <p className={styles['addr-text']}>
                              {selectedAddressObj.street}, {selectedAddressObj.ward}, {selectedAddressObj.district}, {selectedAddressObj.province}
                          </p>
                      </div>
                 ) : (
                      <div className={styles['addr-placeholder']}>
                          Please select a shipping address
                      </div>
                 )}
                 
                 <button className={styles['btn-change-addr']} onClick={handleOpenAddressModal}>
                      {selectedAddressObj ? 'Change' : 'Select Address'}
                 </button>
              </div>
            </div>

            {/* --- ATTRIBUTES --- */}
            {Object.entries(attributeOptions).map(([attrName, values]) => (
              <div className={styles['variant-section']} key={attrName}>
                <span className={styles['label']}>{attrName}</span>
                <div className={styles['options-row']}>
                  {values.map((val) => (
                    <button 
                      key={val}
                      className={`${styles['option-btn']} ${selectedAttributes[attrName] === val ? styles['selected'] : ''}`}
                      onClick={() => handleAttributeSelect(attrName, val)}
                    >
                      {val}
                    </button>
                  ))}
                </div>
              </div>
            ))}

            {/* --- QUANTITY --- */}
            <div className={styles['variant-section']}>
              <span className={styles['label']}>Quantity</span>
              <div className={styles['qty-control']}>
                <button onClick={() => handleQuantity('dec')}><Minus size={24}/></button>
                <input type="text" value={quantity} readOnly />
                <button onClick={() => handleQuantity('inc')}><Plus size={24}/></button>
              </div>
            </div>
            
            {selectedVariant && (
                <div style={{ marginBottom: '15px', color: '#666', fontSize: '14px' }}>
                    Stock available: {selectedVariant.stock_quantity}
                </div>
            )}

            {/* --- ACTIONS --- */}
            <div className={styles['action-buttons']}>
              <button className={styles['btn-add-cart']} onClick={() => handleProductAction('cart')} disabled={cartLoading}>
                <ShoppingCart size={20}/> {cartLoading ? 'Processing...' : 'Add to Cart'}
              </button>
              <button className={styles['btn-buy-now']} onClick={() => handleProductAction('buy_now')} disabled={cartLoading || orderLoading || !selectedVariant}>
                {orderLoading ? 'Processing...' : 'Buy Now'}
              </button>
            </div>  
          </div>
        </div>
      </div>

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

      {/* --- MODAL ADDRESS (LIST & CREATE) --- */}
      {showAddressModal && (
        <div className={styles['modal-overlay']}>
          <div className={styles['modal-content']}>
            <div className={styles['modal-header']}>
                <h3>{modalMode === 'list' ? 'My Addresses' : 'Add New Address'}</h3>
                <button className={styles['close-modal-btn']} onClick={() => setShowAddressModal(false)}><X size={24}/></button>
            </div>

            <div className={styles['modal-body']}>
                {modalMode === 'list' ? (
                    <div className={styles['address-list']}>
                        {addresses.length === 0 ? (
                             <div className={styles['empty-addr']}>No address found.</div>
                        ) : (
                            addresses.map(addr => (
                                <div 
                                    key={addr.id || addr.uuid} 
                                    className={`${styles['address-card']} ${(addressId === addr.id || addressId === addr.uuid) ? styles['active-card'] : ''}`}
                                >
                                    <div className={styles['addr-card-info']}>
                                        <div className={styles['card-row']}>
                                            <span className={styles['card-name']}>{addr.full_name}</span>
                                            <span className={styles['card-phone']}>| {addr.phone}</span>
                                            {addr.is_default && <span className={styles['default-tag']}>Default</span>}
                                        </div>
                                        <div className={styles['card-address']}>
                                            {addr.street}, {addr.ward}, {addr.district}, {addr.province}
                                        </div>
                                    </div>
                                    <div className={styles['addr-card-action']}>
                                        {(addressId === addr.id || addressId === addr.uuid) ? (
                                             <span className={styles['selected-text']}><Check size={16}/> Selected</span>
                                        ) : (
                                            <button className={styles['btn-select']} onClick={() => handleSelectAddressInModal(addr)}>
                                                Select
                                            </button>
                                        )}
                                    </div>
                                </div>
                            ))
                        )}
                        <button className={styles['btn-create-new']} onClick={() => setModalMode('create')}>
                            <PlusIcon size={18}/> Add New Address
                        </button>
                    </div>
                ) : (
                    // MODE CREATE
                    <AddressForm 
                        onSuccess={handleAddressCreated}
                        onCancel={() => setModalMode('list')}
                    />
                )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ProductDetail;