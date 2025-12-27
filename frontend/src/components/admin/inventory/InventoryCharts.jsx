import React from 'react';
import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend, PieChart, Pie, Cell
} from 'recharts';
import { ChevronDown } from 'lucide-react';
import './InventoryCharts.css'; // Import CSS riêng cho chart

const COLORS = { gold: '#c5a47e', dark: '#1e2532', green: '#10b981', red: '#ef4444', orange: '#f59e0b', gray: '#e5e7eb' };

export const InventoryHealthChart = ({ stats }) => {
    const safeStats = stats || { total_skus: 0, out_of_stock_count: 0, low_stock_count: 0 };
    const healthyCount = Math.max(0, safeStats.total_skus - (safeStats.out_of_stock_count + safeStats.low_stock_count));
    
    const data = [
        { name: 'Healthy', value: healthyCount, color: COLORS.green },
        { name: 'Low Stock', value: safeStats.low_stock_count, color: COLORS.orange },
        { name: 'Out of Stock', value: safeStats.out_of_stock_count, color: COLORS.red },
    ].filter(d => d.value > 0);

    return (
        <div className="chart-box">
            <div className="box-header">
                <h3>Inventory Health</h3>
            </div>
            <div className="box-body center-content">
                {!stats ? (
                    <div className="loading-text">Loading...</div>
                ) : data.length === 0 ? (
                    <div className="empty-state">No inventory data available</div>
                ) : (
                    <ResponsiveContainer width="100%" height="100%">
                        <PieChart>
                            <Pie 
                                data={data} 
                                cx="50%" cy="50%" 
                                innerRadius={60} 
                                outerRadius={80} 
                                paddingAngle={5} 
                                dataKey="value" 
                                stroke="none"
                            >
                                {data.map((entry, index) => <Cell key={`cell-${index}`} fill={entry.color} />)}
                            </Pie>
                            <Tooltip contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 12px rgba(0,0,0,0.15)' }} />
                            <Legend verticalAlign="bottom" height={36} iconType="circle"/>
                        </PieChart>
                    </ResponsiveContainer>
                )}
            </div>
        </div>
    );
};

export const StockMovementChart = ({ data, filter, onFilterChange, loading }) => {
    const chartData = Array.isArray(data) ? data : [];
    const currentYear = new Date().getFullYear();
    const years = Array.from({ length: 5 }, (_, i) => currentYear - i);

    return (
        <div className="chart-box">
            <div className="box-header flex-header"> 
                <h3>Stock Movements</h3>
                
                <div className="chart-filters">
                    <div className="filter-select-wrapper">
                        <select className="chart-select" value={filter.period} onChange={(e) => onFilterChange('period', e.target.value)}>
                            <option value="week">Last 7 Days</option>
                            <option value="month">Monthly</option>
                            <option value="year">Yearly</option>
                        </select>
                        <ChevronDown size={14} className="select-icon" />
                    </div>

                    {filter.period === 'month' && (
                        <div className="filter-select-wrapper">
                            <select className="chart-select" value={filter.month} onChange={(e) => onFilterChange('month', parseInt(e.target.value))}>
                                {Array.from({length: 12}, (_, i) => <option key={i+1} value={i+1}>Month {i+1}</option>)}
                            </select>
                            <ChevronDown size={14} className="select-icon" />
                        </div>
                    )}

                    {(filter.period === 'month' || filter.period === 'year') && (
                        <div className="filter-select-wrapper">
                            <select className="chart-select" value={filter.year} onChange={(e) => onFilterChange('year', parseInt(e.target.value))}>
                                {years.map(y => <option key={y} value={y}>{y}</option>)}
                            </select>
                            <ChevronDown size={14} className="select-icon" />
                        </div>
                    )}
                </div>
            </div>
            
            <div className="box-body relative">
                {loading && <div className="loading-overlay"><div className="spinner"></div></div>}

                {chartData.length === 0 && !loading ? (
                    <div className="empty-state">No movement data recorded for this period.</div>
                ) : (
                    <ResponsiveContainer width="100%" height="100%">
                        <BarChart data={chartData} margin={{ top: 20, right: 10, left: -20, bottom: 0 }} barGap={6}>
                            <CartesianGrid strokeDasharray="3 3" vertical={false} stroke={COLORS.gray} />
                            <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fill: '#6b7280', fontSize: 12 }} dy={10} />
                            <YAxis axisLine={false} tickLine={false} tick={{ fill: '#6b7280', fontSize: 12 }} />
                            <Tooltip cursor={{ fill: '#f9fafb' }} contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 12px rgba(0,0,0,0.1)' }} />
                            <Legend verticalAlign="top" wrapperStyle={{ paddingBottom: '20px' }} iconType="circle"/>
                            <Bar dataKey="inbound" name="Inbound (Import)" fill={COLORS.green} radius={[4, 4, 0, 0]} maxBarSize={40} />
                            <Bar dataKey="outbound" name="Outbound (Export)" fill={COLORS.dark} radius={[4, 4, 0, 0]} maxBarSize={40} />
                        </BarChart>
                    </ResponsiveContainer>
                )}
            </div>
        </div>
    );
};