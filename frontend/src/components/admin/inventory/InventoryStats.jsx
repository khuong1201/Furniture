import React from 'react';
import { Package, Layers, AlertTriangle, AlertCircle } from 'lucide-react';
import './InventoryStats.css'; // Import file CSS riêng

const StatCard = ({ title, value, icon: Icon, color, subtext, type }) => (
    <div className={`stat-card stat-${type}`}>
        <div className="stat-icon-wrapper">
            <Icon size={24} strokeWidth={2} />
        </div>
        <div className="stat-content">
            <span className="stat-title">{title}</span>
            <div className="stat-value-row">
                <span className="stat-value">
                    {value?.toLocaleString() || 0}
                </span>
            </div>
            {subtext && <span className="stat-subtext">{subtext}</span>}
        </div>
    </div>
);

const InventoryStats = ({ stats }) => {
    // Nếu chưa có data, hiển thị loading skeleton hoặc null
    if (!stats) {
        return (
            <div className="stats-grid-container">
                {[1, 2, 3, 4].map(i => <div key={i} className="stat-card skeleton"></div>)}
            </div>
        );
    }

    return (
        <div className="stats-grid-container">
            <StatCard 
                title="Total SKUs" 
                value={stats.total_skus} 
                icon={Layers} 
                type="blue"
                subtext="Active Variants"
            />
            <StatCard 
                title="Total Items" 
                value={stats.total_items} 
                icon={Package} 
                type="green"
                subtext="Total Quantity"
            />
            <StatCard 
                title="Low Stock" 
                value={stats.low_stock_count} 
                icon={AlertTriangle} 
                type="amber"
                subtext="Restock Needed"
            />
            <StatCard 
                title="Out of Stock" 
                value={stats.out_of_stock_count} 
                icon={AlertCircle} 
                type="red"
                subtext="Critical Alert"
            />
        </div>
    );
};

export default InventoryStats;