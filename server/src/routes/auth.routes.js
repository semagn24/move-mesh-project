const express = require('express');
const router = express.Router();
const authController = require('../controllers/auth.controller');

router.post('/login', authController.login);
router.post('/register', authController.register);
router.post('/logout', authController.logout);
router.get('/me', authController.getMe);
router.get('/profile', authController.getProfile);
router.put('/profile', authController.updateProfile);
router.post('/forgot-password', authController.forgotPassword);

module.exports = router;
