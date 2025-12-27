import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Save, Loader2, Plus, X, Box, Layers, Tag, Settings, Sparkles } from 'lucide-react';

// Hooks & Services
import { useProduct } from '@/hooks/admin/useProduct';
import { useCategory } from '@/hooks/admin/useCategory';
import { useBrand } from '@/hooks/admin/useBrand';
import { useAttribute } from '@/hooks/admin/useAttribute';
import { useWarehouse } from '@/hooks/admin/useWarehouse';

// Components
import LoadingSpinner from '@/components/admin/shared/LoadingSpinner';
import ProductMediaSection from '@/components/admin/products/ProductMediaSection';
import './ProductForm.css';

// --- Sub-component: Stock Input (Optimized) ---
const StockInput = ({ warehouses, stockData, onChange }) => {
    const handleChange = (whId, value) => {
        const newStock = [...stockData];
        const idx = newStock.findIndex(s => s.warehouse_uuid === whId);
        
        if (idx > -1) {
            newStock[idx].quantity = value;
        } else {
            newStock.push({ warehouse_uuid: whId, quantity: value });
        }
        onChange(newStock);
    };

    const handleBlur = (whId, value) => {
        let finalVal = parseInt(value);
        if (isNaN(finalVal) || value === '') finalVal = 0;
        
        const newStock = [...stockData];
        const idx = newStock.findIndex(s => s.warehouse_uuid === whId);
        
        if (idx > -1) {
            newStock[idx].quantity = finalVal;
            onChange(newStock);
        }
    };

    return (
        <div className="stock-grid">
            {warehouses.map(wh => {
                const stockItem = stockData.find(s => s.warehouse_uuid === wh.uuid);
                const displayValue = stockItem?.quantity ?? 0;

                return (
                    <div key={wh.uuid} className="stock-item">
                        <span className="stock-label" title={wh.name}>{wh.name}</span>
                        <input 
                            type="number" 
                            min="0" 
                            className="form-input text-center h-8" 
                            value={displayValue}
                            onChange={e => handleChange(wh.uuid, e.target.value)}
                            onBlur={e => handleBlur(wh.uuid, e.target.value)}
                            onFocus={e => e.target.select()}
                        />
                    </div>
                );
            })}
        </div>
    );
};

