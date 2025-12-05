import { useState, useCallback, useEffect } from 'react';
import ProductService from '@/services/customer/ProductService';

export const useProduct = () => {
  const [products, setProducts] = useState([]);
  const [productDetail, setProductDetail] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  
  // (Optional) State lưu pagination để dùng cho UI phân trang
  const [pagination, setPagination] = useState({
    currentPage: 1,
    lastPage: 1,
    total: 0
  });

  // --- 1. LẤY DANH SÁCH (Hỗ trợ Filter/Search/Page) ---
  const getProducts = useCallback(async (params = {}) => {
    setLoading(true);
    setError(null);
    try {
      // Gọi Service
      const responseData = await ProductService.getAllProducts(params);
      
      if (responseData && Array.isArray(responseData.data)) {
         setProducts(responseData.data);
         
         setPagination({
            currentPage: responseData.current_page,
            lastPage: responseData.last_page,
            total: responseData.total
         });
      } else if (Array.isArray(responseData)) {
         setProducts(responseData);
      } else {
         setProducts([]);
      }

      console.log('✅ Fetch products success');
    } catch (err) {
      console.error(err);
      setError(err.message || 'Lỗi tải danh sách sản phẩm');
      setProducts([]);
    } finally {
      setLoading(false);
    }
  }, []);

  // Alias cho code cũ
  const fetchProducts = useCallback(() => getProducts(), [getProducts]);

  // --- 2. CHI TIẾT SẢN PHẨM ---
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

  const searchProducts = useCallback(async (keyword) => {
    await getProducts({ search: keyword, page: 1 });
  }, [getProducts]);

  // Tự động load khi component mount
  useEffect(() => {
    fetchProducts();
  }, [fetchProducts]);

  return {
    products,
    productDetail,
    loading,
    error,
    pagination,
    getProducts, 
    fetchProducts,
    getDetail,
    searchProducts
  };
};