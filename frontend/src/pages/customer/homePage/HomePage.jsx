import React, { useEffect, useState, useCallback, useRef } from 'react';
import './HomePage.css';
import ProductCard from '@/pages/customer/components/ProductCard.jsx';
import { useProduct } from '@/hooks/useProduct';
import { useCategory } from '@/hooks/useCategory';
import { Link } from 'react-router-dom'; 
import { AiOutlineLoading3Quarters, AiOutlineWarning } from 'react-icons/ai';

import trendingIcon from '@/assets/icons/trending.svg';

function HomePage() {
  const { 
    categories, 
    loading: categoriesLoading, 
    fetchCategories 
  } = useCategory();

  const { 
    products: flashProducts, 
    loading: flashLoading, 
    getProducts: fetchFlashSale 
  } = useProduct();

  const {
    products: trendingProducts,
    loading: trendingLoading,
    getProducts: fetchTrending
  } = useProduct();

  const { 
    loading: suggestionLoading, 
    fetchProducts: fetchSuggestionsRaw,
    error: suggestionError
  } = useProduct();

  const [suggestionList, setSuggestionList] = useState([]);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);

  const loadingRef = useRef(false);

  useEffect(() => {
    fetchCategories();
    fetchFlashSale({ is_flash_sale: true, per_page: 20 });
    fetchTrending({ sort_by: 'best_selling', per_page: 20 });
    
    loadSuggestions(1);
  }, []); 

  const loadSuggestions = useCallback(async (currentPage) => {
    if (loadingRef.current) return;
    loadingRef.current = true;

    try {
      const newProducts = await fetchSuggestionsRaw({ 
        sort_by: 'view_count', 
        per_page: 12, 
        page: currentPage 
      });

      if (!newProducts || newProducts.length === 0) {
        setHasMore(false);
      } else {
        setSuggestionList(prev => [...prev, ...newProducts]);
      }
    } catch (err) {
      console.error("Load more suggestions failed:", err);
    } finally {
      loadingRef.current = false;
    }
  }, [fetchSuggestionsRaw]);

  useEffect(() => {
    const handleScroll = () => {
      if (
        window.innerHeight + document.documentElement.scrollTop + 300 >= document.documentElement.offsetHeight &&
        !loadingRef.current &&
        hasMore &&
        !suggestionLoading
      ) {
        const nextPage = page + 1;
        setPage(nextPage);
        loadSuggestions(nextPage);
      }
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, [hasMore, suggestionLoading, page, loadSuggestions]);

  const SectionLoading = () => (
    <div style={{ width: '100%', display: 'flex', justifyContent: 'center', padding: '20px' }}>
      <AiOutlineLoading3Quarters className="loading-icon spin" />
    </div>
  );

  return (
    <div className="home-container">
      
      {/* Hiển thị lỗi Global nếu có */}
      {suggestionError && (
        <div className="error-state">
          <AiOutlineWarning className="error-icon" />
          <span>{suggestionError}</span>
        </div>
      )}
      
      {/* --- PHẦN 1: CATEGORIES --- */}
      <section className="categories-section">
        {categoriesLoading ? <SectionLoading /> : (
          <div className="category-list">
            {categories.map((cat) => (
              <Link to={`/category/${cat.slug}`} key={cat.uuid || cat.id} className="cat-item">
                <div className="cat-icon-box">
                  <img 
                    src={cat.image || 'https://placehold.co/150?text=No+Img'} 
                    alt={cat.name} 
                    className="cat-img-svg" 
                    onError={(e) => { e.target.onerror = null; e.target.src = 'https://placehold.co/150?text=Icon'; }}
                  />
                </div>
                <span>{cat.name}</span>
              </Link>
            ))}
            {!categoriesLoading && categories.length === 0 && (
              <div style={{textAlign: 'center', width: '100%', color: '#999'}}>No categories found.</div>
            )}
          </div>
        )}
      </section>

      {/* --- PHẦN 2: FLASH SALE (Horizontal Scroll) --- */}
      <section className="pd-sale-section">
        <div className="flash-header">
          <h3>Flash Sale</h3>
          <Link to="/flash-sale" className="view-all">View all</Link>
        </div>
        {flashLoading ? <SectionLoading /> : (
          // Thêm style overflowX để lướt ngang
          <div className="product-horizontal" style={{ overflowX: 'auto', whiteSpace: 'nowrap', paddingBottom: '10px' }}>
            {flashProducts.map((item) => (
              <ProductCard key={item.uuid || item.id} item={item} variant="flash" />
            ))}
          </div>  
        )}
      </section>

      {/* --- PHẦN 3: TOP TRENDING (Horizontal Scroll) --- */} 
      <section className='pd-trending-section'> 
        <div className="trending-header"> 
          <h3>Top Trending <img src={trendingIcon} alt='trending' className="trending-icon" /></h3> 
          <Link to="/trending" className="view-all">View all</Link> 
        </div> 
        {trendingLoading ? <SectionLoading /> : (
          <div className="product-horizontal" style={{ overflowX: 'auto', whiteSpace: 'nowrap', paddingBottom: '10px' }}>
            {trendingProducts.map((item) => (
              <ProductCard key={item.uuid || item.id} item={item} variant="top" />
            ))}
          </div>  
        )}
      </section>
      
      {/* --- PHẦN 4: SUGGESTIONS (Vertical Infinite Scroll) --- */}
      <section className='pd-suggestion-section'>
        <div className="suggestion-header">
          <h3>Today's Suggestions</h3> 
        </div>

        <div className="product-grid">
          {suggestionList.map((item, index) => (
            // Dùng index làm key dự phòng để tránh lỗi duplicate key khi React render nhanh
            <ProductCard key={`${item.uuid}-${index}`} item={item} variant="default" />
          ))}
        </div>

        {/* Loading Spinner khi đang tải trang tiếp theo */}
        {suggestionLoading && <SectionLoading />}
        
        {/* Thông báo hết dữ liệu */}
        {!hasMore && suggestionList.length > 0 && (
          <div style={{ textAlign: 'center', padding: '30px', color: '#888', fontStyle: 'italic' }}>
            You have reached the end of the list.
          </div>
        )}
      </section>
    </div>
  );
}

export default HomePage;