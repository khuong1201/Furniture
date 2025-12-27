import { Routes, Route } from 'react-router-dom';
import './App.css'
import CustomerLayout from './layouts/CustomerLayout';
import AdminLayout from './layouts/AdminLayout';

// Customer Pages
import HomePage from './pages/customer/homePage/HomePage';
import Register from './pages/customer/register/RegisterForm';
import LogIn from './pages/customer/login/LoginForm';
import ProductDetail from './pages/customer/Product/ProductDetail';
import SearchPage from './pages/customer/searchPage/SearchPage';
import CartPage from './pages/customer/cart/CartPage';
import OrdersDetail from './pages/customer/order/OrderDetail';
import Payment from './pages/customer/payment/PaymentHistory';
import Notification from './pages/customer/notification/NotificationPage';
import CategoryPage from './pages/customer/category/CategoryPage'; 
import ProfilePage from './pages/customer/user/ProfilePage';

// Admin Pages
import Dashboard from './pages/admin/dashboard/Dashboard';
import BrandList from './pages/admin/brands/BrandList';
import BrandForm from './pages/admin/brands/BrandForm';
import ProductList from './pages/admin/products/ProductList';
import ProductForm from './pages/admin/products/ProductForm';
import AdminProductDetail from './pages/admin/products/ProductDetail';
import ProductManager from './pages/admin/products/ProductManager';
import CategoryList from './pages/admin/categories/CategoryList';
import CategoryForm from './pages/admin/categories/CategoryForm';
import OrderList from './pages/admin/orders/OrderList';
import OrderForm from './pages/admin/orders/OrderForm';
import OrderDetail from './pages/admin/orders/OrderDetail';
import UserList from './pages/admin/users/UserList';
import UserForm from './pages/admin/users/UserForm';
import UserDetail from './pages/admin/users/UserDetail';
import InventoryManager from './pages/admin/inventory/InventoryManager'; // Cái này là Tab view cũ (nếu còn dùng)
import InventoryForm from './pages/admin/inventory/InventoryForm';
import InventoryList from './pages/admin/inventory/InventoryList';
import WarehouseList from './pages/admin/warehouses/WarehouseList';
import WarehouseForm from './pages/admin/warehouses/WarehouseForm';
import PromotionList from './pages/admin/promotions/PromotionList';
import PromotionForm from './pages/admin/promotions/PromotionForm';
import RoleList from './pages/admin/roles/RoleList';
import RoleForm from './pages/admin/roles/RoleForm';
import PermissionList from './pages/admin/permissions/PermissionList';
import LogList from './pages/admin/activity-logs/LogList';
import AttributeList from './pages/admin/attributes/AttributeList';
import AttributeForm from './pages/admin/attributes/AttributeForm';
import PaymentList from './pages/admin/payments/PaymentList';
import PaymentDetail from './pages/admin/payments/PaymentDetail';
import ReviewList from './pages/admin/reviews/ReviewList';
import CollectionList from './pages/admin/collections/CollectionList';
import CollectionForm from './pages/admin/collections/CollectionForm';
import ShippingList from './pages/admin/shippings/ShippingList';
import ShippingForm from './pages/admin/shippings/ShippingForm';
import SettingsPage from './pages/admin/settings/SettingsPage';
import AdminProfile from './pages/admin/profile/AdminProfile';
import AdminLogin from './pages/admin/login/AdminLogin';
import NotificationList from './pages/admin/notifications/NotificationList';

