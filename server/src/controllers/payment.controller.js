const pool = require('../config/db');
const axios = require('axios');
const { createNotification } = require('./notification.controller');

// Payment configuration
const CHAPA_SECRET_KEY = process.env.CHAPA_SECRET_KEY || 'CHASECK_TEST-mOK1mVlfwORHemxVMC1004YmFbc9ptYi';
const CHAPA_API_URL = 'https://api.chapa.co/v1';
const PLAN_PRICE = 150; // ETB
const PLAN_DAYS = 30;

// Initialize payment
exports.initializePayment = async (req, res) => {
    console.log('[PAYMENT] Initialize payment request received');

    if (!req.session.user) {
        console.log('[PAYMENT] User not authenticated');
        return res.status(401).json({ success: false, message: 'Not authenticated' });
    }

    try {
        const userId = req.session.user.id;
        const { email, first_name, last_name, type = 'new' } = req.body;

        console.log('[PAYMENT] User ID:', userId, '| Type:', type);

        // Generate unique transaction reference
        const tx_ref = `sub_${Date.now()}_${userId}`;

        // PRODUCTION MODE: Use Chapa API
        console.log('[PAYMENT] Calling Chapa API for', type);

        const validEmail = (email && email.includes('@')) ? email : (req.session.user.email && req.session.user.email.includes('@')) ? req.session.user.email : 'test@example.com';

<<<<<<< HEAD
=======
        // Logic to Split/Truncate Names for Chapa (Max 35 chars)
        let fName = first_name;
        let lName = last_name;

        if (!fName) {
            // Try to split username
            const nameParts = (req.session.user.username || 'User').split(' ');
            fName = nameParts[0];
            lName = nameParts.length > 1 ? nameParts.slice(1).join(' ') : 'User';
        }

        // Enforce Limits
        const cleanName = (str) => str ? str.substring(0, 30).trim() : 'User';

>>>>>>> origin/main
        const paymentData = {
            amount: PLAN_PRICE,
            currency: 'ETB',
            email: validEmail,
<<<<<<< HEAD
            first_name: first_name || req.session.user.username || 'User',
            last_name: last_name || 'User',
=======
            first_name: cleanName(fName),
            last_name: cleanName(lName),
>>>>>>> origin/main
            tx_ref: tx_ref,
            callback_url: `${process.env.BACKEND_URL || 'http://localhost:5000'}/api/payments/verify`,
            return_url: `${process.env.FRONTEND_URL || 'http://localhost:5173'}/profile?payment=success`,
            customization: {
                title: 'MovieStream',
                description: '30 Days Premium Access'
            }
        };

        console.log('[PAYMENT] Sending payload to Chapa:', JSON.stringify(paymentData, null, 2));

        // Call Chapa API
        const response = await axios.post(
            `${CHAPA_API_URL}/transaction/initialize`,
            paymentData,
            {
                headers: {
                    'Authorization': `Bearer ${process.env.CHAPA_SECRET_KEY || CHAPA_SECRET_KEY}`,
                    'Content-Type': 'application/json'
                }
            }
        );

        if (response.data.status === 'success') {
            // Store transaction in database with type
            await pool.query(
                `INSERT INTO transactions (user_id, tx_ref, amount, type, status, payment_method) 
                 VALUES (?, ?, ?, ?, 'pending', 'chapa')`,
                [userId, tx_ref, PLAN_PRICE, type]
            );

            res.json({
                success: true,
                checkout_url: response.data.data.checkout_url,
                tx_ref: tx_ref
            });
        } else {
            res.status(400).json({ success: false, message: 'Payment initialization failed' });
        }

    } catch (error) {
        console.error('[PAYMENT] Error:', error);

        let errorMessage = error.message;
        let detailedError = null;

        if (error.response) {
            console.error('[PAYMENT] Chapa API Error Data:', JSON.stringify(error.response.data, null, 2));
            detailedError = error.response.data;

            // Extract the most readable error message
            if (error.response.data && typeof error.response.data.message === 'string') {
                errorMessage = error.response.data.message;
            } else if (error.response.data && error.response.data.errors) {
                errorMessage = JSON.stringify(error.response.data.errors);
            } else if (error.response.data) {
                errorMessage = JSON.stringify(error.response.data);
            }
        }

        res.status(500).json({
            success: false,
            message: errorMessage,
            details: detailedError
        });
    }
};

