const express = require('express');
const router = express.Router();
const movieController = require('../controllers/movie.controller');

router.get('/', movieController.getMovies);
router.get('/:id', movieController.getMovieById);
router.post('/:id/comment', movieController.postComment);
router.get('/:id/comments', movieController.getComments);
router.post('/:id/progress', movieController.updateProgress);
router.post('/:id/review', movieController.postReview);
router.get('/:id/reviews', movieController.getReviews);
router.get('/user/continue-watching', movieController.getContinueWatching);
router.post('/:id/favorite', movieController.toggleFavorite);
router.get('/:id/favorite', movieController.checkFavorite);

module.exports = router;
