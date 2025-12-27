import React, { useEffect, useState, useCallback, useRef } from 'react';
import { useParams, Link, useNavigate, useLocation } from 'react-router-dom';
import { useOrder } from '@/hooks/useOrder';
import { usePayment } from '@/hooks/usePayment';
import { useAddress } from '@/hooks/useAddress';
import { useCart } from '@/hooks/useCart';
import AddressForm from '@/pages/customer/address/AddressForm.jsx';
import { MapPin, Store, MessageCircle, ChevronLeft, X, Plus, Check } from 'lucide-react';
import { AiOutlineLoading3Quarters, AiOutlineWarning } from 'react-icons/ai';
import styles from './OrderDetail.module.css';

/* ---------- Helpers ---------- */
const parseFormattedPriceToNumber = (str) => {
  if (!str) return 0;
  const digits = ('' + str).replace(/[^\d]/g, ''); 
  return digits ? Number(digits) : 0;
};

const formatVND = (num) => {
  if (num === null || num === undefined) return '-';
  if (typeof num === 'string') return num; // Nếu API trả về string "xxx VND" thì trả về luôn
  return new Intl.NumberFormat('vi-VN').format(num) + ' VND';
};

/* ---------- Components ---------- */
const AddressSection = React.memo(({ shipping_address, onOpenChange }) => (
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
        <div>No shipping address selected.</div>
      )}
      <button className={styles.btnEdit} onClick={onOpenChange}>Change</button>
    </div>
  </div>
));

const ItemsList = React.memo(({ items }) => (
  <div className={styles.section}>
    <div className={styles['store-header']}>
      <Store size={16} /> <span>Atelier Furniture Official Store</span>
      <button className={styles.chatBtn}><MessageCircle size={14} /> Chat</button>
    </div>

    {items?.map((item, i) => {
        // Fix logic link product
        const productLink = item.product_id ? `/products/${item.product_id}` : '#';
        
        // Fix logic hiển thị giá: API trả về unit_price (string) hoặc tính toán
        const displayPrice = item.unit_price || formatVND(item.price || 0);
        const displayTotal = item.total || item.subtotal_formatted || formatVND(parseFormattedPriceToNumber(displayPrice) * item.quantity);

        return (
          <div key={i} className={styles['item-row']}>
            <div className={styles['col-product']}>
              <div className={styles.productFlex}>
                <Link to={productLink} className={styles.linkWrapper}>
                  <img src={item.image || "https://placehold.co/100"} alt={item.product_name} className={styles['product-img']} />
                </Link>
                <div className={styles.productInfo}>
                  <Link to={productLink} className={styles.linkText}>
                      <div className={styles['product-name']}>{item.product_name}</div>
                  </Link>
                  <div className={styles.productVariant}>
                    {/* Ưu tiên variant_text từ API, fallback về SKU */}
                    {item.variant_text || (item.sku ? `SKU: ${item.sku}` : '')}
                  </div>
                </div>
              </div>
            </div>
            <div className={styles['col-price']}>{displayPrice}</div>
            <div className={styles['col-quantity']}>x{item.quantity}</div>
            <div className={`${styles['col-operation']} ${styles.priceText}`}>
              {displayTotal}
            </div>
          </div>
        );
    })}
  </div>
));

const SummaryFooter = React.memo(({ totalFormatted, status, onCancel, onPlaceOrder, isPaying, isCancelling, isReorderMode, onAddAllToCart, isAddingToCart }) => {
  const navigate = useNavigate();

  if (isReorderMode) {
    return (
      <div className={styles.summaryContainer}>
        <div className={`${styles.summaryRow} ${styles.totalRow}`}>
          <span>Total Payment:</span>
          <span className={styles.totalAmount}>{totalFormatted}</span>
        </div>
        <div className={styles.actionArea}>
            <button className={styles.btnPlaceOrder} onClick={onAddAllToCart} disabled={isAddingToCart}>
                {isAddingToCart ? 'Adding...' : 'Add All To Cart'}
            </button>
        </div>
      </div>
    );
  }

  const renderActionButton = () => {
    switch (status) {
      case 'pending':
        return (
          <button className={styles.btnPlaceOrder} onClick={onPlaceOrder} disabled={isPaying || isCancelling}>
            {isPaying ? 'Processing...' : 'Pay Now'}
          </button>
        );
      case 'cancelled':
        return <button className={styles.btnPlaceOrder} disabled style={{opacity: 0.5, cursor: 'not-allowed', backgroundColor:'#999'}}>Cancelled</button>;
      case 'delivered':
      case 'completed':
        return <button className={styles.btnPlaceOrder} onClick={() => navigate('/products')}>Continue Shopping</button>;
      case 'shipping':
      case 'processing':
        return <button className={styles.btnPlaceOrder} disabled style={{opacity: 0.8, backgroundColor:'#26aa99'}}>{status === 'shipping' ? 'In Transit' : 'Processing'}</button>;
      default:
        return null;
    }
  };

  return (
    <div className={styles.summaryContainer}>
      <div className={`${styles.summaryRow} ${styles.totalRow}`}>
        <span>Total Payment:</span>
        <span className={styles.totalAmount}>{totalFormatted}</span>
      </div>
      <div className={styles.actionArea}>
        {status === 'pending' && (
          <button onClick={onCancel} className={styles.btnCancel} disabled={isCancelling || isPaying} style={{ opacity: (isCancelling || isPaying) ? 0.6 : 1 }}>
            {isCancelling ? 'Cancelling...' : 'Cancel Order'}
          </button>
        )}
        {renderActionButton()}
      </div>
    </div>
  );
});

