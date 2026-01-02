import { useState, useEffect, useRef } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api/axios';

const NotificationDropdown = () => {
    const [notifications, setNotifications] = useState([]);
    const [isOpen, setIsOpen] = useState(false);
    const [unreadCount, setUnreadCount] = useState(0);
    const dropdownRef = useRef(null);

    useEffect(() => {
        fetchNotifications();

        // Poll for new notifications every 30 seconds
        const interval = setInterval(fetchNotifications, 30000);

        return () => clearInterval(interval);
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

    const fetchNotifications = async () => {
        try {
            const response = await api.get('/notifications');
            if (response.data.success) {
                setNotifications(response.data.notifications);
                setUnreadCount(response.data.notifications.filter(n => !n.is_read).length);
            }
        } catch (error) {
            console.error('Failed to fetch notifications:', error);
        }
    };

    const markAsRead = async (id) => {
        try {
            await api.put(`/notifications/${id}/read`);
            setNotifications(notifications.map(n =>
                n.id === id ? { ...n, is_read: 1 } : n
            ));
            setUnreadCount(prev => Math.max(0, prev - 1));
        } catch (error) {
            console.error('Failed to mark as read:', error);
        }
    };

    const markAllAsRead = async () => {
        try {
            await api.put('/notifications/read-all');
            setNotifications(notifications.map(n => ({ ...n, is_read: 1 })));
            setUnreadCount(0);
        } catch (error) {
            console.error('Failed to mark all as read:', error);
        }
    };

    const deleteNotification = async (id) => {
        try {
            await api.delete(`/notifications/${id}`);
            setNotifications(notifications.filter(n => n.id !== id));
            const deletedNotif = notifications.find(n => n.id === id);
            if (deletedNotif && !deletedNotif.is_read) {
                setUnreadCount(prev => Math.max(0, prev - 1));
            }
        } catch (error) {
            console.error('Failed to delete notification:', error);
        }
    };

    const getNotificationIcon = (type) => {
        switch (type) {
            case 'payment':
                return 'fa-credit-card';
            case 'movie':
                return 'fa-film';
            case 'welcome':
                return 'fa-hand-wave';
            case 'system':
                return 'fa-info-circle';
            default:
                return 'fa-bell';
        }
    };

    const getNotificationColor = (type) => {
        switch (type) {
            case 'payment':
                return 'text-green-500';
            case 'movie':
                return 'text-primary';
            case 'welcome':
                return 'text-blue-500';
            case 'system':
                return 'text-yellow-500';
            default:
                return 'text-gray-500';
        }
    };

    const formatTime = (timestamp) => {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'Just now';
        if (minutes < 60) return `${minutes}m ago`;
        if (hours < 24) return `${hours}h ago`;
        if (days < 7) return `${days}d ago`;
        return date.toLocaleDateString();
    };

    return (
        <div className="relative" ref={dropdownRef}>
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="text-gray-400 hover:text-white transition-colors relative"
                title="Notifications"
            >
                <i className="fa fa-bell"></i>
                {unreadCount > 0 && (
                    <span className="absolute -top-1 -right-1 w-5 h-5 bg-primary rounded-full text-white text-[10px] font-bold flex items-center justify-center">
                        {unreadCount > 9 ? '9+' : unreadCount}
                    </span>
                )}
            </button>

            {isOpen && (
                <div className="absolute right-0 mt-4 w-96 bg-secondary border border-white/10 rounded-2xl shadow-2xl overflow-hidden z-50">
                    {/* Header */}
                    <div className="px-6 py-4 border-b border-white/10 flex items-center justify-between">
                        <h3 className="font-bold text-lg">Notifications</h3>
                        {unreadCount > 0 && (
                            <button
                                onClick={markAllAsRead}
                                className="text-xs text-primary hover:underline font-bold"
                            >
                                Mark all as read
                            </button>
                        )}
                    </div>

                    {/* Notifications List */}
                    <div className="max-h-96 overflow-y-auto custom-scrollbar">
                        {notifications.length === 0 ? (
                            <div className="px-6 py-12 text-center text-gray-500">
                                <i className="fa fa-bell-slash text-4xl mb-4 block"></i>
                                <p className="font-medium">No notifications yet</p>
                            </div>
                        ) : (
                            notifications.map((notification) => (
                                <div
                                    key={notification.id}
                                    className={`px-6 py-4 border-b border-white/5 hover:bg-white/5 transition-colors ${!notification.is_read ? 'bg-white/5' : ''
                                        }`}
                                >
                                    <div className="flex gap-4">
                                        <div className={`w-10 h-10 rounded-full bg-white/10 flex items-center justify-center shrink-0 ${getNotificationColor(notification.type)}`}>
                                            <i className={`fa ${getNotificationIcon(notification.type)}`}></i>
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-start justify-between gap-2 mb-1">
                                                <h4 className="font-bold text-sm leading-tight">
                                                    {notification.title}
                                                </h4>
                                                {!notification.is_read && (
                                                    <span className="w-2 h-2 bg-primary rounded-full shrink-0 mt-1"></span>
                                                )}
                                            </div>
                                            <p className="text-sm text-gray-400 leading-snug mb-2">
                                                {notification.message}
                                            </p>
                                            <div className="flex items-center justify-between">
                                                <span className="text-xs text-gray-600 font-medium">
                                                    {formatTime(notification.created_at)}
                                                </span>
                                                <div className="flex items-center gap-2">
                                                    {notification.link && (
                                                        <Link
                                                            to={notification.link}
                                                            onClick={() => {
                                                                markAsRead(notification.id);
                                                                setIsOpen(false);
                                                            }}
                                                            className="text-xs text-primary hover:underline font-bold"
                                                        >
                                                            View
                                                        </Link>
                                                    )}
                                                    {!notification.is_read && (
                                                        <button
                                                            onClick={() => markAsRead(notification.id)}
                                                            className="text-xs text-gray-500 hover:text-white font-medium"
                                                        >
                                                            Mark read
                                                        </button>
                                                    )}
                                                    <button
                                                        onClick={() => deleteNotification(notification.id)}
                                                        className="text-xs text-gray-500 hover:text-red-500 font-medium"
                                                    >
                                                        <i className="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>

                    {/* Footer */}
                    {notifications.length > 0 && (
                        <div className="px-6 py-3 border-t border-white/10 text-center">
                            <Link
                                to="/notifications"
                                onClick={() => setIsOpen(false)}
                                className="text-sm text-primary hover:underline font-bold"
                            >
                                View All Notifications
                            </Link>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
};

export default NotificationDropdown;