// --- Main Form Component ---
const ProductForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    const isEdit = !!uuid;

    // Hooks
    const { createProduct, updateProduct, getDetail, uploadImages, generateDescription, loading: pLoading, generating: aiGenerating } = useProduct();
    const { categories, fetchCategories, loading: cLoading } = useCategory();
    const { brands, fetchBrands, loading: bLoading } = useBrand();
    const { attributes, fetchAttributes, createAttribute, loading: aLoading } = useAttribute();
    const { warehouses, fetchWarehouses, loading: wLoading } = useWarehouse();

    // Local State
    const [submitting, setSubmitting] = useState(false);
    const [formData, setFormData] = useState({
        name: '', category_uuid: '', brand_uuid: '', description: '',
        is_active: true, has_variants: false, price: 0, sku: '',
        warehouse_stock: [], variants: [], mediaItems: [], deletedImageUuids: []
    });

    // 1. Initialize Data
    useEffect(() => {
        const initData = async () => {
            try {
                const [_, __, whList, attrList] = await Promise.all([
                    fetchCategories({ per_page: 9999 }), 
                    fetchBrands({ per_page: 9999 }),
                    fetchWarehouses(), 
                    fetchAttributes({ per_page: 9999 })
                ]);

                if (isEdit) {
                    const p = await getDetail(uuid);
                    if (p) {
                        // --- FIX LOGIC LẤY STOCK ---
                        let currentStock = [];
                        
                        // Trường hợp 1: Sản phẩm đơn (Simple Product) -> Lấy stock từ variant đầu tiên (ẩn)
                        if (!p.has_variants && p.variants && p.variants.length > 0) {
                            currentStock = p.variants[0].stock?.map(s => ({
                                warehouse_uuid: s.warehouse_uuid,
                                quantity: s.quantity
                            })) || [];
                        } 
                        // Trường hợp 2: Nếu Backend đã map sẵn ra root (fallback)
                        else if (p.warehouse_stock) {
                            currentStock = p.warehouse_stock;
                        }
                        // ---------------------------

                        const mappedVariants = (p.variants || []).map(v => ({
                            ...v, 
                            stock: v.stock || v.warehouse_stock || [],
                            attributes: v.attributes?.map(a => {
                                const foundAttr = (attrList || []).find(attr => attr.name.toLowerCase() === (a.attribute_name || "").toLowerCase());
                                return { attribute_slug: foundAttr ? foundAttr.slug : (a.attribute_slug || ''), value: a.value || '' };
                            }) || []
                        }));

                        const mappedImages = (p.images || []).map(img => ({
                            id: img.uuid, url: img.url, is_new: false, is_primary: !!img.is_primary
                        }));

                        setFormData({
                            name: p.name, 
                            category_uuid: p.category?.uuid || '', 
                            brand_uuid: p.brand?.uuid || '',
                            description: p.description || '', 
                            is_active: !!p.is_active, 
                            has_variants: !!p.has_variants,
                            price: p.price || 0, 
                            sku: p.sku || '', 
                            
                            // Gán biến stock đã xử lý vào đây
                            warehouse_stock: currentStock, 
                            
                            variants: mappedVariants, 
                            mediaItems: mappedImages, 
                            deletedImageUuids: []
                        });
                    }
                }
            } catch (e) { console.error("Init Error:", e); }
        };
        initData();
        // eslint-disable-next-line
    }, [uuid, isEdit]);

    // 2. Helper Logic: Prepare Payload
    const preparePayload = () => {
        const payload = { ...formData, price: parseInt(formData.price) || 0 };
        
        if (payload.has_variants) {
            delete payload.price; 
            delete payload.sku; 
            delete payload.warehouse_stock;
            
            payload.variants = payload.variants.map(v => ({
                ...v, 
                price: parseInt(v.price) || 0,
                attributes: v.attributes.filter(a => a.attribute_slug && a.value)
            }));
        } else {
            delete payload.variants;
        }

        payload.current_images_state = formData.mediaItems
            .filter(item => !item.is_new)
            .map(item => ({ uuid: item.id, is_primary: item.is_primary ? 1 : 0 }));
        
        payload.deleted_image_uuids = formData.deletedImageUuids;
        delete payload.mediaItems; 

        return payload;
    };

    // 3. Submit Handler
    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        
        try {
            const payload = preparePayload();

            const result = isEdit 
                ? await updateProduct(uuid, payload) 
                : await createProduct(payload);
            
            const targetUuid = result.uuid || result.id || uuid;

            const newMediaItems = formData.mediaItems.filter(item => item.is_new);
            if (newMediaItems.length > 0) {
                await uploadImages(targetUuid, newMediaItems);
            }

            navigate('/admin/product-manager');
        } catch (e) { 
            console.error("Submit Error:", e);
            alert("Error: " + (e.message || "Unknown error occurred")); 
        } finally { 
            setSubmitting(false); 
        }
    };

    // --- Interaction Handlers ---
    const handleVariantChange = (idx, field, val) => {
        const updated = [...formData.variants]; 
        updated[idx][field] = val; 
        setFormData({ ...formData, variants: updated });
    };

    const updateVariantAttr = (vIdx, aIdx, field, val) => {
        const updated = [...formData.variants]; 
        updated[vIdx].attributes[aIdx][field] = val; 
        setFormData({ ...formData, variants: updated });
    };

    const modifyVariant = (action, vIdx, aIdx) => {
        const updated = [...formData.variants];
        if (action === 'add_var') {
            updated.push({ sku: `${formData.sku}-VAR-${updated.length+1}`, price: formData.price, attributes: [], stock: [] });
        } else if (action === 'remove_var') {
            updated.splice(vIdx, 1);
        } else if (action === 'add_attr') {
            updated[vIdx].attributes.push({ attribute_slug: '', value: '' });
        } else if (action === 'remove_attr') {
            updated[vIdx].attributes.splice(aIdx, 1);
        }
        setFormData({ ...formData, variants: updated });
    };

    const handleCreateAttribute = async () => {
        const name = prompt("Enter new attribute name (e.g. Color, Size):");
        if (name) { 
            await createAttribute({ name, type: 'select' }); 
            await fetchAttributes({ per_page: 9999 }); 
        }
    };

    const handleGenerateAI = async () => {
        if (!formData.name) return alert("Please enter Product Name first.");
        try {
            const desc = await generateDescription({ 
                name: formData.name, 
                category_uuid: formData.category_uuid, 
                brand_uuid: formData.brand_uuid 
            });
            setFormData(prev => ({ ...prev, description: desc }));
        } catch (e) { alert(e.message); }
    };

    const handleMediaChange = (items) => {
        const currentIds = items.map(i => i.id);
        const newlyDeleted = formData.mediaItems
            .filter(item => !item.is_new && !currentIds.includes(item.id))
            .map(item => item.id);
            
        setFormData(prev => ({ 
            ...prev, 
            mediaItems: items, 
            deletedImageUuids: [...prev.deletedImageUuids, ...newlyDeleted] 
        }));
    };

    if ((pLoading || cLoading || bLoading) && !isEdit) return <LoadingSpinner />;

    return (
        <div className="product-form-page">
            <div className="form-header-section">
                <div className="header-left">
                    <button onClick={() => navigate('/admin/product-manager')} className="btn-back">
                        <ArrowLeft size={18}/> Back
                    </button>
                    <h1>{isEdit ? 'Edit Product' : 'Create Product'}</h1>
                </div>
            </div>

            <form className="form-layout" onSubmit={handleSubmit}>
                <div className="form-column">
                    {/* 1. General Info */}
                    <div className="form-card">
                        <div className="card-header"><h3 className="card-title"><Box /> General Information</h3></div>
                        <div className="card-body">
                            <div className="form-group">
                                <label className="form-label required">Product Name</label>
                                <input 
                                    className="form-input" 
                                    value={formData.name} 
                                    onChange={e => setFormData({...formData, name: e.target.value})} 
                                    required 
                                    placeholder="e.g. Luxury Sofa" 
                                />
                            </div>
                            <div className="form-group">
                                <div className="flex justify-between items-center mb-2">
                                    <label className="form-label mb-0">Description</label>
                                    <button type="button" className="btn-ai-gen" onClick={handleGenerateAI} disabled={aiGenerating || !formData.name}>
                                        {aiGenerating ? <Loader2 className="animate-spin"/> : <Sparkles />} AI Generate
                                    </button>
                                </div>
                                <textarea 
                                    className="form-textarea" 
                                    value={formData.description} 
                                    onChange={e => setFormData({...formData, description: e.target.value})} 
                                />
                            </div>
                        </div>
                    </div>

                    {/* 2. Media Section */}
                    <ProductMediaSection mediaItems={formData.mediaItems} onChange={handleMediaChange} />

                    {/* 3. Pricing & Inventory */}
                    <div className="form-card">
                        <div className="card-header">
                            <h3 className="card-title"><Tag /> Pricing & Inventory</h3>
                            <label className="toggle-switch">
                                <input type="checkbox" checked={formData.has_variants} onChange={e => setFormData({...formData, has_variants: e.target.checked})} />
                                <span className="slider"></span>
                            </label>
                        </div>
                        
                        <div className="card-body">
                            {!formData.has_variants ? (
                                <div className="simple-product">
                                    <div className="grid-2">
                                        <div className="form-group">
                                            <label className="form-label required">Price</label>
                                            <input type="number" className="form-input" value={formData.price} onChange={e => setFormData({...formData, price: e.target.value})} />
                                        </div>
                                        <div className="form-group">
                                            <label className="form-label required">SKU</label>
                                            <input className="form-input" value={formData.sku} onChange={e => setFormData({...formData, sku: e.target.value})} />
                                        </div>
                                    </div>
                                    <div className="form-group">
                                        <label className="form-label">Stock Quantity</label>
                                        <StockInput warehouses={warehouses} stockData={formData.warehouse_stock} onChange={s => setFormData({...formData, warehouse_stock: s})} />
                                    </div>
                                </div>
                            ) : (
                                <div className="variant-list">
                                    {formData.variants.map((v, vIdx) => (
                                        <div key={vIdx} className="variant-card">
                                            <div className="variant-header">
                                                <span className="variant-title">Variant #{vIdx + 1}</span>
                                                <button type="button" onClick={() => modifyVariant('remove_var', vIdx)} className="btn-icon-danger"><X size={16}/></button>
                                            </div>
                                            
                                            <div className="grid-2 mb-4">
                                                <div className="form-group mb-0">
                                                    <label className="form-label required">SKU</label>
                                                    <input className="form-input" value={v.sku} onChange={e => handleVariantChange(vIdx, 'sku', e.target.value)} />
                                                </div>
                                                <div className="form-group mb-0">
                                                    <label className="form-label required">Price</label>
                                                    <input type="number" className="form-input" value={v.price} onChange={e => handleVariantChange(vIdx, 'price', e.target.value)} />
                                                </div>
                                            </div>

                                            <div className="attr-section">
                                                <div className="attr-header">
                                                    <span className="attr-title">Attributes</span>
                                                    <div className="attr-actions-group">
                                                        <button type="button" className="btn-create-attr-type" onClick={handleCreateAttribute}><Plus size={14}/> Add Type</button>
                                                        <button type="button" onClick={() => modifyVariant('add_attr', vIdx)} className="btn-add-attr-line"><Plus size={14}/> Add Attr</button>
                                                    </div>
                                                </div>
                                                {v.attributes?.map((attr, aIdx) => (
                                                    <div key={aIdx} className="attr-row">
                                                        <select className="form-select" value={attr.attribute_slug} onChange={e => updateVariantAttr(vIdx, aIdx, 'attribute_slug', e.target.value)}>
                                                            <option value="">-- Type --</option>
                                                            {attributes.map((ma, idx) => <option key={ma.uuid || idx} value={ma.slug}>{ma.name}</option>)}
                                                        </select>
                                                        <input className="form-input attr-value-input" placeholder="Value" value={attr.value} onChange={e => updateVariantAttr(vIdx, aIdx, 'value', e.target.value)} />
                                                        <button type="button" onClick={() => modifyVariant('remove_attr', vIdx, aIdx)} className="btn-remove-attr"><X size={16}/></button>
                                                    </div>
                                                ))}
                                            </div>

                                            <div className="form-group mb-0">
                                                <label className="form-label">Stock</label>
                                                <StockInput warehouses={warehouses} stockData={v.stock || []} onChange={s => handleVariantChange(vIdx, 'stock', s)} />
                                            </div>
                                        </div>
                                    ))}
                                    <button type="button" onClick={() => modifyVariant('add_var')} className="btn-add-variant-dashed"><Plus size={18}/> Add Variant</button>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                <div className="form-column sidebar">
                    <div className="form-card">
                        <div className="card-header"><h3 className="card-title"><Layers /> Organization</h3></div>
                        <div className="card-body">
                            <div className="form-group">
                                <label className="form-label required">Category</label>
                                <select className="form-select" value={formData.category_uuid} onChange={e => setFormData({...formData, category_uuid: e.target.value})}>
                                    <option value="">-- Select Category --</option>
                                    {categories.map(c => <option key={c.uuid} value={c.uuid}>{c.name}</option>)}
                                </select>
                            </div>
                            <div className="form-group">
                                <label className="form-label required">Brand</label>
                                <select className="form-select" value={formData.brand_uuid} onChange={e => setFormData({...formData, brand_uuid: e.target.value})}>
                                    <option value="">-- Select Brand --</option>
                                    {brands.map(b => <option key={b.uuid} value={b.uuid}>{b.name}</option>)}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div className="form-card">
                        <div className="card-header"><h3 className="card-title"><Settings /> Status</h3></div>
                        <div className="card-body">
                            <div className="status-row">
                                <span className="status-label">Active</span>
                                <label className="toggle-switch">
                                    <input type="checkbox" checked={formData.is_active} onChange={e => setFormData({...formData, is_active: e.target.checked})} />
                                    <span className="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div className="sidebar-actions sticky bottom-4">
                        <button type="submit" disabled={submitting} className="btn-primary-gradient">
                            {submitting ? <Loader2 className="animate-spin" size={18}/> : <Save size={18}/>} 
                            {isEdit ? 'Save Changes' : 'Publish Product'}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    );
};

export default ProductForm;