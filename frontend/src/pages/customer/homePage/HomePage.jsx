import React, { useEffect } from 'react';
import './HomePage.css';
import ProductCard from '@/pages/customer/components/ProductCard.jsx'
import { useProduct } from '@/hooks/useProduct';
import {Link, useNavigate} from 'react-router-dom';
import { AiOutlineLoading3Quarters, AiOutlineWarning } from 'react-icons/ai';

import bedIcon from '@/assets/icons/categories/bed.svg';
import tableIcon from '@/assets/icons/categories/table.svg';
import sofaIcon from '@/assets/icons/categories/sofa.svg';
import chairIcon from '@/assets/icons/categories/chair.svg';
import wardrobesIcon from '@/assets/icons/categories/wardrobes.svg';
import lightIcon from '@/assets/icons/categories/light.svg';
import shelfIcon from '@/assets/icons/categories/shelf.svg';
import outdoorIcon from '@/assets/icons/categories/outdoor.svg';
import trending from '@/assets/icons/trending.svg';

function HomePage() {

  const { products, loading, error, fetchProducts } = useProduct();

  const { 
    products: flashProducts, 
    loading: flashLoading, 
    getProducts: fetchFlashSale 
  } = useProduct();

  useEffect(() => {
    fetchProducts();
    fetchFlashSale({ is_flash_sale: true, per_page: 8 });
  }, [fetchProducts, fetchFlashSale]);

  const categories = [
    { name: "Bed", img: bedIcon },
    { name: "Table", img: tableIcon },
    { name: "Sofa", img: sofaIcon },
    { name: "Chair", img: chairIcon },
    { name: "Wardrobes", img: wardrobesIcon },
    { name: "Light", img: lightIcon },
    { name: "Shelf", img: shelfIcon },
    { name: "Outdoor", img: outdoorIcon },
  ];
  
  return (
    <div className="home-container">

      {loading && (
        <div className="loading-state">
          <AiOutlineLoading3Quarters className="loading-icon" />
          <span>Đang tải sản phẩm...</span>
        </div>
      )}

      {error && (
        <div className="error-state">
          <AiOutlineWarning className="error-icon" />
          <span>{error}</span>
        </div>
      )}
      
      {/* --- PHẦN 2: CATEGORIES --- */}
      <section className="categories-section">
        <h3>Categories</h3>
        <div className="category-list">
          {categories.map((cat, index) => (
            <div key={index} className="cat-item">
              <div className="cat-icon-box">
                {/* 3. DÙNG THẺ IMG THAY VÌ COMPONENT */}
                <img src={cat.img} alt={cat.name} className="cat-img-svg" />
              </div>
              <span>{cat.name}</span>
            </div>
          ))}
        </div>
      </section>

      {/* --- PHẦN 3: FLASH SALE  --- */}
      <section className="pd-sale-section">
        <div className="flash-header">
          <h3>Flash Sale 
            {/* <span className="timer">01 : 23 : 20</span> */}
          </h3>
          <a href="#" className="view-all">View all</a>
        </div>


        <div className="product-horizontal">
          {flashProducts.map((item) => (
            <ProductCard 
              key={item.uuid || item.id}  
              item={item}
              variant="flash"
            />
          ))}
        </div>  
      </section>

      {/* Top Trending */} 
      <section className='pd-trending-section'> 
        <div className="trending-header"> 
          <h3>Top Trending
            <img src={trending} alt='trending' className="trending-icon" />
          </h3> 
          <a href="#" className="view-all">
            View all
          </a> 
        </div> 

        <div className="product-horizontal">
          {products.map((item) => (
            <ProductCard 
              key={item.uuid || item.id}  
              item={item}
              variant="top"
            />
          ))}
        </div>  
      </section>
      
        {/* Today's Suggestions */}
      <section className='pd-suggestion-section'>
        <div className="suggestion-header">
          <h3>Today's Suggestions</h3> 
        </div>

        <div className="product-grid">
          {products.map((item) => (
            <ProductCard 
              key={item.uuid || item.id}  
              item={item}
              variant="default"
            />
          ))}
        </div>
      </section>
    </div>
  );
}

export default HomePage;