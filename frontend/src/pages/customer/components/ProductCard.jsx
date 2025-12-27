import React from 'react';
import './ProductCard.css';
import { Link } from 'react-router-dom';
import fireSale from '@/assets/icons/flashSale.svg';
import star from '@/assets/icons/star.svg';
import top from '@/assets/icons/top.svg';

const ProductCard = ({ item, variant = "default" }) => {

  const price = item.price_formatted;
  const original_price = item.original_price_formatted;
  
  // Rating: Nếu không có thì mặc định 0, fix cứng 1 số thập phân
  const rating = Number(item.rating_avg || 0).toFixed(1); 

  // SOLD: Sử dụng toán tử ?? (Nullish coalescing) để chấp nhận số 0 là giá trị hợp lệ
  // Nếu item.sold_count là null hoặc undefined thì mới lấy 0
  const sold = item.sold_count ?? 0;

  // Hàm format số lượng bán cho đẹp (ví dụ: 1.2k) nếu cần, ở đây mình để hiển thị số nguyên
  const formatSold = (num) => {
    if (num >= 1000) {
      return (num / 1000).toFixed(1) + 'k';
    }
    return num;
  };

  const getProductImage = () => {
    if (item.images && item.images.length > 0) {
      // Sửa nhẹ: so sánh lỏng (==) hoặc ép kiểu để tránh lỗi '1' khác 1
      const primaryImg = item.images.find(img => img.is_primary == 1 || img.is_primary === true);
      return primaryImg ? primaryImg.url : item.images[0].url;
    }

    if (item.image) {
      if (item.image.startsWith('http')) return item.image;
      return `http://localhost:8000/storage/${item.image}`;
    }
    return 'https://placehold.co/300x250?text=No+Image';
  };

  const displayImage = getProductImage();
  
  return (
    <div className={`product-card ${variant}`}> 
      <Link to={`/product/${item.uuid || item.id}`}>
        <div className="product-img">
          {variant === "top" && 
            <img src={top} alt="top" className="tag-top" />
          }
          {variant === "flash" && (
            <>
              <img src={fireSale} alt="Fire Sale" className="fire-icon" />
              {item.flash_sale?.discount_percent != null && (
                <span className="discount-tag">
                  <span className="discount-text">-{item.flash_sale.discount_percent}%</span>
                </span>
              )}
            </>
          )}
          <img 
              src={displayImage} 
              alt={item.name} 
              onError={(e) => { 
                e.target.onerror = null;
                e.target.src = 'https://placehold.co/300x250?text=Error'; 
              }}
          />
        </div>
      </Link>  
      <div className="product-info-cart">
        <Link to={`/product/${item.uuid || item.id}`} className="product-link">
          <h4>{item.name}</h4> 
        </Link>
        
        <div className="rating-info">
          <img src={star} alt="star" className="rating-star" />
          <span className='rating-number'>{rating}</span>
          <span className="rating-separator">|</span>
          {/* CẬP NHẬT Ở ĐÂY: Bỏ số 155 fix cứng đi */}
          <span className="rating-sold">{formatSold(sold)} sold</span>
        </div>
        
        <div className="price-row">
          <div className='price-info'>
            <span className="price-curent">
              {price}
            </span>

            {variant === "flash" &&
            <span className="price-original">
              {original_price}
            </span>
            }
          </div>

          <button className="btn-add-cart1">Add to cart</button>
        </div>
      </div>
    </div>
  );
};

export default ProductCard;