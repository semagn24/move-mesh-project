import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../../api/axios';

const EditMovie = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [formData, setFormData] = useState({
        title: '',
        description: '',
        actor: '',
        genre: '',
        year: '',
    });
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [message, setMessage] = useState({ type: '', text: '' });

    useEffect(() => {
        const fetchMovie = async () => {
            try {
                const response = await api.get(`movies/${id}`);
                if (response.data.success) {
                    const movie = response.data.data;
                    setFormData({
                        title: movie.title || '',
                        description: movie.description || '',
                        actor: movie.actor || '',
                        genre: movie.genre || '',
                        year: movie.year || '',
                    });
                }
            } catch (error) {
                setMessage({ type: 'error', text: 'Failed to fetch movie details' });
            } finally {
                setLoading(false);
            }
        };
        fetchMovie();
    }, [id]);

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        setMessage({ type: '', text: '' });

        try {
            const response = await api.put(`admin/movies/${id}`, formData);

            if (response.data.success) {
                setMessage({ type: 'success', text: 'Movie updated successfully!' });
                setTimeout(() => navigate('/admin/edit-movies'), 1500);
            } else {
                setMessage({ type: 'error', text: response.data.message || 'Failed to update movie' });
            }
        } catch (error) {
            setMessage({ type: 'error', text: error.response?.data?.message || 'Server error occurred' });
        } finally {
            setSaving(false);
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center py-20">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
            </div>
        );
    }

    return (
        <div className="max-w-4xl mx-auto py-8">
            <h1 className="text-3xl font-bold mb-8">Edit Movie Details</h1>

            {message.text && (
                <div className={`p-4 rounded-xl mb-6 ${message.type === 'success' ? 'bg-green-500/20 text-green-500' : 'bg-red-500/20 text-red-500'}`}>
                    {message.text}
                </div>
            )}

            <form onSubmit={handleSubmit} className="glass p-8 rounded-3xl border border-white/5 shadow-2xl">
                <div className="space-y-6">
                    <div>
                        <label className="block text-sm font-bold text-gray-400 mb-2">Movie Title</label>
                        <input
                            type="text" name="title" required
                            value={formData.title} onChange={handleChange}
                            className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-primary transition-all"
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-bold text-gray-400 mb-2">Lead Actor</label>
                        <input
                            type="text" name="actor"
                            value={formData.actor} onChange={handleChange}
                            className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-primary transition-all"
                        />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-bold text-gray-400 mb-2">Genre</label>
                            <input
                                type="text" name="genre"
                                value={formData.genre} onChange={handleChange}
                                className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-primary transition-all"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-bold text-gray-400 mb-2">Release Year</label>
                            <input
                                type="number" name="year"
                                value={formData.year} onChange={handleChange}
                                className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-primary transition-all"
                            />
                        </div>
                    </div>
                    <div>
                        <label className="block text-sm font-bold text-gray-400 mb-2">Description</label>
                        <textarea
                            name="description" rows="6"
                            value={formData.description} onChange={handleChange}
                            className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-primary transition-all resize-none"
                        ></textarea>
                    </div>

                    <div className="flex gap-4 pt-4">
                        <button
                            type="button"
                            onClick={() => navigate('/admin/edit-movies')}
                            className="flex-1 bg-white/5 hover:bg-white/10 py-4 rounded-xl font-bold text-lg transition-all"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit" disabled={saving}
                            className="flex-[2] bg-primary hover:bg-red-700 py-4 rounded-xl font-bold text-lg transition-all shadow-xl shadow-red-900/20 disabled:opacity-50"
                        >
                            {saving ? 'Saving...' : 'Save Changes'}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    );
};

export default EditMovie;