const AddressModal = ({ visible, onClose, mode, addresses, loadingAddress, shipping_address, onSelect, onEdit, onAdd, onCreateSuccess, editingAddressData, onCancelForm }) => {
  if (!visible) return null;
  return (
    <div className={styles.modalOverlay}>
      <div className={styles.modalContent}>
        <div className={styles.modalHeader}>
          <h3>{mode === 'list' ? 'Select Address' : mode === 'create' ? 'Add New Address' : 'Edit Address'}</h3>
          <button className={styles.btnClose} onClick={onClose}><X size={20} /></button>
        </div>
        <div className={styles.modalBody}>
          {mode === 'list' ? (
            loadingAddress ? <div className={styles.loadingState}><AiOutlineLoading3Quarters className={styles.spin} /> Loading addresses...</div> : (
              <div className={styles.addressList}>
                {addresses.length === 0 && <div style={{textAlign: 'center', margin: '20px 0'}}>No saved addresses found.</div>}
                {addresses.map((addr) => (
                  <div key={addr.uuid || addr.id} className={`${styles.addressCard} ${shipping_address?.id === addr.id ? styles.addressActive : ''}`}>
                    <div className={styles.addrInfo}>
                      <div className={styles.addrRow}>
                        <span className={styles.addrName}>{addr.full_name}</span>
                        <span className={styles.addrPhone}>| {addr.phone}</span>
                        {addr.is_default && <span className={styles.defaultTag}>Default</span>}
                      </div>
                      <div className={styles.addrText}>{addr.street}, {addr.ward}, {addr.district}, {addr.province}</div>
                    </div>
                    <div className={styles.addrActions}>
                      <button className={styles.btnEditAddr} onClick={() => onEdit(addr)}>Update</button>
                      {shipping_address?.id === addr.id ? <span className={styles.selectedMark}><Check size={16} /> Selected</span> : <button className={styles.btnSelectAddr} onClick={() => onSelect(addr)}>Select</button>}
                    </div>
                  </div>
                ))}
                <button className={styles.btnAddAddress} onClick={onAdd}><Plus size={18} /> Add New Address</button>
              </div>
            )
          ) : (
            <AddressForm initialData={editingAddressData} onSuccess={onCreateSuccess} onCancel={onCancelForm} />
          )}
        </div>
      </div>
    </div>
  );
};

