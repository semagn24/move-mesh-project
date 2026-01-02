const express = require('express');
const router = express.Router();
const adminController = require('../controllers/admin.controller');
const upload = require('../middleware/upload.middleware');

router.get('/stats', adminController.getStats);
router.get('/users', adminController.getAllUsers);
router.put('/users/:id', adminController.updateUser);
router.delete('/users/:id', adminController.deleteUser);
router.post('/movies', upload.fields([{ name: 'poster', maxCount: 1 }, { name: 'video', maxCount: 1 }]), adminController.addMovie);
router.put('/movies/:id', adminController.updateMovie);
router.delete('/movies/:id', adminController.deleteMovie);
router.post('/notifications/create', adminController.createNotification);

module.exports = router;
