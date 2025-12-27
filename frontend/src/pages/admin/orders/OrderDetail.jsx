import React, { useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, User, MapPin, CreditCard, Package } from 'lucide-react';
import { useOrder } from '@/hooks/admin/useOrder';
import OrderActions from '@/components/admin/orders/OrderActions';
import './OrderDetail.css';

const OrderDetail = () => {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const { order, loading, error, fetchOrderDetail } = useOrder();

    useEffect(() => {
        fetchOrderDetail(uuid);
    }, [uuid]);

    // Helper: Parse chuỗi variant text "Color: Red | Size: L"
    const renderVariantAttributes = (variantText) => {
        if (!variantText) return null;
        const attributes = variantText.split('|').map(s => s.trim()).filter(s => s);
        
        return attributes.map((attr, idx) => {
            const parts = attr.split(':');
            const key = parts[0]?.trim();
            const value = parts[1]?.trim();

            if (value) {
                return (
                    <span key={idx} className="attr-tag">
                        <span className="attr-key">{key}</span> 
                        <span className="attr-val">{value}</span>
                    </span>
                );
            }
            return <span key={idx} className="attr-tag">{attr}</span>;
        });
    };

    if (loading) return <div className="loading-state"><div className="spinner"></div>Loading order details...</div>;
    if (error || !order) return <div className="error-state">{error || 'Order not found'}</div>;

    // --- SAFETY CHECK ---
    // Đảm bảo object con tồn tại để tránh crash
    const customer = order.customer || {};
    const shipping = order.shipping_info || {};
    const amounts = order.amounts || {};
    const dates = order.dates || {};

    return (
        <div className="order-detail-container">
            {/* Header */}
            <div className="detail-header">
                <div className="header-left">
                    <button onClick={() => navigate('/admin/orders')} className="btn-back" title="Back to list">
                        <ArrowLeft size={18} />
                    </button>
                    <div>
                        <h1 className="detail-title">Order #{order.code}</h1>
                        <span className="detail-date">Placed on {dates.created_at}</span>
                    </div>
                </div>
                
                <div className="action-buttons-wrapper">
                    <OrderActions 
                        order={order} 
                        onUpdate={() => fetchOrderDetail(uuid)} 
                        showDetailBtn={false}
                    />
                </div>
            </div>

            {/* SECTION 1: ITEMS TABLE */}
            <div className="card section-items">
                <div className="card-header">
                    <h3><Package size={16} /> Order Items</h3>
                    <span className={`status-badge badge-${order.status}`}>
                        {order.status_label || order.status}
                    </span>
                </div>
                <div className="table-responsive">
                    <table className="items-table">
                        <thead>
                            <tr>
                                <th style={{ width: '45%' }}>Product</th>
                                <th style={{ width: '15%' }}>Price</th>
                                <th style={{ width: '15%' }} className="text-center">Qty</th>
                                <th style={{ width: '25%' }} className="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {order.items?.map((item, idx) => (
                                <tr key={item.uuid || idx}>
                                    <td>
                                        <div className="product-cell">
                                            <img 
                                                src={item.image || 'https://placehold.co/48'} 
                                                alt="" 
                                                className="product-thumb"
                                            />
                                            <div className="product-info">
                                                <div className="product-name">{item.product_name}</div>
                                                <div className="product-attributes">
                                                    {renderVariantAttributes(item.variant_text)}
                                                </div>
                                                {item.sku && (
                                                    <div className="product-sku">SKU: {item.sku}</div>
                                                )}
                                            </div>
                                        </div>
                                    </td>
                                    {/* API đã format sẵn tiền (VD: "19.160.000 VND"), hiển thị trực tiếp */}
                                    <td className="text-price">{item.unit_price}</td>
                                    <td className="text-center font-medium">x{item.quantity}</td>
                                    <td className="text-right font-bold text-price-total">{item.total}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                
                {/* SUMMARY SECTION */}
                <div className="order-summary">
                    <div className="summary-content">
                        <div className="summary-row">
                            <span>Subtotal</span> 
                            <span>{amounts.subtotal}</span>
                        </div>
                        <div className="summary-row">
                            <span>Shipping Fee</span> 
                            <span>{amounts.shipping_fee}</span>
                        </div>
                        {/* Kiểm tra voucher_discount khác "0 VND" hoặc logic số */}
                        {amounts.voucher_discount && amounts.voucher_discount !== "0 VND" && (
                            <div className="summary-row discount">
                                <span>Discount</span> 
                                <span>-{amounts.voucher_discount}</span>
                            </div>
                        )}
                        <div className="summary-row total">
                            <span>Grand Total</span> 
                            <span>{amounts.grand_total}</span>
                        </div>
                    </div>
                </div>
            </div>

            {/* SECTION 2: INFO GRID (3 COLUMNS) */}
            <div className="info-grid">
                {/* 1. Customer (Người mua) */}
                <div className="card">
                    <div className="card-header"><h3><User size={16} /> Customer (Buyer)</h3></div>
                    <div className="card-body">
                        <p className="info-name">{customer.name || 'N/A'}</p>
                        <p className="info-text">{customer.email}</p>
                        {/* API OrderResource không trả về phone customer trong object customer, 
                            nhưng có thể lấy từ shipping_info nếu cần */}
                    </div>
                </div>

                {/* 2. Shipping Address (Người nhận) */}
                <div className="card">
                    <div className="card-header"><h3><MapPin size={16} /> Shipping Address</h3></div>
                    <div className="card-body">
                        {/* Ưu tiên lấy tên người nhận thực tế */}
                        <p className="info-name">
                            {shipping.name || shipping.details?.full_name || customer.name}
                        </p>
                        <p className="info-text">
                            {shipping.phone || shipping.details?.phone || 'No phone'}
                        </p>
                        <div className="address-box">
                            {shipping.full_address}
                        </div>
                        
                        {order.notes && (
                            <div className="note-box mt-2">
                                <strong>Note:</strong> {order.notes}
                            </div>
                        )}
                    </div>
                </div>

                {/* 3. Payment & Shipping Status */}
                <div className="card">
                    <div className="card-header"><h3><CreditCard size={16} /> Status</h3></div>
                    <div className="card-body">
                        <div className="info-row">
                            <span>Payment</span>
                            <span className={`payment-badge ${order.payment_status}`}>
                                {order.payment_status}
                            </span>
                        </div>
                        <div className="info-row mt-2">
                            <span>Shipping</span>
                            <span className={`status-badge badge-${order.shipping_status}`}>
                                {order.shipping_status}
                            </span>
                        </div>
                        <div className="info-row mt-2">
                            <span>Method</span>
                            <span className="font-medium">COD</span>
                        </div>
                        <div className="info-row mt-2">
                            <span>Currency</span>
                            <span className="font-medium">{order.currency_code}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default OrderDetail;