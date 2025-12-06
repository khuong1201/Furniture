import React, { useState } from 'react';
import { useAuth } from '@/hooks/AuthContext';
import { Link, useNavigate } from 'react-router-dom';
import bellIcon from '@/assets/icons/bell.svg';
import cartIcon from '@/assets/icons/cart.svg';
import accountIcon from '@/assets/icons/account.svg';
import searchIcon from '@/assets/icons/search.svg';

// 1. Import styles từ module
import styles from './Header.module.css';

function Header() {
  
  const { user } = useAuth();
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
    <header className={styles['header-wrapper']}>
      <div className={styles['header-top']}>
    
        <div className={`${styles['container']} ${styles['top-container']}`}>
          <Link to='/customer'>
            <div className={styles['header-logo']}>
              Aterlier
            </div>
          </Link>
          <div className={styles['search-box']}>
            <div className={styles['search-icon-wrapper']}>
              <img src={searchIcon} alt="Search" className={styles['search-icon']} />
            </div>
            <input 
              type="text" 
              placeholder="Search products..." 
              value={keyword}
              onChange={(e) => setKeyword(e.target.value)}
              onKeyDown={handleKeyDown}
            />
          </div>

          <div className={styles['header-actions']}>
            
            <div className={styles['action-item']}>
              <img src={bellIcon} alt="Bell" className={styles['svg-icon']}/>
            </div>

            <div className={styles['action-item']}>
              <Link to="/customer/cart">
                <img src={cartIcon} alt="Cart" className={styles['svg-icon']}/>
              </Link>
            </div>

            <div className={styles['action-item']}>
              <img src={accountIcon} alt="User" className={styles['svg-icon']}/>
              {user ? (
                <span className={styles['auth-text']}>
                  Xin chào, {user.name}
                </span>
              ) : (
                <Link
                  to="/customer/register"
                  className={styles['auth-text']}
                  
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