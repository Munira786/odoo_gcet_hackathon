import React from 'react';
import { Outlet, Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import {
    Users,
    Calendar,
    FileText,
    DollarSign,
    LogOut,
    User,
    LayoutDashboard
} from 'lucide-react';

const SidebarItem = ({ to, icon: Icon, label }) => (
    <Link to={to} className="flex items-center space-x-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
        <Icon size={20} />
        <span>{label}</span>
    </Link>
);

const MainLayout = () => {
    const { user, logout } = useAuth();
    const navigate = useNavigate();

    const handleLogout = () => {
        logout();
        navigate('/login');
    };

    return (
        <div className="flex h-screen bg-gray-100">
            {/* Sidebar */}
            <aside className="w-64 bg-gray-900 text-white flex flex-col shadow-xl">
                <div className="h-16 flex items-center justify-center border-b border-gray-800">
                    <h1 className="text-xl font-bold tracking-wider">HRMS ERP</h1>
                </div>

                <nav className="flex-1 py-4 overflow-y-auto">
                    <SidebarItem to="/" icon={LayoutDashboard} label="Dashboard" />
                    <SidebarItem to="/profile" icon={User} label="My Profile" />

                    {(user?.role === 'Admin' || user?.role === 'HR') && (
                        <>
                            <div className="px-4 py-2 text-xs text-gray-500 uppercase mt-4">Management</div>
                            <SidebarItem to="/employees" icon={Users} label="Employees" />
                            <SidebarItem to="/attendance" icon={Calendar} label="attendance" />
                            <SidebarItem to="/leaves" icon={FileText} label="Leaves" />
                            <SidebarItem to="/salary" icon={DollarSign} label="Payroll" />
                        </>
                    )}

                    {user?.role === 'Employee' && (
                        <>
                            <div className="px-4 py-2 text-xs text-gray-500 uppercase mt-4">Self Service</div>
                            <SidebarItem to="/my-attendance" icon={Calendar} label="My Attendance" />
                            <SidebarItem to="/my-leaves" icon={FileText} label="My Leaves" />
                        </>
                    )}
                </nav>

                <div className="p-4 border-t border-gray-800">
                    <div className="flex items-center space-x-3 mb-4 px-2">
                        <div className="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-sm font-bold">
                            {user?.name?.[0] || 'U'}
                        </div>
                        <div>
                            <p className="text-sm font-medium">{user?.name}</p>
                            <p className="text-xs text-gray-400">{user?.role}</p>
                        </div>
                    </div>
                    <button
                        onClick={handleLogout}
                        className="w-full flex items-center justify-center space-x-2 bg-red-600 hover:bg-red-700 py-2 rounded text-sm transition-colors"
                    >
                        <LogOut size={16} />
                        <span>Sign Out</span>
                    </button>
                </div>
            </aside>

            {/* Main Content */}
            <main className="flex-1 flex flex-col overflow-hidden">
                {/* Header */}
                <header className="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 shadow-sm">
                    <h2 className="text-lg font-semibold text-gray-800">
                        {/* Dynamic Header Title could go here */}
                        Overview
                    </h2>

                    <div className="flex items-center space-x-4">
                        {/* Systray Check-in Placeholder */}
                        <div className="bg-green-50 text-green-700 px-3 py-1 rounded-full text-sm border border-green-200 flex items-center space-x-2 cursor-pointer hover:bg-green-100">
                            <div className="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span>Checked In: 09:00 AM</span>
                        </div>
                    </div>
                </header>

                {/* Content Area */}
                <div className="flex-1 overflow-auto p-6">
                    <Outlet />
                </div>
            </main>
        </div>
    );
};

export default MainLayout;
