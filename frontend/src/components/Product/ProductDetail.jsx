import React, { useState } from 'react';
import { Star, Minus, Plus, ShoppingCart, MessageCircle, Store, ChevronRight } from 'lucide-react';
import './ProductDetail.css';

const ProductDetail = () => {
  // --- MOCK DATA (Dữ liệu giả lập giống ảnh) ---
  const product = {
    id: 1,
    name: "Modern Velvet Sofa - Premium Quality Living Room Furniture",
    rating: 4.9,
    reviews: 156,
    sold: "1.2k",
    price: 4000000,
    originalPrice: 5000000,
    discount: "-66%",
    description: "Ghế sofa nhung hiện đại, mang lại vẻ sang trọng cho phòng khách của bạn.",
    colors: ["Navy Blue", "Blush Pink", "Charcoal Gray"],
    sizes: ["2 - seater", "3 - seater", "4 - seater"],
    images: [
      "https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=80", // Ảnh chính
      "https://images.unsplash.com/photo-1550226891-ef816aed4a98?auto=format&fit=crop&w=200&q=80",
      "https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?auto=format&fit=crop&w=200&q=80",
      "https://images.unsplash.com/photo-1567016432779-094069958ea5?auto=format&fit=crop&w=200&q=80",
      "https://images.unsplash.com/photo-1484101403633-562f891dc89a?auto=format&fit=crop&w=200&q=80",
    ],
    shop: {
      name: "Atelier Furniture Co.",
      avatar: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=100&q=80",
      status: "Active 2 hours ago"
    }
  };

  // --- STATE QUẢN LÝ ---
  const [activeImage, setActiveImage] = useState(product.images[0]);
  const [selectedColor, setSelectedColor] = useState(product.colors[1]); // Mặc định chọn Blush Pink
  const [selectedSize, setSelectedSize] = useState(product.sizes[0]);
  const [quantity, setQuantity] = useState(1);

  // --- HÀM XỬ LÝ ---
  const handleQuantity = (type) => {
    if (type === 'dec' && quantity > 1) setQuantity(quantity - 1);
    if (type === 'inc') setQuantity(quantity + 1);
  };

  return (
    <div className="pd-wrapper">
      {/* Breadcrumb */}
      <div className="breadcrumb">
        <span>Product</span> <ChevronRight size={14} /> <span>Detail</span>
      </div>

      <div className="pd-container">
        {/* --- CỘT TRÁI: HÌNH ẢNH --- */}
        <div className="pd-gallery">
          <div className="main-image">
            <img src={activeImage} alt="Product Main" />
          </div>
          <div className="thumbnail-list">
            {product.images.map((img, index) => (
              <div 
                key={index} 
                className={`thumb-item ${activeImage === img ? 'active' : ''}`}
                onMouseEnter={() => setActiveImage(img)} // Hover là đổi ảnh
              >
                <img src={img} alt={`Thumb ${index}`} />
              </div>
            ))}
          </div>
        </div>

        {/* --- CỘT PHẢI: THÔNG TIN --- */}
        <div className="pd-info">
          <h1 className="product-title">{product.name}</h1>
          
          <div className="product-meta">
            <span className="rating">
              {product.rating} <Star size={14} fill="#ffc107" color="#ffc107" />
            </span>
            <span className="divider">|</span>
            <span className="reviews">{product.reviews} Ratings</span>
            <span className="divider">|</span>
            <span className="sold">{product.sold} sold</span>
          </div>

          <div className="price-section">
            <span className="current-price">{product.price.toLocaleString()} VND</span>
            <span className="original-price">{product.originalPrice.toLocaleString()} VND</span>
            <span className="discount-badge">{product.discount}</span>
          </div>

          <div className="delivery-info">
            <span className="label">Delivery</span>
            <span className="value map-icon">📍 Enter address to see delivery options</span>
          </div>

          {/* Chọn Màu */}
          <div className="variant-section">
            <span className="label">Color</span>
            <div className="options-row">
              {product.colors.map(color => (
                <button 
                  key={color}
                  className={`option-btn ${selectedColor === color ? 'selected' : ''}`}
                  onClick={() => setSelectedColor(color)}
                >
                  {color}
                </button>
              ))}
            </div>
          </div>

          {/* Chọn Size */}
          <div className="variant-section">
            <span className="label">Size</span>
            <div className="options-row">
              {product.sizes.map(size => (
                <button 
                  key={size}
                  className={`option-btn ${selectedSize === size ? 'selected' : ''}`}
                  onClick={() => setSelectedSize(size)}
                >
                  {size}
                </button>
              ))}
            </div>
          </div>

          {/* Chọn Số lượng */}
          <div className="quantity-section">
            <span className="label">Quantity</span>
            <div className="qty-control">
              <button onClick={() => handleQuantity('dec')}><Minus size={16}/></button>
              <input type="text" value={quantity} readOnly />
              <button onClick={() => handleQuantity('inc')}><Plus size={16}/></button>
            </div>
            <span className="stock-available">4 pieces available</span>
          </div>

          {/* Nút hành động */}
          <div className="action-buttons">
            <button className="btn-add-cart">
              <ShoppingCart size={20} /> Add to Cart
            </button>
            <button className="btn-buy-now">Buy Now</button>
          </div>
        </div>
      </div>

      {/* --- PHẦN SHOP INFO --- */}
      <div className="shop-section">
        <div className="shop-info">
          <img src={product.shop.avatar} alt="Shop Avatar" className="shop-avatar" />
          <div className="shop-text">
            <h4>{product.shop.name}</h4>
            <p>{product.shop.status}</p>
          </div>
        </div>
        <div className="shop-actions">
          <button className="btn-shop-chat"><MessageCircle size={16}/> Chat</button>
          <button className="btn-shop-view"><Store size={16}/> View</button>
        </div>
      </div>
    </div>
  );
};

export default ProductDetail;