/* ---------- Main Component ---------- */
const OrderDetail = () => {
  const { uuid } = useParams();
  const location = useLocation();
  const navigate = useNavigate();
  
  const isReorderMode = location.state?.reorder === true;

  // Hooks
  const { getOrderDetail, orderDetail, loading: loadingOrder, error, cancelOrder } = useOrder();
  const { initiatePayment, loading: isPaying } = usePayment();
  const { addresses, fetchAddresses, loading: loadingAddress } = useAddress();
  const { addAllToCart, loading: isAddingToCart } = useCart(); 

  const [isCancelling, setIsCancelling] = useState(false);
  const [showAddressModal, setShowAddressModal] = useState(false);
  const [modalMode, setModalMode] = useState('list'); 
  const [editingAddressData, setEditingAddressData] = useState(null);

  const cancelLock = useRef(false);

  useEffect(() => { if (uuid) getOrderDetail(uuid); }, [uuid]); 

  // Address Handlers
  const handleOpenAddressModal = useCallback(async () => { setShowAddressModal(true); setModalMode('list'); await fetchAddresses(); }, [fetchAddresses]);
  useEffect(() => { if (showAddressModal && modalMode === 'list' && !loadingAddress && addresses.length === 0) setModalMode('create'); }, [showAddressModal, modalMode, loadingAddress, addresses.length]);
  const handleSelectAddress = useCallback((addr) => { setShowAddressModal(false); }, []);
  const handleEditAddressClick = useCallback((addr) => { setEditingAddressData(addr); setModalMode('edit'); }, []);
  const handleAddAddressClick = useCallback(() => { setEditingAddressData(null); setModalMode('create'); }, []);
  const handleAddressFormSuccess = useCallback(async () => { await fetchAddresses(); setModalMode('list'); setEditingAddressData(null); }, [fetchAddresses]);
  const handleCancelForm = useCallback(() => { setModalMode('list'); setEditingAddressData(null); }, []);

  // --- DATA MAPPING FIX START ---
  // Lấy root data
  const orderRaw = orderDetail?.data || orderDetail;

  // 1. Map Address: API trả về trong `shipping_info.details`
  const shippingAddress = orderRaw?.shipping_info?.details || orderRaw?.shipping_address || null;

  // 2. Map Items:
  const items = orderRaw?.items ?? [];
  const status = orderRaw?.status ?? '';
  const paymentMethodCode = orderRaw?.payment_method || 'cod'; // Fallback COD nếu thiếu field

  // 3. Map Amounts: API trả về trong object `amounts`
  const amounts = orderRaw?.amounts || {};
  const subtotalFormatted = amounts.subtotal || orderRaw?.subtotal_formatted || formatVND(0);
  const shippingFeeFormatted = amounts.shipping_fee || orderRaw?.shipping_fee_formatted || formatVND(0);
  const voucherDiscountFormatted = amounts.voucher_discount || formatVND(0); // Nếu API có trả voucher
  const grandTotalFormatted = amounts.grand_total || orderRaw?.grand_total_formatted || formatVND(0);
  // --- DATA MAPPING FIX END ---

  const handlePlaceOrder = useCallback(async () => {
    if (!uuid) return;
    try {
      const result = await initiatePayment(uuid, 'cod'); 
      if (result?.payment_url) window.location.href = result.payment_url;
      else { alert('🎉 Order placed successfully!'); await getOrderDetail(uuid); }
    } catch (err) { alert('Error: ' + (err?.message || 'Please try again later.')); }
  }, [uuid, initiatePayment, getOrderDetail]);

  const handleCancelOrder = useCallback(async () => {
    if (!uuid || cancelLock.current || isCancelling) return;
    if (!window.confirm('Are you sure you want to cancel this order?')) return;
    try {
      cancelLock.current = true; setIsCancelling(true);
      await cancelOrder(uuid); await getOrderDetail(uuid);
      alert('Order cancelled successfully.');
    } catch (err) { alert('Cancellation failed: ' + (err?.message || err)); } 
    finally { cancelLock.current = false; setIsCancelling(false); }
  }, [uuid, cancelOrder, getOrderDetail, isCancelling]);

  const handleAddAllToCart = async () => {
      const success = await addAllToCart(items);
      if (success) {
          navigate('/cart'); 
      }
  };

  if (loadingOrder) return <div className={styles.loadingState}><AiOutlineLoading3Quarters className={styles.spin} /> Loading...</div>;
  if (error) return <div className={styles.errorState}><AiOutlineWarning /> {error}</div>;
  if (!orderRaw) return <div className={styles.errorState}>Order not found.</div>;

  return (
    <div className={styles.container}>
      <div className={styles['content-wrapper']}>
        <div style={{ padding: '10px 0' }}>
          <Link to="/me?tab=orders" className={styles.backLink}><ChevronLeft size={16} /> Back to Orders</Link>
        </div>

        <AddressSection shipping_address={shippingAddress} onOpenChange={handleOpenAddressModal} />
        <ItemsList items={items} />

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
                <div>{paymentMethodCode === 'cod' ? 'Cash on Delivery' : 'Online Payment'}</div>
            </div>
          </div>
          <div className={styles.shippingInfoBlock}>
            <div className={styles.rowBetween}><div className={styles.labelBold}>Shipping Fee</div><div className={styles.textPrice}>{shippingFeeFormatted}</div></div>
          </div>
        </div>
        <div style={{ marginTop: 12 }}>
          <div className={styles.summaryRow}><div>Merchandise Subtotal:</div><div>{subtotalFormatted}</div></div>
          <div className={styles.summaryRow}><div>Shipping Fee:</div><div>{shippingFeeFormatted}</div></div>
          {/* Nếu voucher discount != 0 VND thì hiển thị, tuỳ logic */}
          {parseFormattedPriceToNumber(voucherDiscountFormatted) > 0 && (
            <div className={styles.summaryRow}><div>Voucher Discount:</div><div>-{voucherDiscountFormatted}</div></div>
          )}
        </div>

        <SummaryFooter
          totalFormatted={grandTotalFormatted}
          status={status}
          onCancel={handleCancelOrder}
          onPlaceOrder={handlePlaceOrder}
          isPaying={isPaying}
          isCancelling={isCancelling}
          isReorderMode={isReorderMode}
          onAddAllToCart={handleAddAllToCart}
          isAddingToCart={isAddingToCart}
        />
      </div>
      <AddressModal visible={showAddressModal} onClose={() => setShowAddressModal(false)} mode={modalMode} addresses={addresses} loadingAddress={loadingAddress} shipping_address={shippingAddress} onSelect={handleSelectAddress} onEdit={handleEditAddressClick} onAdd={handleAddAddressClick} onCreateSuccess={handleAddressFormSuccess} editingAddressData={editingAddressData} onCancelForm={handleCancelForm} />
    </div>
  );
};

export default OrderDetail;