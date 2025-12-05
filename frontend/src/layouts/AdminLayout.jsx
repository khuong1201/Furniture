import React, { useState, useEffect } from 'react';
import { Link, Outlet, useLocation, useNavigate } from 'react-router-dom';
import {
  LayoutDashboard,
  Package,
  FolderTree,
  ShoppingCart,
  Users,
  Gift,
  Settings,
  Menu,
  Search,
  Bell,
  User,
  ChevronDown,
  LogOut,
  Star,
  Shield,
  Boxes,
  Layers,
  Truck,
  Activity,
  Sliders,
  DollarSign,
  ChevronRight,
  Warehouse
} from 'lucide-react';
import { useAuth } from '@/hooks/AuthContext';
import './AdminLayout.css';
import logo from '@/assets/icons/assets_admin/logo_admin.png';

const AdminLayout = () => {
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [profileOpen, setProfileOpen] = useState(false);
  const { user, loading, logout } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();
  const [expandedGroups, setExpandedGroups] = useState(['dashboard', 'catalog']);

  useEffect(() => {
    if (!loading && !user) {
      navigate('/admin/login');
    }
  }, [user, loading, navigate]);

  // Grouped menu items
  const menuGroups = [
    {
      id: 'dashboard',
      label: 'Tổng quan',
      icon: LayoutDashboard,
      single: true,
      path: '/admin'
    },
    {
      id: 'catalog',
      label: 'Danh mục',
      icon: Package,
      items: [
        { icon: Package, label: 'Sản phẩm', path: '/admin/products' },
        { icon: FolderTree, label: 'Danh mục', path: '/admin/categories' },
        { icon: Layers, label: 'Bộ sưu tập', path: '/admin/collections' },
        { icon: Sliders, label: 'Thuộc tính', path: '/admin/attributes' },
      ]
    },
    {
      id: 'sales',
      label: 'Bán hàng',
      icon: ShoppingCart,
      items: [
        { icon: ShoppingCart, label: 'Đơn hàng', path: '/admin/orders' },
        { icon: DollarSign, label: 'Thanh toán', path: '/admin/payments' },
        { icon: Gift, label: 'Khuyến mãi', path: '/admin/promotions' },
      ]
    },
    {
      id: 'inventory',
      label: 'Kho & Vận chuyển',
      icon: Boxes,
      items: [
        { icon: Boxes, label: 'Tồn kho', path: '/admin/inventory' },
        { icon: Warehouse, label: 'Kho hàng', path: '/admin/warehouses' },
        { icon: Truck, label: 'Vận chuyển', path: '/admin/shippings' },
      ]
    },
    {
      id: 'users',
      label: 'Quản lý',
      icon: Users,
      items: [
        { icon: Users, label: 'Người dùng', path: '/admin/users' },
        { icon: Shield, label: 'Vai trò', path: '/admin/roles' },
        { icon: Shield, label: 'Quyền hạn', path: '/admin/permissions' },
      ]
    },
    {
      id: 'system',
      label: 'Hệ thống',
      icon: Settings,
      items: [
        { icon: Activity, label: 'Nhật ký', path: '/admin/logs' },
        { icon: Star, label: 'Đánh giá', path: '/admin/reviews' },
        { icon: Settings, label: 'Cài đặt', path: '/admin/settings' },
      ]
    },
  ];

  const toggleGroup = (groupId) => {
    setExpandedGroups(prev =>
      prev.includes(groupId)
        ? prev.filter(id => id !== groupId)
        : [...prev, groupId]
    );
  };

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

  const isGroupActive = (group) => {
    if (group.single) return isActive(group.path);
    return group.items?.some(item => isActive(item.path));
  };

  return (
    <div className="admin-app-container">
      <div className="admin-layout">
        {/* Sidebar */}
        <aside className={`admin-sidebar ${sidebarOpen ? 'open' : 'closed'}`}>
          <div className="sidebar-header">
            <h2 className="sidebar-logo">
              <div className="logo-icon-sidebar">
                <LayoutDashboard size={24} />
              </div>
              {sidebarOpen && <span className="logo-text">Admin Panel</span>}
            </h2>
          </div>

          <nav className="sidebar-nav">
            {menuGroups.map((group) => {
              const GroupIcon = group.icon;
              const isExpanded = expandedGroups.includes(group.id);
              const isGroupItemActive = isGroupActive(group);

              if (group.single) {
                return (
                  <Link
                    key={group.id}
                    to={group.path}
                    className={`nav-item ${isActive(group.path) ? 'active' : ''}`}
                    onClick={() => window.innerWidth < 1024 && setSidebarOpen(false)}
                  >
                    <GroupIcon size={20} className="nav-icon" />
                    {sidebarOpen && <span>{group.label}</span>}
                  </Link>
                );
              }

              return (
                <div key={group.id} className="nav-group">
                  <button
                    className={`nav-group-header ${isGroupItemActive ? 'active' : ''}`}
                    onClick={() => sidebarOpen && toggleGroup(group.id)}
                  >
                    <GroupIcon size={20} className="nav-icon" />
                    {sidebarOpen && (
                      <>
                        <span className="group-label">{group.label}</span>
                        <ChevronRight
                          size={16}
                          className={`expand-icon ${isExpanded ? 'expanded' : ''}`}
                        />
                      </>
                    )}
                  </button>

                  {sidebarOpen && isExpanded && (
                    <div className="nav-group-items">
                      {group.items.map((item) => {
                        const ItemIcon = item.icon;
                        return (
                          <Link
                            key={item.path}
                            to={item.path}
                            className={`nav-sub-item ${isActive(item.path) ? 'active' : ''}`}
                            onClick={() => window.innerWidth < 1024 && setSidebarOpen(false)}
                          >
                            <ItemIcon size={18} className="nav-icon" />
                            <span>{item.label}</span>
                          </Link>
                        );
                      })}
                    </div>
                  )}
                </div>
              );
            })}
          </nav>
        </aside>

        {/* Main Content */}
        <div className={`admin-main ${sidebarOpen ? '' : 'sidebar-closed'}`}>
          {/* Header */}
          <header className="admin-header">
            <div className="header-left">
              <button className="toggle-sidebar-btn" onClick={() => setSidebarOpen(!sidebarOpen)}>
                <Menu size={20} />
              </button>

              <div className="search-box">
                <Search size={16} className="search-icon" />
                <input type="text" placeholder="Tìm kiếm..." />
              </div>
            </div>

            <div className="header-right">
              <button className="icon-btn">
                <Bell size={20} />
                <span className="badge">3</span>
              </button>

              <div className="profile-dropdown">
                <button className="profile-btn" onClick={() => setProfileOpen(!profileOpen)}>
                  <div className="avatar">
                    <User size={20} />
                  </div>
                  <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-start' }}>
                    <span className="profile-name">{user?.name || 'Admin'}</span>
                    <span className="profile-role">Administrator</span>
                  </div>
                  <ChevronDown size={16} />
                </button>

                {profileOpen && (
                  <div className="dropdown-menu">
                    <Link to="/admin/profile" className="dropdown-item">
                      <User size={16} />
                      Hồ sơ
                    </Link>
                    <Link to="/admin/settings" className="dropdown-item">
                      <Settings size={16} />
                      Cài đặt
                    </Link>
                    <hr />
                    <button className="dropdown-item logout" onClick={handleLogout}>
                      <LogOut size={16} />
                      Đăng xuất
                    </button>
                  </div>
                )}
              </div>
            </div>
          </header>

          {/* Content */}
          <div className="admin-content">
            <Outlet />
          </div>

          {/* Footer */}
          <footer className="admin-footer">
            <div className="footer-content">
              <div className="footer-left">
                <p className="copyright">© 2024 Jewelry Admin Panel</p>
              </div>
              <div className="footer-right">
                <div className="footer-links">
                  <a href="#" className="footer-link">Trợ giúp</a>
                  <span className="separator">•</span>
                  <a href="#" className="footer-link">Tài liệu</a>
                </div>
              </div>
            </div>
          </footer>
        </div>
      </div>
    </div>
  );
};

export default AdminLayout;