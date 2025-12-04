import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ShoppingCart, Heart, Star, Eye } from 'lucide-react';
import { useCart } from '@/hooks/useCart';
import './ProductCard.css';

const ProductCard = ({ item }) => {
  const navigate = useNavigate();
  const { addToCart, loading } = useCart();
  const [isWishlisted, setIsWishlisted] = useState(false);
  const [showActions, setShowActions] = useState(false);

  const formatPrice = (price) => {
    return parseInt(price || 0).toLocaleString('vi-VN') + ' đ';
  };

  const handleAddToCart = async (e) => {
    e.preventDefault();
    e.stopPropagation();

    const token = localStorage.getItem('access_token');
    if (!token) {
      alert('Vui lòng đăng nhập để thêm vào giỏ hàng!');
      return navigate('/customer/login');
    }

    // If product has variants, go to detail page
    if (item.variants?.length > 0) {
      return navigate(`/customer/product/${item.uuid || item.id}`);
    }

    try {
      await addToCart(item.uuid || item.id, 1);
      alert('Đã thêm vào giỏ hàng!');
    } catch (err) {
      alert(err.message || 'Không thể thêm vào giỏ hàng');
    }
  };

  const toggleWishlist = (e) => {
    e.preventDefault();
    e.stopPropagation();
    setIsWishlisted(!isWishlisted);
  };

  const discount = item.original_price ?
    Math.round((1 - item.price / item.original_price) * 100) :
    (item.discount || 0);

  return (
    <div
      className="product-card"
      onMouseEnter={() => setShowActions(true)}
      onMouseLeave={() => setShowActions(false)}
    >
      <Link to={`/customer/product/${item.uuid || item.id}`} className="card-link">
        <div className="product-img">
          {discount > 0 && <span className="discount-tag">-{discount}%</span>}
          <img
            src={item.thumbnail || item.image ?
              `http://localhost:8000/storage/${item.thumbnail || item.image}` :
              'https://placehold.co/300x250?text=No+Image'
            }
            alt={item.name}
            onError={(e) => { e.target.src = 'https://placehold.co/300x250?text=Error'; }}
          />

          {/* Quick Actions */}
          <div className={`quick-actions ${showActions ? 'show' : ''}`}>
            <button
              className={`action-btn wishlist ${isWishlisted ? 'active' : ''}`}
              onClick={toggleWishlist}
            >
              <Heart size={18} fill={isWishlisted ? '#ef4444' : 'none'} />
            </button>
            <button className="action-btn view">
              <Eye size={18} />
            </button>
          </div>
        </div>
      </Link>

      <div className="product-info">
        <Link to={`/customer/product/${item.uuid || item.id}`} className="product-link">
          <h4 className="product-name">{item.name}</h4>
        </Link>

        <div className="rating">
          <Star size={14} fill="#fbbf24" color="#fbbf24" />
          <span className="rating-number">{item.rating || 4.5}</span>
          <span className="rating-separator">|</span>
          <span className="rating-sold">{item.sold_count || item.sold || 0} đã bán</span>
        </div>

        <div className="price-row">
          <div className="prices">
            <span className="price">{formatPrice(item.price)}</span>
            {item.original_price && (
              <span className="original-price">{formatPrice(item.original_price)}</span>
            )}
          </div>
          <button
            className="btn-add"
            onClick={handleAddToCart}
            disabled={loading}
          >
            <ShoppingCart size={16} />
          </button>
        </div>
      </div>
    </div>
  );
};

export default ProductCard;