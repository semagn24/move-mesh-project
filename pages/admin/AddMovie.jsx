import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../../api/axios';

const AddMovie = () => {
    const navigate = useNavigate();
    const [formData, setFormData] = useState({
        title: '',
        description: '',
        actor: '',
        genre: '',
        year: new Date().getFullYear(),
    });
    const [poster, setPoster] = useState(null);
    const [video, setVideo] = useState(null);
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState({ type: '', text: '' });

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleFileChange = (e) => {
        if (e.target.name === 'poster') setPoster(e.target.files[0]);
        if (e.target.name === 'video') setVideo(e.target.files[0]);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setMessage({ type: '', text: '' });

        const data = new FormData();
        Object.keys(formData).forEach(key => data.append(key, formData[key]));
        if (poster) data.append('poster', poster);
        if (video) data.append('video', video);

        try {
            const response = await api.post('admin/movies', data, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            if (response.data.success) {
                setMessage({ type: 'success', text: 'Movie added successfully!' });
                setTimeout(() => navigate('/admin'), 2000);
            } else {
                setMessage({ type: 'error', text: response.data.error || 'Failed to add movie' });
            }
        } catch (error) {
            setMessage({ type: 'error', text: error.response?.data?.error || 'Server error occurred' });
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="max-w-4xl mx-auto py-8">
            <h1 className="text-3xl font-bold mb-8">Upload New Content</h1>

            {message.text && (
                <div className={`p-4 rounded-xl mb-6 ${message.type === 'success' ? 'bg-green-500/20 text-green-500' : 'bg-red-500/20 text-red-500'}`}>
                    {message.text}
                </div>
            )}

            <form onSubmit={handleSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div className="space-y-6">
                    <div>
                        <label className="block text-sm font-bold text-gray-400 mb-2">Movie Title</label>
                        <input
                            type="text" name="title" required
                            value={formData.title} onChange={handleChange}
                            className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-primary transition-all"
                            placeholder="e.g. Inception"
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-bold text-gray-400 mb-2">Lead Actor</label>
                        <input
                            type="text" name="actor"
                            value={formData.actor} onChange={handleChange}
                            className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-primary transition-all"
                            placeholder="e.g. Leonardo DiCaprio"
                        />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-bold text-gray-400 mb-2">Genre</label>
                            <input
                                type="text" name="genre"
                                value={formData.genre} onChange={handleChange}
                                className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-primary transition-all"
                                placeholder="Sci-Fi"
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
                            name="description" rows="4"
                            value={formData.description} onChange={handleChange}
                            className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-primary transition-all resize-none"
                            placeholder="Briefly describe the movie..."
                        ></textarea>
                    </div>
                </div>

                <div className="space-y-6">
                    <div className="p-8 bg-white/5 border-2 border-dashed border-white/10 rounded-2xl flex flex-col items-center justify-center text-center">
                        <i className={`fa ${poster ? 'fa-check-circle text-green-500' : 'fa-image text-gray-500'} text-4xl mb-4`}></i>
                        <p className="font-bold mb-1">{poster ? poster.name : 'Poster Image'}</p>
                        <p className="text-xs text-gray-500 mb-4">Recommended: 2:3 aspect ratio</p>
                        <label className="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-lg cursor-pointer transition-all">
                            Browse File
                            <input type="file" name="poster" accept="image/*" className="hidden" onChange={handleFileChange} />
                        </label>
                    </div>

                    <div className="p-8 bg-white/5 border-2 border-dashed border-white/10 rounded-2xl flex flex-col items-center justify-center text-center">
                        <i className={`fa ${video ? 'fa-video text-primary' : 'fa-film text-gray-500'} text-4xl mb-4`}></i>
                        <p className="font-bold mb-1">{video ? video.name : 'Movie Video File'}</p>
                        <p className="text-xs text-gray-500 mb-4">Format: MP4 (max 500MB)</p>
                        <label className="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-lg cursor-pointer transition-all">
                            Browse File
                            <input type="file" name="video" accept="video/mp4" className="hidden" onChange={handleFileChange} />
                        </label>
                    </div>

                    <button
                        type="submit" disabled={loading}
                        className="w-full bg-primary hover:bg-red-700 py-4 rounded-xl font-bold text-lg transition-all shadow-xl shadow-red-900/20 disabled:opacity-50"
                    >
                        {loading ? 'Uploading...' : 'Publish Movie'}
                    </button>
                </div>
            </form>
        </div>
    );
};

export default AddMovie;
