import React from 'react';
import './HomePage.css';
import bedIcon from '../assets/icons/categories/bed.svg';
import tableIcon from '../assets/icons/categories/table.svg';
import sofaIcon from '../assets/icons/categories/sofa.svg';
import chairIcon from '../assets/icons/categories/chair.svg';
import wardrobesIcon from '../assets/icons/categories/wardrobes.svg';
import lightIcon from '../assets/icons/categories/light.svg';
import shelfIcon from '../assets/icons/categories/shelf.svg';
import outdoorIcon from '../assets/icons/categories/outdoor.svg';

function HomePage() {

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

  // Data giả lập cho Sản phẩm
  const products = [1, 2, 3, 4, 5, 6]; // Tạo mảng giả để lặp 6 sản phẩm

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

      {/* --- PHẦN 3: FLASH SALE (PRODUCT LIST) --- */}
      <section className="product-section">
        <div className="section-header">
          <h3>Flash Sale <span className="timer">01 : 23 : 20</span></h3>
          <a href="#" className="view-all">View all</a>
        </div>
        
        <div className="product-grid">
          {products.map((item) => (
            <div key={item} className="product-card">
              <div className="product-img">
                <span className="discount-tag">-50%</span>
                <img src="https://placehold.co/300x250/EEE/31343C?text=Sofa" alt="Product" />
              </div>
              <div className="product-info">
                <h4>Haven Sofa</h4>
                <div className="rating">★ 4.7 | 155 sold</div>
                <div className="price-row">
                  <span className="price">4.000.000 VND</span>
                  <button className="btn-add">Add to cart</button>
                </div>
              </div>
            </div>
          ))}
        </div>
      </section>

    </div>
  );
}

export default HomePage;