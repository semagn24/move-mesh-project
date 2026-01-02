import { useState } from 'react';
import { useNavigate, useParams, Link } from 'react-router-dom';
import api from '../api/axios';

const ResetPassword = () => {
    const [password, setPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();
    const { token } = useParams();

    const handleSubmit = async (e) => {
        e.preventDefault();

        // Validation (Match Register.jsx)
        const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/;

        if (!passwordRegex.test(password)) {
            return setError('Password must be at least 8 characters, with 1 letter & 1 number');
        }
        if (password !== confirmPassword) {
            return setError('Passwords do not match');
        }

        setLoading(true);
        setError('');

        try {
            const response = await api.post(`auth/reset-password/${token}`, { password });
            if (response.data.success) {
                setSuccess('Password reset successful! Redirecting to login...');
                setTimeout(() => navigate('/login'), 3000);
            } else {
                setError(response.data.message || 'Reset failed');
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
                <h2 className="text-3xl font-bold mb-6 text-center">Reset Password</h2>

                {error && (
                    <div className="bg-red-500/20 text-red-400 p-3 rounded-lg mb-4 text-sm text-center border border-red-500/50">
                        {error}
                    </div>
                )}

                {success && (
                    <div className="bg-green-500/20 text-green-400 p-3 rounded-lg mb-4 text-sm text-center border border-green-500/50">
                        {success}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium mb-1">New Password</label>
                        <input
                            type="password"
                            className="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2 focus:border-primary outline-none transition"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            required
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium mb-1">Confirm New Password</label>
                        <input
                            type="password"
                            className="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2 focus:border-primary outline-none transition"
                            value={confirmPassword}
                            onChange={(e) => setConfirmPassword(e.target.value)}
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
                        ) : 'Reset Password'}
                    </button>
                </form>
            </div>
        </div>
    );
};

export default ResetPassword;
