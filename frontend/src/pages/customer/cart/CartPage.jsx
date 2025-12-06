import React, { useEffect, useState, useMemo } from 'react';
import { useCart } from '@/hooks/useCart';
import { Link } from 'react-router-dom';
import styles from './CartPage.module.css'; // ✅ CSS MODULE

const CartPage = () => {
  const { cartItems, loading, error, updateQuantity, removeItem, fetchCart } = useCart();
  
  const [selectedItems, setSelectedItems] = useState(new Set());

  useEffect(() => {
    fetchCart();
  }, [fetchCart]);

  const toggleItem = (uuid) => {
    const newSelected = new Set(selectedItems);
    if (newSelected.has(uuid)) {
      newSelected.delete(uuid);
    } else {
      newSelected.add(uuid);
    }
    setSelectedItems(newSelected);
  };

  const toggleAll = () => {
    if (selectedItems.size === cartItems.length) {
      setSelectedItems(new Set());
    } else {
      const allUuids = cartItems.map(item => item.uuid);
      setSelectedItems(new Set(allUuids));
    }
  };

  const isAllSelected = cartItems.length > 0 && selectedItems.size === cartItems.length;

  const totalSelectedPrice = useMemo(() => {
    return cartItems.reduce((total, item) => {
      if (selectedItems.has(item.uuid)) {
        const price = item.price?.final || item.product?.price || 0;
        return total + (price * item.quantity);
      }
      return total;
    }, 0);
  }, [cartItems, selectedItems]);

  const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-US').format(val);
  };

  const renderAttributes = (attributes) => {
    if (!attributes || !Array.isArray(attributes)) return '';
    return attributes.map(attr => `${attr.name}: ${attr.value}`).join(', ');
  };

  if (loading && cartItems.length === 0) {
    return <div className={styles['cart-loading']}>⏳ Đang tải giỏ hàng...</div>;
  }

  if (error) {
    return <div className={styles['cart-error']}>⚠️ Lỗi: {error}</div>;
  }

  if (!loading && cartItems.length === 0) {
    return (
      <div className={styles['cart-empty']}>
        <div className={styles['empty-icon']}>🛒</div>
        <h2>Giỏ hàng của bạn đang trống</h2>
        <Link to="/customer" className={styles['btn-back']}>Quay lại mua sắm</Link>
      </div>
    );
  }

  return (
    <div className={styles['cart-container']}>
      {/* Header */}
      <div className={styles['cart-header-row']}>
        <div className={styles['col-product']}>
          <input 
            type="checkbox" 
            checked={isAllSelected} 
            onChange={toggleAll}
            className={styles['custom-checkbox']}
          />
          <span style={{ marginLeft: 15 }}>Product</span>
        </div>
        <div className={styles['col-unit-price']}>Unit Price</div>
        <div className={styles['col-quantity']}>Quantity</div>
        <div className={styles['col-amount']}>Amount</div>
        <div className={styles['col-operation']}>Operation</div>
      </div>

      {/* Store group */}
      <div className={styles['cart-store-group']}>
        <div className={styles['store-header']}>
          <input 
            type="checkbox"
            checked={isAllSelected}
            onChange={toggleAll}
            className={styles['custom-checkbox']}
          />
          <span className={styles['store-name']}>
            Store Chính Hãng <span className={styles['chat-icon']}>💬</span>
          </span>
        </div>

        {cartItems.map((item) => {
          const product = item.product || {};
          const priceObj = item.price || {};
          const finalPrice = priceObj.final || 0;
          const subTotal = priceObj.subtotal || (finalPrice * item.quantity);
          const attributesText = renderAttributes(product.attributes);
          const imageUrl = product.image || 'https://via.placeholder.com/100';

          return (
            <div key={item.uuid} className={styles['cart-item-row']}>
              {/* Product */}
              <div className={`${styles['col-product']} ${styles['product-cell']}`}>
                <input 
                  type="checkbox" 
                  checked={selectedItems.has(item.uuid)}
                  onChange={() => toggleItem(item.uuid)}
                  className={styles['custom-checkbox']}
                />
                <img src={imageUrl} alt={product.name} className={styles['product-img']} />
                <div className={styles['product-info-box']}>
                  <div className={styles['product-name']} title={product.name}>
                    {product.name}
                  </div>
                  {attributesText && (
                    <div className={styles['product-variant']}>
                      {attributesText}
                    </div>
                  )}
                  {product.sku && (
                    <div className={styles['product-sku']}>SKU: {product.sku}</div>
                  )}
                </div>
              </div>

              {/* Unit price */}
              <div className={styles['col-unit-price']}>
                {priceObj.original > finalPrice && (
                  <span className={styles['original-price']}>₫{formatCurrency(priceObj.original)}</span>
                )}
                <span className={styles['current-price']}>₫{formatCurrency(finalPrice)}</span>
              </div>

              {/* Quantity */}
              <div className={styles['col-quantity']}>
                <div className={styles['quantity-box']}>
                  <button 
                    onClick={() => updateQuantity(item.uuid, item.quantity - 1)}
                    disabled={item.quantity <= 1}
                  >−</button>
                  <input type="text" value={item.quantity} readOnly />
                  <button onClick={() => updateQuantity(item.uuid, item.quantity + 1)}>+</button>
                </div>
              </div>

              {/* Amount */}
              <div className={`${styles['col-amount']} ${styles['text-red']}`}>
                ₫{formatCurrency(subTotal)}
              </div>

              {/* Operation */}
              <div className={styles['col-operation']}>
                <button className={styles['btn-delete']} onClick={() => removeItem(item.uuid)}>
                  Delete
                </button>
                <div className={styles['btn-find-similar']}>Find Similar</div>
              </div>
            </div>
          );
        })}
      </div>

      {/* Footer */}
      <div className={styles['cart-footer']}>
        <div className={styles['footer-left']}>
          <input 
            type="checkbox" 
            id="selectAllFooter"
            checked={isAllSelected}
            onChange={toggleAll}
            className={styles['custom-checkbox']}
          />
          <label htmlFor="selectAllFooter" style={{cursor: 'pointer'}}>
            Select All ({cartItems.length})
          </label>
          <button className={styles['btn-footer-text']}>Delete</button>
          <button className={styles['btn-footer-text']}>Save for My Likes</button>
        </div>
        
        <div className={styles['footer-right']}>
          <div className={styles['total-info']}>
             Total ({selectedItems.size} items): 
             <span className={styles['total-price-large']}>
               ₫{formatCurrency(totalSelectedPrice)}
             </span>
          </div>
          <button className={styles['btn-buy-now']}>Buy Now</button>
        </div>
      </div>
    </div>
  );
};

export default CartPage;
