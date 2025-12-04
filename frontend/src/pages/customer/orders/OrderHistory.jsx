import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    Package, Clock, CheckCircle, Truck, XCircle, Eye,
    ShoppingBag, Calendar, ChevronRight, RefreshCw
} from 'lucide-react';
import OrderService from '@/services/OrderService';
import './OrderHistory.css';

const OrderHistory = () => {
    const navigate = useNavigate();
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [filter, setFilter] = useState('all');

    useEffect(() => {
        fetchOrders();
    }, []);

    const fetchOrders = async () => {
        try {
            setLoading(true);
            const response = await OrderService.getMyOrders();
            setOrders(response.data || []);
        } catch (err) {
            setError('Không thể tải lịch sử đơn hàng');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const getStatusInfo = (status) => {
        const statusMap = {
            pending: { label: 'Chờ xác nhận', icon: Clock, color: 'warning' },
            confirmed: { label: 'Đã xác nhận', icon: CheckCircle, color: 'info' },
            processing: { label: 'Đang xử lý', icon: Package, color: 'info' },
            shipped: { label: 'Đang giao', icon: Truck, color: 'primary' },
            delivered: { label: 'Đã giao', icon: CheckCircle, color: 'success' },
            completed: { label: 'Hoàn thành', icon: CheckCircle, color: 'success' },
            cancelled: { label: 'Đã hủy', icon: XCircle, color: 'danger' },
        };
        return statusMap[status] || { label: status, icon: Package, color: 'default' };
    };

    const formatPrice = (price) => {
        return parseInt(price || 0).toLocaleString('vi-VN') + ' đ';
    };

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const filteredOrders = orders.filter(order => {
        if (filter === 'all') return true;
        return order.status === filter;
    });

    if (loading) {
        return (
            <div className="order-history-loading">
                <div className="spinner"></div>
                <p>Đang tải đơn hàng...</p>
            </div>
        );
    }

    return (
        <div className="order-history-page">
            <div className="order-history-container">
                {/* Header */}
                <div className="history-header">
                    <div className="header-title">
                        <ShoppingBag size={28} />
                        <div>
                            <h1>Đơn hàng của tôi</h1>
                            <p>{orders.length} đơn hàng</p>
                        </div>
                    </div>
                    <button onClick={fetchOrders} className="refresh-btn">
                        <RefreshCw size={18} />
                        Làm mới
                    </button>
                </div>

                {/* Filter */}
                <div className="filter-tabs">
                    {[
                        { value: 'all', label: 'Tất cả' },
                        { value: 'pending', label: 'Chờ xác nhận' },
                        { value: 'confirmed', label: 'Đã xác nhận' },
                        { value: 'shipped', label: 'Đang giao' },
                        { value: 'completed', label: 'Hoàn thành' },
                        { value: 'cancelled', label: 'Đã hủy' },
                    ].map(tab => (
                        <button
                            key={tab.value}
                            className={`filter-tab ${filter === tab.value ? 'active' : ''}`}
                            onClick={() => setFilter(tab.value)}
                        >
                            {tab.label}
                        </button>
                    ))}
                </div>

                {/* Orders List */}
                {error ? (
                    <div className="error-state">
                        <p>{error}</p>
                        <button onClick={fetchOrders} className="btn btn-secondary">
                            Thử lại
                        </button>
                    </div>
                ) : filteredOrders.length === 0 ? (
                    <div className="empty-state">
                        <Package size={64} />
                        <h3>Chưa có đơn hàng nào</h3>
                        <p>Hãy bắt đầu mua sắm để thấy đơn hàng của bạn!</p>
                        <button onClick={() => navigate('/customer')} className="btn btn-primary">
                            Tiếp tục mua sắm
                        </button>
                    </div>
                ) : (
                    <div className="orders-list">
                        {filteredOrders.map(order => {
                            const statusInfo = getStatusInfo(order.status);
                            const StatusIcon = statusInfo.icon;

                            return (
                                <div key={order.uuid} className="order-card">
                                    <div className="order-header">
                                        <div className="order-id">
                                            <span className="label">Mã đơn:</span>
                                            <span className="value">#{order.order_number || order.uuid?.slice(0, 8)}</span>
                                        </div>
                                        <div className={`order-status status-${statusInfo.color}`}>
                                            <StatusIcon size={16} />
                                            {statusInfo.label}
                                        </div>
                                    </div>

                                    <div className="order-body">
                                        <div className="order-info">
                                            <div className="info-item">
                                                <Calendar size={16} />
                                                <span>{formatDate(order.created_at)}</span>
                                            </div>
                                            <div className="info-item">
                                                <Package size={16} />
                                                <span>{order.items_count || order.items?.length || 0} sản phẩm</span>
                                            </div>
                                        </div>

                                        <div className="order-total">
                                            <span className="label">Tổng tiền:</span>
                                            <span className="value">{formatPrice(order.total)}</span>
                                        </div>
                                    </div>

                                    <div className="order-footer">
                                        <button
                                            onClick={() => navigate(`/customer/orders/${order.uuid}`)}
                                            className="btn btn-view"
                                        >
                                            <Eye size={16} />
                                            Xem chi tiết
                                            <ChevronRight size={16} />
                                        </button>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}
            </div>
        </div>
    );
};

export default OrderHistory;
