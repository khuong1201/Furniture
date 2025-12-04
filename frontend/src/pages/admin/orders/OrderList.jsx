import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Eye, Search, Filter, Package } from 'lucide-react';
import OrderService from '@/services/customer/OrderService';
import '../products/ProductList.css';

const OrderList = () => {
    const navigate = useNavigate();
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchOrders = async () => {
        try {
            setLoading(true);
            setError(null);

            const response = await OrderService.getMyOrders();

            if (response.success && response.data) {
                setOrders(response.data);
            }
        } catch (err) {
            setError(err.message || 'Không thể tải danh sách đơn hàng');
            console.error('Error fetching orders:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchOrders();
    }, []);

    const getStatusBadge = (status) => {
        const statusMap = {
            'pending': { label: 'Chờ xử lý', class: 'badge-warning' },
            'processing': { label: 'Đang xử lý', class: 'badge-info' },
            'shipping': { label: 'Đang giao', class: 'badge-info' },
            'completed': { label: 'Hoàn thành', class: 'badge-success' },
            'cancelled': { label: 'Đã hủy', class: 'badge-danger' },
        };

        const statusInfo = statusMap[status] || { label: status, class: 'badge-secondary' };
        return <span className={`badge ${statusInfo.class}`}>{statusInfo.label}</span>;
    };

    return (
        <div className="product-list">
            <div className="page-header">
                <div>
                    <h1>Quản lý Đơn hàng</h1>
                    <p className="page-subtitle">Danh sách tất cả đơn hàng trong hệ thống</p>
                </div>
            </div>

            <div className="table-container">
                {loading ? (
                    <div className="loading-state">
                        <div className="spinner"></div>
                        <p>Đang tải dữ liệu...</p>
                    </div>
                ) : error ? (
                    <div className="error-state">
                        <p>{error}</p>
                        <button onClick={fetchOrders} className="btn btn-secondary">
                            Thử lại
                        </button>
                    </div>
                ) : orders.length === 0 ? (
                    <div className="empty-state">
                        <Package size={48} color="#9ca3af" />
                        <p>Chưa có đơn hàng nào</p>
                    </div>
                ) : (
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Thanh toán</th>
                                <th className="text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            {orders.map((order) => (
                                <tr key={order.uuid}>
                                    <td>
                                        <strong>#{order.order_number || order.uuid?.slice(0, 8)}</strong>
                                    </td>
                                    <td>{order.user?.name || order.customer_name || '-'}</td>
                                    <td>
                                        {order.created_at
                                            ? new Date(order.created_at).toLocaleDateString('vi-VN')
                                            : '-'
                                        }
                                    </td>
                                    <td>
                                        <strong>
                                            {order.total_amount?.toLocaleString('vi-VN') || '0'} đ
                                        </strong>
                                    </td>
                                    <td>{getStatusBadge(order.status)}</td>
                                    <td>
                                        {order.payment_status === 'paid' ? (
                                            <span className="badge badge-success">Đã thanh toán</span>
                                        ) : (
                                            <span className="badge badge-warning">Chưa thanh toán</span>
                                        )}
                                    </td>
                                    <td className="text-right">
                                        <div className="action-buttons">
                                            <button
                                                className="btn-icon"
                                                onClick={() => navigate(`/admin/orders/${order.uuid}`)}
                                                title="Xem chi tiết"
                                            >
                                                <Eye size={16} />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>
        </div>
    );
};

export default OrderList;
