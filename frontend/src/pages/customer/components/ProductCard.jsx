import React from 'react';
import { Link } from 'react-router-dom';

const ProductCard = ({ item }) => {
  return (
    <div className="product-card">
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
          <span className="rating-separator">|</span>
          <span className="rating-sold">{item.sold || 155} sold</span>
        </div>
        
        <div className="price-row">
          <span className="price">
            {Number(item.price).toLocaleString('vi-VN')} VND
          </span>
          <button className="btn-add">Add to cart</button>
        </div>
      </div>
    </div>
  );
};

export default ProductCard;