// Verify payment
exports.verifyPayment = async (req, res) => {
    try {
        const tx_ref = req.query.tx_ref || req.query.reference;

        if (!tx_ref) {
            return res.redirect(`${process.env.FRONTEND_URL || 'http://localhost:5173'}/profile?payment=failed`);
        }

        // Verify with Chapa
        const response = await axios.get(
            `${CHAPA_API_URL}/transaction/verify/${tx_ref}`,
            {
                headers: {
                    'Authorization': `Bearer ${CHAPA_SECRET_KEY}`
                }
            }
        );

        const paymentData = response.data.data;
        const status = paymentData.status.toLowerCase();

        // Get transaction from database
        const [transactions] = await pool.query(
            'SELECT * FROM transactions WHERE tx_ref = ?',
            [tx_ref]
        );

        if (transactions.length === 0) {
            return res.redirect(`${process.env.FRONTEND_URL || 'http://localhost:5173'}/profile?payment=failed`);
        }

        const transaction = transactions[0];
        const userId = transaction.user_id;

        if (status === 'success' || status === 'successful') {
            // Update transaction status
            await pool.query(
                'UPDATE transactions SET status = ?, verified_at = NOW() WHERE tx_ref = ?',
                ['completed', tx_ref]
            );

            // Calculate subscription expiry
            const [users] = await pool.query(
                'SELECT subscription_expiry FROM users WHERE id = ?',
                [userId]
            );

            let newExpiry;
            const currentExpiry = users[0]?.subscription_expiry;

            if (currentExpiry && new Date(currentExpiry) > new Date()) {
                // Extend existing subscription
                newExpiry = new Date(currentExpiry);
                newExpiry.setDate(newExpiry.getDate() + PLAN_DAYS);
            } else {
                // New subscription
                newExpiry = new Date();
                newExpiry.setDate(newExpiry.getDate() + PLAN_DAYS);
            }

            // Update user subscription
            await pool.query(
                `UPDATE users 
                 SET subscription_status = 'premium', 
                     subscription_expiry = ? 
                 WHERE id = ?`,
                [newExpiry, userId]
            );

            // Create notification
            await createNotification(
                userId,
                'payment',
                'Premium Subscription Activated!',
                `Your premium subscription is now active until ${newExpiry.toLocaleDateString()}`,
                '/profile'
            );

            res.redirect(`${process.env.FRONTEND_URL || 'http://localhost:5173'}/profile?payment=success`);
        } else {
            // Update transaction as failed
            await pool.query(
                'UPDATE transactions SET status = ? WHERE tx_ref = ?',
                ['failed', tx_ref]
            );

            res.redirect(`${process.env.FRONTEND_URL || 'http://localhost:5173'}/profile?payment=failed`);
        }

    } catch (error) {
        console.error('Verify Payment Error:', error.response?.data || error.message);
        res.redirect(`${process.env.FRONTEND_URL || 'http://localhost:5173'}/profile?payment=error`);
    }
};

// Get user transactions
exports.getTransactions = async (req, res) => {
    if (!req.session.user) {
        return res.status(401).json({ success: false, message: 'Not authenticated' });
    }

    try {
        const userId = req.session.user.id;
        const [transactions] = await pool.query(
            `SELECT * FROM transactions 
             WHERE user_id = ? 
             ORDER BY created_at DESC`,
            [userId]
        );

        res.json({ success: true, transactions });
    } catch (error) {
        console.error('Get Transactions Error:', error);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

// Get all transactions (admin only)
exports.getAllTransactions = async (req, res) => {
    if (!req.session.user || req.session.user.role !== 'admin') {
        return res.status(403).json({ success: false, message: 'Unauthorized' });
    }

    try {
        const [transactions] = await pool.query(
            `SELECT t.*, u.username, u.email 
             FROM transactions t 
             JOIN users u ON t.user_id = u.id 
             ORDER BY t.created_at DESC`
        );

        res.json({ success: true, transactions });
    } catch (error) {
        console.error('Get All Transactions Error:', error);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

// Check subscription status
exports.checkSubscription = async (req, res) => {
    if (!req.session.user) {
        return res.status(401).json({ success: false, message: 'Not authenticated' });
    }

    try {
        const userId = req.session.user.id;
        const [users] = await pool.query(
            'SELECT subscription_status, subscription_expiry FROM users WHERE id = ?',
            [userId]
        );

        if (users.length === 0) {
            return res.status(404).json({ success: false, message: 'User not found' });
        }

        const user = users[0];
        const isPremium = user.subscription_status === 'premium' &&
            user.subscription_expiry &&
            new Date(user.subscription_expiry) > new Date();

        res.json({
            success: true,
            isPremium,
            subscriptionStatus: user.subscription_status,
            subscriptionExpiry: user.subscription_expiry
        });

    } catch (error) {
        console.error('Check Subscription Error:', error);
        res.status(500).json({ success: false, message: 'Server error' });
    }
};

module.exports = exports;
