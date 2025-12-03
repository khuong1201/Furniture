import React from 'react';
import { Outlet, Link } from 'react-router-dom';

const AdminLayout = () => {
  return (
    <div>
      <aside style={{ width: 250, background: '#111', color: '#fff', height: '100vh', position: 'fixed' }}>
        <h3>Admin Panel</h3>
        <ul>
          <li><Link to="/admin" style={{ color: '#fff' }}>Dashboard</Link></li>
          <li><Link to="/admin/orders" style={{ color: '#fff' }}>Orders</Link></li>
        </ul>
      </aside>

      <main style={{ marginLeft: 260, padding: 20 }}>
        <Outlet />
      </main>
    </div>
  );
};

export default AdminLayout;
