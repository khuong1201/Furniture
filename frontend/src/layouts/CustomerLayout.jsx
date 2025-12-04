import { Outlet } from 'react-router-dom';
import Header from '../pages/customer/header/Header';
import Footer from '../pages/customer/footer/Footer';

const CustomerLayout = () => {
  return (
    <div className="customer-layout">
      <Header />
      <main className="customer-main">
        <Outlet />
      </main>
      <Footer />
    </div>
  );
};

export default CustomerLayout;

