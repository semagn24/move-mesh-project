import { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../../api/axios';

const SearchDropdown = () => {
    const [isOpen, setIsOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedGenre, setSelectedGenre] = useState('all');
    const [selectedYear, setSelectedYear] = useState('all');
    const [results, setResults] = useState([]);
    const [loading, setLoading] = useState(false);
    const [genres, setGenres] = useState([]);
    const dropdownRef = useRef(null);
    const navigate = useNavigate();

    useEffect(() => {
        // Fetch available genres
        const fetchGenres = async () => {
            try {
                const response = await api.get('/movies');
                if (response.data.success) {
                    const uniqueGenres = [...new Set(response.data.data.map(m => m.genre))].filter(Boolean);
                    setGenres(uniqueGenres);
                }
            } catch (error) {
                console.error('Failed to fetch genres:', error);
            }
        };
        fetchGenres();
    }, []);

    useEffect(() => {
        // Close dropdown when clicking outside
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setIsOpen(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    useEffect(() => {
        // Search when query, genre, or year changes
        if (searchQuery || selectedGenre !== 'all' || selectedYear !== 'all') {
            performSearch();
        } else {
            setResults([]);
        }
    }, [searchQuery, selectedGenre, selectedYear]);

    const performSearch = async () => {
        setLoading(true);
        try {
            const response = await api.get('/movies');
            if (response.data.success) {
                let filtered = response.data.data;

                // Filter by search query
                if (searchQuery) {
                    const query = searchQuery.toLowerCase();
                    filtered = filtered.filter(movie =>
                        movie.title.toLowerCase().includes(query) ||
                        movie.description?.toLowerCase().includes(query) ||
                        movie.genre?.toLowerCase().includes(query)
                    );
                }

                // Filter by genre
                if (selectedGenre !== 'all') {
                    filtered = filtered.filter(movie => movie.genre === selectedGenre);
                }

                // Filter by year
                if (selectedYear !== 'all') {
                    filtered = filtered.filter(movie => movie.year === parseInt(selectedYear));
                }

                setResults(filtered.slice(0, 10)); // Limit to 10 results
            }
        } catch (error) {
            console.error('Search failed:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleMovieClick = (movieId) => {
        navigate(`/movies/${movieId}`);
        setIsOpen(false);
        setSearchQuery('');
        setSelectedGenre('all');
        setSelectedYear('all');
    };

    const currentYear = new Date().getFullYear();
    const years = Array.from({ length: 30 }, (_, i) => currentYear - i);

    return (
        <div className="relative" ref={dropdownRef}>
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="hidden sm:flex items-center bg-white/5 border border-white/5 rounded-2xl px-4 py-2 hover:bg-white/10 transition-all group"
            >
                <i className="fa fa-search text-gray-500 group-hover:text-primary transition-colors"></i>
                <span className="px-4 py-1 text-sm text-gray-500">Search movies...</span>
            </button>

            {isOpen && (
                <div className="absolute left-0 mt-4 w-[600px] bg-secondary border border-white/10 rounded-2xl shadow-2xl overflow-hidden z-50">
                    {/* Search Header */}
                    <div className="p-6 border-b border-white/10">
                        <h3 className="font-bold text-lg mb-4">Advanced Search</h3>

                        {/* Search Input */}
                        <div className="relative mb-4">
                            <i className="fa fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                            <input
                                type="text"
                                placeholder="Search by title, description, or genre..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="w-full bg-black/40 border border-white/10 rounded-xl pl-12 pr-4 py-3 text-white focus:outline-none focus:border-primary transition-all"
                                autoFocus
                            />
                        </div>

                        {/* Filters */}
                        <div className="grid grid-cols-2 gap-4">
                            {/* Genre Filter */}
                            <div>
                                <label className="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-2">
                                    Genre
                                </label>
                                <select
                                    value={selectedGenre}
                                    onChange={(e) => setSelectedGenre(e.target.value)}
                                    className="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-primary transition-all"
                                >
                                    <option value="all">All Genres</option>
                                    {genres.map(genre => (
                                        <option key={genre} value={genre}>{genre}</option>
                                    ))}
                                </select>
                            </div>

                            {/* Year Filter */}
                            <div>
                                <label className="text-xs font-bold text-gray-500 uppercase tracking-widest block mb-2">
                                    Year
                                </label>
                                <select
                                    value={selectedYear}
                                    onChange={(e) => setSelectedYear(e.target.value)}
                                    className="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-primary transition-all"
                                >
                                    <option value="all">All Years</option>
                                    {years.map(year => (
                                        <option key={year} value={year}>{year}</option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        {/* Quick Actions */}
                        <div className="flex gap-2 mt-4">
                            <button
                                onClick={() => {
                                    setSearchQuery('');
                                    setSelectedGenre('all');
                                    setSelectedYear('all');
                                }}
                                className="text-xs text-gray-500 hover:text-white font-medium"
                            >
                                Clear Filters
                            </button>
                        </div>
                    </div>

                    {/* Results */}
                    <div className="max-h-96 overflow-y-auto custom-scrollbar">
                        {loading ? (
                            <div className="px-6 py-12 text-center">
                                <div className="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary mx-auto"></div>
                            </div>
                        ) : results.length === 0 ? (
                            <div className="px-6 py-12 text-center text-gray-500">
                                <i className="fa fa-film text-4xl mb-4 block"></i>
                                <p className="font-medium">
                                    {searchQuery || selectedGenre !== 'all' || selectedYear !== 'all'
                                        ? 'No movies found'
                                        : 'Start searching for movies'}
                                </p>
                            </div>
                        ) : (
                            <div className="divide-y divide-white/5">
                                {results.map((movie) => (
                                    <button
                                        key={movie.id}
                                        onClick={() => handleMovieClick(movie.id)}
                                        className="w-full px-6 py-4 hover:bg-white/5 transition-colors text-left flex gap-4"
                                    >
                                        <img
                                            src={movie.poster_url}
                                            alt={movie.title}
                                            className="w-16 h-24 object-cover rounded-lg shrink-0"
                                        />
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-start justify-between gap-2 mb-1">
                                                <h4 className="font-bold text-sm leading-tight line-clamp-1">
                                                    {movie.title}
                                                </h4>
                                                {movie.is_premium && (
                                                    <span className="bg-gradient-to-r from-yellow-500 to-primary px-2 py-1 rounded text-xs font-black uppercase shrink-0">
                                                        <i className="fa fa-crown"></i>
                                                    </span>
                                                )}
                                            </div>
                                            <div className="flex items-center gap-2 text-xs text-gray-500 mb-2">
                                                <span className="bg-primary/10 text-primary px-2 py-0.5 rounded uppercase font-bold">
                                                    {movie.genre}
                                                </span>
                                                <span>{movie.year}</span>
                                                {movie.rating && (
                                                    <>
                                                        <span>•</span>
                                                        <div className="flex items-center gap-1 text-yellow-500">
                                                            <i className="fa fa-star"></i>
                                                            <span>{movie.rating}</span>
                                                        </div>
                                                    </>
                                                )}
                                            </div>
                                            <p className="text-xs text-gray-400 line-clamp-2 leading-relaxed">
                                                {movie.description || 'No description available'}
                                            </p>
                                        </div>
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Footer */}
                    {results.length > 0 && (
                        <div className="px-6 py-3 border-t border-white/10 text-center">
                            <button
                                onClick={() => {
                                    navigate(`/?q=${searchQuery}&genre=${selectedGenre}&year=${selectedYear}`);
                                    setIsOpen(false);
                                }}
                                className="text-sm text-primary hover:underline font-bold"
                            >
                                View All Results
                            </button>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
};

export default SearchDropdown;
