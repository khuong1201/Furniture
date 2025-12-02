import React, { useEffect } from 'react';
import './HomePage.css';
import { useProduct } from '../../hooks/useProducts';

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
        
        {/* 3. Kiểm tra trạng thái tải trang */}
        {loading && <div className="loading-state">Đang tải sản phẩm...</div>}
        {error && <div className="error-state">{error}</div>}

        {/* 4. Hiển thị dữ liệu khi đã tải xong */}
        {!loading && !error && (
          <div className="product-grid">
            {products.map((item) => (
              // Lưu ý: Dùng item.id làm key thay vì item index
              <div key={item.id} className="product-card">
                <div className="product-img">
                  <span className="discount-tag">-50%</span>
                  
                  {/* 5. Xử lý ảnh động từ API */}
                  {/* Nếu API trả về link full thì dùng item.image, nếu chỉ tên file thì nối chuỗi */}
                  <img 
                    src={item.image ? `http://localhost:8000/storage/${item.image}` : 'https://placehold.co/300x250?text=No+Image'} 
                    alt={item.name} 
                    onError={(e) => { e.target.src = 'https://placehold.co/300x250?text=Error'; }} // Fallback nếu ảnh lỗi
                  />
                </div>
                
                <div className="product-info">
                  {/* 6. Hiển thị Tên sản phẩm */}
                  <h4>{item.name}</h4> 
                  
                  <div className="rating">★ 4.5 | 155 sold</div>
                  
                  <div className="price-row">
                    {/* 7. Format giá tiền VND */}
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