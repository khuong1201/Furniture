import React, { useEffect, useState } from 'react';
import {
    TrendingUp,
    ShoppingCart,
    Users,
    Package,
    DollarSign,
    ArrowUp,
    ArrowDown
} from 'lucide-react';
import './Dashboard.css';

const Dashboard = () => {
    const [stats, setStats] = useState({
        totalRevenue: 0,
        totalOrders: 0,
        totalUsers: 0,
        totalProducts: 0,
    });

    const [loading, setLoading] = useState(true);

    useEffect(() => {
        // TODO: Fetch real data from API
        // Giả lập data tạm thời
        setTimeout(() => {
            setStats({
                totalRevenue: 125000000,
                totalOrders: 342,
                totalUsers: 1250,
                totalProducts: 156,
            });
            setLoading(false);
        }, 500);
    }, []);

    const statCards = [
        {
            title: 'Doanh thu',
            value: stats.totalRevenue.toLocaleString('vi-VN') + ' đ',
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
            title: 'Người dùng',
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
            trend: '-2.4%',
            isUp: false,
        },
    ];

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
                <p className="dashboard-subtitle">Tổng quan hệ thống</p>
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

            {/* Charts Section - Placeholder */}
            <div className="dashboard-charts">
                <div className="chart-card">
                    <h3>Doanh thu theo tháng</h3>
                    <div className="chart-placeholder">
                        <TrendingUp size={48} />
                        <p>Biểu đồ sẽ được thêm vào sau</p>
                    </div>
                </div>

                <div className="chart-card">
                    <h3>Đơn hàng gần đây</h3>
                    <div className="recent-orders">
                        <table>
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#ORD-001</td>
                                    <td>Nguyễn Văn A</td>
                                    <td>1,250,000 đ</td>
                                    <td><span className="badge badge-success">Hoàn thành</span></td>
                                </tr>
                                <tr>
                                    <td>#ORD-002</td>
                                    <td>Trần Thị B</td>
                                    <td>850,000 đ</td>
                                    <td><span className="badge badge-warning">Đang xử lý</span></td>
                                </tr>
                                <tr>
                                    <td>#ORD-003</td>
                                    <td>Lê Văn C</td>
                                    <td>2,100,000 đ</td>
                                    <td><span className="badge badge-info">Đang giao</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Dashboard;
