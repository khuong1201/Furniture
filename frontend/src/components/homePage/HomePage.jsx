import React, { useEffect } from 'react';
import './HomePage.css';
import { useProduct } from '../../hooks/useProducts';
import {Link, useNavigate } from 'react-router-dom';

import bedIcon from '../../assets/icons/categories/bed.svg';
import tableIcon from '../../assets/icons/categories/table.svg';
import sofaIcon from '../../assets/icons/categories/sofa.svg';
import chairIcon from '../../assets/icons/categories/chair.svg';
import wardrobesIcon from '../../assets/icons/categories/wardrobes.svg';
import lightIcon from '../../assets/icons/categories/light.svg';
import shelfIcon from '../../assets/icons/categories/shelf.svg';
import outdoorIcon from '../../assets/icons/categories/outdoor.svg';

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
        
        {loading && <div className="loading-state">Đang tải sản phẩm...</div>}
        {error && <div className="error-state">{error}</div>}

        {/* 4. Hiển thị dữ liệu khi đã tải xong */}
        {!loading && !error && (
          <div className="product-grid">
            {products.map((item) => (
       
              <div key={item.id} className="product-card">
                <Link to={`/product/${item.uuid || item.id}`}>
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
                  
                  <Link to={`/product/${item.uuid || item.id}`} className="product-link">
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
      </section>  

    </div>
  );
}

export default HomePage;