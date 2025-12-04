import React, { useState } from 'react';
import { useAuth } from '@/hooks/AuthContext';
import { Link, useNavigate } from 'react-router-dom';
import {
  Bell, ShoppingCart, User, Search, Package,
  Settings, LogOut, ChevronDown, Heart
} from 'lucide-react';
import './Header.css';

function Header() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [keyword, setKeyword] = useState('');
  const [showDropdown, setShowDropdown] = useState(false);

  const handleSearch = () => {
    if (keyword.trim()) {
      navigate(`/customer/product?search=${encodeURIComponent(keyword)}`);
    }
  };

  const handleKeyDown = (e) => {
    if (e.key === 'Enter') handleSearch();
  };

  const handleLogout = async () => {
    await logout();
    setShowDropdown(false);
    navigate('/customer/login');
  };

  return (
    <header className="header-wrapper">
      <div className="header-top">
        <div className="container top-container">
          {/* Logo */}
          <Link to="/customer" className="header-logo">
            <span className="logo-text">✨ Jewelry</span>
          </Link>

          {/* Search */}
          <div className="search-box">
            <Search size={18} className="search-icon" />
            <input
              type="text"
              placeholder="Tìm kiếm sản phẩm..."
              value={keyword}
              onChange={(e) => setKeyword(e.target.value)}
              onKeyDown={handleKeyDown}
            />
            <button onClick={handleSearch} className="search-btn">Tìm kiếm</button>
          </div>

          {/* Actions */}
          <div className="header-actions">
            <button className="action-btn notification-btn">
              <Bell size={20} />
              <span className="badge">2</span>
            </button>

            <Link to="/customer/cart" className="action-btn cart-btn">
              <ShoppingCart size={20} />
            </Link>

            {user ? (
              <div className="user-dropdown">
                <button
                  className="user-btn"
                  onClick={() => setShowDropdown(!showDropdown)}
                >
                  <div className="user-avatar">
                    {user.name?.charAt(0).toUpperCase() || 'U'}
                  </div>
                  <span className="user-name">{user.name?.split(' ')[0]}</span>
                  <ChevronDown size={16} className={showDropdown ? 'rotate' : ''} />
                </button>

                {showDropdown && (
                  <>
                    <div className="dropdown-overlay" onClick={() => setShowDropdown(false)} />
                    <div className="dropdown-menu">
                      <div className="dropdown-header">
                        <div className="dropdown-avatar">
                          {user.name?.charAt(0).toUpperCase()}
                        </div>
                        <div className="dropdown-info">
                          <strong>{user.name}</strong>
                          <span>{user.email}</span>
                        </div>
                      </div>
                      <div className="dropdown-divider"></div>
                      <Link to="/customer/profile" className="dropdown-item" onClick={() => setShowDropdown(false)}>
                        <User size={18} />
                        Tài khoản của tôi
                      </Link>
                      <Link to="/customer/orders" className="dropdown-item" onClick={() => setShowDropdown(false)}>
                        <Package size={18} />
                        Đơn hàng
                      </Link>
                      <div className="dropdown-divider"></div>
                      <button className="dropdown-item logout" onClick={handleLogout}>
                        <LogOut size={18} />
                        Đăng xuất
                      </button>
                    </div>
                  </>
                )}
              </div>
            ) : (
              <div className="auth-actions">
                <Link to="/customer/login" className="auth-link login">Đăng nhập</Link>
                <Link to="/customer/register" className="auth-link register">Đăng ký</Link>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Navigation */}
      <nav className="header-nav">
        <div className="container">
          <ul className="nav-list">
            <li><Link to="/customer">Trang chủ</Link></li>
            <li><Link to="/customer/product">Sản phẩm</Link></li>
            <li><Link to="/customer/product?category=nhan">Nhẫn</Link></li>
            <li><Link to="/customer/product?category=day-chuyen">Dây chuyền</Link></li>
            <li><Link to="/customer/product?category=bong-tai">Bông tai</Link></li>
            <li><Link to="/customer/product?category=vong-tay">Vòng tay</Link></li>
          </ul>
        </div>
      </nav>
    </header>
  );
}

export default Header;