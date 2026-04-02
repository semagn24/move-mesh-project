import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api/axios';

const EditMovies = () => {
    const [movies, setMovies] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchMovies = async () => {
            try {
                const response = await api.get('movies');
                if (response.data.success) {
                    setMovies(response.data.data);
                }
            } catch (err) {
                console.error("Failed to fetch movies");
            } finally {
                setLoading(false);
            }
        };
        fetchMovies();
    }, []);

    const handleDelete = async (id) => {
        if (!confirm('Are you sure you want to delete this movie?')) return;

        try {
            await api.delete(`admin/movies/${id}`);
            setMovies(movies.filter(m => m.id !== id));
            alert('Movie deleted successfully!');
        } catch (err) {
            alert('Failed to delete movie');
        }
    };

    return (
        <div className="max-w-7xl mx-auto py-8">
            <div className="flex justify-between items-center mb-8">
                <h1 className="text-3xl font-bold">Edit Movies</h1>
                <Link to="/admin/add-movie" className="bg-primary hover:bg-red-700 px-6 py-3 rounded-xl font-bold flex items-center gap-2 transition-all">
                    <i className="fa fa-plus"></i> Add New Movie
                </Link>
            </div>

            {loading ? (
                <div className="flex justify-center py-20">
                    <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {movies.map(movie => (
                        <div key={movie.id} className="glass rounded-2xl overflow-hidden border border-white/5 group hover:border-primary/20 transition-all">
                            <div className="relative aspect-[2/3] overflow-hidden">
                                <img
                                    src={movie.poster_url || 'https://via.placeholder.com/300x450?text=No+Poster'}
                                    alt={movie.title}
                                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                                <div className="absolute top-3 right-3 flex gap-2">
                                    <button
                                        onClick={() => handleDelete(movie.id)}
                                        className="w-8 h-8 bg-red-500/80 hover:bg-red-500 backdrop-blur-md rounded-lg flex items-center justify-center transition-all"
                                    >
                                        <i className="fa fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            <div className="p-4">
                                <h3 className="font-bold truncate mb-2">{movie.title}</h3>
                                <div className="flex items-center justify-between text-xs text-gray-500 mb-3">
                                    <span>{movie.year || '2024'}</span>
                                    <span className="bg-white/5 px-2 py-1 rounded">{movie.genre || 'N/A'}</span>
                                </div>
                                <Link
                                    to={`/admin/edit-movie/${movie.id}`}
                                    className="w-full bg-white/5 hover:bg-white/10 border border-white/10 py-2 rounded-lg font-bold text-sm flex items-center justify-center gap-2 transition-all"
                                >
                                    <i className="fa fa-edit text-xs"></i> Edit Details
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {movies.length === 0 && !loading && (
                <div className="text-center py-24 bg-white/5 rounded-3xl border border-dashed border-white/10">
                    <i className="fa fa-film text-5xl text-gray-700 mb-6"></i>
                    <p className="text-xl text-gray-500 font-medium mb-4">No movies in library yet.</p>
                    <Link to="/admin/add-movie" className="inline-block bg-primary hover:bg-red-700 px-6 py-3 rounded-xl font-bold transition-all">
                        Upload Your First Movie
                    </Link>
                </div>
            )}
        </div>
    );
};

export default EditMovies;
