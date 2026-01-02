import { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import api from '../api/axios';

const Watch = () => {
    const { id } = useParams();
    const [movie, setMovie] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [comments, setComments] = useState([]);
    const [newComment, setNewComment] = useState('');
    const [reviews, setReviews] = useState([]);
    const [userRating, setUserRating] = useState(0);
    const [reviewText, setReviewText] = useState('');
    const [isSaving, setIsSaving] = useState(false);
    const [isFavorite, setIsFavorite] = useState(false);
    const [subscription, setSubscription] = useState(null);
    const navigate = useNavigate();

    const videoRef = useRef(null);
    const [hasResumed, setHasResumed] = useState(false);

    useEffect(() => {
        const checkFav = async () => {
            try {
                const res = await api.get(`movies/${id}/favorite`);
                if (res.data.success) setIsFavorite(res.data.isFavorite);
            } catch (err) { /* ignore */ }
        };
        checkFav();
    }, [id]);

    const handleToggleFavorite = async () => {
        try {
            const res = await api.post(`movies/${id}/favorite`);
            if (res.data.success) {
                setIsFavorite(res.data.isFavorite);
                if (res.data.isFavorite) alert("Added to Favorites");
                else alert("Removed from Favorites");
            } else {
                alert(res.data.message || "Action failed");
            }
        } catch (err) {
            alert("Please login to add favorites");
        }
    };

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [movieRes, commentsRes, reviewsRes, subRes] = await Promise.all([
                    api.get(`movies/${id}`),
                    api.get(`movies/${id}/comments`),
                    api.get(`movies/${id}/reviews`),
                    api.get('/payments/subscription/check').catch(() => ({ data: { success: false } }))
                ]);

                if (movieRes.data.success) {
                    const movieData = movieRes.data.data;
                    setMovie(movieData);

                    // Resume progress from DB
                    if (movieRes.data.progress && videoRef.current && !hasResumed) {
                        videoRef.current.currentTime = movieRes.data.progress;
                        setHasResumed(true);
                    }
                }
                if (commentsRes.data.success) setComments(commentsRes.data.data);
                if (reviewsRes.data.success) setReviews(reviewsRes.data.data);
                if (subRes.data.success) setSubscription(subRes.data);

            } catch (err) {
                setError('Failed to load movie details');
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, [id, hasResumed]);

    const lastSaveRef = useRef(0);
    const handleProgressUpdate = async (e) => {
        try {
            const currentTime = Math.floor(e.target.currentTime);
            // Only update if 10 seconds have passed since last save
            if (currentTime % 10 === 0 && currentTime !== lastSaveRef.current) {
                lastSaveRef.current = currentTime;
                await api.post(`movies/${id}/progress`, { last_time: currentTime });
            }
        } catch (err) { /* Silent fail */ }
    };

    const handleCommentSubmit = async (e) => {
        e.preventDefault();
        if (!newComment.trim()) return;
        setIsSaving(true);
        try {
            const res = await api.post(`movies/${id}/comment`, { comment: newComment });
            if (res.data.success) {
                setNewComment('');
                const commentsRes = await api.get(`movies/${id}/comments`);
                setComments(commentsRes.data.data);
            }
        } catch (err) {
            alert('Failed to post comment. Maybe login?');
        } finally {
            setIsSaving(false);
        }
    };

    const handleReviewSubmit = async (e) => {
        e.preventDefault();
        if (userRating === 0) return;
        setIsSaving(true);
        try {
            const res = await api.post(`movies/${id}/review`, { rating: userRating, comment: reviewText });
            if (res.data.success) {
                setReviewText('');
                const reviewsRes = await api.get(`movies/${id}/reviews`);
                setReviews(reviewsRes.data.data);
            }
        } catch (err) {
            alert('Failed to submit review');
        } finally {
            setIsSaving(false);
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center h-64">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="text-center py-20">
                <p className="text-red-400 mb-4">{error}</p>
                <button onClick={() => navigate('/')} className="text-primary hover:underline">Go Back Home</button>
            </div>
        );
    }

    // Check if movie is premium and user doesn't have subscription
    const isPremiumMovie = movie?.is_premium;
    const userRole = localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')).role : null;
    const isPremiumUser = subscription?.isPremium;
    const isAdmin = userRole === 'admin';

    // Admins always have access, or if movie is not premium, or if user has premium subscription
    const hasAccess = isAdmin || !isPremiumMovie || isPremiumUser;

    return (
        <div className="max-w-6xl mx-auto">
            <Link to="/movies" className="inline-flex items-center gap-2 text-gray-400 hover:text-white mb-6 transition-colors">
                <i className="fa fa-arrow-left"></i> Back to Browse
            </Link>

            <div className="relative aspect-video bg-black rounded-3xl overflow-hidden shadow-2xl mb-8 group border border-white/5">
                {!hasAccess ? (
                    // Premium Content Locked Overlay
                    <div className="absolute inset-0 z-10 bg-gradient-to-br from-black/95 via-black/90 to-primary/20 flex items-center justify-center">
                        <div className="text-center px-8 max-w-lg">
                            <div className="w-24 h-24 mx-auto mb-6 rounded-full bg-primary/20 flex items-center justify-center">
                                <i className="fa fa-crown text-primary text-4xl"></i>
                            </div>
                            <h2 className="text-3xl font-black mb-4">Premium Content</h2>
                            <p className="text-gray-400 mb-8 leading-relaxed">
                                This movie is exclusive to premium members. Upgrade now to unlock unlimited access to all premium content!
                            </p>
                            <div className="flex flex-col sm:flex-row gap-4 justify-center">
                                <Link
                                    to="/profile"
                                    className="bg-primary hover:bg-red-700 text-white px-8 py-4 rounded-xl font-black transition-all shadow-lg shadow-red-900/20 active:scale-95"
                                >
                                    <i className="fa fa-crown mr-2"></i>
                                    Upgrade to Premium
                                </Link>
                                <button
                                    onClick={() => navigate('/')}
                                    className="bg-white/10 hover:bg-white/20 text-white px-8 py-4 rounded-xl font-black transition-all active:scale-95"
                                >
                                    Browse Free Content
                                </button>
                            </div>
                        </div>
                    </div>
                ) : null}

                <video
                    ref={videoRef}
                    controls
                    autoPlay
                    onTimeUpdate={handleProgressUpdate}
                    className="w-full h-full"
<<<<<<< HEAD
                    poster={movie.poster_url?.startsWith('http') ? movie.poster_url : `${window.location.origin}${movie.poster_url}`}
                    src={movie.video_url?.startsWith('http') ? movie.video_url : `${window.location.origin}${movie.video_url}`}
=======
                    poster={movie.poster_url?.startsWith('http') ? movie.poster_url : (movie.poster_url?.startsWith('/') ? movie.poster_url : `/uploads/posters/${movie.poster_url}`)}
                    src={movie.video_url?.startsWith('http') ? movie.video_url : (movie.video_url?.startsWith('/') ? movie.video_url : `/uploads/videos/${movie.video_url}`)}
>>>>>>> origin/main
                    style={{ pointerEvents: hasAccess ? 'auto' : 'none' }}
                >
                    Your browser does not support the video tag.
                </video>
            </div>

            <div className="grid lg:grid-cols-3 gap-12 pb-20">
                <div className="lg:col-span-2 space-y-12">
                    <section>
                        <div className="flex items-center gap-4 mb-4">
                            <h1 className="text-5xl font-black tracking-tight">{movie.title}</h1>
                            {isPremiumMovie && (
                                <span className="bg-gradient-to-r from-yellow-500 to-primary px-4 py-2 rounded-xl text-sm font-black uppercase tracking-wider flex items-center gap-2 shrink-0">
                                    <i className="fa fa-crown"></i>
                                    Premium
                                </span>
                            )}
                        </div>
                        <div className="flex flex-wrap items-center gap-4 text-gray-500 mb-8 font-bold text-sm">
                            <span className="bg-primary/10 text-primary px-3 py-1 rounded-lg uppercase tracking-widest">{movie.genre}</span>
                            <div className="w-1 h-1 bg-gray-700 rounded-full"></div>
                            <span>{movie.year}</span>
                            <div className="w-1 h-1 bg-gray-700 rounded-full"></div>
                            <div className="flex items-center gap-1 text-yellow-500">
                                <i className="fa fa-star text-xs"></i>
                                <span>{(reviews.reduce((acc, r) => acc + r.rating, 0) / (reviews.length || 1)).toFixed(1)} Rating</span>
                            </div>
                        </div>

                        <div className="bg-white/5 p-8 rounded-3xl border border-white/10">
                            <h3 className="text-lg font-bold mb-4 uppercase tracking-widest text-gray-400">Overview</h3>
                            <p className="text-gray-300 leading-relaxed text-lg">
                                {movie.description || 'No description available for this title.'}
                            </p>
                        </div>
                    </section>

                    {/* Ratings Section */}
                    <section className="bg-white/5 p-8 rounded-3xl border border-white/10">
                        <h2 className="text-2xl font-black mb-6">Reviews & Ratings</h2>
                        <form onSubmit={handleReviewSubmit} className="mb-8 space-y-4">
                            <div className="flex gap-2">
                                {[1, 2, 3, 4, 5].map(star => (
                                    <button
                                        key={star}
                                        type="button"
                                        onClick={() => setUserRating(star)}
                                        className={`text-2xl transition-all ${userRating >= star ? 'text-yellow-500 scale-110' : 'text-gray-600 hover:text-yellow-600'}`}
                                    >
                                        <i className="fa fa-star"></i>
                                    </button>
                                ))}
                            </div>
                            <textarea
                                value={reviewText}
                                onChange={(e) => setReviewText(e.target.value)}
                                placeholder="Write your review here..."
                                className="w-full bg-black/40 border border-white/10 rounded-2xl p-4 text-white focus:outline-none focus:border-primary transition-all min-h-[100px]"
                            />
                            <button
                                type="submit"
                                disabled={isSaving || userRating === 0}
                                className="bg-primary hover:bg-red-700 text-white px-8 py-3 rounded-xl font-bold transition-all disabled:opacity-50"
                            >
                                Submit Review
                            </button>
                        </form>

                        <div className="space-y-4">
                            {reviews.map(review => (
                                <div key={review.id} className="bg-white/5 p-6 rounded-2xl border border-white/5">
                                    <div className="flex justify-between items-center mb-2">
                                        <span className="font-bold">{review.username}</span>
                                        <div className="flex gap-1 text-yellow-500 text-xs">
                                            {[...Array(review.rating)].map((_, i) => <i key={i} className="fa fa-star"></i>)}
                                        </div>
                                    </div>
                                    <p className="text-gray-400 text-sm">{review.comment}</p>
                                </div>
                            ))}
                        </div>
                    </section>

                    {/* Comments Section */}
                    <section className="bg-white/5 p-8 rounded-3xl border border-white/10">
                        <h2 className="text-2xl font-black mb-6">Discussion</h2>
                        <form onSubmit={handleCommentSubmit} className="flex gap-4 mb-8">
                            <input
                                value={newComment}
                                onChange={(e) => setNewComment(e.target.value)}
                                placeholder="Add a public comment..."
                                className="flex-1 bg-black/40 border border-white/10 rounded-2xl px-6 py-4 text-white focus:outline-none focus:border-primary transition-all"
                            />
                            <button
                                type="submit"
                                disabled={isSaving}
                                className="bg-white text-black hover:bg-gray-200 px-8 py-4 rounded-2xl font-black transition-all disabled:opacity-50"
                            >
                                Post
                            </button>
                        </form>

                        <div className="space-y-6">
                            {comments.map(comment => (
                                <div key={comment.id} className="flex gap-4 group">
                                    <div className="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center font-bold text-primary shrink-0 uppercase">
                                        {comment.username[0]}
                                    </div>
                                    <div className="flex-1">
                                        <div className="flex items-center gap-3 mb-1">
                                            <span className="font-bold text-sm">{comment.username}</span>
                                            <span className="text-xs text-gray-500">
                                                {new Date(comment.created_at).toLocaleDateString()}
                                            </span>
                                        </div>
                                        <p className="text-gray-300">{comment.comment}</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </section>
                </div>

                <div className="space-y-6">
                    <div className="glass p-8 rounded-3xl border border-white/5 shadow-2xl sticky top-24">
                        <h3 className="text-lg font-bold mb-6 flex items-center gap-2">
                            <i className="fa fa-info-circle text-primary"></i> Details
                        </h3>
                        <div className="space-y-4 mb-8">
                            <div>
                                <span className="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-1">Director</span>
                                <span className="font-medium text-white">{movie.director || 'Unknown'}</span>
                            </div>
                            <div>
                                <span className="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-1">Starring</span>
                                <span className="font-medium text-white">{movie.actor || movie.cast || 'N/A'}</span>
                            </div>
                        </div>
                        <div className="flex flex-col gap-3">
                            <button
                                onClick={handleToggleFavorite}
                                className={`w-full py-4 rounded-2xl font-black transition-all flex items-center justify-center gap-3 active:scale-95 shadow-lg ${isFavorite ? 'bg-white text-black hover:bg-gray-200' : 'bg-primary text-white hover:bg-red-700 shadow-red-900/20'}`}
                            >
                                <i className={`fa ${isFavorite ? 'fa-check' : 'fa-plus'}`}></i>
                                {isFavorite ? 'In Favorites' : 'Add to Favorites'}
                            </button>
                            <button className="w-full bg-white/5 hover:bg-white/10 py-4 rounded-2xl font-black transition-all flex items-center justify-center gap-3 active:scale-95 border border-white/5">
                                <i className="fa fa-share"></i> Share Video
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Watch;
