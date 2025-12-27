import React, { useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, CreditCard, Calendar, Hash, DollarSign, Activity, Database } from 'lucide-react';
import { usePayment } from '@/hooks/admin/usePayment';
import PaymentActions from '@/components/admin/payments/PaymentActions'; 
import './PaymentDetail.css';

const PaymentDetail = () => {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const { payment, loading, error, fetchPaymentDetail } = usePayment();

    useEffect(() => {
        fetchPaymentDetail(uuid);
    }, [uuid]);

    if (loading) return <div className="pd-loading-state"><div className="pd-spinner"></div></div>;
    if (error || !payment) return <div className="pd-error-state">{error || 'Payment not found'}</div>;

    return (
        <div className="pd-detail-container">
            {/* Header - Đã bỏ dòng ID phụ */}
            <div className="pd-detail-header">
                <div className="pd-header-left">
                    <button onClick={() => navigate('/admin/payments')} className="pd-btn-back">
                        <ArrowLeft size={18} />
                    </button>
                    <div>
                        <h1 className="pd-detail-title">Transaction Details</h1>
                        {/* Đã xóa dòng ID tại đây */}
                    </div>
                </div>
                <div className="pd-action-wrapper">
                    <PaymentActions payment={payment} onUpdate={() => fetchPaymentDetail(uuid)} />
                </div>
            </div>

            {/* SECTION 1: MAIN INFO CARD */}
            <div className="pd-card pd-main-info-section">
                <div className="pd-card-header">
                    <h3><Activity size={16} /> Payment Overview</h3>
                    <span className={`pd-status-badge pd-badge-${payment.status}`}>
                        {payment.status_label || payment.status}
                    </span>
                </div>
                <div className="pd-card-body">
                    <div className="pd-overview-grid">
                        <div className="pd-overview-item">
                            <label>Amount Paid</label>
                            <div className="pd-value-large">{payment.amount_format}</div>
                        </div>
                        <div className="pd-overview-item">
                            <label>Payment Method</label>
                            <div className="pd-method-box">{(payment.method || 'COD').toUpperCase()}</div>
                        </div>
                        <div className="pd-overview-item">
                            <label>Order Reference</label>
                            <div className="pd-order-ref">#{payment.order?.code}</div>
                        </div>
                    </div>
                </div>
            </div>

            {/* SECTION 2: INFO GRID */}
            <div className="pd-info-grid">
                {/* 1. Timeline */}
                <div className="pd-card">
                    <div className="pd-card-header"><h3><Calendar size={16} /> Timeline</h3></div>
                    <div className="pd-card-body">
                        <div className="pd-info-row">
                            <span>Created At</span>
                            <span>{payment.created_at}</span>
                        </div>
                        <div className="pd-info-row">
                            <span>Paid Date</span>
                            <span className="pd-text-success">{payment.paid_at || 'Waiting for payment'}</span>
                        </div>
                        {/* Đưa ID vào đây nếu bạn vẫn muốn quản trị viên xem được khi cần thiết */}
                        <div className="pd-info-row">
                            <span>System ID</span>
                            <span style={{ fontSize: '10px', color: '#9ca3af' }}>{payment.uuid}</span>
                        </div>
                    </div>
                </div>

                {/* 2. Order Details */}
                <div className="pd-card">
                    <div className="pd-card-header"><h3><Hash size={16} /> Order Details</h3></div>
                    <div className="pd-card-body">
                        <div className="pd-info-row">
                            <span>Order Number</span>
                            <span className="pd-text-gold font-bold">{payment.order?.order_number}</span>
                        </div>
                        <div className="pd-info-row">
                            <span>Currency</span>
                            <span>VND</span>
                        </div>
                    </div>
                </div>
            </div>
            
            {/* SECTION 3: RAW DATA */}
            {payment.payment_data && (
                <div className="pd-card pd-mt-4">
                    <div className="pd-card-header">
                        <h3><Database size={16} /> Gateway Metadata</h3>
                    </div>
                    <div className="pd-card-body">
                        <pre className="pd-raw-json">{JSON.stringify(payment.payment_data, null, 2)}</pre>
                    </div>
                </div>
            )}
        </div>
    );
};

export default PaymentDetail;