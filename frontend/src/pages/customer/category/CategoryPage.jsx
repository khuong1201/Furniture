import React, { useEffect, useState, useCallback, useRef, useMemo } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useProduct } from '@/hooks/useProduct';
import { useCategory } from '@/hooks/useCategory';
import ProductCard from '@/pages/customer/components/ProductCard';
import { AiOutlineLoading3Quarters, AiOutlineWarning, AiOutlineHome, AiOutlineInbox } from 'react-icons/ai';
import './CategoryPage.css';

const CategoryPage = () => {
  const { slug } = useParams();
  
  // --- 1. HOOKS ---
  const { loading: productLoading, error, fetchProducts } = useProduct();
  const { categories, fetchCategories } = useCategory();
  
  // --- 2. STATE ---
  const [productList, setProductList] = useState([]);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const loadingRef = useRef(false);

  // --- 3. LOGIC TÌM DANH MỤC (CHA & CON) ---
  const categoryInfo = useMemo(() => {
    if (!categories || categories.length === 0) return { current: null, parent: null };

    // BƯỚC 1: Tìm trong danh sách Cha (Cấp 1)
    const parentMatch = categories.find(c => c.slug === slug);
    if (parentMatch) {
      return { current: parentMatch, parent: null }; // Nó là cha, không có parent để hiển thị
    }

    // BƯỚC 2: Nếu không thấy, tìm trong danh sách Con của từng Cha (Cấp 2)
    for (const cat of categories) {
      if (cat.children && cat.children.length > 0) {
        const childMatch = cat.children.find(c => c.slug === slug);
        if (childMatch) {
          return { current: childMatch, parent: cat }; // Tìm thấy con, trả về cả cha của nó
        }
      }
    }

    return { current: null, parent: null };
  }, [categories, slug]);

  const { current: currentCategory, parent: parentCategory } = categoryInfo;

  // Lấy danh sách con (chỉ hiện nếu đang đứng ở Cha)
  const subCategories = currentCategory?.children || [];
  
  // Tên hiển thị
  const categoryName = currentCategory ? currentCategory.name : slug.replace(/-/g, ' ');

  // --- 4. EFFECTS ---
  useEffect(() => {
    fetchCategories(); 
  }, []);

  useEffect(() => {
    setProductList([]);
    setPage(1);
    setHasMore(true);
    loadingRef.current = false;
    window.scrollTo(0, 0);
    loadMoreProducts(1, true); 
  }, [slug]);

  const loadMoreProducts = useCallback(async (currentPage, isReset = false) => {
    if (loadingRef.current) return;
    loadingRef.current = true;
    try {
      const newProducts = await fetchProducts({ 
        category_slug: slug, per_page: 12, page: currentPage, is_active: true 
      });

      if (!newProducts || newProducts.length === 0) {
        setHasMore(false);
      } else {
        setProductList(prev => isReset ? newProducts : [...prev, ...newProducts]);
      }
    } catch (err) {
      console.error("Load products failed:", err);
    } finally {
      loadingRef.current = false;
    }
  }, [slug, fetchProducts]);

  // Infinite Scroll
  useEffect(() => {
    const handleScroll = () => {
      if (window.innerHeight + document.documentElement.scrollTop + 300 >= document.documentElement.offsetHeight && !loadingRef.current && hasMore) {
        setPage(prev => prev + 1);
        loadMoreProducts(page + 1, false);
      }
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, [hasMore, page, loadMoreProducts]);

  // --- 5. RENDER ---
  const LoadingSpinner = () => (<div className="loading-container"><AiOutlineLoading3Quarters className="spin" /></div>);

  return (
    <div className="category-page-container">
      
      {/* ================= BREADCRUMB (ĐÃ FIX) ================= */}
      <div className="breadcrumb">
        <Link to="/"><AiOutlineHome /> Home</Link>
        
        {/* Nếu có Parent (tức là đang ở trang Con), hiển thị link về Parent */}
        {parentCategory && (
           <>
             <span>/</span>
             <Link to={`/category/${parentCategory.slug}`}>{parentCategory.name}</Link>
           </>
        )}

        {/* Category hiện tại */}
        <span>/</span>
        <span style={{textTransform: 'capitalize', fontWeight: 'bold', color: '#2D2A29'}}>
            {categoryName}
        </span>
      </div>
      {/* ========================================================= */}


      {/* Header */}
      <div className="category-header">
        <h2 style={{textTransform: 'capitalize'}}>{categoryName} Collection</h2>
      </div>

      {/* DANH MỤC CON (Chỉ hiện nếu subCategories có dữ liệu - Tức là đang ở Cha) */}
      {subCategories.length > 0 && (
        <section className="categories-section" style={{ marginBottom: '40px' }}>
          <div className="category-list">
            {subCategories.map((sub) => (
              <Link to={`/category/${sub.slug}`} key={sub.uuid || sub.id} className="cat-item">
                <div className="cat-icon-box">
                  <img 
                    src={sub.image || 'https://placehold.co/150?text=Icon'} 
                    alt={sub.name} 
                    className="cat-img-svg" 
                    onError={(e) => { e.target.onerror = null; e.target.src = 'https://placehold.co/150?text=Icon'; }}
                  />
                </div>
                <span>{sub.name}</span>
              </Link>
            ))}
          </div>
        </section>
      )}

      {/* Error */}
      {error && <div className="error-box"><AiOutlineWarning style={{ color: '#ff4d4f' }} /><p>{error}</p></div>}

      {/* Product List */}
      <div className="product-grid">
        {productList.map((item, index) => (
            <ProductCard key={`${item.uuid}-${index}`} item={item} variant="default" />
        ))}
      </div>

      {/* Loading & Empty States */}
      {productLoading && <LoadingSpinner />}
      
      {!productLoading && productList.length === 0 && !error && (
        <div className="empty-state">
          <AiOutlineInbox />
          <p>No products found in "{categoryName}".</p>
          <Link to="/">Discover other items</Link>
        </div>
      )}

      {!hasMore && productList.length > 0 && (
        <div style={{ textAlign: 'center', padding: '40px', color: '#999', fontStyle: 'italic' }}>End of collection.</div>
      )}
    </div>
  );
};

export default CategoryPage;