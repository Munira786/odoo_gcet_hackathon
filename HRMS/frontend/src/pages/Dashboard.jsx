import React from 'react';

const StatCard = ({ title, value, color }) => (
    <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <h3 className="text-gray-500 text-sm font-medium uppercase tracking-wider">{title}</h3>
        <p className={`text-2xl font-bold mt-2 ${color}`}>{value}</p>
    </div>
);

const EmployeeCard = ({ name, role, status }) => (
    <div className="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow cursor-pointer">
        <div className="flex items-center space-x-4">
            <div className="w-12 h-12 bg-gray-200 rounded-full flex-shrink-0"></div>
            <div>
                <h4 className="font-semibold text-gray-800">{name}</h4>
                <p className="text-sm text-gray-500">{role}</p>
            </div>
            <div className="ml-auto">
                {status === 'Present' && <span className="w-3 h-3 block bg-green-500 rounded-full" title="Present"></span>}
                {status === 'Absent' && <span className="w-3 h-3 block bg-yellow-500 rounded-full" title="Absent"></span>}
                {status === 'Leave' && <span className="text-lg" title="On Leave">✈️</span>}
            </div>
        </div>
    </div>
);

const Dashboard = () => {
    return (
        <div className="space-y-6">
            {/* Stats Row */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                <StatCard title="Total Employees" value="124" color="text-blue-600" />
                <StatCard title="Present Today" value="112" color="text-green-600" />
                <StatCard title="On Leave" value="5" color="text-orange-500" />
                <StatCard title="Absent" value="7" color="text-red-500" />
            </div>

            {/* Employee Grid */}
            <div>
                <div className="flex items-center justify-between mb-4">
                    <h3 className="text-lg font-semibold text-gray-800">Team Status</h3>
                    <input
                        type="text"
                        placeholder="Search employees..."
                        className="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none w-64"
                    />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <EmployeeCard name="John Doe" role="Senior Dev" status="Present" />
                    <EmployeeCard name="Sarah Smith" role="HR Manager" status="Leave" />
                    <EmployeeCard name="Mike Johnson" role="Product Owner" status="Present" />
                    {/* Add more cards */}
                </div>
            </div>
        </div>
    );
};

export default Dashboard;
