import React from 'react';
import { Link } from 'react-router-dom';
import { Mail, Phone, MapPin, Facebook, Instagram, Youtube, CreditCard, Truck, Shield } from 'lucide-react';
import './Footer.css';

const Footer = () => {
    return (
        <footer className="customer-footer">
            {/* Features Bar */}
            <div className="features-bar">
                <div className="container">
                    <div className="feature-item">
                        <Truck size={24} />
                        <div>
                            <strong>Miễn phí vận chuyển</strong>
                            <span>Đơn hàng từ 500.000đ</span>
                        </div>
                    </div>
                    <div className="feature-item">
                        <Shield size={24} />
                        <div>
                            <strong>Bảo hành 12 tháng</strong>
                            <span>Đổi trả 30 ngày</span>
                        </div>
                    </div>
                    <div className="feature-item">
                        <CreditCard size={24} />
                        <div>
                            <strong>Thanh toán an toàn</strong>
                            <span>Bảo mật 100%</span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Main Footer */}
            <div className="footer-main">
                <div className="container">
                    <div className="footer-grid">
                        {/* About */}
                        <div className="footer-col">
                            <h3 className="footer-logo">✨ Jewelry</h3>
                            <p>Chúng tôi mang đến những sản phẩm trang sức cao cấp, tinh xảo nhất cho phái đẹp Việt Nam.</p>
                            <div className="social-links">
                                <a href="#" className="social-link"><Facebook size={18} /></a>
                                <a href="#" className="social-link"><Instagram size={18} /></a>
                                <a href="#" className="social-link"><Youtube size={18} /></a>
                            </div>
                        </div>

                        {/* Quick Links */}
                        <div className="footer-col">
                            <h4>Liên kết</h4>
                            <ul>
                                <li><Link to="/customer">Trang chủ</Link></li>
                                <li><Link to="/customer/product">Sản phẩm</Link></li>
                                <li><Link to="/customer/orders">Đơn hàng</Link></li>
                                <li><Link to="/customer/profile">Tài khoản</Link></li>
                            </ul>
                        </div>

                        {/* Categories */}
                        <div className="footer-col">
                            <h4>Danh mục</h4>
                            <ul>
                                <li><Link to="/customer/product?category=nhan">Nhẫn</Link></li>
                                <li><Link to="/customer/product?category=day-chuyen">Dây chuyền</Link></li>
                                <li><Link to="/customer/product?category=bong-tai">Bông tai</Link></li>
                                <li><Link to="/customer/product?category=vong-tay">Vòng tay</Link></li>
                            </ul>
                        </div>

                        {/* Contact */}
                        <div className="footer-col">
                            <h4>Liên hệ</h4>
                            <div className="contact-info">
                                <p><MapPin size={16} /> 123 Đường ABC, Quận 1, TP.HCM</p>
                                <p><Phone size={16} /> 0123 456 789</p>
                                <p><Mail size={16} /> contact@jewelry.vn</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Bottom Bar */}
            <div className="footer-bottom">
                <div className="container">
                    <p>© {new Date().getFullYear()} Jewelry Store. All rights reserved.</p>
                </div>
            </div>
        </footer>
    );
};

export default Footer;
