import { useState, useCallback, useEffect } from 'react';
import ProductService from '@/services/productService';

export const useProduct = () => {

  const [products, setProducts] = useState([]);
  const [productDetail, setProductDetail] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // 1. Hàm lấy danh sách Flash Sale
  // Dùng useCallback để tránh hàm bị tạo lại liên tục gây re-render
  const fetchProducts = useCallback(async () => {
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
    setProductDetail(null);
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
  const searchProducts = useCallback(async (keyword, page = 1, perPage = 15) => {
    setLoading(true);
    try {

      const data = await ProductService.searchProducts(keyword, page, perPage);

      setProducts(data); // Cập nhật list bằng kết quả tìm kiếm

    } catch (err) {
      console.error('Search error:', err);
      setError(err.message || 'Lỗi tìm kiếm');
      setProducts([]);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchProducts();
  }, [fetchProducts]);

  return {
    products,       // Danh sách (Array)
    productDetail,  // Chi tiết (Object)
    loading,        // Trạng thái tải
    error,          // Lỗi nếu có
    fetchProducts,  // Hàm gọi API lấy list
    getDetail,      // Hàm gọi API lấy chi tiết
    searchProducts  // Hàm tìm kiếm
  };
};