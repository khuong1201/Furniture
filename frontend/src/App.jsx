import { Routes, Route } from 'react-router-dom';
import './App.css'
import CustomerLayout from './layouts/CustomerLayout';
import AdminLayout from './layouts/AdminLayout';
import HomePage from './pages/customer/homePage/HomePage';
import Register from './pages/customer/register/RegisterForm';
import LogIn from './pages/customer/login/LoginForm';
import ProductDetail from './pages/customer/Product/ProductDetail';

function App() {
  return (
    <Routes>

      {/*CUSTOMER */}
      <Route path="/customer" element={<CustomerLayout />}>

        <Route index element={<HomePage />} />
        <Route path="register" element={<Register />} />
        <Route path="login" element={<LogIn />} />
        <Route path="product/:id" element={<ProductDetail />} />

      </Route>

      <Route path='/admin' element={<AdminLayout/>}>

      </Route>
    </Routes>
  );
}

export default App;
