import React, { useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import { useProduct } from '@/hooks/useProduct';
import ProductCard from '@/pages/customer/components/ProductCard';
import { AiOutlineLoading3Quarters } from 'react-icons/ai';

const SearchPage = () => {
  const [searchParams] = useSearchParams();
  
  // 1. Lấy keyword từ URL (khớp với bên Header là 'search')
  const keyword = searchParams.get('search') || ''; 

  // 2. Sử dụng Hook useProduct để quản lý state và gọi API
  const { products, loading, error, searchProducts } = useProduct();

  // 3. Khi keyword thay đổi -> Gọi API search server-side
  useEffect(() => {
    if (keyword) {
      searchProducts(keyword);
    }
  }, [keyword, searchProducts]);

  return (
    <div className="search-page-container" style={{ padding: '20px' }}>
      <h2>Kết quả tìm kiếm cho: "{keyword}"</h2>
      
      {/* Hiển thị lỗi nếu có */}
      {error && <div className="error-message" style={{color: 'red'}}>Lỗi: {error}</div>}

      {loading ? (
         <div className="loading" style={{textAlign: 'center', padding: '50px'}}>
            <AiOutlineLoading3Quarters className="spin" size={30} /> 
            <p>Đang tìm sản phẩm...</p>
         </div>
      ) : (
        <>
          {products && products.length > 0 ? (
            <div className="product-grid">
              {products.map(item => (
                <ProductCard key={item.id} item={item} />
              ))}
            </div>
          ) : (
            <div className="no-result" style={{textAlign: 'center', marginTop: '50px'}}>
                <h3>Không tìm thấy sản phẩm nào phù hợp.</h3>
                <p>Thử tìm kiếm bằng từ khóa khác (ví dụ: "sofa", "bàn", "đèn").</p>
            </div>
          )}
        </>
      )}
    </div>
  );
};

export default SearchPage;