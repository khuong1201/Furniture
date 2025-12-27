import React, { useEffect, useState, useMemo } from 'react';
import { Package, ArrowDownLeft, ArrowUpRight, RefreshCw, Warehouse, ChevronDown, Clock } from 'lucide-react';
import WarehouseService from '@/services/admin/WarehouseService';
import InventoryService from '@/services/admin/InventoryService';
import { InventoryHealthChart, StockMovementChart } from '@/components/admin/inventory/InventoryCharts';
import InventoryStats from '@/components/admin/inventory/InventoryStats';
import './InventoryDashboard.css';

const InventoryDashboard = () => {
    const [warehouses, setWarehouses] = useState([]);
    const [warehouseUuid, setWarehouseUuid] = useState('');
    const [statsData, setStatsData] = useState(null);
    const [chartData, setChartData] = useState([]);
    const [loading, setLoading] = useState({ wh: true, stats: false, chart: false });
    const [chartFilter, setChartFilter] = useState({
        period: 'week',
        month: new Date().getMonth() + 1,
        year: new Date().getFullYear()
    });

    // 1. Load Warehouses
    useEffect(() => {
        const fetchWarehouses = async () => {
            try {
                const res = await WarehouseService.getWarehouses({ per_page: 100, is_active: 1 });
                const list = res.data || [];
                setWarehouses(list);
                if (list.length > 0 && !warehouseUuid) setWarehouseUuid(list[0].uuid);
            } catch (e) { console.error(e); } 
            finally { setLoading(prev => ({ ...prev, wh: false })); }
        };
        fetchWarehouses();
    }, []);

    // 2. Load Stats
    useEffect(() => {
        if (!warehouseUuid) return;
        const fetchStats = async () => {
            setLoading(prev => ({ ...prev, stats: true }));
            try {
                const res = await InventoryService.getDashboardStats(warehouseUuid);
                if (res.success) setStatsData(res.data || {});
            } catch (e) { console.error(e); } 
            finally { setLoading(prev => ({ ...prev, stats: false })); }
        };
        fetchStats();
    }, [warehouseUuid]);

    // 3. Load Charts
    useEffect(() => {
        if (!warehouseUuid) return;
        const fetchChart = async () => {
            setLoading(prev => ({ ...prev, chart: true }));
            try {
                const res = await InventoryService.getMovementChart(
                    warehouseUuid, chartFilter.period, chartFilter.month, chartFilter.year
                );
                setChartData(res.success ? (res.data || []) : []);
            } catch (e) { console.error(e); setChartData([]); } 
            finally { setLoading(prev => ({ ...prev, chart: false })); }
        };
        fetchChart();
    }, [warehouseUuid, chartFilter]);

    const handleChartFilterChange = (key, value) => setChartFilter(prev => ({ ...prev, [key]: value }));
    const handleRefresh = () => {
        const currentUuid = warehouseUuid;
        setWarehouseUuid(''); 
        setTimeout(() => setWarehouseUuid(currentUuid), 10);
    };

    const dynamicMovementStats = useMemo(() => {
        return chartData.reduce((acc, item) => ({
            inbound: acc.inbound + (parseInt(item.inbound) || 0),
            outbound: acc.outbound + (parseInt(item.outbound) || 0)
        }), { inbound: 0, outbound: 0 });
    }, [chartData]);

    if (loading.wh) return <div className="loading-state">Loading Dashboard...</div>;

    return (
        <div className="dashboard-layout">
            {/* HEADER */}
            <div className="dashboard-header">
                <div className="header-title-group">
                    <h1 className="page-title">Inventory Overview</h1>
                    <p className="page-subtitle">Real-time stock monitoring & analysis</p>
                </div>

                <div className="header-controls">
                    <div className="custom-select-wrapper">
                        <Warehouse size={18} className="select-icon-left" />
                        <select value={warehouseUuid} onChange={(e) => setWarehouseUuid(e.target.value)}>
                            {warehouses.map(wh => (
                                <option key={wh.uuid} value={wh.uuid}>{wh.name}</option>
                            ))}
                        </select>
                        <ChevronDown size={14} className="select-icon-right" />
                    </div>
                    
                    <button className="refresh-button" onClick={handleRefresh} title="Refresh Data">
                        <RefreshCw size={18} className={(loading.stats || loading.chart) ? 'animate-spin' : ''} />
                    </button>
                </div>
            </div>

            {/* SECTION 1: KEY METRICS */}
            <InventoryStats stats={statsData?.cards} />

            {/* SECTION 2: DYNAMIC MOVEMENT CARDS */}
            <div className="stats-grid-container">
                 {/* Card 1: Inbound */}
                 <div className="stat-card" style={{ '--card-accent': '#c5a47e' }}>
                    <div className="stat-icon-wrapper" style={{ color: '#c5a47e', backgroundColor: '#c5a47e15' }}>
                        <ArrowDownLeft size={24} strokeWidth={2} />
                    </div>
                    <div className="stat-content">
                        <span className="stat-title">Inbound Volume</span>
                        <div className="stat-value-row">
                            <span className="stat-value">{dynamicMovementStats.inbound.toLocaleString()}</span>
                        </div>
                        <span className="stat-subtext">Selected period</span>
                    </div>
                    <div className="stat-decoration" style={{ backgroundColor: '#c5a47e' }} />
                </div>

                {/* Card 2: Outbound */}
                <div className="stat-card" style={{ '--card-accent': '#1e2532' }}>
                    <div className="stat-icon-wrapper" style={{ color: '#1e2532', backgroundColor: '#1e253215' }}>
                        <ArrowUpRight size={24} strokeWidth={2} />
                    </div>
                    <div className="stat-content">
                        <span className="stat-title">Outbound Volume</span>
                        <div className="stat-value-row">
                            <span className="stat-value">{dynamicMovementStats.outbound.toLocaleString()}</span>
                        </div>
                        <span className="stat-subtext">Selected period</span>
                    </div>
                    <div className="stat-decoration" style={{ backgroundColor: '#1e2532' }} />
                </div>
                
                {/* Card 3: Old Stock */}
                 <div className="stat-card" style={{ '--card-accent': '#6b7280' }}>
                    <div className="stat-icon-wrapper" style={{ color: '#6b7280', backgroundColor: '#f3f4f6' }}>
                        <Clock size={24} strokeWidth={2} />
                    </div>
                    <div className="stat-content">
                        <span className="stat-title">Old Stock</span>
                        <div className="stat-value-row">
                            <span className="stat-value">{statsData?.cards?.old_stock_count || 0}</span>
                        </div>
                        <span className="stat-subtext">&gt; 90 days inactive</span>
                    </div>
                    <div className="stat-decoration" style={{ backgroundColor: '#6b7280' }} />
                </div>
            </div>

            {/* SECTION 3: CHARTS */}
            <div className="charts-grid-container">
                <div className="chart-wrapper">
                    <StockMovementChart 
                        data={chartData} 
                        filter={chartFilter} 
                        onFilterChange={handleChartFilterChange} 
                        loading={loading.chart} 
                    />
                </div>
                <div className="chart-wrapper">
                    <InventoryHealthChart stats={statsData?.cards} />
                </div>
            </div>
        </div>
    );
};

export default InventoryDashboard;