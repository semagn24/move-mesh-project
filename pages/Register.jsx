import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import api from '../api/axios';

const Register = () => {
    const [formData, setFormData] = useState({
        username: '',
        email: '',
        password: '',
        confirmPassword: ''
    });
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const { username, email, password, confirmPassword } = formData;

        // Validation
        if (username.length < 3) return setError('Username too short');
        if (!email.includes('@')) return setError('Invalid email format');
        if (password.length < 6) return setError('Password must be at least 6 characters');
        if (password !== confirmPassword) {
            setError('Passwords do not match');
            return;
        }

        setLoading(true);
        setError('');

        try {
            const response = await api.post('auth/register', formData);
            if (response.data.success) {
                navigate('/login');
            } else {
                setError(response.data.message || 'Registration failed');
            }
        } catch (err) {
            setError(err.response?.data?.message || 'Server error occurred');
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
                <h2 className="text-3xl font-bold mb-6 text-center">Create Account</h2>

                {error && (
                    <div className="bg-red-500/20 text-red-400 p-3 rounded-lg mb-4 text-sm text-center border border-red-500/50">
                        {error}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium mb-1">Username</label>
                        <input
                            name="username"
                            type="text"
                            className="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2 focus:border-primary outline-none transition"
                            onChange={handleChange}
                            required
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium mb-1">Email Address</label>
                        <input
                            name="email"
                            type="email"
                            className="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2 focus:border-primary outline-none transition"
                            onChange={handleChange}
                            required
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium mb-1">Password</label>
                        <input
                            name="password"
                            type="password"
                            className="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2 focus:border-primary outline-none transition"
                            onChange={handleChange}
                            required
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium mb-1">Confirm Password</label>
                        <input
                            name="confirmPassword"
                            type="password"
                            className="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2 focus:border-primary outline-none transition"
                            onChange={handleChange}
                            required
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={loading}
                        className="w-full bg-primary hover:bg-opacity-90 py-3 rounded-lg font-bold mt-4 transition flex justify-center items-center"
                    >
                        {loading ? (
                            <div className="animate-spin rounded-full h-5 w-5 border-t-2 border-white"></div>
                        ) : 'Sign Up'}
                    </button>
                </form>

                <p className="mt-6 text-center text-gray-400 text-sm">
                    Already have an account? <Link to="/login" className="text-primary hover:underline">Log in</Link>
                </p>
            </div>
        </div>
    );
};

export default Register;
