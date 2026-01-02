import { Routes, Route, Navigate } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth';
import Home from '../pages/Home';
import Login from '../pages/Login';
import Register from '../pages/Register';
import Watch from '../pages/Watch';
import Profile from '../pages/Profile';
import About from '../pages/About';
import ForgotPassword from '../pages/ForgotPassword';
import ResetPassword from '../pages/ResetPassword';
import AdminDashboard from '../pages/admin/Dashboard';
import AddMovie from '../pages/admin/AddMovie';
import EditMovies from '../pages/admin/EditMovies';
import EditMovie from '../pages/admin/EditMovie';
import UserManagement from '../pages/admin/UserManagement';
import Payments from '../pages/admin/Payments';


const AppRoutes = () => {
    const { user, loading } = useAuth();

    if (loading) {
        return <div className="min-h-screen flex items-center justify-center text-white">Loading...</div>;
    }

    return (
        <Routes>
            <Route path="/" element={<Home key="home" />} />
            <Route path="/movies" element={<Home key="movies" viewMode="all" />} />
            <Route path="/trending" element={<Home key="trending" viewMode="trending" />} />
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            <Route path="/forgot-password" element={<ForgotPassword />} />
            <Route path="/reset-password/:token" element={<ResetPassword />} />
            <Route path="/movies/:id" element={<Watch />} />
            <Route path="/about" element={<About />} />
            <Route path="/profile" element={user ? <Profile /> : <Navigate to="/login" />} />
            <Route path="/admin" element={user?.role === 'admin' ? <AdminDashboard /> : <Navigate to="/" />} />
            <Route path="/admin/add-movie" element={user?.role === 'admin' ? <AddMovie /> : <Navigate to="/" />} />
            <Route path="/admin/edit-movies" element={user?.role === 'admin' ? <EditMovies /> : <Navigate to="/" />} />
            <Route path="/admin/edit-movie/:id" element={user?.role === 'admin' ? <EditMovie /> : <Navigate to="/" />} />
            <Route path="/admin/users" element={user?.role === 'admin' ? <UserManagement /> : <Navigate to="/" />} />
            <Route path="/admin/payments" element={user?.role === 'admin' ? <Payments /> : <Navigate to="/" />} />

            <Route path="*" element={<Navigate to="/" />} />
        </Routes>
    );
};

export default AppRoutes;
