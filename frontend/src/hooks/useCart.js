import { useState, useCallback, useEffect } from 'react';
import CartService from '@/services/cartService';

export const useCart = () => {
  const [cartItems, setCartItems] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [totalPrice, setTotalPrice] = useState(0);

  // Hàm tính tổng tiền (Client side calculation)
  const calculateTotal = (items) => {
    const total = items.reduce((acc, item) => {
      // Giả sử API trả về structure: { product: { price: 100 }, quantity: 2 }
      // Bạn cần điều chỉnh price/quantity tùy theo response thực tế
      const price = item.product?.price || item.price || 0;
      return acc + (price * item.quantity);
    }, 0);
    setTotalPrice(total);
  };

  // 1. Lấy giỏ hàng
  const fetchCart = useCallback(async () => {
    setLoading(true);
    try {
      const data = await CartService.getCart();
      // Nếu data trả về là mảng items
      const items = Array.isArray(data) ? data : (data.items || []);
      setCartItems(items);
      calculateTotal(items);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, []);

  // 2. Cập nhật số lượng
  const updateQuantity = async (itemId, newQuantity) => {
    if (newQuantity < 1) return; // Không cho nhỏ hơn 1
    try {
      // Optimistic Update: Cập nhật giao diện trước khi gọi API để mượt hơn
      const oldItems = [...cartItems];
      const newItems = cartItems.map(item => 
        item.id === itemId ? { ...item, quantity: newQuantity } : item
      );
      setCartItems(newItems);
      calculateTotal(newItems);

      // Gọi API
      await CartService.updateItem(itemId, newQuantity);
    } catch (err) {
      console.error('Lỗi update:', err);
      // Nếu lỗi thì revert lại (Optional)
      fetchCart(); 
    }
  };

  // 3. Xóa sản phẩm
  const removeItem = async (itemId) => {
    if (!window.confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
    try {
      await CartService.removeItem(itemId);
      // Xóa item khỏi state hiện tại
      const newItems = cartItems.filter(item => item.id !== itemId);
      setCartItems(newItems);
      calculateTotal(newItems);
    } catch (err) {
      alert('Xóa thất bại: ' + err.message);
    }
  };

  // Tự động fetch khi mount hook
  useEffect(() => {
    fetchCart();
  }, [fetchCart]);

  return {
    cartItems,
    loading,
    error,
    totalPrice,
    fetchCart,
    updateQuantity,
    removeItem
  };
};