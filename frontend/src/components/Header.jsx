import React, { useState } from 'react'; // Thêm useState nếu muốn làm click
import { FaSearch, FaShoppingCart, FaUser, FaBars, FaChevronDown, FaChevronRight } from 'react-icons/fa';
import { FaCouch, FaTable, FaBox, FaBed, FaLightbulb, FaTree } from 'react-icons/fa';
import { BiSolidCabinet } from "react-icons/bi"; // Ví dụ thêm icon tủ
import './Header.css';
const categories = [
  { name: "Seating", icon: <FaCouch /> },
  { name: "Tables", icon: <FaTable /> },
  { name: "Storage", icon: <BiSolidCabinet /> },
  { name: "Beds & Bedroom Furniture", icon: <FaBed /> },
  { name: "Lighting", icon: <FaLightbulb /> },
  { name: "Decor & Accessories", icon: <FaBox /> },
  { name: "Outdoor Furniture", icon: <FaTree /> },
];

function Header() {
  return (
    <header className="header-wrapper">
      
      {/* --- TẦNG 1: TOP BAR (Màu xanh đậm) --- */}
      <div className="header-top">
        <div className="container top-container">
          <div className="logo">
            <h1>chaleureux</h1>
          </div>
          <div className="search-box">
            <input type="text" placeholder="Search for product..." />
            <button className="search-btn">
              <FaSearch />
            </button>
          </div>
          <div className="header-actions">
            <div className="action-item">
              <FaShoppingCart className="icon" />
              <span>Shopping cart</span>
            </div>
            <div className="action-item">
              <FaUser className="icon" />
              <span>Jessica_1456</span>
            </div>
          </div>
        </div>
      </div>

      {/* --- TẦNG 2: NAVIGATION (Màu be) --- */}
      <div className="header-bottom">
        <div className="container bottom-container">

          <div className="category-dropdown">
            <div className="dropdown-trigger">
                <FaBars className="icon" />
                <span>SHOP BY CATEGORY</span>
            </div>

            <ul className="category-menu">
                {categories.map((cat, index) => (
                <li key={index}>
                    <a href="#">
                    <span className="cat-icon">{cat.icon}</span>
                    <span className="cat-name">{cat.name}</span>
                    <span className="cat-arrow"><FaChevronRight size={10}/></span>
                    </a>
                </li>
                ))}
            </ul>
          </div>
          <nav className="main-nav">
            <ul>
              <li><a href="#">HOME</a></li>
              <li>
                <a href="#">SHOP <FaChevronDown size={10} /></a>
              </li>
              <li><a href="#">COLLECTIONS</a></li>
              <li>
                <a href="#">SERVICES <FaChevronDown size={10} /></a>
              </li>
              <li><a href="#">BLOG</a></li>
              <li><a href="#">ABOUT</a></li>
              <li><a href="#">CONTACT</a></li>
            </ul>
          </nav>
        </div>
      </div>

    </header>
  );
}

export default Header;