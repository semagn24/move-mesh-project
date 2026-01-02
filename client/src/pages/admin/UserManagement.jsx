
import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api/axios';

const UserManagement = () => {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [editingUser, setEditingUser] = useState(null);
    const [editData, setEditData] = useState({ username: '', email: '', role: '' });

    const fetchUsers = async () => {
        setLoading(true);
        try {
            const response = await api.get('admin/users');
            if (response.data.success) {
                setUsers(response.data.users);
            }
        } catch (err) {
            console.error("Failed to fetch users");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchUsers();
    }, []);

    const handleDelete = async (id) => {
        // Get current logged-in user
        const currentUser = localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : null;
        const currentUserId = currentUser?.id;

        // Prevent self-deletion
        if (id === currentUserId) {
            alert('⚠️ You cannot delete your own account!\n\nTo prevent accidental lockout, admins cannot delete themselves.');
            return;
        }

        if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;

        try {
            await api.delete(`admin/users/${id}`);
            setUsers(users.filter(u => u.id !== id));
            alert('User deleted successfully');
        } catch (err) {
            console.error('Delete error:', err);
            alert('Failed to delete user. ' + (err.response?.data?.message || ''));
        }
    };

    const startEdit = (user) => {
        setEditingUser(user.id);
        setEditData({ username: user.username, email: user.email, role: user.role });
    };

    const handleUpdate = async (id) => {
        try {
            await api.put(`admin/users/${id}`, editData);
            setEditingUser(null);
            fetchUsers();
        } catch (err) {
            alert('Failed to update user');
        }
    };

    return (
        <div className="max-w-6xl mx-auto py-8">
            <Link to="/admin" className="inline-flex items-center gap-2 text-gray-400 hover:text-white mb-6 transition-colors">
                <i className="fa fa-arrow-left"></i> Back to Dashboard
            </Link>
            <h1 className="text-3xl font-bold mb-8">User Management</h1>
            <div className="glass rounded-3xl overflow-hidden border border-white/5 shadow-2xl">
                <table className="w-full text-left border-collapse">
                    <thead>
                        <tr className="bg-white/5 text-gray-500 text-xs uppercase tracking-[0.2em] font-bold">
                            <th className="px-8 py-6">User</th>
                            <th className="px-8 py-6">Email</th>
                            <th className="px-8 py-6">Role</th>
                            <th className="px-8 py-6">Joined</th>
                            <th className="px-8 py-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-white/5">
                        {users.map(user => {
                            const currentUser = localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : null;
                            const isCurrentUser = user.id === currentUser?.id;

                            return (
                                <tr key={user.id} className="hover:bg-white/5 transition-colors group">
                                    <td className="px-8 py-6">
                                        <div className="flex items-center gap-4">
                                            <div className="w-10 h-10 bg-primary/20 text-primary flex items-center justify-center rounded-xl font-bold">
                                                {user.username[0].toUpperCase()}
                                            </div>
                                            {editingUser === user.id ? (
                                                <input
                                                    className="bg-white/10 border border-white/20 rounded px-2 py-1 outline-none focus:border-primary"
                                                    value={editData.username}
                                                    onChange={(e) => setEditData({ ...editData, username: e.target.value })}
                                                />
                                            ) : (
                                                <div className="flex items-center gap-2">
                                                    <div className="font-bold">{user.username}</div>
                                                    {isCurrentUser && (
                                                        <span className="px-2 py-1 rounded-lg text-xs font-bold uppercase bg-blue-500/20 text-blue-500">
                                                            You
                                                        </span>
                                                    )}
                                                </div>
                                            )}
                                        </div>
                                    </td>
                                    <td className="px-8 py-6 text-gray-400">
                                        {editingUser === user.id ? (
                                            <input
                                                className="bg-white/10 border border-white/20 rounded px-2 py-1 outline-none focus:border-primary w-full"
                                                value={editData.email}
                                                onChange={(e) => setEditData({ ...editData, email: e.target.value })}
                                            />
                                        ) : user.email}
                                    </td>
                                    <td className="px-8 py-6">
                                        {editingUser === user.id ? (
                                            <select
                                                className="bg-white/10 border border-white/20 rounded px-2 py-1 outline-none focus:border-primary text-white"
                                                value={editData.role}
                                                onChange={(e) => setEditData({ ...editData, role: e.target.value })}
                                            >
                                                <option value="user" className="bg-secondary">User</option>
                                                <option value="admin" className="bg-secondary">Admin</option>
                                            </select>
                                        ) : (
                                            <span className={`px - 3 py - 1 rounded - lg text - xs font - bold uppercase ${user.role === 'admin' ? 'bg-primary/20 text-primary' : 'bg-green-500/20 text-green-500'} `}>
                                                {user.role}
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-8 py-6 text-gray-500 text-sm">
                                        {user.created_at ? new Date(user.created_at).toLocaleDateString() : 'Jan 1, 2024'}
                                    </td>
                                    <td className="px-8 py-6 text-right">
                                        <div className="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            {editingUser === user.id ? (
                                                <>
                                                    <button onClick={() => handleUpdate(user.id)} className="w-8 h-8 bg-green-500/10 hover:bg-green-500/20 flex items-center justify-center rounded-lg text-green-500">
                                                        <i className="fa fa-check text-xs"></i>
                                                    </button>
                                                    <button onClick={() => setEditingUser(null)} className="w-8 h-8 bg-white/5 hover:bg-white/10 flex items-center justify-center rounded-lg text-gray-400">
                                                        <i className="fa fa-times text-xs"></i>
                                                    </button>
                                                </>
                                            ) : (
                                                <>
                                                    <button onClick={() => startEdit(user)} className="w-8 h-8 bg-white/5 hover:bg-white/10 flex items-center justify-center rounded-lg">
                                                        <i className="fa fa-edit text-xs"></i>
                                                    </button>
                                                    <button
                                                        onClick={() => handleDelete(user.id)}
                                                        disabled={isCurrentUser}
                                                        className={`w - 8 h - 8 flex items - center justify - center rounded - lg ${isCurrentUser
                                                            ? 'bg-gray-500/10 text-gray-600 cursor-not-allowed'
                                                            : 'bg-red-500/10 hover:bg-red-500/20 text-red-500'
                                                            } `}
                                                        title={isCurrentUser ? 'You cannot delete your own account' : 'Delete user'}
                                                    >
                                                        <i className="fa fa-trash text-xs"></i>
                                                    </button>
                                                </>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            )
                        })}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default UserManagement;
