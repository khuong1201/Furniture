import { useState, useCallback } from 'react';
import ProductService from '@/services/productService';

export const useProduct = () => {
  // State quản lý dữ liệu
  const [products, setProducts] = useState([]); // Danh sách sản phẩm 
  const [productDetail, setProductDetail] = useState(null); // Chi tiết 1 sản phẩm
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // 1. Hàm lấy danh sách Flash Sale
  // Dùng useCallback để tránh hàm bị tạo lại liên tục gây re-render
  const getAllProduct = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      // Gọi qua Service Static Wrapper
      const data = await ProductService.getAllProducts();
      
      console.log('✅featch data success')
      setProducts(data);
    } catch (err) {
      console.error(err);
      setError(err.message || 'Lỗi tải danh sách sản phẩm');
    } finally {
      setLoading(false);
    }
  }, []);

  // 2. Hàm lấy chi tiết sản phẩm
  const getDetail = useCallback(async (id) => {
    setLoading(true);
    setError(null);
    try {
      const data = await ProductService.getProductDetail(id);
      setProductDetail(data);
    } catch (err) {
      setError(err.message || 'Lỗi tải chi tiết sản phẩm');
    } finally {
      setLoading(false);
    }
  }, []);

  // 3. Hàm tìm kiếm
  const searchProducts = useCallback(async (keyword) => {
    setLoading(true);
    try {
      const data = await ProductService.searchProducts(keyword);
      setProducts(data); // Cập nhật list bằng kết quả tìm kiếm
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, []);

  // Trả về mọi thứ cần thiết cho View
  return {
    products,       // Danh sách (Array)
    productDetail,  // Chi tiết (Object)
    loading,        // Trạng thái tải
    error,          // Lỗi nếu có
    getAllProduct,  // Hàm gọi API lấy list
    getDetail,      // Hàm gọi API lấy chi tiết
    searchProducts  // Hàm tìm kiếm
  };
};