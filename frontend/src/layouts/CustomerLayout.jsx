import { Outlet } from 'react-router-dom';
import Header from '../pages/customer/header/Header';

const CustomerLayout = () => {
  return (
    <div>
      <Header />
      <Outlet />
    </div>
  );
};

export default CustomerLayout;
