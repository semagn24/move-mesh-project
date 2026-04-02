import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api/axios';

const Payments = () => {
    const [transactions, setTransactions] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchTransactions();
    }, []);

    const fetchTransactions = async () => {
        try {
            const response = await api.get('/payments/transactions/all');
            if (response.data.success) {
                setTransactions(response.data.transactions);
            }
        } catch (error) {
            console.error('Failed to fetch transactions:', error);
        } finally {
            setLoading(false);
        }
    };

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };

    const getStatusColor = (status) => {
        switch (status.toLowerCase()) {
            case 'completed':
                return 'bg-green-500/20 text-green-500';
            case 'pending':
                return 'bg-yellow-500/20 text-yellow-500';
            case 'failed':
                return 'bg-red-500/20 text-red-500';
            default:
                return 'bg-gray-500/20 text-gray-500';
        }
    };

    if (loading) {
        return (
            <div className="max-w-6xl mx-auto py-8">
                <h1 className="text-3xl font-bold mb-8">Payment Transactions</h1>
                <div className="flex justify-center items-center h-64">
                    <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
                </div>
            </div>
        );
    }

    return (
        <div className="max-w-6xl mx-auto py-8">
            <Link to="/admin" className="inline-flex items-center gap-2 text-gray-400 hover:text-white mb-6 transition-colors">
                <i className="fa fa-arrow-left"></i> Back to Dashboard
            </Link>
            <div className="flex items-center justify-between mb-8">
                <h1 className="text-3xl font-bold">Payment Transactions</h1>
                <span className="text-gray-500 font-medium">{transactions.length} Total</span>
            </div>
            <div className="glass rounded-3xl overflow-hidden border border-white/5 shadow-2xl">
                <table className="w-full text-left">
                    <thead>
                        <tr className="bg-white/5 text-gray-500 text-xs uppercase tracking-[0.2em] font-bold">
                            <th className="px-8 py-6">Transaction ID</th>
                            <th className="px-8 py-6">Customer</th>
                            <th className="px-8 py-6">Type</th>
                            <th className="px-8 py-6">Amount</th>
                            <th className="px-8 py-6">Status</th>
                            <th className="px-8 py-6">Date</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-white/5">
                        {transactions.length === 0 ? (
                            <tr>
                                <td colSpan="6" className="px-8 py-12 text-center text-gray-500">
                                    <i className="fa fa-receipt text-4xl mb-4 block"></i>
                                    <p className="font-medium">No transactions yet</p>
                                </td>
                            </tr>
                        ) : (
                            transactions.map(tx => (
                                <tr key={tx.id} className="hover:bg-white/5 transition-colors">
                                    <td className="px-8 py-6 font-mono text-xs text-primary font-bold">
                                        {tx.tx_ref}
                                    </td>
                                    <td className="px-8 py-6">
                                        <div>
                                            <div className="font-bold">{tx.username}</div>
                                            <div className="text-xs text-gray-500">{tx.email}</div>
                                        </div>
                                    </td>
                                    <td className="px-8 py-6">
                                        <span className={`px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider ${tx.type === 'renew' ? 'bg-blue-500/10 text-blue-500 border border-blue-500/20' : 'bg-purple-500/10 text-purple-500 border border-purple-500/20'}`}>
                                            {tx.type || 'new'}
                                        </span>
                                    </td>
                                    <td className="px-8 py-6 font-bold">{tx.amount} ETB</td>
                                    <td className="px-8 py-6">
                                        <span className={`px-3 py-1 rounded-lg text-xs font-bold uppercase ${getStatusColor(tx.status)}`}>
                                            {tx.status}
                                        </span>
                                    </td>
                                    <td className="px-8 py-6 text-gray-500 text-sm">{formatDate(tx.created_at)}</td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default Payments;