function App() {
  return (
    <Routes>
      {/* --- CUSTOMER ROUTES --- */}
      <Route path="/" element={<CustomerLayout />}>
        <Route index element={<HomePage />} />
        <Route path="category/:slug" element={<CategoryPage />} />
        <Route path="register" element={<Register />} />
        <Route path="login" element={<LogIn />} />
        <Route path="me" element={<ProfilePage />} />
        <Route path="product/:id" element={<ProductDetail />} />
        <Route path="product" element={<SearchPage />} />
        <Route path="cart" element={<CartPage />} />
        <Route path="orders/:uuid" element={<OrdersDetail />} /> 
        <Route path="payment" element={<Payment />} />
        <Route path="notification" element={<Notification />} />
      </Route>

      {/* --- ADMIN LOGIN --- */}
      <Route path="/admin/login" element={<AdminLogin />} />

      {/* --- ADMIN ROUTES --- */}
      <Route path='/admin' element={<AdminLayout />}>
        <Route index element={<Dashboard />} />
        
        {/* Attributes */}
        <Route path="attributes" element={<AttributeList />} />
        <Route path="attributes/create" element={<AttributeForm />} />
        <Route path="attributes/:uuid/edit" element={<AttributeForm />} />
        
        {/* Brands */}
        <Route path="brands" element={<BrandList />} />
        <Route path="brands/create" element={<BrandForm />} />
        <Route path="brands/:uuid/edit" element={<BrandForm />} />
        
        {/* Products */}
        <Route path="products" element={<ProductList />} />
        <Route path="product-manager" element={<ProductManager />} />
        <Route path="products/create" element={<ProductForm />} />
        <Route path="products/:uuid/edit" element={<ProductForm />} />
        <Route path="products/:uuid" element={<AdminProductDetail />} />
        
        {/* Categories */}
        <Route path="categories" element={<CategoryList />} />
        <Route path="categories/create" element={<CategoryForm />} />
        <Route path="categories/:uuid/edit" element={<CategoryForm />} />
        
        {/* Orders */}
        <Route path="orders" element={<OrderList />} />
        <Route path="orders/create" element={<OrderForm />} />
        <Route path="orders/:uuid" element={<OrderDetail />} />
        
        {/* Users */}
        <Route path="users" element={<UserList />} />
        <Route path="users/create" element={<UserForm />} />
        <Route path="users/:uuid" element={<UserDetail />} />
        <Route path="users/:uuid/edit" element={<UserForm />} />
        
        {/* Inventory & Warehouses (UPDATED) */}
        <Route path="inventory-manager" element={<InventoryManager />} />
        
        {/* Sửa 'inventory' thành 'inventories' để khớp với code navigate */}
        <Route path="inventories" element={<InventoryList />} />
        
        {/* Bỏ dấu '/' ở đầu để thành relative path trong AdminLayout */}
        <Route path="inventories/:uuid/adjust" element={<InventoryForm />} />
        
        <Route path="warehouses" element={<WarehouseList />} />
        <Route path="warehouses/create" element={<WarehouseForm />} />
        <Route path="warehouses/:uuid/edit" element={<WarehouseForm />} />
        
        {/* Promotions */}
        <Route path="promotions" element={<PromotionList />} />
        <Route path="promotions/create" element={<PromotionForm />} />
        <Route path="promotions/:uuid/edit" element={<PromotionForm />} />
        
        {/* Roles & Permissions */}
        <Route path="roles" element={<RoleList />} />
        <Route path="roles/create" element={<RoleForm />} />
        <Route path="roles/:uuid/edit" element={<RoleForm />} />
        <Route path="permissions" element={<PermissionList />} />
        
        {/* Others */}
        <Route path="logs" element={<LogList />} />
        <Route path="payments" element={<PaymentList />} />
        <Route path="payments/:uuid" element={<PaymentDetail />} />
        <Route path="reviews" element={<ReviewList />} />
        
        {/* Collections */}
        <Route path="collections" element={<CollectionList />} />
        <Route path="collections/create" element={<CollectionForm />} />
        <Route path="collections/:uuid/edit" element={<CollectionForm />} />
        
        {/* Shippings */}
        <Route path="shippings" element={<ShippingList />} />
        <Route path="shippings/create" element={<ShippingForm />} />
        <Route path="shippings/:uuid/edit" element={<ShippingForm />} />
        
        <Route path="settings" element={<SettingsPage />} />
        <Route path="notifications" element={<NotificationList />} />
        <Route path="notifications/:uuid" element={<NotificationList />} />
        <Route path="profile" element={<AdminProfile />} />
      </Route>
    </Routes>
  );
}

export default App;