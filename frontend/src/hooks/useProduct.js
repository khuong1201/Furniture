import { useState, useCallback, useEffect } from 'react';
import ProductService from '@/services/customer/productService';

export const useProduct = () => {

  const [products, setProducts] = useState([]);
  const [productDetail, setProductDetail] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // 1. Hàm lấy danh sách sản phẩm với params
  const getProducts = useCallback(async (params = {}) => {
    setLoading(true);
    setError(null);
    try {
      const data = await ProductService.getAllProducts(params);
      console.log('✅ Fetch products success');
      setProducts(data);
    } catch (err) {
      console.error(err);
      setError(err.message || 'Lỗi tải danh sách sản phẩm');
    } finally {
      setLoading(false);
    }
  }, []);

  // Alias for backward compatibility
  const fetchProducts = useCallback(() => getProducts(), [getProducts]);

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
      setProducts(data);
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
    products,
    productDetail,
    loading,
    error,
    fetchProducts,
    getProducts,
    getDetail,
    searchProducts
  };
};