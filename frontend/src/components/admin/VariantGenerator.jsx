import React, { useState } from 'react';
import { RefreshCw, Trash2, Plus } from 'lucide-react';
import MultiWarehouseStock from './MultiWarehouseStock';

const VariantGenerator = ({ attributesList, warehouses, variants, onChange }) => {
    // State quản lý việc cấu hình thuộc tính (VD: Color: [Red, Blue])
    const [selectedConfigs, setSelectedConfigs] = useState([{ slug: '', values: [] }]);

    // Logic Tích Đề-các
    const cartesian = (args) => {
        return args.reduce((a, b) => a.flatMap(d => b.map(e => [d, e].flat())));
    };

    const handleGenerate = () => {
        const validConfigs = selectedConfigs.filter(c => c.slug && c.values.length > 0);
        if (validConfigs.length === 0) return;

        const combinations = cartesian(validConfigs.map(c => c.values));
        
        const newVariants = combinations.map(combo => {
            const comboArray = Array.isArray(combo) ? combo : [combo];
            // Map ngược lại để lấy slug cho đúng format Backend
            const variantAttrs = comboArray.map((val, idx) => ({
                attribute_slug: validConfigs[idx].slug,
                value: val
            }));

            return {
                sku: `SKU-${comboArray.join('-').toUpperCase()}`,
                price: 0,
                attributes: variantAttrs,
                stock: warehouses.map(wh => ({ warehouse_uuid: wh.uuid, quantity: 0 }))
            };
        });

        onChange(newVariants);
    };

    const addConfigRow = () => setSelectedConfigs([...selectedConfigs, { slug: '', values: [] }]);

    return (
        <div className="variant-generator">
            {/* Cấu hình thuộc tính */}
            <div className="config-section card-sub">
                {selectedConfigs.map((config, idx) => (
                    <div key={idx} className="config-row">
                        <select 
                            className="form-select"
                            value={config.slug}
                            onChange={(e) => {
                                const newConfigs = [...selectedConfigs];
                                newConfigs[idx].slug = e.target.value;
                                setSelectedConfigs(newConfigs);
                            }}
                        >
                            <option value="">Select Attribute</option>
                            {attributesList.map(attr => <option key={attr.uuid} value={attr.slug}>{attr.name}</option>)}
                        </select>
                        <input 
                            className="form-input"
                            placeholder="Values (comma separated: Red, Blue)"
                            onBlur={(e) => {
                                const newConfigs = [...selectedConfigs];
                                newConfigs[idx].values = e.target.value.split(',').map(v => v.trim()).filter(Boolean);
                                setSelectedConfigs(newConfigs);
                            }}
                        />
                        <button type="button" onClick={() => setSelectedConfigs(selectedConfigs.filter((_, i) => i !== idx))}><Trash2 size={16}/></button>
                    </div>
                ))}
                <button type="button" className="btn-text" onClick={addConfigRow}><Plus size={14}/> Add Attribute</button>
                <button type="button" className="btn-primary-sm" onClick={handleGenerate}><RefreshCw size={14}/> Generate Matrix</button>
            </div>

            {/* Bảng ma trận biến thể */}
            {variants.length > 0 && (
                <div className="variant-matrix">
                    <table className="matrix-table">
                        <thead>
                            <tr>
                                <th>Variant</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Stock per Warehouse</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {variants.map((v, i) => (
                                <tr key={i}>
                                    <td className="font-bold">{v.attributes.map(a => a.value).join(' / ')}</td>
                                    <td><input className="form-input sm" value={v.sku} onChange={(e) => {
                                        const next = [...variants]; next[i].sku = e.target.value; onChange(next);
                                    }}/></td>
                                    <td><input type="number" className="form-input sm" value={v.price} onChange={(e) => {
                                        const next = [...variants]; next[i].price = e.target.value; onChange(next);
                                    }}/></td>
                                    <td>
                                        <MultiWarehouseStock 
                                            warehouses={warehouses} 
                                            value={v.stock} 
                                            onChange={(newStock) => {
                                                const next = [...variants]; next[i].stock = newStock; onChange(next);
                                            }}
                                        />
                                    </td>
                                    <td><button type="button" onClick={() => onChange(variants.filter((_, idx) => idx !== i))}><X size={16}/></button></td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
};

export default VariantGenerator;