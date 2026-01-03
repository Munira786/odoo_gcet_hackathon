import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import SalaryTab from '../components/profile/SalaryTab';

const TabButton = ({ active, label, onClick }) => (
    <button
        onClick={onClick}
        className={`px-6 py-3 font-medium text-sm focus:outline-none transition-colors ${active
                ? 'border-b-2 border-blue-600 text-blue-600 bg-blue-50'
                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
            }`}
    >
        {label}
    </button>
);

const MyProfile = () => {
    const { user } = useAuth();
    const [activeTab, setActiveTab] = useState('personal');

    return (
        <div className="max-w-4xl mx-auto">
            {/* Header */}
            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6 flex items-start gap-6">
                <div className="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center text-2xl font-bold text-gray-500">
                    {user?.name?.[0]}
                </div>
                <div>
                    <h1 className="text-2xl font-bold text-gray-800">{user?.name}</h1>
                    <p className="text-blue-600 font-medium">{user?.role}</p>
                    <div className="mt-2 text-sm text-gray-500 space-y-1">
                        <p>📧 {user?.email}</p>
                        <p>🆔 {user?.id} (User ID)</p>
                    </div>
                </div>
            </div>

            {/* Tabs */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div className="flex border-b border-gray-200">
                    <TabButton active={activeTab === 'personal'} label="Personal Info" onClick={() => setActiveTab('personal')} />
                    <TabButton active={activeTab === 'resume'} label="Resume / CV" onClick={() => setActiveTab('resume')} />
                    <TabButton active={activeTab === 'salary'} label="Salary Info" onClick={() => setActiveTab('salary')} />
                </div>

                <div className="p-6">
                    {activeTab === 'personal' && (
                        <div>
                            <h3 className="text-lg font-semibold mb-4">Personal Information</h3>
                            <p className="text-sm text-gray-600">This section would contain address, phone, emergency contacts etc.</p>
                        </div>
                    )}
                    {activeTab === 'resume' && (
                        <div>
                            <h3 className="text-lg font-semibold mb-4">Resume & Documents</h3>
                            <div className="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500">
                                No resume uploaded.
                            </div>
                        </div>
                    )}
                    {activeTab === 'salary' && <SalaryTab />}
                </div>
            </div>
        </div>
    );
};

export default MyProfile;
