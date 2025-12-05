import React from 'react';
import { useCart } from '@/hooks/useCart';
import { Link } from 'react-router-dom';
import styles from './CartPage.module.css'; // 👈 Import styles object

const CartPage = () => {
  const { cartItems, loading, error, totalPrice, updateQuantity, removeItem } = useCart();

  // Loading & Error States
  if (loading && cartItems.length === 0) {
    return <div className={styles['cart-loading']}>⏳ Đang tải giỏ hàng...</div>;
  }
  if (error) {
    return <div className={styles['cart-error']}>⚠️ Lỗi: {error}</div>;
  }

  // Empty State
  if (!loading && cartItems.length === 0) {
    return (
      <div className={styles['cart-empty']}>
        <div className={styles['empty-icon']}>🛒</div>
        <h2>Giỏ hàng của bạn đang trống</h2>
        <p>Có vẻ như bạn chưa thêm sản phẩm nào vào giỏ hàng.</p>
        <Link to="/customer" className={styles['btn-back']}>Quay lại mua sắm</Link>
      </div>
    );
  }

  return (
    <div className={styles['cart-container']}>
      <h1 className={styles['cart-title']}>
        Giỏ hàng của bạn <span>({cartItems.length} sản phẩm)</span>
      </h1>

      <div className={styles['cart-layout']}>
        
        {/* === CỘT TRÁI: DANH SÁCH SẢN PHẨM === */}
        <div className={styles['cart-list']}>
          <div className={styles['cart-table-header']}>
            <div className={styles['col-product']}>Sản phẩm</div>
            <div className={styles['col-price']}>Đơn giá</div>
            <div className={styles['col-qty']}>Số lượng</div>
            <div className={styles['col-total']}>Thành tiền</div>
            <div className={styles['col-action']}></div>
          </div>

          {cartItems.map((item) => {
            const product = item.product || {};
            const variant = item.variant || {};
            
            const name = product.name || 'Sản phẩm không tên';
            const variantName = variant.sku ? `(${variant.sku})` : ''; 
            const imageUrl = variant.image || product.image || 'https://via.placeholder.com/100';
            const price = Number(variant.price || product.price || 0);
            const lineTotal = price * item.quantity;

            return (
              <div key={item.uuid} className={styles['cart-row']}>
                
                {/* 1. Sản phẩm */}
                {/* Kết hợp nhiều class bằng template literal */}
                <div className={`${styles['cart-product']} ${styles['col-product']}`}>
                  <div className={styles['product-img']}>
                    <img src={imageUrl} alt={name} />
                  </div>
                  <div className={styles['product-info']}>
                    <h3>
                      {name} <span className={styles['variant-sku']}>{variantName}</span>
                    </h3>
                    <p className={styles['mobile-price-display']}>
                      {price.toLocaleString()} đ
                    </p>
                  </div>
                </div>

                {/* 2. Đơn giá (Desktop) */}
                <div className={`${styles['col-price']} ${styles['desktop-only']}`}>
                  {price.toLocaleString()} đ
                </div>

                {/* 3. Số lượng */}
                <div className={`${styles['cart-quantity']} ${styles['col-qty']}`}>
                  <button 
                    onClick={() => updateQuantity(item.uuid, item.quantity - 1)}
                    disabled={item.quantity <= 1}
                  >
                    −
                  </button>
                  <input type="text" readOnly value={item.quantity} />
                  <button onClick={() => updateQuantity(item.uuid, item.quantity + 1)}>
                    +
                  </button>
                </div>

                {/* 4. Thành tiền */}
                <div className={`${styles['cart-item-total']} ${styles['col-total']}`}>
                  {lineTotal.toLocaleString()} đ
                </div>

                {/* 5. Nút xóa */}
                <div className={styles['col-action']}>
                   <button 
                     className={styles['btn-remove']} 
                     onClick={() => removeItem(item.uuid)}
                     title="Xóa sản phẩm"
                   >
                     ×
                   </button>
                </div>
              </div>
            );
          })}
        </div>

        {/* === CỘT PHẢI: TỔNG TIỀN === */}
        <div className={styles['cart-summary']}>
          <h3>Cộng giỏ hàng</h3>
          <div className={styles['summary-content']}>
            <div className={styles['summary-row']}>
              <span>Tạm tính:</span>
              <span>{totalPrice.toLocaleString()} đ</span>
            </div>
            <div className={styles['summary-row']}>
              <span>Phí vận chuyển:</span>
              <span className={styles['text-green']}>Miễn phí</span>
            </div>
            <hr />
            
            {/* Class 'total' được nối thêm */}
            <div className={`${styles['summary-row']} ${styles['total']}`}>
              <span>Tổng cộng:</span>
              <span className={styles['total-price']}>{totalPrice.toLocaleString()} đ</span>
            </div>
            
            <button className={styles['checkout-btn']}>Tiến hành thanh toán</button>
            
            <Link to="/customer" className={styles['continue-shopping']}>
              ← Tiếp tục mua sắm
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
};

export default CartPage;