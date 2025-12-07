import React, { useEffect, useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { useOrder } from '@/hooks/useOrder';
import { usePayment } from '@/hooks/usePayment';
import { useAddress } from '@/hooks/useAddress';
import AddressForm from '@/pages/customer/address/AddressForm.jsx'; 
import { 
    MapPin, Store, MessageCircle, ChevronLeft, Bell, ShoppingCart, User, 
    X, Plus, Check, Edit2 // Import thêm icon cho Modal
} from 'lucide-react';
import { AiOutlineLoading3Quarters, AiOutlineWarning } from "react-icons/ai";
import styles from './OrderDetail.module.css';

const OrderDetail = () => {
  const { uuid } = useParams(); 
  const navigate = useNavigate();

  // --- Hooks ---
  const { getOrderDetail, orderDetail, loading: loadingOrder, error, cancelOrder } = useOrder();
  const { initiatePayment, loading: isPaying } = usePayment();
  
  // Hook Address
  const { addresses, fetchAddresses, loading: loadingAddress } = useAddress();

  // --- States ---
  const [selectedMethod, setSelectedMethod] = useState('cod'); 
  
  // Modal State
  const [showAddressModal, setShowAddressModal] = useState(false);
  const [modalMode, setModalMode] = useState('list'); // 'list' | 'create' | 'edit'
  const [editingAddressData, setEditingAddressData] = useState(null);

  // --- Init Data ---
  useEffect(() => {
    if (uuid) getOrderDetail(uuid);
  }, [uuid, getOrderDetail]);

  // --- Handlers Order ---
  const handlePlaceOrder = async () => {
    if (!uuid) return;
    try {
        const result = await initiatePayment(uuid, selectedMethod);
        if (result && result.payment_url) {
            window.location.href = result.payment_url;
        } else {
            alert('Đặt hàng / Thanh toán thành công!');
            navigate('/customer');
        }
    } catch (err) {
        alert('Có lỗi xảy ra: ' + err.message);
    }
  };

  const handleCancelOrder = async () => {
    if (window.confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) {
        const success = await cancelOrder(uuid);
        if (success) getOrderDetail(uuid);
    }
  };

  // --- Handlers Address Modal ---
  const handleOpenAddressModal = async () => {
    setShowAddressModal(true);
    setModalMode('list'); // Mặc định mở list trước
    await fetchAddresses(); // Gọi API lấy list mới nhất
  };

  // Logic chuyển đổi view trong modal
  useEffect(() => {
    // Nếu đang mở modal, mode là list, không loading và list rỗng -> Chuyển sang form tạo mới
    if (showAddressModal && modalMode === 'list' && !loadingAddress && addresses.length === 0) {
        setModalMode('create');
    }
  }, [showAddressModal, modalMode, loadingAddress, addresses.length]);

  const handleSelectAddress = (addr) => {
    // TODO: Gọi API cập nhật địa chỉ cho đơn hàng này (cần Backend hỗ trợ)
    // Ví dụ: await OrderService.updateShippingAddress(uuid, addr.uuid);
    console.log("Selected Address for Order:", addr);
    
    // Tạm thời đóng modal và reload lại chi tiết đơn (nếu backend đã update)
    setShowAddressModal(false);
    alert(`Đã chọn địa chỉ: ${addr.street}, ${addr.ward}`);
    // getOrderDetail(uuid); 
  };

  const handleEditAddressClick = (addr) => {
      setEditingAddressData(addr);
      setModalMode('edit');
  };

  // Callback khi Form thêm/sửa thành công
  const handleAddressFormSuccess = async () => {
      await fetchAddresses(); // Load lại list
      setModalMode('list'); // Quay về list
      setEditingAddressData(null);
  };

  // --- Helpers ---
  const formatVariant = (sku) => sku ? `SKU: ${sku}` : '';

  // --- Loading / Error Main Page ---
  if (loadingOrder) return <div className={styles['loading-state']}><AiOutlineLoading3Quarters className={styles.spin} /> Đang tải...</div>;
  if (error) return <div className={styles['error-state']}><AiOutlineWarning /> {error}</div>;
  if (!orderDetail) return <div className={styles['error-state']}>Không tìm thấy đơn hàng.</div>;

  const { shipping_address, items, total_formatted, status, payment_status } = orderDetail;
  const shippingFeeFormatted = "43.900 ₫"; 
  const merchandiseSubtotal = items.reduce((acc, item) => acc + (item.quantity * parseFloat(item.unit_price_formatted.replace(/\D/g,''))), 0);
  const merchandiseSubtotalFormatted = new Intl.NumberFormat('vi-VN').format(merchandiseSubtotal) + " VND";

  return (
    <div className={styles.container}>
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
            {/* NÚT EDIT GỌI MODAL */}
            <button className={styles.btnEdit} onClick={handleOpenAddressModal}>Change</button>
          </div>
        </div>

        {/* ITEMS SECTION */}
        <div className={styles.section}>
             {/* ... (Giữ nguyên code hiển thị sản phẩm) ... */}
             <div className={styles['store-header']}>
                <Store size={16} /> <span>Atelier Furniture Official Store</span> 
                <span className={styles.chatBtn}><MessageCircle size={14}/> Chat</span>
            </div>
            {/* ... Loop items ... */}
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

        {/* ... (Các phần Vouchers, Payment Method, Footer Summary giữ nguyên) ... */}
         <div className={styles.section}>
            <div className={styles.rowBetween}>
                <div className={styles.labelFlex}><span className={styles.iconVoucher}>🏷️</span> Vouchers</div>
                <div className={styles.linkAction}>Select or enter code {'>'}</div>
            </div>
        </div>

        <div className={styles.section}>
             <div className={styles.rowBetween}>
                <div className={styles.labelBold}>Payment Method</div>
                <div className={styles.paymentTags}>
                    <button className={`${styles.tagItem} ${selectedMethod === 'cod' ? styles.activeTag : ''}`} onClick={() => setSelectedMethod('cod')}>Cash on Delivery</button>
                    <button className={`${styles.tagItem} ${selectedMethod === 'vnpay' ? styles.activeTag : ''}`} onClick={() => setSelectedMethod('vnpay')}>VNPay / NAPAS</button>
                </div>
             </div>
             <div className={styles.shippingInfoBlock}>
                <div className={styles.rowBetween}>
                    <div className={styles.labelBold}>Shipping Method: Fast</div>
                    <div className={styles.rowPriceAction}>
                        <span className={styles.textChange}>Change</span>
                        <span className={styles.textPrice}>{shippingFeeFormatted}</span>
                    </div>
                </div>
                <div className={styles.shipDate}>🚚 Arrives between Dec 8 – Dec 10</div>
             </div>
        </div>

        <div className={styles.summaryContainer}>
             <div className={`${styles.summaryRow} ${styles.totalRow}`}>
                <span>Total Payment:</span>
                <span className={styles.totalAmount}>{total_formatted}</span>
            </div>
            <div className={styles.actionArea}>
                {status === 'pending' && <button onClick={handleCancelOrder} className={styles.btnCancel}>Cancel Order</button>}
                <button className={styles.btnPlaceOrder} onClick={handlePlaceOrder} disabled={status === 'cancelled' || isPaying}>
                    {isPaying ? 'Processing...' : status === 'cancelled' ? 'Cancelled' : 'Place Order'}
                </button>
            </div>
        </div>

      </div>

      {/* --- ADDRESS MODAL --- */}
      {showAddressModal && (
        <div className={styles.modalOverlay}>
            <div className={styles.modalContent}>
                <div className={styles.modalHeader}>
                    <h3>{modalMode === 'list' ? 'Select Address' : modalMode === 'create' ? 'Add New Address' : 'Edit Address'}</h3>
                    <button className={styles.btnClose} onClick={() => setShowAddressModal(false)}><X size={20}/></button>
                </div>

                <div className={styles.modalBody}>
                    {/* VIEW 1: LIST ADDRESS */}
                    {modalMode === 'list' && (
                        <>
                            {loadingAddress ? (
                                <div className={styles.loadingState}><AiOutlineLoading3Quarters className={styles.spin}/> Loading addresses...</div>
                            ) : (
                                <div className={styles.addressList}>
                                    {addresses.map(addr => (
                                        <div key={addr.uuid} className={`${styles.addressCard} ${shipping_address?.id === addr.id ? styles.addressActive : ''}`}>
                                            <div className={styles.addrInfo}>
                                                <div className={styles.addrRow}>
                                                    <span className={styles.addrName}>{addr.full_name}</span>
                                                    <span className={styles.addrPhone}>| {addr.phone}</span>
                                                    {addr.is_default && <span className={styles.defaultTag}>Default</span>}
                                                    {addr.type && <span className={styles.typeTag}>{addr.type}</span>}
                                                </div>
                                                <div className={styles.addrText}>
                                                    {addr.street}, {addr.ward}, {addr.district}, {addr.province}
                                                </div>
                                            </div>
                                            <div className={styles.addrActions}>
                                                <button className={styles.btnEditAddr} onClick={() => handleEditAddressClick(addr)}>
                                                    Update
                                                </button>
                                                {/* Nếu địa chỉ này đang được chọn thì hiện dấu tích, ngược lại hiện nút Select */}
                                                {shipping_address?.id === addr.id ? (
                                                    <span className={styles.selectedMark}><Check size={16}/> Selected</span>
                                                ) : (
                                                    <button className={styles.btnSelectAddr} onClick={() => handleSelectAddress(addr)}>
                                                        Select
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                    
                                    <button className={styles.btnAddAddress} onClick={() => {
                                        setEditingAddressData(null);
                                        setModalMode('create');
                                    }}>
                                        <Plus size={18} /> Add New Address
                                    </button>
                                </div>
                            )}
                        </>
                    )}

                    {/* VIEW 2: FORM (CREATE / EDIT) */}
                    {(modalMode === 'create' || modalMode === 'edit') && (
                        <AddressForm 
                            initialData={editingAddressData}
                            onSuccess={handleAddressFormSuccess}
                            onCancel={() => setModalMode('list')}
                        />
                    )}
                </div>
            </div>
        </div>
      )}

    </div>
  );
};

export default OrderDetail;