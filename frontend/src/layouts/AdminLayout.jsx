import React, { useState, useEffect } from 'react';
import { Link, Outlet, useLocation, useNavigate } from 'react-router-dom';
import {
  LayoutDashboard, ShoppingCart, Users, Settings,
  Menu, User, LogOut,
  Activity, DollarSign, ChevronRight, Warehouse, Gift, ArrowLeft,
  Star, FolderKanban
} from 'lucide-react';
import { useAuth } from '@/hooks/AuthContext';
import './AdminLayout.css';
import NotificationBell from '@/components/admin/NotificationBell.jsx';

const AdminLayout = () => {
  const [sidebarOpen, setSidebarOpen] = useState(window.innerWidth > 1024);
  const [profileOpen, setProfileOpen] = useState(false);
  const { user, loading, logout } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();

  const [expandedGroups, setExpandedGroups] = useState(['dashboard']);


  useEffect(() => {
    if (window.innerWidth <= 1024) {
      setSidebarOpen(false);
    }
  }, [location.pathname]);

  useEffect(() => {
    if (!loading && !user) {
      navigate('/admin/login');
    }
  }, [user, loading, navigate]);

  const menuGroups = [
    {
      id: 'dashboard',
      label: 'Dashboard',
      icon: LayoutDashboard,
      single: true,
      path: '/admin'
    },

    // === SALES ===
    {
      id: 'sales',
      label: 'Sales & Orders',
      icon: ShoppingCart,
      items: [
        { icon: ShoppingCart, label: 'Orders', path: '/admin/orders' },
        { icon: DollarSign, label: 'Payments', path: '/admin/payments' },
        { icon: Gift, label: 'Promotions', path: '/admin/promotions' },
      ]
    },

    // === CATALOG ===
    {
      id: 'catalog',
      label: 'Catalog',
      icon: FolderKanban,
      items: [
        { icon: FolderKanban, label: 'Product Management', path: '/admin/product-manager' },
        { icon: Warehouse, label: 'Inventory', path: '/admin/inventory-manager' },
        { icon: Star, label: 'Reviews', path: '/admin/reviews' },
      ]
    },

    // === USERS ===
    {
      id: 'users',
      label: 'User Management',
      icon: Users,
      items: [
        { icon: Users, label: 'Users', path: '/admin/users' },
      ]
    },

    // === SYSTEM ===
    {
      id: 'others',
      label: 'System',
      icon: Settings,
      items: [
        { icon: Activity, label: 'Activity Logs', path: '/admin/logs' },
        { icon: Settings, label: 'Settings', path: '/admin/settings' },
      ]
    },
  ];

  const toggleGroup = (groupId) => {
    if (!sidebarOpen) {
      setSidebarOpen(true);
      setExpandedGroups([groupId]);
    } else {
      setExpandedGroups(prev =>
        prev.includes(groupId)
          ? prev.filter(id => id !== groupId)
          : [...prev, groupId]
      );
    }
  };

  const isActive = (path) =>
    path === '/admin'
      ? location.pathname === '/admin'
      : location.pathname.startsWith(path);

  const isGroupActive = (group) =>
    group.single
      ? isActive(group.path)
      : group.items?.some(item => isActive(item.path));

  return (
    <div className="admin-app-container">
      <div className="admin-layout">

        {/* Sidebar */}
        <aside className={`admin-sidebar ${sidebarOpen ? 'open' : 'collapsed'}`}>
          <div className="sidebar-header">
            <Link to="/admin" className="sidebar-logo">
              <span className={`logo-text serif-font ${!sidebarOpen && 'hidden'}`}>
                Atelier
              </span>
            </Link>
          </div>

          <nav className="sidebar-nav">
            {menuGroups.map(group => {
              const GroupIcon = group.icon;
              const isExpanded = expandedGroups.includes(group.id);
              const isGroupItemActive = isGroupActive(group);

              return (
                <div key={group.id} className="nav-group-container">
                  {group.single ? (
                    <Link
                      to={group.path}
                      className={`nav-item single-item ${isActive(group.path) ? 'active' : ''}`}
                      title={!sidebarOpen ? group.label : ''}
                    >
                      <GroupIcon size={20} className="nav-icon" />
                      <span className={`nav-text ${!sidebarOpen && 'hidden'}`}>
                        {group.label}
                      </span>
                    </Link>
                  ) : (
                    <div className={`nav-group ${isExpanded ? 'expanded' : ''}`}>
                      <div
                        className={`nav-group-header ${isGroupItemActive ? 'group-active' : ''}`}
                        onClick={() => toggleGroup(group.id)}
                        title={!sidebarOpen ? group.label : ''}
                      >
                        <div className="group-header-left">
                          <GroupIcon size={20} className="nav-icon" />
                          <span className={`group-label ${!sidebarOpen && 'hidden'}`}>
                            {group.label}
                          </span>
                        </div>
                        {sidebarOpen && (
                          <ChevronRight
                            size={16}
                            className={`expand-icon ${isExpanded ? 'rotated' : ''}`}
                          />
                        )}
                      </div>

                      <div className={`nav-group-items ${(!sidebarOpen || !isExpanded) ? 'hidden' : ''}`}>
                        {group.items.map(item => (
                          <Link
                            key={item.path}
                            to={item.path}
                            className={`nav-sub-item ${isActive(item.path) ? 'active' : ''}`}
                          >
                            <span className="dot-indicator" />
                            <span>{item.label}</span>
                          </Link>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              );
            })}
          </nav>

          <div className="sidebar-footer">
            <button
              className="sidebar-back-btn"
              onClick={async () => {
                await logout();
                navigate('/customer/login');
              }}
              title="Back to Customer Site"
            >
              <ArrowLeft size={20} />
            </button>
          </div>
        </aside>

        {/* Main */}
        <div className={`admin-main ${sidebarOpen ? 'sidebar-open' : 'sidebar-collapsed'}`}>
          <header className="admin-header">
            <div className="header-left">
              <button
                className="toggle-sidebar-btn"
                onClick={() => setSidebarOpen(!sidebarOpen)}
              >
                <Menu size={20} color="#fff" />
              </button>
            </div>

            <div className="header-right">
              <NotificationBell />
              <div className="profile-dropdown">
                <button
                  className="profile-btn"
                  onClick={() => setProfileOpen(!profileOpen)}
                >
                  <div className="profile-info">
                    <span className="welcome-text">
                      Hello {user?.name || 'Admin'}
                    </span>
                  </div>
                  <div className="avatar-circle">
                    <User size={20} />
                  </div>
                </button>

                {profileOpen && (
                  <div className="dropdown-menu">
                    <Link to="/admin/profile" className="dropdown-item">
                      <User size={16} /> Profile
                    </Link>
                    <Link to="/admin/settings" className="dropdown-item">
                      <Settings size={16} /> Settings
                    </Link>
                    <hr />
                    <button
                      className="dropdown-item logout"
                      onClick={async () => {
                        await logout();
                        navigate('/customer/login');
                      }}
                    >
                      <LogOut size={16} /> Logout
                    </button>
                  </div>
                )}
              </div>
            </div>
          </header>

          <div className="admin-content">
            <Outlet />
          </div>

          <footer className="admin-footer-text">
            © 2024 Atelier Management
          </footer>
        </div>
      </div>
    </div>
  );
};

export default AdminLayout;