import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Package, User, MapPin, CreditCard, Truck } from 'lucide-react';
import OrderService from '@/services/admin/OrderService';
import './OrderDetail.css';

const OrderDetail = () => {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const [order, setOrder] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchOrderDetail();
    }, [uuid]);

    const fetchOrderDetail = async () => {
        try {
            setLoading(true);
            const response = await OrderService.getOrder(uuid);

            if (response.success && response.data) {
                setOrder(response.data);
            }
        } catch (err) {
            setError(err.message || 'Không thể tải chi tiết đơn hàng');
        } finally {
            setLoading(false);
        }
    };

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

    if (loading) {
        return (
            <div className="order-detail">
                <div className="loading-state">
                    <div className="spinner"></div>
                    <p>Đang tải dữ liệu...</p>
                </div>
            </div>
        );
    }

    if (error || !order) {
        return (
            <div className="order-detail">
                <div className="error-state">
                    <p>{error || 'Không tìm thấy đơn hàng'}</p>
                    <button onClick={() => navigate('/admin/orders')} className="btn btn-secondary">
                        Quay lại danh sách
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="order-detail">
            {/* Header */}
            <div className="detail-header">
                <button onClick={() => navigate('/admin/orders')} className="btn-back">
                    <ArrowLeft size={20} />
                    Quay lại
                </button>
                <div className="header-info">
                    <h1>Đơn hàng #{order.order_number || order.uuid?.slice(0, 8)}</h1>
                    <div className="header-meta">
                        {getStatusBadge(order.status)}
                        <span className="date">
                            {order.created_at ? new Date(order.created_at).toLocaleString('vi-VN') : '-'}
                        </span>
                    </div>
                </div>
            </div>

            <div className="detail-grid">
                {/* Customer Info */}
                <div className="detail-card">
                    <div className="card-header">
                        <User size={20} />
                        <h3>Thông tin khách hàng</h3>
                    </div>
                    <div className="card-body">
                        <div className="info-row">
                            <span className="label">Tên:</span>
                            <span className="value">{order.user?.name || order.customer_name || '-'}</span>
                        </div>
                        <div className="info-row">
                            <span className="label">Email:</span>
                            <span className="value">{order.user?.email || order.customer_email || '-'}</span>
                        </div>
                        <div className="info-row">
                            <span className="label">SĐT:</span>
                            <span className="value">{order.customer_phone || '-'}</span>
                        </div>
                    </div>
                </div>

                {/* Shipping Address */}
                <div className="detail-card">
                    <div className="card-header">
                        <MapPin size={20} />
                        <h3>Địa chỉ giao hàng</h3>
                    </div>
                    <div className="card-body">
                        <p>{order.shipping_address || 'Chưa có thông tin'}</p>
                    </div>
                </div>

                {/* Payment Info */}
                <div className="detail-card">
                    <div className="card-header">
                        <CreditCard size={20} />
                        <h3>Thanh toán</h3>
                    </div>
                    <div className="card-body">
                        <div className="info-row">
                            <span className="label">Phương thức:</span>
                            <span className="value">{order.payment_method || 'COD'}</span>
                        </div>
                        <div className="info-row">
                            <span className="label">Trạng thái:</span>
                            <span className="value">
                                {order.payment_status === 'paid' ? (
                                    <span className="badge badge-success">Đã thanh toán</span>
                                ) : (
                                    <span className="badge badge-warning">Chưa thanh toán</span>
                                )}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Shipping Info */}
                <div className="detail-card">
                    <div className="card-header">
                        <Truck size={20} />
                        <h3>Vận chuyển</h3>
                    </div>
                    <div className="card-body">
                        <div className="info-row">
                            <span className="label">Đơn vị:</span>
                            <span className="value">{order.shipping_method || '-'}</span>
                        </div>
                        <div className="info-row">
                            <span className="label">Phí ship:</span>
                            <span className="value">
                                {order.shipping_fee?.toLocaleString('vi-VN') || '0'} đ
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Order Items */}
            <div className="detail-card full-width">
                <div className="card-header">
                    <Package size={20} />
                    <h3>Sản phẩm</h3>
                </div>
                <div className="card-body">
                    <table className="items-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th className="text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            {order.items?.map((item, index) => (
                                <tr key={index}>
                                    <td>
                                        <strong>{item.product_name || item.name}</strong>
                                        {item.variant_name && (
                                            <div className="variant-info">{item.variant_name}</div>
                                        )}
                                    </td>
                                    <td>{item.unit_price_formatted || (item.unit_price?.toLocaleString('vi-VN') + ' đ')}</td>
                                    <td>{item.quantity}</td>
                                    <td className="text-right">
                                        <strong>{item.subtotal_formatted || (item.subtotal?.toLocaleString('vi-VN') + ' đ')}</strong>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    {/* Summary */}
                    <div className="order-summary">
                        <div className="summary-row">
                            <span>Tạm tính:</span>
                            <span>{order.total_formatted || (order.total_amount?.toLocaleString('vi-VN') + ' đ')}</span>
                        </div>
                        <div className="summary-row">
                            <span>Phí vận chuyển:</span>
                            <span>{order.shipping_fee?.toLocaleString('vi-VN') || '0'} đ</span>
                        </div>
                        {order.voucher_discount > 0 && (
                            <div className="summary-row discount">
                                <span>Giảm giá:</span>
                                <span>-{order.voucher_discount?.toLocaleString('vi-VN')} đ</span>
                            </div>
                        )}
                        <div className="summary-row total">
                            <span>Tổng cộng:</span>
                            <span>{order.total_formatted || (order.total_amount?.toLocaleString('vi-VN') + ' đ')}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default OrderDetail;
