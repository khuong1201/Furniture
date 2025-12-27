import { useState, useEffect, useCallback } from 'react';
import DashboardService from '@/services/admin/DashboardService';
import OrderService from '@/services/admin/OrderService';

export const useDashboard = () => {
    const [loading, setLoading] = useState(true);
    
    // Filter States
    const [filterType, setFilterType] = useState('month'); // today, week, month, year
    const [selectedMonth, setSelectedMonth] = useState(new Date().getMonth() + 1);
    const [selectedYear, setSelectedYear] = useState(new Date().getFullYear());

    // Data States
    const [stats, setStats] = useState({
        totalRevenue: 0, revenueGrowth: 0,
        totalOrders: 0, orderGrowth: 0,
        totalUsers: 0, newUsers: 0,
        lowStock: 0, compareLabel: 'vs previous'
    });
    const [revenueData, setRevenueData] = useState([]);
    const [bestSellers, setBestSellers] = useState([]); 
    const [topCustomers, setTopCustomers] = useState([]);
    
    // Orders Pagination States
    const [recentOrders, setRecentOrders] = useState([]);
    const [orderPage, setOrderPage] = useState(1);
    const [hasMoreOrders, setHasMoreOrders] = useState(true);
    const [loadingOrders, setLoadingOrders] = useState(false);

    const fetchSummaryData = useCallback(async () => {
        try {
            setLoading(true);
            
            // Build Params
            const params = {
                range: filterType,
                year: selectedYear,
            };
            if (filterType === 'month') {
                params.month = selectedMonth;
            }

            // Gọi API (Backend tự quyết định chart period dựa trên range)
            const res = await DashboardService.getSummary(params);

            if (res && res.data) {
                const d = res.data;
                const cards = d.cards || {};

                setStats({
                    totalRevenue: Number(cards.total_revenue || 0),
                    revenueGrowth: Number(cards.revenue_growth || 0),
                    totalOrders: Number(cards.total_orders || 0),
                    orderGrowth: Number(cards.order_growth || 0),
                    totalUsers: Number(cards.total_users || 0),
                    newUsers: Number(cards.new_users_period || 0),
                    lowStock: Number(cards.low_stock_variants || 0),
                    compareLabel: cards.compare_label || 'vs previous'
                });

                // Map Chart
                let rawRevenue = Array.isArray(d.revenue_chart) 
                    ? d.revenue_chart 
                    : Object.values(d.revenue_chart || {});

                setRevenueData(rawRevenue.map(item => ({
                    name: item.label, // "Monday" | "Week 1" | "Jan"
                    value: Number(item.value || 0),
                    date: item.date 
                })));

                setBestSellers(d.best_sellers || []);
                setTopCustomers(d.top_spenders || []);
            }
        } catch (error) {
            console.error('Dashboard Error:', error);
        } finally {
            setLoading(false);
        }
    }, [filterType, selectedMonth, selectedYear]);

    const fetchOrders = useCallback(async (page, isLoadMore = false) => {
        try {
            setLoadingOrders(true);
            const res = await OrderService.getOrders({ page: page, per_page: 10 });
            
            // --- FIX TẠI ĐÂY ---
            // API trả về: { success: true, data: [...Array], meta: {...} }
            // Code cũ: res.data?.data (sai, vì res.data chính là Array rồi)
            
            // Logic kiểm tra an toàn:
            // 1. Nếu res.data là mảng -> lấy luôn
            // 2. Nếu res.data.data tồn tại (cấu trúc Laravel chuẩn khác) -> lấy cái đó
            // 3. Không có gì -> trả về mảng rỗng
            const orderList = Array.isArray(res.data) 
                ? res.data 
                : (res.data?.data || []); 

            if (isLoadMore) {
                setRecentOrders(prev => [...prev, ...orderList]);
            } else {
                setRecentOrders(orderList);
            }
            
            // Logic ẩn nút Load More nếu hết dữ liệu
            if (orderList.length < 10) setHasMoreOrders(false); 

        } catch (error) {
            console.error('Error fetching orders:', error);
        } finally {
            setLoadingOrders(false);
        }
    }, []);

    const loadMoreOrders = () => {
        if (!loadingOrders && hasMoreOrders) {
            const nextPage = orderPage + 1;
            setOrderPage(nextPage);
            fetchOrders(nextPage, true);
        }
    };

    useEffect(() => { fetchSummaryData(); }, [fetchSummaryData]);
    useEffect(() => { setOrderPage(1); setHasMoreOrders(true); fetchOrders(1, false); }, []);

    return {
        loading,
        filterType, setFilterType,
        selectedMonth, setSelectedMonth,
        selectedYear, setSelectedYear,
        stats, revenueData, 
        bestSellers, topCustomers,
        recentOrders, loadingOrders, loadMoreOrders,
    };
};