import { useState, useEffect } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import api from '../api/axios';
import { useAuth } from '../hooks/useAuth';
import MovieCard from '../components/movie/MovieCard';

const Home = ({ viewMode: initialViewMode }) => {
    const [movies, setMovies] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchParams] = useSearchParams();
    const searchQuery = searchParams.get('q') || '';
    const [viewMode, setViewMode] = useState(initialViewMode || 'all');

    const [continueWatching, setContinueWatching] = useState([]);
    const { user } = useAuth(); // Import useAuth to check login status

    // Sync viewMode if prop changes
    useEffect(() => {
        if (initialViewMode) setViewMode(initialViewMode);
    }, [initialViewMode]);

    useEffect(() => {
        const fetchMovies = async () => {
            setLoading(true);
            try {
                const endpoint = `movies?view=${viewMode}${searchQuery ? `&q=${encodeURIComponent(searchQuery)}` : ''}`;
                const response = await api.get(endpoint);

                if (response.data.success) {
                    setMovies(response.data.data);
                }
            } catch (error) {
                console.error("Failed to fetch movies:", error);
            } finally {
                setLoading(false);
            }
        };

        const fetchContinueWatching = async () => {
            if (user && !searchQuery && viewMode === 'all') {
                try {
                    const res = await api.get('movies/user/continue-watching');
                    if (res.data.success) setContinueWatching(res.data.data);
                } catch (err) {
                    console.error("Failed to fetch continue watching:", err);
                }
            }
        };

        fetchMovies();
        fetchContinueWatching();
    }, [viewMode, searchQuery, user]);

    if (loading) {
        return (
            <div className="flex justify-center items-center h-64">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
            </div>
        );
    }

    return (
        <div>
            {/* Hero Banner - Only show on home page without search */}
            {!searchQuery && viewMode === 'all' && movies.length > 0 && (
                <div className="relative h-[60vh] -mx-6 mb-12 rounded-3xl overflow-hidden group">
                    <img
                        src={movies[0].poster_url?.startsWith('http') ? movies[0].poster_url : `${window.location.origin}${movies[0].poster_url}`}
                        alt={movies[0].title}
                        className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                    />
                    <div className="absolute inset-0 bg-gradient-to-r from-secondary via-secondary/60 to-transparent"></div>
                    <div className="absolute inset-0 bg-gradient-to-t from-secondary to-transparent"></div>

                    <div className="absolute inset-0 flex flex-col justify-center px-12 max-w-2xl">
                        <div className="flex items-center gap-3 mb-6 animate-in slide-in-from-left duration-500">
                            <span className="bg-primary px-3 py-1 rounded text-xs font-bold tracking-widest uppercase">Featured Content</span>
                            <span className="text-sm font-bold text-gray-400">{movies[0].year} • {movies[0].genre.toUpperCase()} • 4K</span>
                        </div>
                        <h1 className="text-6xl md:text-7xl font-black mb-6 leading-tight animate-in slide-in-from-left duration-700">
                            {movies[0].title}
                        </h1>
                        <p className="text-gray-300 text-lg mb-8 leading-relaxed line-clamp-3 animate-in slide-in-from-left duration-1000">
                            {movies[0].description || "Experience cinema like never before. Stream thousands of hand-picked movies and documentaries in stunning 4K quality."}
                        </p>
                        <div className="flex gap-4 animate-in slide-in-from-bottom duration-1000 delay-300">
                            <Link to={`/movies/${movies[0].id}`} className="bg-primary text-white px-8 py-4 rounded-2xl font-black flex items-center gap-3 hover:bg-red-700 transition-all shadow-xl shadow-red-900/20 active:scale-95">
                                <i className="fa fa-play"></i> Watch Now
                            </Link>
                            <button className="bg-white/10 text-white backdrop-blur-md px-8 py-4 rounded-2xl font-black flex items-center gap-3 hover:bg-white/20 transition-all border border-white/10 active:scale-95">
                                <i className="fa fa-plus"></i> My List
                            </button>
                        </div>
                    </div>
                </div>
            )}


            {/* Continue Watching Section */}
            {!searchQuery && viewMode === 'all' && continueWatching.length > 0 && (
                <section className="mb-12">
                    <h2 className="text-2xl font-black mb-6 flex items-center gap-3">
                        <i className="fa fa-history text-primary"></i> Continue Watching
                    </h2>
                    <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                        {continueWatching.map(movie => (
                            <MovieCard key={movie.id} movie={movie} viewMode="history" />
                        ))}
                    </div>
                </section>
            )}

            <header className="mb-12">
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <h2 className="text-3xl font-black mb-2 flex items-center gap-3">
                            {searchQuery ? (
                                <>
                                    <i className="fa fa-search text-primary"></i>
                                    Search Results for "{searchQuery}"
                                </>
                            ) : viewMode === 'trending' ? (
                                <>
                                    <i className="fa fa-fire text-primary"></i>
                                    Trending Now
                                </>
                            ) : (
                                <>
                                    <i className="fa fa-film text-primary"></i>
                                    Explore Library
                                </>
                            )}
                        </h2>
                        <p className="text-gray-500 font-medium italic">
                            {searchQuery ? `Found ${movies.length} movies matching your request.` :
                                viewMode === 'trending' ? 'The most popular movies this week.' : 'Our latest and greatest releases picked for you.'}
                        </p>
                    </div>

                    <div className="flex gap-4">
                        <Link
                            to="/movies"
                            className={`px-6 py-2.5 rounded-xl font-bold transition-all border ${viewMode === 'all' ? 'bg-primary border-primary shadow-lg shadow-red-900/20' : 'bg-white/5 border-white/5 text-gray-500 hover:text-white hover:bg-white/10'}`}
                        >
                            All Movies
                        </Link>
                        <Link
                            to="/trending"
                            className={`px-6 py-2.5 rounded-xl font-bold transition-all border ${viewMode === 'trending' ? 'bg-primary border-primary shadow-lg shadow-red-900/20' : 'bg-white/5 border-white/5 text-gray-500 hover:text-white hover:bg-white/10'}`}
                        >
                            Trending
                        </Link>
                    </div>
                </div>
            </header>

            <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                {movies.map(movie => (
                    <MovieCard key={movie.id} movie={movie} viewMode={viewMode} />
                ))}
            </div>

            {movies.length === 0 && (
                <div className="text-center py-24 bg-white/5 rounded-3xl border border-dashed border-white/10">
                    <i className="fa fa-film text-5xl text-gray-700 mb-6"></i>
                    <p className="text-xl text-gray-500 font-medium">No movies found in this section.</p>
                </div>
            )}
        </div>
    );
};

export default Home;
