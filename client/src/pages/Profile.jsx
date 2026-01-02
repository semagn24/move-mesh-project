import { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import api from '../api/axios';
import PremiumSubscription from '../components/PremiumSubscription';

const Profile = () => {
    const [user, setUser] = useState(null);
    const [favorites, setFavorites] = useState([]);
    const [loading, setLoading] = useState(true);
    const [isEditing, setIsEditing] = useState(false);
    const [formData, setFormData] = useState({
        username: '',
        email: '',
        currentPassword: '',
        newPassword: ''
    });
    const [message, setMessage] = useState({ type: '', text: '' });
    const navigate = useNavigate();
    const location = useLocation();

    useEffect(() => {
        const fetchProfile = async () => {
            try {
                const response = await api.get('/auth/profile');
                if (response.data.success) {
                    setUser(response.data.user);
                    setFavorites(response.data.favorites || []);
                    setFormData({
                        username: response.data.user.username,
                        email: response.data.user.email,
                        currentPassword: '',
                        newPassword: ''
                    });
                }
            } catch (error) {
                console.error("Failed to fetch profile:", error);
            } finally {
                setLoading(false);
            }
        };

        fetchProfile();

        // Check for payment status in URL
        const params = new URLSearchParams(location.search);
        const paymentStatus = params.get('payment');
        if (paymentStatus === 'success') {
            setMessage({ type: 'success', text: 'Payment successful! Your premium subscription is now active.' });
            // Clear the URL parameter
            window.history.replaceState({}, '', '/profile');
        } else if (paymentStatus === 'failed') {
            setMessage({ type: 'error', text: 'Payment failed. Please try again.' });
            window.history.replaceState({}, '', '/profile');
        } else if (paymentStatus === 'error') {
            setMessage({ type: 'error', text: 'Payment verification error. Please contact support.' });
            window.history.replaceState({}, '', '/profile');
        }
    }, [location]);

    const handleUpdateProfile = async (e) => {
        e.preventDefault();
        setMessage({ type: '', text: '' });
        try {
            const res = await api.put('/auth/profile', formData);
            if (res.data.success) {
                setMessage({ type: 'success', text: 'Profile updated successfully!' });
                setIsEditing(false);
                const meRes = await api.get('/auth/me');
                if (meRes.data.success) {
                    localStorage.setItem('user', JSON.stringify(meRes.data.user));
                    setUser(prev => ({ ...prev, ...formData }));
                }
            }
        } catch (err) {
            setMessage({ type: 'error', text: err.response?.data?.message || 'Failed to update profile' });
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center h-64">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
            </div>
        );
    }

    if (!user) return <div className="text-center p-20 text-gray-500">Not authenticated</div>;

    return (
        <div className="max-w-6xl mx-auto space-y-12 pb-20">
            <Link to="/" className="inline-flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
                <i className="fa fa-arrow-left"></i> Back to Home
            </Link>

            <div className="relative h-64 rounded-3xl overflow-hidden border border-white/5">
                <div className="absolute inset-0 bg-gradient-to-br from-primary/40 to-secondary transition-all"></div>
                <div className="absolute inset-0 flex flex-col md:flex-row items-center px-12 gap-8 justify-center md:justify-start">
                    <div className="w-24 h-24 md:w-32 md:h-32 rounded-full bg-secondary border-4 border-white/10 flex items-center justify-center text-4xl font-black text-primary shadow-2xl shrink-0 uppercase">
                        {user.username[0]}
                    </div>
                    <div>
                        <h1 className="text-4xl font-black mb-2 text-center md:text-left">{user.username}</h1>
                        <p className="text-gray-400 font-medium text-center md:text-left">{user.email} • {user.role.toUpperCase()}</p>
                    </div>
                    <button
                        onClick={() => setIsEditing(!isEditing)}
                        className="md:ml-auto bg-white/10 hover:bg-white/20 px-8 py-4 rounded-2xl font-black transition-all backdrop-blur-md active:scale-95"
                    >
                        {isEditing ? 'Cancel Edit' : 'Edit Profile'}
                    </button>
                </div>
            </div>

            {/* Payment Status Message */}
            {message.text && (
                <div className={`p-6 rounded-2xl text-center font-bold ${message.type === 'success' ? 'bg-green-500/10 text-green-500 border border-green-500/20' : 'bg-red-500/10 text-red-500 border border-red-500/20'}`}>
                    <i className={`fa ${message.type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2`}></i>
                    {message.text}
                </div>
            )}

            <div className="grid lg:grid-cols-3 gap-12">
                <div className="lg:col-span-1 space-y-8">
                    {isEditing ? (
                        <div className="bg-white/5 p-8 rounded-3xl border border-white/10">
                            <h3 className="text-xl font-black mb-6">Settings</h3>
                            <form onSubmit={handleUpdateProfile} className="space-y-4">
                                {message.text && (
                                    <div className={`p-4 rounded-xl text-sm font-bold ${message.type === 'success' ? 'bg-green-500/10 text-green-500' : 'bg-red-500/10 text-red-500'}`}>
                                        {message.text}
                                    </div>
                                )}
                                <div>
                                    <label className="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-2">Username</label>
                                    <input
                                        type="text"
                                        value={formData.username}
                                        onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                                        className="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-primary"
                                    />
                                </div>
                                <div>
                                    <label className="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-2">Email</label>
                                    <input
                                        type="email"
                                        value={formData.email}
                                        onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                                        className="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-primary"
                                    />
                                </div>
                                <div className="pt-4 border-t border-white/5">
                                    <label className="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-2">New Password (optional)</label>
                                    <input
                                        type="password"
                                        value={formData.newPassword}
                                        onChange={(e) => setFormData({ ...formData, newPassword: e.target.value })}
                                        className="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-primary mb-4"
                                    />
                                    <label className="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-1">Current Password</label>
                                    <p className="text-[10px] text-gray-600 mb-2 font-bold uppercase">Required to save changes</p>
                                    <input
                                        type="password"
                                        required
                                        value={formData.currentPassword}
                                        onChange={(e) => setFormData({ ...formData, currentPassword: e.target.value })}
                                        className="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-primary"
                                    />
                                </div>
                                <button type="submit" className="w-full bg-primary py-4 rounded-xl font-black shadow-lg shadow-red-900/20 active:scale-95 transition-all">
                                    Save Profile
                                </button>
                            </form>
                        </div>
                    ) : (
                        <div className="bg-white/5 p-8 rounded-3xl border border-white/10">
                            <h3 className="text-xl font-black mb-6">Account</h3>
                            <div className="space-y-6">
                                <div>
                                    <span className="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-1">Access Level</span>
                                    <span className={`px-3 py-1 rounded-lg text-xs font-extrabold uppercase tracking-widest ${user.role === 'admin'
                                        ? 'bg-primary/10 text-primary'
                                        : 'bg-green-500/10 text-green-500'
                                        }`}>
                                        {user.role === 'admin' ? 'Administrator' : 'Member'}
                                    </span>
                                </div>
                                <div>
                                    <span className="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-1">Created At</span>
                                    <span className="font-medium text-white">{new Date(user.created_at).toLocaleDateString()}</span>
                                </div>
                                <div className="pt-6 border-t border-white/5">
                                    <Link to="/payments" className="text-primary hover:underline font-bold text-sm flex items-center gap-2">
                                        <i className="fa fa-receipt text-xs"></i> Billing & History
                                    </Link>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Premium Subscription Section */}
                    <PremiumSubscription />
                </div>

                <div className="lg:col-span-2">
                    <div className="flex items-center justify-between mb-8">
                        <h2 className="text-3xl font-black">Favorites</h2>
                        <span className="text-gray-500 font-bold">{favorites.length} Titles</span>
                    </div>

                    {favorites.length > 0 ? (
                        <div className="grid grid-cols-2 sm:grid-cols-3 gap-6">
                            {favorites.map(movie => (
                                <Link key={movie.id} to={`/movies/${movie.id}`} className="group relative aspect-[2/3] rounded-2xl overflow-hidden border border-white/5 shadow-xl transition-all hover:scale-105">
                                    <img
                                        src={movie.poster_url?.startsWith('http') ? movie.poster_url : `${window.location.origin}${movie.poster_url}`}
                                        alt=""
                                        className="w-full h-full object-cover"
                                        onError={(e) => { e.target.src = 'https://via.placeholder.com/300x450?text=No+Poster'; }}
                                    />
                                    <div className="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-all flex flex-col justify-end p-4">
                                        <h4 className="font-bold text-white leading-tight">{movie.title}</h4>
                                        <span className="text-primary text-xs font-black mt-1">{movie.genre.toUpperCase()}</span>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-24 bg-white/5 rounded-3xl border border-dashed border-white/10">
                            <i className="fa fa-film text-5xl text-gray-700 mb-6"></i>
                            <p className="text-xl text-gray-500 font-medium">Explore and add movies to your list!</p>
                            <Link to="/" className="text-primary hover:underline font-bold mt-4 inline-block">Start Watching</Link>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default Profile;
