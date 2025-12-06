import React, { useEffect, useState } from 'react';
import {
    TrendingUp,
    ShoppingCart,
    Users,
    Package,
    DollarSign,
    ArrowUp,
    ArrowDown,
    Calendar,
    PieChart as PieChartIcon,
    BarChart as BarChartIcon
} from 'lucide-react';
import {
    AreaChart,
    Area,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer,
    PieChart,
    Pie,
    Cell,
    BarChart,
    Bar,
    Legend
} from 'recharts';
import DashboardService from '@/services/admin/DashboardService';
import OrderService from '@/services/customer/OrderService';
import ProductService from '@/services/customer/ProductService';
import './Dashboard.css';

const COLORS = ['#CBA890', '#a78b6e', '#8a7159', '#e5d5c5', '#d4a574'];

const Dashboard = () => {
    const [loading, setLoading] = useState(true);
    const [stats, setStats] = useState({
        totalRevenue: 0,
        totalOrders: 0,
        totalUsers: 0,
        totalProducts: 0,
    });
    const [revenueData, setRevenueData] = useState([]);
    const [recentOrders, setRecentOrders] = useState([]);
    const [topProducts, setTopProducts] = useState([]);
    const [orderStatusData, setOrderStatusData] = useState([]);
    const [categorySalesData, setCategorySalesData] = useState([]);
    const [topCustomers, setTopCustomers] = useState([]);
    const [selectedYear, setSelectedYear] = useState(new Date().getFullYear());

    useEffect(() => {
        fetchAllData();
    }, [selectedYear]);

    const fetchAllData = async () => {
        try {
            setLoading(true);
            const [summaryRes, ordersRes] = await Promise.all([
                DashboardService.getSummary(),
                OrderService.getOrders({ limit: 5 })
            ]);

            // Process Summary
            if (summaryRes.success && summaryRes.data) {
                const data = summaryRes.data;

                // Stats Cards
                if (data.cards) {
                    setStats({
                        totalRevenue: data.cards.total_revenue || 0,
                        totalOrders: data.cards.total_orders || 0,
                        totalUsers: data.cards.total_users || 0,
                        totalProducts: data.cards.low_stock_variants || 0, // Using low_stock as placeholder or if available
                    });
                }

                // Top Products (Best Sellers)
                if (data.best_sellers) {
                    setTopProducts(data.best_sellers);
                }

                // Top Customers (Top Spenders)
                if (data.top_spenders) {
                    setTopCustomers(data.top_spenders);
                }

                // Process Revenue Chart
                if (data.revenue_chart) {
                    // Map API data directly for the chart
                    const chartData = data.revenue_chart.map(item => ({
                        name: item.label || item.date, // Use label (Monday) or date
                        value: parseFloat(item.value),
                        date: item.date
                    }));
                    setRevenueData(chartData);
                }
            }

            // Process Recent Orders
            if (ordersRes.success && ordersRes.data) {
                const orders = Array.isArray(ordersRes.data) ? ordersRes.data : (ordersRes.data.data || []);
                setRecentOrders(orders.slice(0, 5));
            }

        } catch (error) {
            console.error('Error fetching dashboard data:', error);
        } finally {
            setLoading(false);
        }
    };

    const statCards = [
        {
            title: 'Doanh thu',
            value: new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(stats.totalRevenue),
            icon: DollarSign,
            color: '#10b981',
            trend: '+12.5%',
            isUp: true,
        },
        {
            title: 'Đơn hàng',
            value: stats.totalOrders,
            icon: ShoppingCart,
            color: '#3b82f6',
            trend: '+8.2%',
            isUp: true,
        },
        {
            title: 'Khách hàng',
            value: stats.totalUsers,
            icon: Users,
            color: '#8b5cf6',
            trend: '+15.3%',
            isUp: true,
        },
        {
            title: 'Sản phẩm',
            value: stats.totalProducts, // Note: API returns low_stock_variants, not total products count in cards
            icon: Package,
            color: '#f59e0b',
            trend: '+2.4%',
            isUp: true,
        },
    ];

    const getStatusBadge = (status) => {
        const statusMap = {
            'pending': { label: 'Chờ xử lý', class: 'badge-warning' },
            'processing': { label: 'Đang xử lý', class: 'badge-info' },
            'shipped': { label: 'Đang giao', class: 'badge-info' },
            'delivered': { label: 'Hoàn thành', class: 'badge-success' },
            'cancelled': { label: 'Đã hủy', class: 'badge-danger' },
        };
        const info = statusMap[status] || { label: status, class: 'badge-secondary' };
        return <span className={`badge ${info.class}`}>{info.label}</span>;
    };

    if (loading) {
        return (
            <div className="dashboard-loading">
                <div className="spinner"></div>
                <p>Đang tải dữ liệu...</p>
            </div>
        );
    }

    return (
        <div className="dashboard">
            <div className="dashboard-header">
                <h1>Dashboard</h1>
                <p className="dashboard-subtitle">Tổng quan hoạt động kinh doanh</p>
            </div>

            {/* Stat Cards */}
            <div className="stat-cards">
                {statCards.map((card, index) => {
                    const Icon = card.icon;
                    return (
                        <div key={index} className="stat-card">
                            <div className="stat-card-header">
                                <div className="stat-icon" style={{ background: `${card.color}15`, color: card.color }}>
                                    <Icon size={24} />
                                </div>
                                <div className={`stat-trend ${card.isUp ? 'up' : 'down'}`}>
                                    {card.isUp ? <ArrowUp size={16} /> : <ArrowDown size={16} />}
                                    <span>{card.trend}</span>
                                </div>
                            </div>
                            <div className="stat-card-body">
                                <h3 className="stat-title">{card.title}</h3>
                                <p className="stat-value">{card.value}</p>
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Main Charts Row */}
            <div className="dashboard-grid">
                {/* Revenue Chart */}
                <div className="chart-card revenue-chart">
                    <div className="chart-header">
                        <h3>Biểu đồ doanh thu</h3>
                        <div className="chart-actions">
                            <select
                                value={selectedYear}
                                onChange={(e) => setSelectedYear(parseInt(e.target.value))}
                                className="chart-select"
                            >
                                <option value={2024}>2024</option>
                                <option value={2025}>2025</option>
                            </select>
                        </div>
                    </div>
                    <div className="chart-content">
                        <ResponsiveContainer width="100%" height="100%">
                            <AreaChart data={revenueData}>
                                <defs>
                                    <linearGradient id="colorRevenue" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="5%" stopColor="#d4af37" stopOpacity={0.8} />
                                        <stop offset="95%" stopColor="#d4af37" stopOpacity={0} />
                                    </linearGradient>
                                </defs>
                                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f0f0f0" />
                                <XAxis
                                    dataKey="name"
                                    axisLine={false}
                                    tickLine={false}
                                    tick={{ fill: '#9ca3af', fontSize: 12 }}
                                    dy={10}
                                />
                                <YAxis
                                    axisLine={false}
                                    tickLine={false}
                                    tick={{ fill: '#9ca3af', fontSize: 12 }}
                                    tickFormatter={(value) => `${value / 1000000}M`}
                                />
                                <Tooltip
                                    contentStyle={{
                                        backgroundColor: '#fff',
                                        border: 'none',
                                        borderRadius: '8px',
                                        boxShadow: '0 4px 20px rgba(0,0,0,0.1)'
                                    }}
                                    formatter={(value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)}
                                />
                                <Area
                                    type="monotone"
                                    dataKey="value"
                                    stroke="#d4af37"
                                    strokeWidth={3}
                                    fillOpacity={1}
                                    fill="url(#colorRevenue)"
                                />
                            </AreaChart>
                        </ResponsiveContainer>
                    </div>
                </div>

                {/* Order Status Pie Chart - Placeholder or Hidden if no data */}
                <div className="chart-card">
                    <div className="chart-header">
                        <h3>Trạng thái đơn hàng</h3>
                    </div>
                    <div className="chart-content" style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#9ca3af' }}>
                        <p>Chưa có dữ liệu</p>
                    </div>
                </div>
            </div>

            {/* Secondary Charts Row */}
            <div className="dashboard-grid">
                {/* Category Sales Bar Chart - Placeholder */}
                <div className="chart-card">
                    <div className="chart-header">
                        <h3>Doanh thu theo danh mục</h3>
                    </div>
                    <div className="chart-content" style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#9ca3af' }}>
                        <p>Chưa có dữ liệu</p>
                    </div>
                </div>

                {/* Top Customers */}
                <div className="chart-card">
                    <div className="chart-header">
                        <h3>Khách hàng tiêu biểu</h3>
                    </div>
                    <div className="top-products-list">
                        {topCustomers.map((customer, index) => (
                            <div key={index} className="top-product-item">
                                <div className="product-rank">#{index + 1}</div>
                                <div className="product-details">
                                    <h4 className="product-name">{customer.user?.name || 'Unknown'}</h4>
                                    <span className="product-sales">{customer.user?.email}</span>
                                </div>
                                <div className="text-right">
                                    <div className="product-price">
                                        {new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(customer.total_spent)}
                                    </div>
                                    {/* Removed order count as it is not in API response */}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Recent Orders & Top Products */}
            <div className="dashboard-grid">
                <div className="chart-card recent-orders-card">
                    <div className="chart-header">
                        <h3>Đơn hàng gần đây</h3>
                    </div>
                    <div className="recent-orders">
                        <table className="recent-orders-table">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày đặt</th>
                                </tr>
                            </thead>
                            <tbody>
                                {recentOrders.map((order) => (
                                    <tr key={order.uuid}>
                                        <td><span className="order-id">#{order.code || order.uuid.substring(0, 8)}</span></td>
                                        <td>
                                            <div className="customer-info">
                                                <span className="customer-name">{order.user?.name || 'Khách lẻ'}</span>
                                                <span className="customer-email">{order.user?.email}</span>
                                            </div>
                                        </td>
                                        <td className="amount">
                                            {new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(order.total_amount)}
                                        </td>
                                        <td>{getStatusBadge(order.status)}</td>
                                        <td>{new Date(order.created_at).toLocaleDateString('vi-VN')}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="chart-card">
                    <div className="chart-header">
                        <h3>Sản phẩm bán chạy</h3>
                    </div>
                    <div className="top-products-list">
                        {topProducts.map((product, index) => (
                            <div key={product.uuid} className="top-product-item">
                                <div className="product-rank">#{index + 1}</div>
                                <img
                                    src={product.image_url || 'https://via.placeholder.com/48'}
                                    alt={product.name}
                                    className="product-image"
                                />
                                <div className="product-details">
                                    <h4 className="product-name">{product.name}</h4>
                                    <span className="product-price">
                                        {/* Displaying Total Revenue as Price is not available in best_sellers API */}
                                        {new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(product.total_revenue)}
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Dashboard;
