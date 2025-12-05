  import React, { useState } from 'react';
  import {useAuth} from '@/hooks/AuthContext'
  import { Link, useNavigate } from 'react-router-dom';
  import bellIcon from '@/assets/icons/bell.svg';
  import cartIcon from '@/assets/icons/cart.svg';
  import accountIcon from '@/assets/icons/account.svg';
  import searchIcon from '@/assets/icons/search.svg';
  import './Header.css';

  function Header() {
    
    const{user} = useAuth();
    const navigate = useNavigate();
    const [keyword, setKeyword] = useState('');

    const handleSearch = () => {
      if (keyword.trim()) {
        navigate(`/customer/product?search=${encodeURIComponent(keyword)}`);
      }
    };

    const handleKeyDown = (e) => {
      if (e.key === 'Enter') {
        handleSearch();
      }
    };

    return (
      <header className="header-wrapper">
        <div className="header-top">
          <div className="container top-container">

            <div className="search-box">
              <div className="search-icon-wrapper">
                <img src={searchIcon} alt="Search" className="search-icon" />
              </div>
              <input 
                type="text" 
                placeholder="Search products..." 
                value={keyword}
                onChange={(e) => setKeyword(e.target.value)}
                onKeyDown={handleKeyDown}
              />
            </div>

            <div className="header-actions">
              
              <div className="action-item">
                <img src={bellIcon} alt="Bell" className="svg-icon"/>
              </div>

              <div className="action-item">
                <Link to="/customer/cart">
                  <img src={cartIcon} alt="Cart" className="svg-icon"/>
                </Link>
              </div>

              <div className="action-item user-action">
                <img src={accountIcon} alt="User" className="svg-icon"/>
                {user ? (
                  <span className="auth-text">
                    Xin chào, {user.name}
                  </span>
                ) : (
                  <Link
                    to="/customer/register"
                    className="auth-text"
                    style={{ textDecoration: 'none', color: 'inherit' }}
                  >
                    Log In / Sign up
                  </Link>
                )}
              </div>

            </div>
          </div>
        </div>
      </header>
    );
  }

  export default Header;