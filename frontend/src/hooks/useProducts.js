import { useState, useEffect } from 'react';
import { getFlashSaleProducts } from '../services/productService';

const useProducts = () => {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const loadData = async () => {
      try {
        setLoading(true);
        // Gọi hàm từ Service, code rất gọn
        const data = await getFlashSaleProducts(); 
        setProducts(data);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    loadData();
  }, []);

  return { products, loading, error };
};

export default useProducts;