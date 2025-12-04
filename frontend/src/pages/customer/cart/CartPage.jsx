import React from 'react';
import { useCart } from '@/hooks/useCart';
import { Link } from 'react-router-dom';
import './CartPage.css';

const CartPage = () => {
  const { cartItems, loading, error, totalPrice, updateQuantity, removeItem } = useCart();

  if (loading) return <div className="cart-loading">Đang tải giỏ hàng...</div>;
  if (error) return <div className="cart-error">Lỗi: {error}</div>;

  if (cartItems.length === 0) {
    return (
      <div className="cart-empty">
        <h2>Giỏ hàng của bạn đang trống</h2>
        <Link to="/customer">Quay lại mua sắm</Link>
      </div>
    );
  }

  return (
    <div className="cart-container">
      <h1 className="cart-title">Giỏ hàng ({cartItems.length} sản phẩm)</h1>

      <div className="cart-layout">

        {/* DANH SÁCH SẢN PHẨM */}
        <div className="cart-list">
          <div className="cart-table">
            <div className="cart-header">
              <div>Sản phẩm</div>
              <div>Đơn giá</div>
              <div>Số lượng</div>
              <div>Thành tiền</div>
            </div>

            {cartItems.map((item) => {
              const product = item.product || {}; 
              const imageUrl = product.image || 'https://via.placeholder.com/100';

              return (
                <div key={item.id} className="cart-row">
                  <div className="cart-product">
                    <img src={imageUrl} alt={product.name} />
                    <div>
                      <h3>{product.name}</h3>
                      <button onClick={() => removeItem(item.id)}>Xóa</button>
                    </div>
                  </div>

                  <div>{Number(product.price).toLocaleString()} đ</div>

                  <div className="cart-quantity">
                    <button
                      onClick={() => updateQuantity(item.id, item.quantity - 1)}
                      disabled={item.quantity <= 1}
                    >
                      -
                    </button>
                    <span>{item.quantity}</span>
                    <button onClick={() => updateQuantity(item.id, item.quantity + 1)}>
                      +
                    </button>
                  </div>

                  <div className="cart-item-total">
                    {(Number(product.price) * item.quantity).toLocaleString()} đ
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* TỔNG TIỀN */}
        <div className="cart-summary">
          <h3>Cộng giỏ hàng</h3>

          <div className="summary-row">
            <span>Tạm tính:</span>
            <span>{totalPrice.toLocaleString()} đ</span>
          </div>

          <div className="summary-row">
            <span>Phí vận chuyển:</span>
            <span className="free">Miễn phí</span>
          </div>

          <div className="summary-row total">
            <span>Tổng cộng:</span>
            <span>{totalPrice.toLocaleString()} đ</span>
          </div>

          <button className="checkout-btn">Tiến hành thanh toán</button>

          <Link to="/customer" className="continue-shopping">
            ← Tiếp tục mua sắm
          </Link>
        </div>
      </div>
    </div>
  );
};

export default CartPage;
