import React, { useEffect, useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { useOrder } from '@/hooks/useOrder';
import { usePayment } from '@/hooks/usePayment'; // <--- 1. Import hook Payment
import { MapPin, Store, MessageCircle, ChevronLeft, Bell, ShoppingCart, User } from 'lucide-react';
import { AiOutlineLoading3Quarters, AiOutlineWarning } from "react-icons/ai";
import styles from './OrderDetail.module.css';

const OrderDetail = () => {
  const { uuid } = useParams(); 
  const navigate = useNavigate(); // Dùng để chuyển trang nếu cần

  // Hook Order
  const { getOrderDetail, orderDetail, loading: loadingOrder, error, cancelOrder } = useOrder();
  
  // Hook Payment
  // Đổi tên loading thành isPaying để tránh trùng lặp với loadingOrder
  const { initiatePayment, loading: isPaying } = usePayment(); 

  // State cho phương thức thanh toán được chọn
  const [selectedMethod, setSelectedMethod] = useState('cod'); 

  // --- Init Data ---
  useEffect(() => {
    if (uuid) {
      getOrderDetail(uuid);
    }
  }, [uuid, getOrderDetail]);

  // --- Handlers ---

  // 1. Xử lý khi click "Place Order" / "Thanh toán ngay"
  const handlePlaceOrder = async () => {
    if (!uuid) return;

    try {
        // Gọi hàm từ hook usePayment
        const result = await initiatePayment(uuid, selectedMethod);

        // Kiểm tra kết quả trả về
        // Trường hợp 1: Thanh toán Online (VNPay, Momo) -> Có payment_url
        if (result && result.payment_url) {
            window.location.href = result.payment_url; // Redirect sang cổng thanh toán
        } 
        // Trường hợp 2: COD hoặc thành công ngay lập tức
        else {
            alert('Đặt hàng / Thanh toán thành công!');
            return navigate('/customer')
        }
    } catch (err) {
        // Lỗi đã được log trong hook, ở đây có thể hiện toast thông báo
        alert('Có lỗi xảy ra khi khởi tạo thanh toán: ' + err.message);
    }
  };

  const handleCancelOrder = async () => {
    if (window.confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) {
        const success = await cancelOrder(uuid);
        if (success) getOrderDetail(uuid);
    }
  };

  // --- Helpers ---
  const formatVariant = (sku) => sku ? `SKU: ${sku}` : '';

  // --- Loading / Error Checks ---
  if (loadingOrder) {
    return <div className={styles['loading-state']}><AiOutlineLoading3Quarters className={styles.spin} /> Đang tải...</div>;
  }

  if (error) {
    return <div className={styles['error-state']}><AiOutlineWarning /> {error}</div>;
  }

  if (!orderDetail) {
    return <div className={styles['error-state']}>Không tìm thấy đơn hàng.</div>;
  }

  // --- Destructuring Data ---
  const { 
    shipping_address, 
    items, 
    total_formatted, 
    status, 
    payment_status
  } = orderDetail;

  const shippingFeeFormatted = "43.900 ₫"; 
  const merchandiseSubtotal = items.reduce((acc, item) => acc + (item.quantity * parseFloat(item.unit_price_formatted.replace(/\D/g,''))), 0);
  const merchandiseSubtotalFormatted = new Intl.NumberFormat('vi-VN').format(merchandiseSubtotal) + " VND";

  return (
    <div className={styles.container}>
        {/* TOP HEADER */}
        <div className={styles.topHeader}>
            <div className={styles.headerContent}>
                <div className={styles.logoArea}>
                    <h1 className={styles.pageTitle}>Payment</h1>
                    <div className={styles.searchBar}>
                        <input type="text" placeholder="Search" />
                    </div>
                </div>
                <div className={styles.headerIcons}>
                    <Bell size={20} />
                    <ShoppingCart size={20} />
                    <div className={styles.userMenu}><User size={20} /> Log in/ Sign up</div>
                </div>
            </div>
        </div>

      <div className={styles['content-wrapper']}>
        
        <div style={{ padding: '10px 0' }}>
            <Link to="/customer/orders" className={styles.backLink}>
                <ChevronLeft size={16} /> Trở lại đơn hàng
            </Link>
        </div>

        {/* ADDRESS SECTION */}
        <div className={`${styles.section} ${styles.addressSection}`}>
          <div className={styles.addressDecoration}></div>
          <div className={styles['section-header']}>
            <div className={styles['section-title']}>
              <MapPin size={18} className={styles['icon-marker']} /> Shipping Address
            </div>
          </div>
          
          <div className={styles['address-content']}>
            {shipping_address ? (
              <div className={styles['address-info']}>
                <span className={styles['user-name']}>{shipping_address.full_name}</span>
                <span className={styles['user-phone']}>(+84) {shipping_address.phone}</span>
                <span className={styles['address-text']}>
                  {shipping_address.street}, {shipping_address.ward}, {shipping_address.district}, {shipping_address.province}
                </span>
                {shipping_address.is_default && <span className={styles.defaultBadge}>Default</span>}
              </div>
            ) : (
              <div>Chưa có địa chỉ giao hàng.</div>
            )}
            <button className={styles.btnEdit}>Edit</button>
          </div>
        </div>

        {/* ITEMS SECTION */}
        <div className={styles.section}>
            <div className={styles['store-header']}>
                <Store size={16} /> <span>Atelier Furniture Official Store</span> 
                <span className={styles.chatBtn}><MessageCircle size={14}/> Chat</span>
            </div>

            <div className={styles['product-table-header']}>
                <div className={styles['col-product']}>Product</div>
                <div className={styles['col-price']}>Unit Price</div>
                <div className={styles['col-quantity']}>Quantity</div>
                <div className={styles['col-operation']}>Total Item</div>
            </div>

            {items?.map((item, index) => (
            <div key={index} className={styles['item-row']}>
                <div className={styles['col-product']}>
                    <div className={styles.productFlex}>
                        <img src={item.image} alt={item.product_name} className={styles['product-img']} />
                        <div className={styles.productInfo}>
                            <div className={styles['product-name']}>{item.product_name}</div>
                            <div className={styles['product-variant']}>{formatVariant(item.sku)}</div>
                        </div>
                    </div>
                </div>
                <div className={styles['col-price']}>{item.unit_price_formatted}</div>
                <div className={styles['col-quantity']}>{item.quantity}</div>
                <div className={`${styles['col-operation']} ${styles.priceText}`}>
                    {item.subtotal_formatted}
                </div>
            </div>
            ))}
        </div>

        {/* VOUCHERS */}
        <div className={styles.section}>
            <div className={styles.rowBetween}>
                <div className={styles.labelFlex}><span className={styles.iconVoucher}>🏷️</span> Vouchers</div>
                <div className={styles.linkAction}>Select or enter code {'>'}</div>
            </div>
        </div>

        {/* --- PAYMENT METHOD & SHIPPING --- */}
        <div className={styles.section}>
             <div className={styles.rowBetween}>
                <div className={styles.labelBold}>Payment Method</div>
                <div className={styles.paymentTags}>
                    {/* Nút chọn COD */}
                    <button 
                        className={`${styles.tagItem} ${selectedMethod === 'cod' ? styles.activeTag : ''}`}
                        onClick={() => setSelectedMethod('cod')}
                    >
                        Cash on Delivery
                    </button>

                    {/* Nút chọn VNPay (Thẻ ATM/Nội địa) */}
                    <button 
                        className={`${styles.tagItem} ${selectedMethod === 'vnpay' ? styles.activeTag : ''}`}
                        onClick={() => setSelectedMethod('vnpay')}
                    >
                        VNPay / NAPAS
                    </button>

                    {/* Nút chọn Momo (Ví dụ thêm) */}
                    <button 
                        className={`${styles.tagItem} ${selectedMethod === 'momo' ? styles.activeTag : ''}`}
                        onClick={() => setSelectedMethod('momo')}
                    >
                        Momo E-Wallet
                    </button>
                </div>
             </div>
             
             {/* Shipping Info Display */}
             <div className={styles.shippingInfoBlock}>
                <div className={styles.rowBetween}>
                    <div className={styles.labelBold}>Shipping Method: Fast</div>
                    <div className={styles.rowPriceAction}>
                        <span className={styles.textChange}>Change</span>
                        <span className={styles.textPrice}>{shippingFeeFormatted}</span>
                    </div>
                </div>
                <div className={styles.shipDate}>
                    🚚 Arrives between Dec 8 – Dec 10
                </div>
             </div>
        </div>

        {/* FOOTER SUMMARY & ACTION */}
        <div className={styles.summaryContainer}>
            <div className={styles.summaryRow}>
                <span>Merchandise Subtotal:</span>
                <span>{merchandiseSubtotalFormatted}</span>
            </div>
            <div className={styles.summaryRow}>
                <span>Shipping Fee:</span>
                <span>{shippingFeeFormatted}</span>
            </div>
            <div className={`${styles.summaryRow} ${styles.totalRow}`}>
                <span>Total Payment:</span>
                <span className={styles.totalAmount}>{total_formatted}</span>
            </div>
            
            <div className={styles.actionArea}>
                {status === 'pending' && (
                     <button onClick={handleCancelOrder} className={styles.btnCancel} disabled={isPaying}>
                        Cancel Order
                     </button>
                )}

                {/* NÚT PLACE ORDER GỌI API */}
                <button 
                    className={styles.btnPlaceOrder} 
                    onClick={handlePlaceOrder}
                    disabled={status === 'cancelled' || isPaying || payment_status === 'paid'}
                >
                    {isPaying ? (
                        <><AiOutlineLoading3Quarters className={styles.spin} /> Processing...</>
                    ) : (
                        status === 'cancelled' ? 'Order Cancelled' : 
                        payment_status === 'paid' ? 'Paid Successfully' : 'Place Order'
                    )}
                </button>
            </div>
        </div>

      </div>
    </div>
  );
};

export default OrderDetail;