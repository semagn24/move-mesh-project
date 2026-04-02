const express = require('express');
const router = express.Router();
const paymentController = require('../controllers/payment.controller');

router.post('/initialize', paymentController.initializePayment);
router.get('/verify', paymentController.verifyPayment);
router.get('/transactions', paymentController.getTransactions);
router.get('/transactions/all', paymentController.getAllTransactions);
router.get('/subscription/check', paymentController.checkSubscription);

module.exports = router;
