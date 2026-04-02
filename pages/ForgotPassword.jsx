import { useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../api/axios';

const ForgotPassword = () => {
    const [email, setEmail] = useState('');
    const [message, setMessage] = useState({ type: '', text: '' });
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setMessage({ type: '', text: '' });
        try {
            const res = await api.post('/auth/forgot-password', { email });
            setMessage({ type: 'success', text: res.data.message });
        } catch (err) {
            setMessage({ type: 'error', text: 'Something went wrong. Please try again.' });
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-[80vh] flex items-center justify-center">
            <div className="bg-white/5 p-12 rounded-[40px] border border-white/10 w-full max-w-md shadow-2xl backdrop-blur-3xl">
                <div className="text-center mb-10">
                    <div className="w-20 h-20 bg-primary/10 rounded-3xl flex items-center justify-center mx-auto mb-6 text-primary text-3xl">
                        <i className="fa fa-key"></i>
                    </div>
                    <h2 className="text-4xl font-black mb-3">Recover Account</h2>
                    <p className="text-gray-500 font-medium">Enter your email to receive a reset link.</p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {message.text && (
                        <div className={`p-4 rounded-xl text-sm font-bold ${message.type === 'success' ? 'bg-green-500/10 text-green-500 border border-green-500/20' : 'bg-red-500/10 text-red-500 border border-red-500/20'}`}>
                            {message.text}
                        </div>
                    )}
                    <div>
                        <label className="text-xs font-bold text-gray-400 uppercase tracking-[0.2em] block mb-3 ml-1">Email Address</label>
                        <input
                            type="email"
                            required
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            placeholder="your@email.com"
                            className="w-full bg-black/40 border border-white/5 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-primary transition-all placeholder:text-gray-700"
                        />
                    </div>
                    <button
                        type="submit"
                        disabled={loading}
                        className="w-full bg-primary hover:bg-red-700 text-white py-5 rounded-2xl font-black text-lg transition-all shadow-xl shadow-red-900/20 active:scale-95 disabled:opacity-50"
                    >
                        {loading ? 'Sending...' : 'Send Reset Link'}
                    </button>
                    <div className="text-center pt-4">
                        <Link to="/login" className="text-gray-500 hover:text-white font-bold transition-colors">
                            <i className="fa fa-arrow-left text-xs mr-2"></i> Back to login
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default ForgotPassword;
