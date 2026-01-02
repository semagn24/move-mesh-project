import { useState, useEffect } from 'react';
import api from '../api/axios';

const PremiumSubscription = () => {
    const [subscription, setSubscription] = useState(null);
    const [loading, setLoading] = useState(true);
    const [processing, setProcessing] = useState(false);

    useEffect(() => {
        checkSubscription();
    }, []);

    const checkSubscription = async () => {
        try {
            const response = await api.get('/payments/subscription/check');
            if (response.data.success) {
                setSubscription(response.data);
            }
        } catch (error) {
            console.error('Failed to check subscription:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleSubscribe = async () => {
        const isCurrentlyPremium = subscription?.isPremium;
        const type = isCurrentlyPremium ? 'renew' : 'new';

        console.log(`[SUBSCRIPTION] Starting ${type} process...`);
        setProcessing(true);
        try {
            const userData = localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : null;

            const payload = {
                email: userData?.email || '',
                first_name: userData?.username || '',
                last_name: 'User',
                type: type
            };
            console.log('[SUBSCRIPTION] Sending payload:', payload);

            const response = await api.post('/payments/initialize', payload);
            console.log('[SUBSCRIPTION] Response received:', response.data);

            if (response.data.success) {
                if (response.data.checkout_url) {
                    // Production mode - redirect to Chapa
                    window.location.href = response.data.checkout_url;
                } else {
                    alert('Unexpected response from server: No checkout URL provided.');
                }
            } else {
                alert('Subscription failed: ' + (response.data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('[SUBSCRIPTION] Error occurred:', error);
            console.error('[SUBSCRIPTION] Error response:', error.response);
            alert(error.response?.data?.message || error.message || 'Failed to initialize payment. Please try again.');
        } finally {
            setProcessing(false);
            console.log('[SUBSCRIPTION] Process completed');
        }
    };

    if (loading) {
        return (
            <div className="bg-white/5 p-8 rounded-3xl border border-white/10">
                <div className="animate-pulse">
                    <div className="h-6 bg-white/10 rounded w-1/2 mb-4"></div>
                    <div className="h-4 bg-white/10 rounded w-3/4"></div>
                </div>
            </div>
        );
    }

    const isPremium = subscription?.isPremium;
    const expiryDate = subscription?.subscriptionExpiry ? new Date(subscription.subscriptionExpiry) : null;
    const daysRemaining = expiryDate ? Math.ceil((expiryDate - new Date()) / (1000 * 60 * 60 * 24)) : 0;

    return (
        <div className="bg-gradient-to-br from-primary/10 to-secondary/50 p-8 rounded-3xl border border-primary/20 shadow-2xl">
            {isPremium ? (
                <>
                    <div className="flex items-center gap-3 mb-4">
                        <div className="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center">
                            <i className="fa fa-crown text-primary text-xl"></i>
                        </div>
                        <div>
                            <h3 className="text-xl font-black text-primary">Premium Member</h3>
                            <p className="text-sm text-gray-400 font-medium">Unlimited Access</p>
                        </div>
                    </div>

                    <div className="bg-black/30 rounded-2xl p-6 mb-6">
                        <div className="flex items-center justify-between mb-3">
                            <span className="text-xs font-bold text-gray-500 uppercase tracking-widest">Subscription Status</span>
                            <span className="px-3 py-1 rounded-lg text-xs font-extrabold uppercase bg-green-500/20 text-green-500">
                                Active
                            </span>
                        </div>
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-bold text-gray-500 uppercase tracking-widest">Expires On</span>
                            <span className="font-bold text-white">
                                {expiryDate?.toLocaleDateString()}
                            </span>
                        </div>
                        {daysRemaining <= 7 && daysRemaining > 0 && (
                            <div className="mt-4 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-xl">
                                <p className="text-xs text-yellow-500 font-bold">
                                    <i className="fa fa-exclamation-triangle mr-2"></i>
                                    Your subscription expires in {daysRemaining} day{daysRemaining !== 1 ? 's' : ''}
                                </p>
                            </div>
                        )}
                    </div>

                    <button
                        onClick={handleSubscribe}
                        disabled={processing}
                        className="w-full bg-primary/20 hover:bg-primary/30 border border-primary/50 py-4 rounded-xl font-black transition-all active:scale-95 disabled:opacity-50"
                    >
                        {processing ? (
                            <><i className="fa fa-spinner fa-spin mr-2"></i>Processing...</>
                        ) : (
                            <><i className="fa fa-sync mr-2"></i>Renew Subscription</>
                        )}
                    </button>
                </>
            ) : (
                <>
                    <div className="flex items-center gap-3 mb-4">
                        <div className="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center">
                            <i className="fa fa-star text-yellow-500 text-xl"></i>
                        </div>
                        <div>
                            <h3 className="text-xl font-black">Upgrade to Premium</h3>
                            <p className="text-sm text-gray-400 font-medium">Unlock exclusive content</p>
                        </div>
                    </div>

                    <div className="space-y-3 mb-6">
                        <div className="flex items-center gap-3">
                            <i className="fa fa-check-circle text-green-500"></i>
                            <span className="text-sm font-medium">Access to all premium movies</span>
                        </div>
                        <div className="flex items-center gap-3">
                            <i className="fa fa-check-circle text-green-500"></i>
                            <span className="text-sm font-medium">Ad-free streaming experience</span>
                        </div>
                        <div className="flex items-center gap-3">
                            <i className="fa fa-check-circle text-green-500"></i>
                            <span className="text-sm font-medium">Early access to new releases</span>
                        </div>
                        <div className="flex items-center gap-3">
                            <i className="fa fa-check-circle text-green-500"></i>
                            <span className="text-sm font-medium">HD & 4K quality streaming</span>
                        </div>
                    </div>

                    <div className="bg-black/30 rounded-2xl p-6 mb-6">
                        <div className="flex items-end justify-between">
                            <div>
                                <span className="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-1">Monthly Plan</span>
                                <div className="flex items-baseline gap-2">
                                    <span className="text-4xl font-black text-primary">150</span>
                                    <span className="text-xl font-bold text-gray-400">ETB</span>
                                </div>
                            </div>
                            <span className="text-xs font-bold text-gray-500 uppercase">per month</span>
                        </div>
                    </div>

                    <button
                        onClick={handleSubscribe}
                        disabled={processing}
                        className="w-full bg-primary hover:bg-red-700 py-4 rounded-xl font-black shadow-lg shadow-red-900/20 transition-all active:scale-95 disabled:opacity-50"
                    >
                        {processing ? (
                            <><i className="fa fa-spinner fa-spin mr-2"></i>Processing...</>
                        ) : (
                            <><i className="fa fa-crown mr-2"></i>Subscribe Now</>
                        )}
                    </button>

                    <p className="text-xs text-center text-gray-600 mt-4 font-medium">
                        Secure payment powered by Chapa
                    </p>
                </>
            )}
        </div>
    );
};

export default PremiumSubscription;
