import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    ArrowLeft, MapPin, Phone, User, CreditCard, Truck,
    CheckCircle, ShoppingBag, AlertCircle, Package
} from 'lucide-react';
import CartService from '@/services/CartService';
import OrderService from '@/services/OrderService';
import './CheckoutPage.css';

const CheckoutPage = () => {
    const navigate = useNavigate();
    const [cart, setCart] = useState(null);
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [success, setSuccess] = useState(false);
    const [error, setError] = useState(null);

    const [formData, setFormData] = useState({
        shipping_name: '',
        shipping_phone: '',
        shipping_address: '',
        shipping_city: '',
        shipping_note: '',
        payment_method: 'cod'
    });

    useEffect(() => {
        fetchCart();
    }, []);

    const fetchCart = async () => {
        try {
            setLoading(true);
            const data = await CartService.getCart();
            if (!data || !data.items || data.items.length === 0) {
                navigate('/customer/cart');
                return;
            }
            setCart(data);
        } catch (err) {
            setError('Không thể tải giỏ hàng');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const validateForm = () => {
        if (!formData.shipping_name.trim()) return 'Vui lòng nhập họ tên';
        if (!formData.shipping_phone.trim()) return 'Vui lòng nhập số điện thoại';
        if (!formData.shipping_address.trim()) return 'Vui lòng nhập địa chỉ';
        if (!formData.shipping_city.trim()) return 'Vui lòng nhập thành phố';
        return null;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const validationError = validateForm();
        if (validationError) {
            setError(validationError);
            return;
        }

        setSubmitting(true);
        setError(null);

        try {
            const response = await OrderService.checkout({
                ...formData,
                items: cart.items.map(item => ({
                    variant_uuid: item.variant?.uuid || item.variant_uuid,
                    quantity: item.quantity
                }))
            });

            setSuccess(true);
            setTimeout(() => {
                navigate('/customer/orders');
            }, 2000);
        } catch (err) {
            setError(err.message || 'Đặt hàng thất bại. Vui lòng thử lại.');
        } finally {
            setSubmitting(false);
        }
    };

    const formatPrice = (price) => {
        return parseInt(price || 0).toLocaleString('vi-VN') + ' đ';
    };

    if (loading) {
        return (
            <div className="checkout-loading">
                <div className="spinner"></div>
                <p>Đang tải thông tin...</p>
            </div>
        );
    }

    if (success) {
        return (
            <div className="checkout-success">
                <div className="success-icon">
                    <CheckCircle size={64} />
                </div>
                <h2>Đặt hàng thành công!</h2>
                <p>Cảm ơn bạn đã mua hàng. Đơn hàng của bạn đang được xử lý.</p>
                <button onClick={() => navigate('/customer/orders')} className="btn btn-primary">
                    Xem đơn hàng
                </button>
            </div>
        );
    }

    return (
        <div className="checkout-page">
            <div className="checkout-container">
                {/* Header */}
                <div className="checkout-header">
                    <button onClick={() => navigate('/customer/cart')} className="back-btn">
                        <ArrowLeft size={20} />
                        Quay lại giỏ hàng
                    </button>
                    <h1>
                        <ShoppingBag size={28} />
                        Thanh toán
                    </h1>
                </div>

                {error && (
                    <div className="alert alert-error">
                        <AlertCircle size={20} />
                        <span>{error}</span>
                    </div>
                )}

                <div className="checkout-content">
                    {/* Form Section */}
                    <div className="checkout-form-section">
                        <form onSubmit={handleSubmit}>
                            {/* Shipping Info */}
                            <div className="form-card">
                                <div className="card-header">
                                    <Truck size={20} />
                                    <h3>Thông tin giao hàng</h3>
                                </div>
                                <div className="card-body">
                                    <div className="form-row">
                                        <div className="form-group">
                                            <label className="form-label">
                                                <User size={16} />
                                                Họ và tên *
                                            </label>
                                            <input
                                                type="text"
                                                name="shipping_name"
                                                value={formData.shipping_name}
                                                onChange={handleChange}
                                                className="form-input"
                                                placeholder="Nguyễn Văn A"
                                                required
                                            />
                                        </div>
                                        <div className="form-group">
                                            <label className="form-label">
                                                <Phone size={16} />
                                                Số điện thoại *
                                            </label>
                                            <input
                                                type="tel"
                                                name="shipping_phone"
                                                value={formData.shipping_phone}
                                                onChange={handleChange}
                                                className="form-input"
                                                placeholder="0912 345 678"
                                                required
                                            />
                                        </div>
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label">
                                            <MapPin size={16} />
                                            Địa chỉ *
                                        </label>
                                        <input
                                            type="text"
                                            name="shipping_address"
                                            value={formData.shipping_address}
                                            onChange={handleChange}
                                            className="form-input"
                                            placeholder="Số nhà, đường, phường/xã"
                                            required
                                        />
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label">
                                            <MapPin size={16} />
                                            Thành phố / Tỉnh *
                                        </label>
                                        <input
                                            type="text"
                                            name="shipping_city"
                                            value={formData.shipping_city}
                                            onChange={handleChange}
                                            className="form-input"
                                            placeholder="Hồ Chí Minh"
                                            required
                                        />
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label">Ghi chú</label>
                                        <textarea
                                            name="shipping_note"
                                            value={formData.shipping_note}
                                            onChange={handleChange}
                                            className="form-textarea"
                                            placeholder="Ghi chú cho người giao hàng..."
                                            rows="3"
                                        />
                                    </div>
                                </div>
                            </div>

                            {/* Payment Method */}
                            <div className="form-card">
                                <div className="card-header">
                                    <CreditCard size={20} />
                                    <h3>Phương thức thanh toán</h3>
                                </div>
                                <div className="card-body">
                                    <div className="payment-options">
                                        <label className={`payment-option ${formData.payment_method === 'cod' ? 'active' : ''}`}>
                                            <input
                                                type="radio"
                                                name="payment_method"
                                                value="cod"
                                                checked={formData.payment_method === 'cod'}
                                                onChange={handleChange}
                                            />
                                            <div className="option-content">
                                                <Package size={24} />
                                                <div>
                                                    <strong>Thanh toán khi nhận hàng (COD)</strong>
                                                    <span>Thanh toán bằng tiền mặt khi nhận hàng</span>
                                                </div>
                                            </div>
                                        </label>

                                        <label className={`payment-option ${formData.payment_method === 'banking' ? 'active' : ''}`}>
                                            <input
                                                type="radio"
                                                name="payment_method"
                                                value="banking"
                                                checked={formData.payment_method === 'banking'}
                                                onChange={handleChange}
                                            />
                                            <div className="option-content">
                                                <CreditCard size={24} />
                                                <div>
                                                    <strong>Chuyển khoản ngân hàng</strong>
                                                    <span>Thanh toán qua internet banking</span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    {/* Order Summary */}
                    <div className="checkout-summary">
                        <div className="summary-card">
                            <div className="card-header">
                                <ShoppingBag size={20} />
                                <h3>Đơn hàng của bạn</h3>
                            </div>
                            <div className="card-body">
                                <div className="order-items">
                                    {cart?.items?.map((item, index) => (
                                        <div key={index} className="order-item">
                                            <div className="item-image">
                                                <Package size={24} />
                                            </div>
                                            <div className="item-info">
                                                <h4>{item.variant?.product?.name || item.product_name}</h4>
                                                <span className="item-variant">
                                                    {item.variant?.sku || 'SKU'}
                                                </span>
                                                <span className="item-qty">x{item.quantity}</span>
                                            </div>
                                            <div className="item-price">
                                                {formatPrice(item.subtotal || (item.price * item.quantity))}
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                <div className="order-totals">
                                    <div className="total-row">
                                        <span>Tạm tính</span>
                                        <span>{formatPrice(cart?.subtotal)}</span>
                                    </div>
                                    <div className="total-row">
                                        <span>Phí vận chuyển</span>
                                        <span className="free">Miễn phí</span>
                                    </div>
                                    <div className="total-row grand-total">
                                        <span>Tổng cộng</span>
                                        <span>{formatPrice(cart?.total || cart?.subtotal)}</span>
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    onClick={handleSubmit}
                                    className="btn btn-checkout"
                                    disabled={submitting}
                                >
                                    {submitting ? (
                                        <>
                                            <div className="spinner-small"></div>
                                            Đang xử lý...
                                        </>
                                    ) : (
                                        <>
                                            <CheckCircle size={20} />
                                            Đặt hàng ngay
                                        </>
                                    )}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default CheckoutPage;
