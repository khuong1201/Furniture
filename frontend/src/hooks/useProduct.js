import { useState, useCallback } from 'react';
import ProductService from '@/services/customer/ProductService';

export const useProduct = () => {
  const [products, setProducts] = useState([]);
  const [productDetail, setProductDetail] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  
  const [pagination, setPagination] = useState({
    currentPage: 1,
    lastPage: 1,
    total: 0
  });

  // --- 1. LẤY DANH SÁCH (Có trả về data để xử lý ở UI) ---
  const getProducts = useCallback(async (params = {}) => {
    setLoading(true);
    setError(null);
    try {
      const responseData = await ProductService.getAllProducts(params);
      
      let fetchedData = [];

      if (responseData && Array.isArray(responseData.data)) {
         fetchedData = responseData.data;
         setProducts(fetchedData);
         
         setPagination({
           currentPage: responseData.current_page,
           lastPage: responseData.last_page,
           total: responseData.total
         });
      } else if (Array.isArray(responseData)) {
         fetchedData = responseData;
         setProducts(fetchedData);
      } else {
         setProducts([]);
      }

      // [QUAN TRỌNG] Return data để component cha có thể dùng (ví dụ: nối mảng)
      return fetchedData; 

    } catch (err) {
      console.error(err);
      setError(err.message || 'Failed to fetch products');
      setProducts([]);
      return []; // Return mảng rỗng khi lỗi
    } finally {
      setLoading(false);
    }
  }, []);

  // [FIX] Alias cần nhận params để truyền vào getProducts
  const fetchProducts = useCallback((params) => getProducts(params), [getProducts]);

  // --- 2. CHI TIẾT SẢN PHẨM ---
  const getDetail = useCallback(async (id) => {
    setLoading(true);
    setError(null);
    setProductDetail(null);
    try {
      const data = await ProductService.getProductDetail(id);
      setProductDetail(data);
      return data;
    } catch (err) {
      setError(err.message || 'Failed to fetch product detail');
    } finally {
      setLoading(false);
    }
  }, []);

  const searchProducts = useCallback(async (keyword) => {
    await getProducts({ search: keyword, page: 1 });
  }, [getProducts]);

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