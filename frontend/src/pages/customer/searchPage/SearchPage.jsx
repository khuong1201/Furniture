import React, { useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import { useProduct } from '@/hooks/useProduct';
import ProductCard from '@/pages/customer/components/ProductCard';
import { AiOutlineLoading3Quarters } from 'react-icons/ai';

// Import CSS Module
import styles from './SearchPage.module.css';

const SearchPage = () => {
  const [searchParams] = useSearchParams();
  
  // 1. Lấy keyword từ URL
  const keyword = searchParams.get('search') || ''; 

  // 2. Sử dụng Hook (đã fix ở bước trước)
  const { products, loading, error, searchProducts } = useProduct();

  // 3. Gọi API khi keyword thay đổi
  useEffect(() => {
    if (keyword.trim()) {
      searchProducts(keyword);
    }
  }, [keyword, searchProducts]);

  return (
    <div className={styles.container}>
      <h2 className={styles.title}>
        Kết quả tìm kiếm cho: <span>"{keyword}"</span>
      </h2>
      
      {/* Hiển thị lỗi nếu có */}
      {error && <div className={styles.error}>⚠️ Lỗi: {error}</div>}

      {loading ? (
         <div className={styles.loading}>
            <AiOutlineLoading3Quarters className={styles.spin} size={40} /> 
            <p>Đang tìm sản phẩm...</p>
         </div>
      ) : (
        <>
          {products && products.length > 0 ? (
            <div className={styles.grid}>
              {products.map(item => (
                // ⚠️ QUAN TRỌNG: Dùng uuid thay vì id
                <ProductCard key={item.uuid || item.id} item={item} />
              ))}
            </div>
          ) : (
            // Trạng thái không tìm thấy
            <div className={styles.noResult}>
                <div className={styles.icon}>🔍</div>
                <h3>Không tìm thấy sản phẩm nào phù hợp.</h3>
                <p>Hãy thử tìm kiếm bằng từ khóa khác (ví dụ: "sofa", "bàn", "đèn").</p>
            </div>
          )}
        </>
      )}
    </div>
  );
};

export default SearchPage;