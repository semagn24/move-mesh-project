import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import NotificationDropdown from './NotificationDropdown';
import SearchDropdown from './SearchDropdown';

const Navbar = ({ toggleSidebar }) => {
    const navigate = useNavigate();
    const { user, logout } = useAuth(); // Use Auth Hook

    const handleLogout = async () => {
        await logout();
        navigate('/login');
    };

    return (
        <nav className="sticky top-0 z-40 px-6 py-5 flex justify-between items-center mb-4 transition-all bg-secondary/80 backdrop-blur-xl">
            <div className="flex items-center gap-4">
                <button
                    onClick={toggleSidebar}
                    className="lg:hidden w-10 h-10 flex items-center justify-center bg-white/5 rounded-xl text-white hover:bg-white/10 transition-all shadow-lg"
                >
                    <i className="fa fa-bars"></i>
                </button>

<<<<<<< HEAD
=======
                {/* Mobile Logo */}
                <Link to="/" className="lg:hidden text-lg font-black text-primary tracking-widest ml-2">
                    MOVIESTREAM
                </Link>

                {/* Desktop Breadcrumb/Title */}
>>>>>>> origin/main
                <div className="hidden lg:block">
                    <h2 className="text-sm font-bold text-gray-500 uppercase tracking-[0.2em]">
                        {window.location.pathname === '/' ? 'Home' : 'Explore'}
                    </h2>
                </div>
            </div>

            <div className="flex items-center gap-6">
                <SearchDropdown />

                {user ? (
                    <div className="flex items-center gap-4">
                        <NotificationDropdown />
                        <div className="h-8 w-px bg-white/10 mx-2"></div>
                        <button onClick={handleLogout} className="flex items-center gap-2 text-gray-400 hover:text-red-500 transition-colors font-medium text-sm">
                            <i className="fa fa-sign-out-alt"></i>
                            <span className="hidden md:inline">Logout</span>
                        </button>
                    </div>
                ) : (
                    <div className="flex items-center gap-4">
                        <Link to="/login" className="text-gray-400 hover:text-white font-bold transition-colors">Sign In</Link>
                        <Link to="/register" className="px-6 py-2.5 bg-primary text-white rounded-xl font-bold hover:bg-red-700 transition-all shadow-lg shadow-red-900/20 active:scale-95">
                            Join Now
                        </Link>
                    </div>
                )}
            </div>
        </nav>
    );
};

export default Navbar;
