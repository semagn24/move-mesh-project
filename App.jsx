// Test change for git detection
import { useState } from 'react';
import { AuthProvider } from './context/AuthContext';
import Sidebar from './components/common/Sidebar';
import Navbar from './components/common/Navbar';
import AppRoutes from './routes/AppRoutes';

const AppContent = () => {
    const [isSidebarOpen, setSidebarOpen] = useState(false);

    return (
        <div className="min-h-screen bg-secondary text-white font-sans selection:bg-primary selection:text-white">
            <Sidebar isOpen={isSidebarOpen} toggleSidebar={() => setSidebarOpen(!isSidebarOpen)} />

            <div className={`transition-all duration-300 ${isSidebarOpen ? 'lg:pl-[260px]' : 'lg:pl-[260px]'}`}>
                <Navbar toggleSidebar={() => setSidebarOpen(!isSidebarOpen)} />
                <main className="px-6 pb-20 max-w-[1600px] mx-auto">
                    <AppRoutes />
                </main>
            </div>
        </div>
    );
};

const App = () => {
    return (
        <AuthProvider>
            <AppContent />
        </AuthProvider>
    );
};

export default App;
