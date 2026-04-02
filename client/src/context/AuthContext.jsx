<<<<<<< HEAD
// Test change for git detection
=======
>>>>>>> origin/main
import { createContext, useState, useEffect, useContext } from 'react';
import { getMe, loginUser, logoutUser } from '../api/auth.api';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    // Check if user is logged in on mount
    useEffect(() => {
        const checkUser = async () => {
            try {
                // Try from local storage first for immediate UI
                const storedUser = localStorage.getItem('user');
                console.log("[Auth] Checking storage:", storedUser);
                if (storedUser) {
                    setUser(JSON.parse(storedUser));
                }

                // Verify with backend
                console.log("[Auth] Verifying with backend...");
                const { user: apiUser } = await getMe();
                console.log("[Auth] Backend verified:", apiUser);
                setUser(apiUser);
                localStorage.setItem('user', JSON.stringify(apiUser));
            } catch (error) {
                console.warn("[Auth] Verification failed:", error);
                // If API fails (401), clear local storage
                localStorage.removeItem('user');
                setUser(null);
            } finally {
                setLoading(false);
            }
        };
        checkUser();
    }, []);

    const login = async (email, password) => {
        const data = await loginUser(email, password);
        setUser(data.user);
        localStorage.setItem('user', JSON.stringify(data.user));
        return data;
    };

    const logout = async () => {
        try {
            await logoutUser();
        } catch (error) {
            console.error(error);
        }
        setUser(null);
        localStorage.removeItem('user');
    };

    return (
        <AuthContext.Provider value={{ user, login, logout, loading }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => useContext(AuthContext);
