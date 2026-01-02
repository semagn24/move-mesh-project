import { useState } from 'react';
import { Link } from 'react-router-dom';

const MovieCard = ({ movie, viewMode }) => {
    const [isMenuOpen, setIsMenuOpen] = useState(false);

    const toggleMenu = (e) => {
        e.preventDefault();
        e.stopPropagation();
        setIsMenuOpen(!isMenuOpen);
    };

    const handleShare = (e) => {
        e.preventDefault();
        const url = `${window.location.origin}/movies/${movie.id}`;
        navigator.clipboard.writeText(url);
        alert("Link copied to clipboard!");
        setIsMenuOpen(false);
    };

    // Construct image URL (handles both relative and absolute paths)
    const getPosterUrl = () => {
        if (!movie.poster) return 'https://via.placeholder.com/300x450?text=No+Poster';
        if (movie.poster.startsWith('http')) return movie.poster;
<<<<<<< HEAD
        if (movie.poster_url) {
            if (movie.poster_url.startsWith('http')) return movie.poster_url;
            return `${window.location.origin}${movie.poster_url.startsWith('/') ? '' : '/'}${movie.poster_url}`;
        }
        // Fallback for raw poster field
        return `${window.location.origin}/uploads/posters/${movie.poster}`;
=======

        // Backend provides full relative path (e.g. /uploads/posters/...)
        if (movie.poster_url) {
            return movie.poster_url;
        }

        // Fallback for raw poster field
        return `/uploads/posters/${movie.poster}`;
>>>>>>> origin/main
    };

    return (
        <div className="group relative bg-[#1a1a1a] rounded-xl overflow-visible transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-black/50">
            {/* Options Menu */}
            <div className="absolute top-3 right-3 z-30">
                <button
                    onClick={toggleMenu}
                    className="w-8 h-8 flex items-center justify-center bg-black/60 backdrop-blur-md text-white rounded-full border border-white/10 hover:bg-primary transition-all active:scale-90"
                >
                    <i className={`fa ${isMenuOpen ? 'fa-times' : 'fa-ellipsis-v'} text-xs`}></i>
                </button>

                {isMenuOpen && (
                    <div className="absolute right-0 top-10 w-48 bg-[#222] border border-white/10 rounded-xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
                        <Link to={`/movies/${movie.id}`} className="flex items-center gap-3 px-4 py-3 text-sm text-gray-300 hover:bg-primary hover:text-white transition-colors">
                            <i className="fa fa-play w-4 text-center"></i> Watch Now
                        </Link>
                        <button className="w-full flex items-center gap-3 px-4 py-3 text-sm text-gray-300 hover:bg-primary hover:text-white transition-colors">
                            <i className="fa fa-plus w-4 text-center"></i> My List
                        </button>
                        <button onClick={handleShare} className="w-full flex items-center gap-3 px-4 py-3 text-sm text-gray-300 hover:bg-primary hover:text-white transition-colors">
                            <i className="fa fa-share w-4 text-center"></i> Share
                        </button>
                    </div>
                )}
            </div>

            <Link to={`/movies/${movie.id}`} className="block relative aspect-[2/3] overflow-hidden rounded-t-xl">
                <img
                    src={getPosterUrl()}
                    alt={movie.title}
                    className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                    onError={(e) => { e.target.src = 'https://via.placeholder.com/300x450?text=No+Poster'; }}
                />

                {/* Play Overlay */}
                <div className="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                    <div className="w-12 h-12 bg-primary rounded-full flex items-center justify-center text-white shadow-xl scale-75 group-hover:scale-100 transition-transform">
                        <i className="fa fa-play ml-1"></i>
                    </div>
                </div>
            </Link>

            <div className="p-4">
                <h3 className="font-bold text-sm truncate group-hover:text-primary transition-colors mb-1" title={movie.title}>
                    {movie.title}
                </h3>

                <div className="flex items-center justify-between mt-2">
                    <span className="text-[10px] uppercase tracking-widest text-gray-500 font-bold">
                        {movie.year || '2024'} • {movie.genre || 'Action'}
                    </span>

                    {viewMode === 'trending' && (
                        <span className="flex items-center gap-1.5 text-[10px] font-bold text-primary bg-primary/10 px-2 py-1 rounded-md">
                            <i className="fa fa-eye"></i>
                            {new Number(movie.view_count || movie.views || 0).toLocaleString()}
                        </span>
                    )}
                </div>
            </div>
        </div>
    );
};

export default MovieCard;
