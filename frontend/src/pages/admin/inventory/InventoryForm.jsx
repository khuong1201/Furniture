import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Save, Loader2, Package, ClipboardList } from 'lucide-react';
import { useInventory } from '@/hooks/admin/useInventory'; // Import Hook
import './InventoryForm.css'; 

const InventoryForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    
    // 1. Khởi tạo Hook
    const { getInventory, adjustStock } = useInventory(); 

    const [stockItem, setStockItem] = useState(null);
    const [loadingData, setLoadingData] = useState(true);
    const [submitting, setSubmitting] = useState(false);

    // Form State
    const [formData, setFormData] = useState({
        type: 'add',
        quantity: '',
        reason: 'Manual Adjustment'
    });

    useEffect(() => {
        const fetchDetail = async () => {
            try {
                // 2. Dùng hàm từ Hook (getInventory)
                const data = await getInventory(uuid); 
                
                if (data) {
                    setStockItem(data);
                } else {
                    alert("Inventory item not found");
                    // Chuyển hướng về trang danh sách (giữ path cũ)
                    navigate('/admin/inventory-manager?tab=stocks'); 
                }
            } catch (error) {
                console.error("Error loading inventory:", error);
                navigate('/admin/inventory-manager?tab=stocks');
            } finally {
                setLoadingData(false);
            }
        };
        
        if (uuid) {
            fetchDetail();
        }
    }, [uuid, navigate, getInventory]);

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!formData.quantity || formData.quantity <= 0) {
            return alert("Please enter a valid quantity");
        }

        setSubmitting(true);
        try {
            const adjustQty =
                formData.type === 'add'
                    ? parseInt(formData.quantity, 10)
                    : -parseInt(formData.quantity, 10);

            await adjustStock({
                inventory_uuid: stockItem.uuid, 
                quantity: adjustQty,
                reason: formData.reason,
            });

            navigate('/admin/inventory-manager?tab=stocks');
        } catch (error) {
            console.error(error);
            alert("Failed to save adjustment.");
        } finally {
            setSubmitting(false);
        }
    };

    if (loadingData) return <div className="p-8 text-center text-gray-500">Loading details...</div>;
    if (!stockItem) return null;

    // Display Info Logic
    const productName = stockItem.variant?.product?.name || stockItem.variant?.name || 'Unknown Product';
    const variantName = (stockItem.variant?.name && stockItem.variant.name !== productName) ? stockItem.variant.name : '';
    const sku = stockItem.variant?.sku || 'N/A';
    const warehouseName = stockItem.warehouse?.name || 'Unknown Warehouse';

    return (
        <div className="inventory-form-page"> 
            
            {/* Header */}
            <div className="form-header-section">
                <div className="header-left">
                    {/* Nút Back về trang cũ */}
                    <button onClick={() => navigate('/admin/inventory-manager?tab=stocks')} className="btn-back">
                        <ArrowLeft size={18}/> Back to List
                    </button>
                    <h1>Adjust Stock</h1>
                </div>
            </div>

            <form onSubmit={handleSubmit}>
                {/* Product Info Card */}
                <div className="form-card">
                    <div className="card-header">
                        <h3 className="card-title"><Package /> Product Information</h3>
                    </div>
                    <div className="card-body">
                        <div className="product-info-box">
                            <div className="info-row">
                                <span className="info-label">Product Name:</span>
                                <span className="info-value">{productName}</span>
                            </div>
                            {variantName && (
                                <div className="info-row">
                                    <span className="info-label">Variant:</span>
                                    <span className="info-value">{variantName}</span>
                                </div>
                            )}
                            <div className="info-row">
                                <span className="info-label">SKU:</span>
                                <span className="info-value font-mono">{sku}</span>
                            </div>
                            <div className="info-row">
                                <span className="info-label">Location:</span>
                                <span className="info-value">{warehouseName}</span>
                            </div>
                            <div className="info-row">
                                <span className="info-label">Current Stock:</span>
                                <span className="info-value text-blue-600">{stockItem.quantity} units</span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Adjustment Form Card */}
                <div className="form-card">
                    <div className="card-header">
                        <h3 className="card-title"><ClipboardList /> Adjustment Details</h3>
                    </div>
                    <div className="card-body">
                        <div className="form-grid">
                            <div className="form-group">
                                <label className="form-label required">Action Type</label>
                                <select 
                                    className="form-select" 
                                    value={formData.type} 
                                    onChange={e => setFormData({...formData, type: e.target.value})}
                                >
                                    <option value="add">Add Stock (+)</option>
                                    <option value="subtract">Remove Stock (-)</option>
                                </select>
                            </div>

                            <div className="form-group">
                                <label className="form-label required">Quantity</label>
                                <input 
                                    type="number" 
                                    className="form-input" 
                                    min="1" 
                                    placeholder="Enter quantity"
                                    value={formData.quantity}
                                    onChange={e => setFormData({...formData, quantity: e.target.value})}
                                />
                            </div>

                            <div className="form-group" style={{ gridColumn: '1 / -1' }}>
                                <label className="form-label">Reason / Note</label>
                                <input 
                                    type="text" 
                                    className="form-input" 
                                    placeholder="e.g. Stocktake correction, Damaged goods..."
                                    value={formData.reason}
                                    onChange={e => setFormData({...formData, reason: e.target.value})}
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {/* Footer Actions */}
                <div className="form-actions">
                    <button type="button" onClick={() => navigate('/admin/inventory-manager?tab=stocks')} className="btn-secondary" disabled={submitting}>
                        Cancel
                    </button>
                    <button type="submit" className="btn-primary-gradient" disabled={submitting}>
                        {submitting ? <Loader2 className="animate-spin" size={18}/> : <Save size={18}/>}
                        Confirm Adjustment
                    </button>
                </div>
            </form>
        </div>
    );
};

export default InventoryForm;