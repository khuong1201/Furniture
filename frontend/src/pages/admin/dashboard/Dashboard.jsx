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
import DashboardService from '@/services/DashboardService';
import OrderService from '@/services/OrderService';
import ProductService from '@/services/ProductService';
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
            const [summaryRes, revenueRes, ordersRes, productsRes, statsRes] = await Promise.all([
                DashboardService.getSummary(),
                DashboardService.getRevenue({ year: selectedYear }),
                OrderService.getOrders({ limit: 5 }), // Assuming backend supports limit or we slice
                ProductService.getProducts({ limit: 5 }),
                DashboardService.getStats()
            ]);

            // Process Summary
            if (summaryRes.success && summaryRes.data) {
                setStats({
                    totalRevenue: summaryRes.data.total_revenue || 0,
                    totalOrders: summaryRes.data.total_orders || 0,
                    totalUsers: summaryRes.data.total_users || 0,
                    totalProducts: summaryRes.data.total_products || 0,
                });
            }

            // Process Revenue Chart
            if (revenueRes.success && revenueRes.data) {
                // API returns array [{ month: 1, revenue: 1000, total_orders: 5 }, ...]
                // We need to map it to Recharts format and ensure all months are present
                const rawData = Array.isArray(revenueRes.data) ? revenueRes.data : (revenueRes.data.data || []);

                // Create array for all 12 months initialized to 0
                const fullYearData = Array.from({ length: 12 }, (_, i) => {
                    const monthIndex = i + 1;
                    const monthData = rawData.find(item => item.month === monthIndex);
                    return {
                        name: `T${monthIndex}`,
                        value: monthData ? parseFloat(monthData.revenue) : 0,
                        orders: monthData ? monthData.total_orders : 0
                    };
                });

                setRevenueData(fullYearData);
            }

            // Process Recent Orders
            if (ordersRes.success && ordersRes.data) {
                const orders = Array.isArray(ordersRes.data) ? ordersRes.data : (ordersRes.data.data || []);
                setRecentOrders(orders.slice(0, 5));
            }

            // Process Top Products (using recent products as proxy if top selling not available)
            if (productsRes.success && productsRes.data) {
                const products = Array.isArray(productsRes.data) ? productsRes.data : (productsRes.data.data || []);
                setTopProducts(products.slice(0, 5));
            }

            // Process Detailed Stats
            if (statsRes.success && statsRes.data) {
                setOrderStatusData(statsRes.data.order_status || []);
                setCategorySalesData(statsRes.data.category_sales || []);
                setTopCustomers(statsRes.data.top_customers || []);
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
            value: stats.totalProducts,
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

                {/* Order Status Pie Chart */}
                <div className="chart-card">
                    <div className="chart-header">
                        <h3>Trạng thái đơn hàng</h3>
                    </div>
                    <div className="chart-content">
                        <ResponsiveContainer width="100%" height="100%">
                            <PieChart>
                                <Pie
                                    data={orderStatusData}
                                    cx="50%"
                                    cy="50%"
                                    innerRadius={60}
                                    outerRadius={80}
                                    fill="#8884d8"
                                    paddingAngle={5}
                                    dataKey="count"
                                    nameKey="status"
                                >
                                    {orderStatusData.map((entry, index) => (
                                        <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                    ))}
                                </Pie>
                                <Tooltip />
                                <Legend />
                            </PieChart>
                        </ResponsiveContainer>
                    </div>
                </div>
            </div>

            {/* Secondary Charts Row */}
            <div className="dashboard-grid">
                {/* Category Sales Bar Chart */}
                <div className="chart-card">
                    <div className="chart-header">
                        <h3>Doanh thu theo danh mục</h3>
                    </div>
                    <div className="chart-content">
                        <ResponsiveContainer width="100%" height="100%">
                            <BarChart data={categorySalesData} layout="vertical">
                                <CartesianGrid strokeDasharray="3 3" horizontal={false} />
                                <XAxis type="number" hide />
                                <YAxis dataKey="name" type="category" width={100} tick={{ fontSize: 12 }} />
                                <Tooltip formatter={(value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)} />
                                <Bar dataKey="revenue" fill="#d4af37" radius={[0, 4, 4, 0]} barSize={20} />
                            </BarChart>
                        </ResponsiveContainer>
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
                                    <h4 className="product-name">{customer.name}</h4>
                                    <span className="product-sales">{customer.email}</span>
                                </div>
                                <div className="text-right">
                                    <div className="product-price">
                                        {new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(customer.total_spent)}
                                    </div>
                                    <span className="product-sales">{customer.order_count} đơn</span>
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
                                        {new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(product.price)}
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
