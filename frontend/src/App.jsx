import { Routes, Route } from 'react-router-dom';
import './App.css'
import CustomerLayout from './layouts/CustomerLayout';
import AdminLayout from './layouts/AdminLayout';
import HomePage from './pages/customer/homePage/HomePage';
import Register from './pages/customer/register/RegisterForm';
import LogIn from './pages/customer/login/LoginForm';
import ProductDetail from './pages/customer/Product/ProductDetail';
import SearchPage from './pages/customer/SearchPage';
import CartPage from './pages/customer/cart/CartPage';

function App() {
  return (
    <Routes>

      {/*CUSTOMER */}
      <Route path="/customer" element={<CustomerLayout />}>

        <Route index element={<HomePage />} />
        <Route path="register" element={<Register />} />
        <Route path="login" element={<LogIn />} />
        <Route path="product/:id" element={<ProductDetail />} />
        <Route path="product" element={<SearchPage />} />
        <Route path="cart" element={<CartPage />} />

      </Route>

      <Route path='/admin' element={<AdminLayout/>}>

      </Route>
    </Routes>
  );
}

export default App;
