import React from 'react';
import './ProductCard.css';
import { Link } from 'react-router-dom';
import fireSale from '@/assets/icons/flashSale.svg';
import star from '@/assets/icons/star.svg';
import top from '@/assets/icons/top.svg';


const ProductCard = ({ item, variant = "default" }) => {

  const price = item.price_formatted;
  const original_price = item.original_price_formatted;
  const rating = Number(item.rating_avg || 0).toFixed(1);
  const sold = item.sold_count || 0;

  return (
    <div className={`product-card ${variant}`}> 
      <Link to={`/customer/product/${item.uuid || item.id}`}>
        <div className="product-img">
          {variant === "top" && 
            <img src={top} alt="top" className="tag-top" />
          }
          {variant === "flash" && (
            <>
              <img src={fireSale} alt="Fire Sale" className="fire-icon" />
              <span className="discount-tag">
                <span className="discount-text">-{item.flash_sale.discount_percent}%</span>
              </span>
            </> 
          )}
          <img 
             src={item.image ? `http://localhost:8000/storage/${item.image}` : 'https://placehold.co/300x250?text=No+Image'} 
             alt={item.name} 
             onError={(e) => { e.target.src = 'https://placehold.co/300x250?text=Error'; }}
          />
        </div>
      </Link>  
      <div className="product-info-cart">
        <Link to={`/customer/product/${item.uuid || item.id}`} className="product-link">
          <h4>{item.name}</h4> 
        </Link>
        
        <div className="rating-info">
          <img src={star} alt="star" className="rating-star" />
          <span className='rating-number'>{rating || 4.5}</span>
          <span className="rating-separator">|</span>
          <span className="rating-sold">{sold || 155} sold</span>
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