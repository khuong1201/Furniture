import React, { useEffect } from 'react';
import './HomePage.css';
import { useProduct } from '@/hooks/useProducts';
import {Link, useNavigate } from 'react-router-dom';
import { AiOutlineLoading3Quarters, AiOutlineWarning } from 'react-icons/ai';

import bedIcon from '@/assets/icons/categories/bed.svg';
import tableIcon from '@/assets/icons/categories/table.svg';
import sofaIcon from '@/assets/icons/categories/sofa.svg';
import chairIcon from '@/assets/icons/categories/chair.svg';
import wardrobesIcon from '@/assets/icons/categories/wardrobes.svg';
import lightIcon from '@/assets/icons/categories/light.svg';
import shelfIcon from '@/assets/icons/categories/shelf.svg';
import outdoorIcon from '@/assets/icons/categories/outdoor.svg';

function HomePage() {

  const { products, loading, error, getAllProduct } = useProduct();

  useEffect(() => {
    getAllProduct();
  }, [getAllProduct]);

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

      {/* --- PHẦN 3: FLASH SALE (DÙNG API TỪ HOOK) --- */}
      <section className="product-section">
        <div className="section-header">
          <h3>Flash Sale <span className="timer">01 : 23 : 20</span></h3>
          <a href="#" className="view-all">View all</a>
        </div>

        {/* 4. Hiển thị dữ liệu khi đã tải xong */}
        {!loading && !error && (
          <div className="product-grid">
            {products.map((item) => (
       
              <div key={item.id} className="product-card">
                <Link to={`/customer/product/${item.uuid || item.id}`}>
                  <div className="product-img">
                    <span className="discount-tag">-50%</span>
                    <img 
                      src={item.image ? `http://localhost:8000/storage/${item.image}` : 'https://placehold.co/300x250?text=No+Image'} 
                      alt={item.name} 
                      onError={(e) => { e.target.src = 'https://placehold.co/300x250?text=Error'; }}
                    />
                  </div>
                </Link>  
                <div className="product-info">
                  
                  <Link to={`/customer/product/${item.uuid || item.id}`} className="product-link">
                    <h4>{item.name}</h4> 
                  </Link>
                  
                  <div className="rating">
                    
                    <span className="rating-star">★</span>
                    
                    <span className='rating-number'>{item.rating || 4.5}</span>
                    
                    
                    {/* 4. Số lượt bán */}
                    <span className="rating-separator">|</span>
                    <span className="rating-sold">
                      {item.sold || 155} sold
                    </span>
                  </div>
                  
                  <div className="price-row">
                    <span className="price">
                      {Number(item.price).toLocaleString('vi-VN')} VND
                    </span>
                    <button className="btn-add">Add to cart</button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}

        {/* Top Trending */}
        <div className="section-header">
          <h3>Top Trending</h3>
          <a href="#" className="view-all">View all</a>
        </div>

        {/* Today's Suggestions */}
        <div className="section-header">
          <h3>Today's Suggestions</h3>
        </div>
      </section>  

    </div>
  );
}

export default HomePage;