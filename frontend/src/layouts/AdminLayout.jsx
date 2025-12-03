import React, { useState } from 'react';
import { Outlet, Link, useNavigate, useLocation } from 'react-router-dom';
import {
  LayoutDashboard,
  Package,
  FolderTree,
  ShoppingCart,
  Users,
  Warehouse,
  Gift,
  Star,
  Settings,
  Menu,
  X,
  Bell,
  Search,
  LogOut,
  ChevronDown
} from 'lucide-react';
import { useAuth } from '@/hooks/AuthContext';
import './AdminLayout.css';

const AdminLayout = () => {
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [profileOpen, setProfileOpen] = useState(false);
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();

  const menuItems = [
    { icon: LayoutDashboard, label: 'Dashboard', path: '/admin' },
    { icon: Package, label: 'Sản phẩm', path: '/admin/products' },
    { icon: FolderTree, label: 'Danh mục', path: '/admin/categories' },
    { icon: ShoppingCart, label: 'Đơn hàng', path: '/admin/orders' },
    { icon: Users, label: 'Người dùng', path: '/admin/users' },
    { icon: Warehouse, label: 'Tồn kho', path: '/admin/inventory' },
    { icon: Gift, label: 'Khuyến mãi', path: '/admin/promotions' },
    { icon: Star, label: 'Đánh giá', path: '/admin/reviews' },
    { icon: Settings, label: 'Cài đặt', path: '/admin/settings' },
  ];

  const handleLogout = async () => {
    await logout();
    navigate('/customer/login');
  };

  const isActive = (path) => {
    if (path === '/admin') {
      return location.pathname === '/admin';
    }
    return location.pathname.startsWith(path);
  };

  return (
    <div className="admin-layout">
      {/* Sidebar */}
      <aside className={`admin-sidebar ${sidebarOpen ? 'open' : 'closed'}`}>
        <div className="sidebar-header">
          <h2 className="sidebar-logo">
            {sidebarOpen ? 'Admin Panel' : 'AP'}
          </h2>
        </div>

        <nav className="sidebar-nav">
          {menuItems.map((item) => {
            const Icon = item.icon;
            return (
              <Link
                key={item.path}
                to={item.path}
                className={`nav-item ${isActive(item.path) ? 'active' : ''}`}
              >
                <Icon size={20} />
                {sidebarOpen && <span>{item.label}</span>}
              </Link>
            );
          })}
        </nav>
      </aside>

      {/* Main Content */}
      <div className={`admin-main ${sidebarOpen ? 'sidebar-open' : 'sidebar-closed'}`}>
        {/* Header */}
        <header className="admin-header">
          <div className="header-left">
            <button
              className="toggle-sidebar-btn"
              onClick={() => setSidebarOpen(!sidebarOpen)}
            >
              {sidebarOpen ? <X size={20} /> : <Menu size={20} />}
            </button>

            <div className="search-box">
              <Search size={18} />
              <input type="text" placeholder="Tìm kiếm..." />
            </div>
          </div>

          <div className="header-right">
            <button className="icon-btn">
              <Bell size={20} />
              <span className="badge">3</span>
            </button>

            <div className="profile-dropdown">
              <button
                className="profile-btn"
                onClick={() => setProfileOpen(!profileOpen)}
              >
                <div className="avatar">
                  {user?.name?.charAt(0).toUpperCase() || 'A'}
                </div>
                <span className="profile-name">{user?.name || 'Admin'}</span>
                <ChevronDown size={16} />
              </button>

              {profileOpen && (
                <div className="dropdown-menu">
                  <Link to="/admin/profile" className="dropdown-item">
                    Hồ sơ
                  </Link>
                  <Link to="/admin/settings" className="dropdown-item">
                    Cài đặt
                  </Link>
                  <hr />
                  <button onClick={handleLogout} className="dropdown-item logout">
                    <LogOut size={16} />
                    Đăng xuất
                  </button>
                </div>
              )}
            </div>
          </div>
        </header>

        {/* Content Area */}
        <main className="admin-content">
          <Outlet />
        </main>
      </div>
    </div>
  );
};

export default AdminLayout;
