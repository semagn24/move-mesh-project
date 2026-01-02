import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth';

const Login = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();
    const { login } = useAuth();

    const handleSubmit = async (e) => {
        e.preventDefault();

        // Basic Validation
        if (!email.includes('@')) {
            setError('Please enter a valid email address');
            return;
        }
        if (password.length < 6) {
            setError('Password must be at least 6 characters');
            return;
        }

        setLoading(true);
        setError('');

        try {
            await login(email, password);
            navigate('/');
        } catch (err) {
            // AuthContext login might handle errors differently or strict api response
            // Assuming login throws or returns data. api/auth.api.js returns response.data.
            // But if it fails (401), axios throws.
            setError(err.response?.data?.message || 'Login failed');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="flex justify-center items-center min-h-[70vh] relative">
            <Link to="/" className="absolute top-4 left-4 text-gray-400 hover:text-white flex items-center gap-2 transition-colors">
                <i className="fa fa-arrow-left"></i> Back to Home
            </Link>
            <div className="glass p-8 rounded-2xl w-full max-w-md mt-12">
                <h2 className="text-3xl font-bold mb-6 text-center">Welcome Back</h2>

                {error && (
                    <div className="bg-red-500/20 text-red-400 p-3 rounded-lg mb-4 text-sm text-center border border-red-500/50">
                        {error}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium mb-1">Email Address</label>
                        <input
                            type="email"
                            className="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2 focus:border-primary outline-none transition"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            required
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium mb-1">Password</label>
                        <input
                            type="password"
                            className="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2 focus:border-primary outline-none transition"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            required
                        />
                    </div>

                    <div className="text-right">
                        <Link to="/forgot-password" size="sm" className="text-xs font-bold text-gray-500 hover:text-primary transition-colors">
                            Forgot password?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        disabled={loading}
                        className="w-full bg-primary hover:bg-opacity-90 py-3 rounded-lg font-bold mt-4 transition flex justify-center items-center"
                    >
                        {loading ? (
                            <div className="animate-spin rounded-full h-5 w-5 border-t-2 border-white"></div>
                        ) : 'Log In'}
                    </button>
                </form>

                <p className="mt-6 text-center text-gray-400 text-sm">
                    Don't have an account? <Link to="/register" className="text-primary hover:underline">Register here</Link>
                </p>
            </div>
        </div>
    );
};

export default Login;
