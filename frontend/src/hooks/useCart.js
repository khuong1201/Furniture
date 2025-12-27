import React, { useState, useEffect, useCallback } from 'react';
import CartService from '@/services/customer/CartService';

export const useCart = () => {
  const [cartItems, setCartItems] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [totalPrice, setTotalPrice] = useState(0);
  const [message, setMessage] = useState(null); 

  const calculateTotal = (items) => {
    const total = items.reduce((acc, item) => {
      const price = item.variant?.price ?? item.product?.price ?? item.price ?? 0;
      return acc + price * item.quantity;
    }, 0);
    setTotalPrice(total);
  };

  const fetchCart = useCallback(async () => {
    setLoading(true);
    try {
      const data = await CartService.getCart();
      const items = Array.isArray(data) ? data : data?.items || [];
      setCartItems(items);
      calculateTotal(items);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, []);

  const addToCart = async (variantUuid, quantity = 1) => {
    setLoading(true);
    setError(null);
    setMessage(null);
    try {
      const result = await CartService.addToCart(variantUuid, quantity);
      setMessage('✅ Đã thêm vào giỏ hàng!');
      await fetchCart();
      return result;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const addAllToCart = async (items) => {
    setLoading(true);
    setError(null);
    try {
      const promises = items.map(item => {
        const targetId = item.variant_uuid || item.product_id || item.id;
        
        if (!targetId) return Promise.resolve(); 

        return CartService.addToCart(targetId, item.quantity)
          .catch(e => console.error(`Failed to add item ${targetId}`, e));
      });

      await Promise.all(promises);

      setMessage('✅ Đã thêm các sản phẩm vào giỏ!');
      await fetchCart();
      return true;
    } catch (err) {
      setError(err.message || "Có lỗi khi thêm vào giỏ hàng");
      return false;
    } finally {
      setLoading(false);
    }
  };

  const updateQuantity = async (itemUuid, newQuantity) => {
    if (newQuantity < 0) return;
    if (newQuantity === 0) return removeItem(itemUuid);
    try {
      const newItems = cartItems.map(item => item.uuid === itemUuid ? { ...item, quantity: newQuantity } : item);
      setCartItems(newItems);
      calculateTotal(newItems);
      await CartService.updateItem(itemUuid, newQuantity);
    } catch (err) {
      fetchCart();
    }
  };

  const removeItem = async (itemUuid) => {
    if (!window.confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
    try {
      await CartService.removeItem(itemUuid);
      const newItems = cartItems.filter(item => item.uuid !== itemUuid);
      setCartItems(newItems);
      calculateTotal(newItems);
    } catch (err) {
      alert(err.message);
    }
  };
  
  return {
    cartItems, loading, error, message, totalPrice,
    fetchCart, addToCart, addAllToCart, // Export hàm mới
    updateQuantity, removeItem,
  };
};