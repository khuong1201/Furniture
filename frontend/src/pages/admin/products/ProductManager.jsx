import React, { useState } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { Package, Layers, Tag, Sliders, Plus } from 'lucide-react';
import ProductList from './ProductList';
import CategoryList from '../categories/CategoryList';
import BrandList from '../brands/BrandList';
import AttributeList from '../attributes/AttributeList';
import './ProductManager.css';

const ProductManager = () => {
    const navigate = useNavigate();
    const location = useLocation();
    const query = new URLSearchParams(location.search);
    const activeTab = query.get('tab') || 'products';

    // State quản lý Modal của Attribute (Do nút bấm nằm ở đây)
    const [isAttrModalOpen, setIsAttrModalOpen] = useState(false);

    // Cấu hình Tabs
    const tabs = [
        { id: 'products', label: 'Products', icon: Package },
        { id: 'categories', label: 'Categories', icon: Layers },
        { id: 'brands', label: 'Brands', icon: Tag },
        { id: 'attributes', label: 'Attributes', icon: Sliders },
    ];

    // Xử lý sự kiện bấm nút "Add New"
    const handleAddAction = () => {
        switch (activeTab) {
            case 'products':
                navigate('/admin/products/create');
                break;
            case 'categories':
                navigate('/admin/categories/create');
                break;
            case 'brands':
                navigate('/admin/brands/create');
                break;
            case 'attributes':
                setIsAttrModalOpen(true); // Mở modal truyền xuống component con
                break;
            default:
                break;
        }
    };

    // Helper: Lấy tên nút bấm dựa trên tab
    const getButtonLabel = () => {
        switch (activeTab) {
            case 'products': return 'Add Product';
            case 'categories': return 'Add Category';
            case 'brands': return 'Create Brand';
            case 'attributes': return 'Create Attribute';
            default: return 'Add New';
        }
    };

    return (
        <div className="product-manager-wrapper">
            {/* --- 1. HEADER CHUNG (Chứa Title + Nút Add) --- */}
            <div className="manager-header-section">
                <div className="header-left">
                    <h1 className="page-title">Product Management</h1>
                    <p className="page-subtitle">Central hub for your catalog configuration.</p>
                </div>
                
                {/* Nút Add nằm ở đây */}
                <div className="header-right">
                    <button onClick={handleAddAction} className="btn-primary-gradient">
                        <Plus size={18} /> {getButtonLabel()}
                    </button>
                </div>
            </div>

            {/* --- 2. TABS NAVIGATION --- */}
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

            {/* --- 3. CONTENT AREA --- */}
            <div className="manager-content-area">
                {activeTab === 'products' && <div className="animate-fade-in"><ProductList /></div>}
                {activeTab === 'categories' && <div className="animate-fade-in"><CategoryList /></div>}
                {activeTab === 'brands' && <div className="animate-fade-in"><BrandList /></div>}
                
                {/* Truyền props điều khiển Modal xuống AttributeList */}
                {activeTab === 'attributes' && (
                    <div className="animate-fade-in">
                        <AttributeList 
                            externalModalOpen={isAttrModalOpen} 
                            setExternalModalOpen={setIsAttrModalOpen} 
                        />
                    </div>
                )}
            </div>
        </div>
    );
};

export default ProductManager;