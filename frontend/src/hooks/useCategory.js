import { useState, useCallback } from 'react';
import CategoryService from '@/services/customer/CategoryService';

export const useCategory = () => {
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetchCategories = useCallback(async (params = {}) => {
    setLoading(true);
    setError(null);
    try {
      const defaultParams = { tree: true, ...params };
      
      const data = await CategoryService.getCategories(defaultParams);
      
      if (Array.isArray(data)) {
        setCategories(data);
      } else {
        setCategories([]);
      }
    } catch (err) {
      console.error(err);
      setError(err.message || 'Không thể tải danh mục sản phẩm');
      setCategories([]);
    } finally {
      setLoading(false);
    }
  }, []);

  return {
    categories,
    loading,
    error,
    fetchCategories
  };
};