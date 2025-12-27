import React from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { LayoutDashboard, Warehouse, Package, Plus } from 'lucide-react';
import InventoryDashboard from './InventoryDashboard';
import WarehouseList from '../warehouses/WarehouseList';
import InventoryList from './InventoryList';
import './InventoryManager.css';

const InventoryManager = () => {
    const navigate = useNavigate();
    const location = useLocation();
    const query = new URLSearchParams(location.search);
    const activeTab = query.get('tab') || 'dashboard';

    const tabs = [
        { id: 'dashboard', label: 'Overview', icon: LayoutDashboard },
        { id: 'warehouses', label: 'Warehouses', icon: Warehouse },
        { id: 'stocks', label: 'Inventory List', icon: Package }
    ];

    const handleAddAction = () => {
        if (activeTab === 'warehouses') {
            navigate('/admin/warehouses/create');
        }
    };

    return (
        <div className="inventory-manager-wrapper">
            {/* Header */}
            <div className="manager-header-section">
                <div className="header-left">
                    <h1 className="page-title">Inventory Management</h1>
                    <p className="page-subtitle">Central hub for warehouse and stock coordination.</p>
                </div>
                
                <div className="header-right">
                    {activeTab === 'warehouses' && (
                        <button onClick={handleAddAction} className="btn-primary-gradient">
                            <Plus size={18} /> Add Warehouse
                        </button>
                    )}
                </div>
            </div>

            {/* Tabs */}
            <div className="manager-tabs-wrapper">
                <div className="manager-tabs">
                    {tabs.map(tab => (
                        <button
                            key={tab.id}
                            onClick={() => navigate(`?tab=${tab.id}`, { replace: true })}
                            className={`tab-item ${activeTab === tab.id ? 'active' : ''}`}
                        >
                            <tab.icon size={18} className="tab-icon" /> 
                            <span>{tab.label}</span>
                            {activeTab === tab.id && <div className="active-indicator" />}
                        </button>
                    ))}
                </div>
            </div>

            {/* Content Area */}
            <div className="manager-content-area">
                {activeTab === 'dashboard' && (
                    <div className="animate-fade-in h-full">
                        <InventoryDashboard />
                    </div>
                )}
                {activeTab === 'warehouses' && (
                    <div className="animate-fade-in h-full">
                        <WarehouseList isManagerMode={true} />
                    </div>
                )}
                {activeTab === 'stocks' && (
                    <div className="animate-fade-in h-full">
                        <InventoryList />
                    </div>
                )}
            </div>
        </div>
    );
};

export default InventoryManager;