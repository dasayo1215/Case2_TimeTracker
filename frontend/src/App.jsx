import { BrowserRouter, Routes } from 'react-router-dom';
import { useAuth } from './contexts/AuthContext';
import GuestHeader from './components/headers/GuestHeader';
import AdminHeader from './components/headers/AdminHeader';
import UserHeader from './components/headers/UserHeader';
import './css/header.css';

import { UserRoutes } from './routes/UserRoutes';
import { AdminRoutes } from './routes/AdminRoutes';
import { CommonRoutes } from './routes/CommonRoutes';

function Layout() {
    const { user } = useAuth();
    const role = user ? user.role : 'guest';

    return (
        <>
            {role === 'guest' && <GuestHeader />}
            {role === 'admin' && <AdminHeader />}
            {role === 'user' && <UserHeader />}

            <Routes>
                {UserRoutes()}
                {AdminRoutes()}
                {CommonRoutes()}
            </Routes>
        </>
    );
}

function App() {
    return (
        <BrowserRouter>
            <Layout />
        </BrowserRouter>
    );
}

export default App;
