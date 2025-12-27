import React, { useEffect, useState, useMemo } from 'react';
import { useCart } from '@/hooks/useCart';
import { useOrder } from '@/hooks/useOrder';
import { useAddress } from '@/hooks/useAddress';
import { Link, useNavigate } from 'react-router-dom';
import styles from './CartPage.module.css';

const CartPage = () => {
  const { cartItems, loading, error, updateQuantity, removeItem, fetchCart } = useCart();
  const { checkout, loading: orderLoading } = useOrder();
  const { addresses, fetchAddresses } = useAddress();

  const [selectedItems, setSelectedItems] = useState(new Set());
  const navigate = useNavigate();

  useEffect(() => {
    fetchCart();
    fetchAddresses();
  }, [fetchCart, fetchAddresses]);

  const toggleItem = (uuid) => {
    const next = new Set(selectedItems);
    next.has(uuid) ? next.delete(uuid) : next.add(uuid);
    setSelectedItems(next);
  };

  const toggleAll = () => {
    if (selectedItems.size === cartItems.length) return setSelectedItems(new Set());
    setSelectedItems(new Set(cartItems.map(i => i.uuid)));
  };

  const isAllSelected = cartItems.length > 0 && selectedItems.size === cartItems.length;

  const totalSelectedPrice = useMemo(() => {
    return cartItems.reduce((t, item) => {
      if (!selectedItems.has(item.uuid)) return t;
      const price = item.price?.raw || 0;
      return t + price * item.quantity;
    }, 0);
  }, [cartItems, selectedItems]);

  const formatCurrency = (v) => new Intl.NumberFormat('en-US').format(v);

  const handleCheckout = async () => {
    if (selectedItems.size === 0) return alert('Vui lòng chọn ít nhất một sản phẩm!');

    const address = addresses.find(a => a.is_default) || addresses[0];
    if (!address) {
      alert('Bạn chưa có địa chỉ giao hàng.');
      return navigate('/address');
    }

    const selectedUuids = cartItems
      .filter(i => selectedItems.has(i.uuid))
      .map(i => i.uuid);

    if (selectedUuids.length === 0) return alert('Lỗi dữ liệu sản phẩm');

    const payload = {
      address_id: Number(address.id),
      notes: 'Thanh toán từ giỏ hàng',
      selected_item_uuids: selectedUuids,
    };

    if (!window.confirm(`Xác nhận thanh toán ${selectedUuids.length} sản phẩm?`)) return;

    try {
      const result = await checkout(payload);
      // alert('Đặt hàng thành công!');

      await fetchCart();
      setSelectedItems(new Set());

      if (result?.uuid) navigate(`/orders/${result.uuid}`);
    } catch {}
  };

  if (loading && cartItems.length === 0) return <div className={styles['cart-loading']}>⏳ Đang tải giỏ hàng...</div>;
  if (error) return <div className={styles['cart-error']}>⚠️ Lỗi: {error}</div>;

  if (!loading && cartItems.length === 0) {
    return (
      <div className={styles['cart-empty']}>
        <div className={styles['empty-icon']}>🛒</div>
        <h2>Giỏ hàng của bạn đang trống</h2>
        <Link to="/" className={styles['btn-back']}>Quay lại mua sắm</Link>
      </div>
    );
  }

  return (
    <div className={styles['cart-container']}>

      <div className={styles['cart-header-row']}>
        <div className={styles['col-product']}>
          <input type="checkbox" checked={isAllSelected} onChange={toggleAll} className={styles['custom-checkbox']} />
          <span style={{ marginLeft: 15 }}>Product</span>
        </div>
        <div className={styles['col-unit-price']}>Unit Price</div>
        <div className={styles['col-quantity']}>Quantity</div>
        <div className={styles['col-amount']}>Amount</div>
        <div className={styles['col-operation']}>Operation</div>
      </div>

      <div className={styles['cart-store-group']}>
        {cartItems.map(item => {
          const price = item.price?.raw || 0;
          const subTotal = price * item.quantity;

          return (
            <div key={item.uuid} className={styles['cart-item-row']}>

              <div className={`${styles['col-product']} ${styles['product-cell']}`}>
                <input
                  type="checkbox"
                  checked={selectedItems.has(item.uuid)}
                  onChange={() => toggleItem(item.uuid)}
                  className={styles['custom-checkbox']}
                />

                <img src={item.image || 'https://placehold.co/100'} alt={item.product_name} className={styles['product-img']} />

                <div className={styles['product-info-box']}>
                  <div className={styles['product-name']} title={item.product_name}>{item.product_name}</div>
                  {item.options && <div className={styles['product-variant']}>{item.options}</div>}
                  {item.sku && <div className={styles['product-sku']}>SKU: {item.sku}</div>}
                </div>
              </div>

              <div className={styles['col-unit-price']}>
                <span className={styles['current-price']}>₫{formatCurrency(price)}</span>
              </div>

              <div className={styles['col-quantity']}>
                <div className={styles['quantity-box']}>
                  <button onClick={() => updateQuantity(item.uuid, item.quantity - 1)} disabled={item.quantity <= 1}>−</button>
                  <input type="text" value={item.quantity} readOnly />
                  <button onClick={() => updateQuantity(item.uuid, item.quantity + 1)}>+</button>
                </div>
              </div>

              <div className={`${styles['col-amount']} ${styles['text-red']}`}>₫{formatCurrency(subTotal)}</div>

              <div className={styles['col-operation']}>
                <button className={styles['btn-delete']} onClick={() => removeItem(item.uuid)}>Delete</button>
                <div className={styles['btn-find-similar']}>Find Similar</div>
              </div>

            </div>
          );
        })}
      </div>

      <div className={styles['cart-footer']}>
        <div className={styles['footer-left']}>
          <input type="checkbox" checked={isAllSelected} onChange={toggleAll} className={styles['custom-checkbox']} />
          <label style={{ cursor: 'pointer' }}>Select All ({cartItems.length})</label>
          <button className={styles['btn-footer-text']}>Delete</button>
          <button className={styles['btn-footer-text']}>Save for My Likes</button>
        </div>

        <div className={styles['footer-right']}>
          <div className={styles['total-info']}>
            Total ({selectedItems.size} items):
            <span className={styles['total-price-large']}>₫{formatCurrency(totalSelectedPrice)}</span>
          </div>

          <button
            className={styles['btn-buy-now']}
            onClick={handleCheckout}
            disabled={orderLoading || selectedItems.size === 0}
            style={{ opacity: (orderLoading || selectedItems.size === 0) ? 0.6 : 1 }}
          >
            {orderLoading ? 'Processing...' : 'Buy Now'}
          </button>
        </div>
      </div>

    </div>
  );
};

export default CartPage;
