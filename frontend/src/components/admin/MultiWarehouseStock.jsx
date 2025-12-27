import React from 'react';

const MultiWarehouseStock = ({ warehouses, value = [], onChange }) => {
    
    const handleQuantityChange = (warehouseUuid, qty) => {
        const quantity = parseInt(qty) || 0;
        let newValue = [...value];
        
        const index = newValue.findIndex(s => s.warehouse_uuid === warehouseUuid);
        if (index > -1) {
            newValue[index] = { ...newValue[index], quantity };
        } else {
            newValue.push({ warehouse_uuid: warehouseUuid, quantity });
        }
        
        onChange(newValue);
    };

    return (
        <div className="stock-manager-grid">
            {warehouses.map(wh => {
                const stockItem = value.find(s => s.warehouse_uuid === wh.uuid);
                return (
                    <div key={wh.uuid} className="stock-item-row">
                        <span className="wh-name">{wh.name}</span>
                        <input
                            type="number"
                            min="0"
                            className="form-input sm"
                            placeholder="Qty"
                            value={stockItem?.quantity || 0}
                            onChange={(e) => handleQuantityChange(wh.uuid, e.target.value)}
                        />
                    </div>
                );
            })}
        </div>
    );
};

export default MultiWarehouseStock;