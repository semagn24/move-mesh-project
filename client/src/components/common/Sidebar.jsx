import { Link, useLocation } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';

const Sidebar = ({ isOpen, toggleSidebar }) => {
    const location = useLocation();
    const { user } = useAuth(); // Use Auth Hook

    const menuItems = [
        { path: '/', label: 'Home', icon: 'fa-home' },
        { path: '/movies', label: 'Movies', icon: 'fa-film' },
        { path: '/trending', label: 'Trending', icon: 'fa-fire' },
        { path: '/about', label: 'About', icon: 'fa-info-circle' },
    ];

    return (
        <>
            {/* Overlay for mobile */}
            {isOpen && (
                <div
                    className="fixed inset-0 bg-black/60 z-40 lg:hidden backdrop-blur-sm"
                    onClick={toggleSidebar}
                ></div>
            )}

            <aside className={`fixed top-0 left-0 h-full w-[260px] bg-secondary border-right border-white/5 z-50 transition-transform duration-300 lg:translate-x-0 ${isOpen ? 'translate-x-0' : '-translate-x-full'}`}>
                <div className="p-8">
                    <Link to="/" className="text-2xl font-black text-primary tracking-widest block mb-12">
                        MOVIESTREAM
                    </Link>

                    <nav className="space-y-4">
                        <div className="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Menu</div>
                        {menuItems.map((item) => (
                            <Link
                                key={item.path}
                                to={item.path}
                                onClick={() => window.innerWidth < 1024 && toggleSidebar()}
                                className={`flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all ${location.pathname === item.path
                                    ? 'bg-primary/10 text-primary border border-primary/20'
                                    : 'text-gray-400 hover:text-white hover:bg-white/5'
                                    }`}
                            >
                                <i className={`fa ${item.icon} w-5 text-center`}></i>
                                {item.label}
                            </Link>
                        ))}

                        {user?.role === 'admin' && (
                            <>
                                <div className="text-xs font-bold text-gray-500 uppercase tracking-widest mt-8 mb-4">Admin</div>
                                <Link
                                    to="/admin"
                                    onClick={() => window.innerWidth < 1024 && toggleSidebar()}
                                    className={`flex items-center gap-4 px-4 py-3 rounded-xl font-medium transition-all ${location.pathname === '/admin'
                                        ? 'bg-primary/10 text-primary border border-primary/20'
                                        : 'text-gray-400 hover:text-white hover:bg-white/5'
                                        }`}
                                >
                                    <i className="fa fa-chart-line w-5 text-center"></i>
                                    Dashboard
                                </Link>
                                <Link
                                    to="/admin/add-movie"
                                    className="flex items-center gap-4 px-4 py-3 rounded-xl font-medium text-gray-400 hover:text-white hover:bg-white/5"
                                >
                                    <i className="fa fa-plus-square w-5 text-center"></i>
                                    Add Movie
                                </Link>
                            </>
                        )}
                    </nav>
                </div>

                <div className="absolute bottom-8 left-0 right-0 px-8">
                    {user ? (
                        <Link to="/profile" className="block">
                            <div className="flex items-center gap-3 p-4 bg-white/5 rounded-2xl border border-white/5 hover:bg-white/10 transition-colors">
                                <div className="w-10 h-10 bg-primary/20 text-primary flex items-center justify-center rounded-xl font-bold">
                                    {user.username[0].toUpperCase()}
                                </div>
                                <div className="overflow-hidden">
                                    <div className="text-sm font-bold truncate">{user.username}</div>
                                    <div className="text-xs text-gray-500 truncate">{user.email}</div>
                                </div>
                            </div>
                        </Link>
                    ) : (
                        <Link to="/login" className="flex items-center justify-center gap-3 w-full py-4 bg-primary rounded-2xl font-bold hover:bg-red-700 transition-all shadow-lg shadow-red-900/20">
                            <i className="fa fa-sign-in-alt"></i> Sign In
                        </Link>
                    )}
                </div>
            </aside>
        </>
    );
};

export default Sidebar;
