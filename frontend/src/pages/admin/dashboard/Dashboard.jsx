import React, { useRef } from 'react';
import { ShoppingCart, Users, Package, DollarSign, Calendar, Filter } from 'lucide-react';
import {
    AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer
} from 'recharts';
import { useDashboard } from '@/hooks/admin/useDashboard';
import './Dashboard.css';

// Import assets
import trendUpIcon from '@/assets/icons/assets_admin/iconamoon_trend-up.png';
import trendDownIcon from '@/assets/icons/assets_admin/iconamoon_trend-down.png';

const Dashboard = () => {
    const {
        loading, stats, revenueData, bestSellers, recentOrders,
        loadingOrders, loadMoreOrders, topCustomers,
        filterType, setFilterType,
        selectedMonth, setSelectedMonth,
        selectedYear, setSelectedYear
    } = useDashboard();

    const tableContainerRef = useRef(null);
    const currentYear = new Date().getFullYear();
    const years = Array.from({ length: 5 }, (_, i) => currentYear - i);

    // Helpers Formatting
    const formatDate = (dateString) => {
        if (!dateString) return '---';
        // Date format từ API là "YYYY-MM-DD HH:mm"
        const date = new Date(dateString.replace(' ', 'T')); 
        return isNaN(date.getTime()) ? dateString : date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    };

    // FIX: Format tiền tệ chuẩn từ số hoặc chuỗi số
    const formatCurrency = (value) => {
        const num = typeof value === 'string' ? parseFloat(value.replace(/[^0-9.-]+/g, "")) : value;
        return new Intl.NumberFormat('vi-VN').format(num || 0) + ' VND';
    };

    const getStatusColor = (status) => {
        const s = status?.toLowerCase();
        if (['delivered', 'completed', 'paid'].includes(s)) return 'success';
        if (['cancelled', 'failed'].includes(s)) return 'danger';
        if (['shipping', 'processing', 'shipped'].includes(s)) return 'info';
        if (s === 'pending' || s === 'unpaid') return 'warning';
        return 'secondary';
    };

    const handleScrollOrders = (e) => {
        const { scrollTop, scrollHeight, clientHeight } = e.target;
        if (scrollHeight - scrollTop <= clientHeight + 50) loadMoreOrders();
    };

    const statCards = [
        { title: 'Total Revenue', value: formatCurrency(stats.totalRevenue), icon: DollarSign, color: '#10b981', trend: stats.revenueGrowth, isUp: stats.revenueGrowth >= 0 },
        { title: 'Total Orders', value: stats.totalOrders, icon: ShoppingCart, color: '#3b82f6', trend: stats.orderGrowth, isUp: stats.orderGrowth >= 0 },
        { title: 'New Users', value: stats.totalUsers, icon: Users, color: '#8b5cf6', trend: stats.newUsers, isUp: true },
        { title: 'Low Stock', value: stats.lowStock, icon: Package, color: '#f59e0b', trend: null, isUp: false },
    ];

    if (loading && stats.totalRevenue === 0) return <div className="dashboard-loading"><div className="spinner"></div><span>Fetching data...</span></div>;

    return (
        <div className="dashboard-container">
            {/* 1. HEADER & FILTER */}
            <div className="dashboard-header-row">
                <div>
                    <h1 className="dashboard-title">Dashboard Overview</h1>
                    <p className="dashboard-subtitle">Business real-time performance analytics</p>
                </div>
                
                <div className="filter-group">
                    <div className="filter-dropdown">
                        <Filter size={16} className="text-secondary"/>
                        <select value={filterType} onChange={(e) => setFilterType(e.target.value)}>
                            <option value="today">Today</option>
                            <option value="week">Weekly</option>
                            <option value="month">Monthly</option>
                            <option value="year">Yearly</option>
                        </select>
                    </div>

                    {filterType === 'month' && (
                        <div className="filter-dropdown">
                            <Calendar size={16} className="text-secondary"/>
                            <select value={selectedMonth} onChange={(e) => setSelectedMonth(parseInt(e.target.value))}>
                                {Array.from({length: 12}, (_, i) => (
                                    <option key={i+1} value={i+1}>Month {i+1}</option>
                                ))}
                            </select>
                        </div>
                    )}

                    {(filterType === 'month' || filterType === 'year') && (
                        <div className="filter-dropdown">
                            <select value={selectedYear} onChange={(e) => setSelectedYear(parseInt(e.target.value))}>
                                {years.map(y => <option key={y} value={y}>{y}</option>)}
                            </select>
                        </div>
                    )}
                </div>
            </div>

            {/* 2. STATS CARDS */}
            <div className="stats-grid">
                {statCards.map((card, i) => (
                    <div key={i} className="stat-card">
                        <div className="stat-icon" style={{ color: card.color, background: `${card.color}15` }}>
                            <card.icon size={24} />
                        </div>
                        <div className="stat-info">
                            <span className="stat-label">{card.title}</span>
                            <h3 className="stat-number">{card.value}</h3>
                            {card.trend !== null && (
                                <div className={`stat-trend ${card.isUp ? 'up' : 'down'}`}>
                                    <img src={card.isUp ? trendUpIcon : trendDownIcon} alt="trend" className="trend-icon" />
                                    <span>{Math.abs(card.trend)}%</span>
                                    <span className="text-muted text-xs ml-1">{stats.compareLabel}</span>
                                </div>
                            )}
                        </div>
                    </div>
                ))}
            </div>

            {/* 3. CHARTS & BEST SELLERS */}
            <div className="charts-section">
                <div className="chart-box revenue-box">
                    <div className="box-header"><h3>Revenue Trends</h3></div>
                    <div className="chart-wrapper" style={{ height: 400, width: '100%' }}>
                        {revenueData.length > 0 ? (
                            <ResponsiveContainer width="100%" height="100%">
                                <AreaChart data={revenueData} margin={{ top: 10, right: 30, left: 0, bottom: 0 }}>
                                    <defs>
                                        <linearGradient id="colorRev" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="5%" stopColor="#c5a47e" stopOpacity={0.3} />
                                            <stop offset="95%" stopColor="#c5a47e" stopOpacity={0} />
                                        </linearGradient>
                                    </defs>
                                    <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f3f4f6" />
                                    <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fill: '#6b7280', fontSize: 12 }} dy={10} />
                                    <YAxis axisLine={false} tickLine={false} tick={{ fill: '#6b7280', fontSize: 11 }} tickFormatter={(val) => `${(val / 1000000).toFixed(0)}M`} />
                                    <Tooltip formatter={(val) => formatCurrency(val)} />
                                    <Area type="monotone" dataKey="value" stroke="#c5a47e" strokeWidth={3} fillOpacity={1} fill="url(#colorRev)" />
                                </AreaChart>
                            </ResponsiveContainer>
                        ) : <div className="empty-state">No data</div>}
                    </div>
                </div>

                <div className="chart-box lists-box">
                    <div className="box-header"><h3>Top Selling Products</h3></div>
                    <div className="list-content custom-scroll">
                        {bestSellers.map((prod, i) => (
                            <div key={i} className="product-list-item">
                                <div className="prod-img-wrapper">
                                    <img src={prod.image || 'https://placehold.co/50'} alt={prod.name} />
                                    <div className={`rank-dot rank-${i+1}`}>{i+1}</div>
                                </div>
                                <div className="prod-info">
                                    <h4 className="prod-name">{prod.name}</h4>
                                    <div className="prod-meta">
                                        <span className="prod-sold">{prod.total_sold} sold</span>
                                        <span className="prod-revenue">{formatCurrency(prod.total_revenue)}</span>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* 4. ORDERS & VIP CUSTOMERS */}
            <div className="bottom-section">
                <div className="chart-box orders-box">
                    <div className="box-header"><h3>Recent Orders</h3></div>
                    <div className="table-wrapper custom-scroll" ref={tableContainerRef} onScroll={handleScrollOrders}>
                        <table className="modern-table">
                            <thead>
                                <tr><th>Order Code</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                {recentOrders.map((order) => (
                                    <tr key={order.uuid}>
                                        <td className="font-mono" style={{ color: '#c5a47e' }}>{order.code}</td>
                                        <td>
                                            <div className="fw-600">{order.customer?.name || 'Guest'}</div>
                                            <div style={{ fontSize: '11px', color: '#9ca3af' }}>{order.customer?.email}</div>
                                        </td>
                                        {/* FIX TẠI ĐÂY: Truy cập vào object amounts */}
                                        <td className="fw-bold">
                                            {order.amounts?.grand_total || formatCurrency(order.grand_total)}
                                        </td>
                                        <td>
                                            <span className={`status-badge ${getStatusColor(order.status)}`}>
                                                {order.status_label || order.status}
                                            </span>
                                        </td>
                                        <td className="text-muted">{formatDate(order.dates?.ordered_at)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="chart-box lists-box">
                    <div className="box-header"><h3>Top Spenders</h3></div>
                    <div className="list-content custom-scroll">
                        {topCustomers.map((c, i) => (
                            <div key={i} className="list-item">
                                <div className={`rank-badge rank-${i+1}`}>{i+1}</div>
                                <div className="list-details">
                                    <span className="list-title">{c.customer?.name || c.user?.name}</span>
                                    <span className="list-subtitle">{c.customer?.email || c.user?.email}</span>
                                </div>
                                <div className="list-value">{formatCurrency(c.total_spent)}</div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Dashboard;