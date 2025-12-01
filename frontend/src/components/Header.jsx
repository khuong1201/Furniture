import React from 'react';
// Giữ nguyên các import SVG của bạn
import bellIcon from '../assets/icons/bell.svg';
import cartIcon from '../assets/icons/cart.svg';
import accountIcon from '../assets/icons/account.svg';
import searchIcon from '../assets/icons/search.svg';
import './Header.css';

function Header() {
  return (
    <header className="header-wrapper">
      <div className="header-top">
        <div className="container top-container">

          <div className="search-box">
            <div className="search-icon-wrapper">
              <img src={searchIcon} alt="Search" className="search-icon" />
            </div>
            <input type="text" placeholder="Search" />
          </div>

          <div className="header-actions">
            
            <div className="action-item">
              <img src={bellIcon} alt="Bell" className="svg-icon"/>
            </div>

            <div className="action-item">
              <img src={cartIcon} alt="Cart" className="svg-icon"/>
            </div>

            <div className="action-item user-action">
              <img src={accountIcon} alt="User" className="svg-icon"/>
              <span className="auth-text">Log In/ Sign up</span>
            </div>

          </div>
        </div>
      </div>
    </header>
  );
}

export default Header;