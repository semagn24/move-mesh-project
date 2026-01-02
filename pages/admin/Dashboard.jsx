import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api/axios';

const Dashboard = () => {
    const [stats, setStats] = useState({
        total_movies: 0,
        total_users: 0,
        watch_count: 0,
        revenue: 0,
        premium_users: 0,
        admins: 0
    });
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchStats = async () => {
            try {
                const response = await api.get('admin/stats');
                if (response.data.success) {
                    setStats(response.data.stats);
                }
            } catch (error) {
                console.error("Failed to fetch admin stats:", error);
            } finally {
                setLoading(false);
            }
        };

        fetchStats();
    }, []);

    if (loading) {
        return (
            <div className="flex justify-center items-center h-64">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
            </div>
        );
    }

    return (
        <div className="max-w-7xl mx-auto px-4">
            <div className="mb-12">
                <h1 className="text-3xl font-bold flex items-center gap-3">
                    <i className="fa fa-chart-line text-primary"></i> Analytics Overview
                </h1>
                <p className="text-gray-500 mt-1">
                    Logged in as: <strong className="text-white">{JSON.parse(localStorage.getItem('user'))?.username || 'Admin'}</strong>
                </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                {/* Revenue Box */}
                <div className="glass p-8 rounded-2xl border-l-4 border-l-green-500 relative overflow-hidden group hover:-translate-y-2 transition-transform duration-300">
                    <i className="fa fa-money-bill-wave absolute -right-4 -bottom-4 text-7xl opacity-5 group-hover:opacity-10 transition-opacity"></i>
                    <div className="text-green-500 text-3xl font-bold mb-1">
                        {new Number(stats.revenue || 0).toLocaleString()} <small className="text-sm">ETB</small>
                    </div>
                    <div className="text-gray-500 text-xs uppercase tracking-wider font-bold">Total Revenue</div>
                </div>

                {/* Premium Members Box */}
                <div className="glass p-8 rounded-2xl border-l-4 border-l-blue-500 relative overflow-hidden group hover:-translate-y-2 transition-transform duration-300">
                    <i className="fa fa-crown absolute -right-4 -bottom-4 text-7xl opacity-5 group-hover:opacity-10 transition-opacity"></i>
                    <div className="text-blue-500 text-3xl font-bold mb-1">{stats.premium_users || 0}</div>
                    <div className="text-gray-500 text-xs uppercase tracking-wider font-bold">Premium Members</div>
                </div>

                {/* Library Movies Box */}
                <div className="glass p-8 rounded-2xl border border-white/5 relative overflow-hidden group hover:-translate-y-2 transition-transform duration-300">
                    <i className="fa fa-film absolute -right-4 -bottom-4 text-7xl opacity-5 group-hover:opacity-10 transition-opacity"></i>
                    <div className="text-3xl font-bold mb-1">{stats.total_movies || 0}</div>
                    <div className="text-gray-500 text-xs uppercase tracking-wider font-bold">Library Movies</div>
                </div>

                {/* Total Registered Box */}
                <div className="glass p-8 rounded-2xl border border-white/5 relative overflow-hidden group hover:-translate-y-2 transition-transform duration-300">
                    <i className="fa fa-users absolute -right-4 -bottom-4 text-7xl opacity-5 group-hover:opacity-10 transition-opacity"></i>
                    <div className="text-3xl font-bold mb-1">{stats.total_users || 0}</div>
                    <div className="text-gray-500 text-xs uppercase tracking-wider font-bold">Total Registered</div>
                </div>

                {/* Total Streamed Box */}
                <div className="glass p-8 rounded-2xl border border-white/5 relative overflow-hidden group hover:-translate-y-2 transition-transform duration-300">
                    <i className="fa fa-play-circle absolute -right-4 -bottom-4 text-7xl opacity-5 group-hover:opacity-10 transition-opacity"></i>
                    <div className="text-3xl font-bold mb-1">{stats.watch_count || 0}</div>
                    <div className="text-gray-500 text-xs uppercase tracking-wider font-bold">Total Streamed</div>
                </div>

                {/* System Admins Box */}
                <div className="glass p-8 rounded-2xl border border-white/5 relative overflow-hidden group hover:-translate-y-2 transition-transform duration-300">
                    <i className="fa fa-user-shield absolute -right-4 -bottom-4 text-7xl opacity-5 group-hover:opacity-10 transition-opacity"></i>
                    <div className="text-3xl font-bold mb-1">{stats.admins || 0}</div>
                    <div className="text-gray-500 text-xs uppercase tracking-wider font-bold">System Admins</div>
                </div>
            </div>

            <div className="mb-8">
                <h3 className="text-gray-400 font-medium mb-6">Quick Actions</h3>
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <Link to="/admin/add-movie" className="bg-primary hover:bg-red-700 p-4 rounded-xl font-bold flex items-center gap-3 transition-all">
                        <i className="fa fa-plus-square"></i> Upload Content
                    </Link>
                    <Link to="/admin/edit-movies" className="bg-white/5 hover:bg-white/10 border border-white/10 p-4 rounded-xl font-bold flex items-center gap-3 transition-all">
                        <i className="fa fa-edit"></i> Edit Movies
                    </Link>
                    <Link to="/admin/users" className="bg-white/5 hover:bg-white/10 border border-white/10 p-4 rounded-xl font-bold flex items-center gap-3 transition-all">
                        <i className="fa fa-user-cog"></i> User Management
                    </Link>
                    <Link to="/admin/payments" className="bg-white/5 hover:bg-white/10 border border-white/10 p-4 rounded-xl font-bold flex items-center gap-3 transition-all">
                        <i className="fa fa-history"></i> Payments
                    </Link>

                    <Link to="/" className="bg-white/5 hover:bg-white/10 border border-white/10 p-4 rounded-xl font-bold flex items-center gap-3 transition-all">
                        <i className="fa fa-external-link-alt"></i> Preview Site
                    </Link>
                </div>
            </div>
        </div>
    );
};

export default Dashboard;
