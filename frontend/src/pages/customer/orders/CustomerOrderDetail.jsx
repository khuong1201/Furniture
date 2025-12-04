import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import {
    ArrowLeft, Package, Clock, CheckCircle, Truck, XCircle,
    MapPin, Phone, User, Calendar, CreditCard, AlertCircle
} from 'lucide-react';
import OrderService from '@/services/OrderService';
import './OrderHistory.css';

const CustomerOrderDetail = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    const [order, setOrder] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [cancelling, setCancelling] = useState(false);

    useEffect(() => {
        fetchOrder();
    }, [uuid]);

    const fetchOrder = async () => {
        try {
            setLoading(true);
            const response = await OrderService.getOrderDetail(uuid);
            setOrder(response.data);
        } catch (err) {
            setError('Không thể tải thông tin đơn hàng');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const handleCancel = async () => {
        if (!window.confirm('Bạn có chắc muốn hủy đơn hàng này?')) return;

        try {
            setCancelling(true);
            await OrderService.cancelOrder(uuid);
            fetchOrder();
        } catch (err) {
            alert('Không thể hủy đơn hàng: ' + err.message);
        } finally {
            setCancelling(false);
        }
    };

    const getStatusInfo = (status) => {
        const statusMap = {
            pending: { label: 'Chờ xác nhận', icon: Clock, color: 'warning', step: 1 },
            confirmed: { label: 'Đã xác nhận', icon: CheckCircle, color: 'info', step: 2 },
            processing: { label: 'Đang xử lý', icon: Package, color: 'info', step: 2 },
            shipped: { label: 'Đang giao', icon: Truck, color: 'primary', step: 3 },
            delivered: { label: 'Đã giao', icon: CheckCircle, color: 'success', step: 4 },
            completed: { label: 'Hoàn thành', icon: CheckCircle, color: 'success', step: 4 },
            cancelled: { label: 'Đã hủy', icon: XCircle, color: 'danger', step: 0 },
        };
        return statusMap[status] || { label: status, icon: Package, color: 'default', step: 0 };
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

    if (loading) {
        return (
            <div className="order-history-loading">
                <div className="spinner"></div>
                <p>Đang tải thông tin đơn hàng...</p>
            </div>
        );
    }

    if (error) {
        return (
            <div className="order-history-page">
                <div className="error-state">
                    <AlertCircle size={48} />
                    <p>{error}</p>
                    <button onClick={() => navigate('/customer/orders')} className="btn btn-secondary">
                        Quay lại
                    </button>
                </div>
            </div>
        );
    }

    const statusInfo = getStatusInfo(order?.status);
    const StatusIcon = statusInfo.icon;

    return (
        <div className="order-history-page">
            <div className="order-detail-container">
                {/* Header */}
                <div className="detail-header">
                    <button onClick={() => navigate('/customer/orders')} className="back-btn">
                        <ArrowLeft size={20} />
                        Quay lại
                    </button>
                    <div className="header-info">
                        <h1>Đơn hàng #{order?.order_number || order?.uuid?.slice(0, 8)}</h1>
                        <div className={`order-status status-${statusInfo.color}`}>
                            <StatusIcon size={16} />
                            {statusInfo.label}
                        </div>
                    </div>
                </div>

                {/* Timeline */}
                {order?.status !== 'cancelled' && (
                    <div className="order-timeline">
                        {['Đặt hàng', 'Xác nhận', 'Đang giao', 'Hoàn thành'].map((step, index) => (
                            <div
                                key={index}
                                className={`timeline-step ${index + 1 <= statusInfo.step ? 'active' : ''}`}
                            >
                                <div className="step-dot"></div>
                                <span>{step}</span>
                            </div>
                        ))}
                    </div>
                )}

                <div className="detail-grid">
                    {/* Order Info */}
                    <div className="detail-card">
                        <div className="card-header">
                            <Package size={20} />
                            <h3>Thông tin đơn hàng</h3>
                        </div>
                        <div className="card-body">
                            <div className="info-row">
                                <span className="label">Mã đơn</span>
                                <span className="value">{order?.order_number || order?.uuid}</span>
                            </div>
                            <div className="info-row">
                                <span className="label">Ngày đặt</span>
                                <span className="value">{formatDate(order?.created_at)}</span>
                            </div>
                            <div className="info-row">
                                <span className="label">Thanh toán</span>
                                <span className="value">
                                    {order?.payment_method === 'cod' ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản'}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Shipping Info */}
                    <div className="detail-card">
                        <div className="card-header">
                            <Truck size={20} />
                            <h3>Thông tin giao hàng</h3>
                        </div>
                        <div className="card-body">
                            <div className="info-row">
                                <User size={16} />
                                <span>{order?.shipping_name}</span>
                            </div>
                            <div className="info-row">
                                <Phone size={16} />
                                <span>{order?.shipping_phone}</span>
                            </div>
                            <div className="info-row">
                                <MapPin size={16} />
                                <span>{order?.shipping_address}, {order?.shipping_city}</span>
                            </div>
                            {order?.shipping_note && (
                                <div className="shipping-note">
                                    <strong>Ghi chú:</strong> {order.shipping_note}
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Products */}
                <div className="detail-card full-width">
                    <div className="card-header">
                        <Package size={20} />
                        <h3>Sản phẩm đã đặt</h3>
                    </div>
                    <div className="card-body">
                        <div className="order-items-list">
                            {order?.items?.map((item, index) => (
                                <div key={index} className="order-item">
                                    <div className="item-image">
                                        <Package size={24} />
                                    </div>
                                    <div className="item-details">
                                        <h4>{item.product_name || item.variant?.product?.name}</h4>
                                        <span className="item-sku">{item.sku || item.variant?.sku}</span>
                                    </div>
                                    <div className="item-qty">x{item.quantity}</div>
                                    <div className="item-price">{formatPrice(item.subtotal || item.price * item.quantity)}</div>
                                </div>
                            ))}
                        </div>

                        <div className="order-summary">
                            <div className="summary-row">
                                <span>Tạm tính</span>
                                <span>{formatPrice(order?.subtotal)}</span>
                            </div>
                            <div className="summary-row">
                                <span>Phí vận chuyển</span>
                                <span>{order?.shipping_fee > 0 ? formatPrice(order.shipping_fee) : 'Miễn phí'}</span>
                            </div>
                            {order?.discount > 0 && (
                                <div className="summary-row discount">
                                    <span>Giảm giá</span>
                                    <span>-{formatPrice(order.discount)}</span>
                                </div>
                            )}
                            <div className="summary-row total">
                                <span>Tổng cộng</span>
                                <span>{formatPrice(order?.total)}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Actions */}
                {order?.status === 'pending' && (
                    <div className="detail-actions">
                        <button
                            onClick={handleCancel}
                            className="btn btn-cancel"
                            disabled={cancelling}
                        >
                            {cancelling ? 'Đang hủy...' : 'Hủy đơn hàng'}
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
};

export default CustomerOrderDetail;
