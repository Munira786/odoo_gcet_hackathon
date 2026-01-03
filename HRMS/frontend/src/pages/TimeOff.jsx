import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';

const TimeOff = () => {
  const navigate = useNavigate();
  const [showProfileMenu, setShowProfileMenu] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [showNewRequestForm, setShowNewRequestForm] = useState(false);

  // Dummy time off data
  const [timeOffRequests] = useState([
    { id: 1, employee: 'Adept Dinosaur', type: 'Annual Leave', startDate: '2024-01-20', endDate: '2024-01-25', status: 'Approved', days: 5 },
    { id: 2, employee: 'Striking Turkey', type: 'Sick Leave', startDate: '2024-01-18', endDate: '2024-01-19', status: 'Pending', days: 2 },
    { id: 3, employee: 'Diligent Mouse', type: 'Personal Leave', startDate: '2024-01-22', endDate: '2024-01-23', status: 'Rejected', days: 2 },
    { id: 4, employee: 'Velvety Meerkat', type: 'Annual Leave', startDate: '2024-01-15', endDate: '2024-01-17', status: 'Approved', days: 3 },
    { id: 5, employee: 'Cultured Ibex', type: 'Maternity Leave', startDate: '2024-02-01', endDate: '2024-05-01', status: 'Approved', days: 90 },
    { id: 6, employee: 'Cultivated Wolves', type: 'Sick Leave', startDate: '2024-01-16', endDate: '2024-01-16', status: 'Approved', days: 1 },
    { id: 7, employee: 'Ambitious Seal', type: 'Annual Leave', startDate: '2024-01-24', endDate: '2024-01-26', status: 'Pending', days: 3 },
    { id: 8, employee: 'Maulik Shah', type: 'Personal Leave', startDate: '2024-01-19', endDate: '2024-01-20', status: 'Approved', days: 2 },
  ]);

  const filteredRequests = timeOffRequests.filter(request => 
    request.employee.toLowerCase().includes(searchQuery.toLowerCase()) ||
    request.type.toLowerCase().includes(searchQuery.toLowerCase()) ||
    request.status.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const getStatusBadge = (status) => {
    switch (status) {
      case 'Approved':
        return <span className="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approved</span>;
      case 'Pending':
        return <span className="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>;
      case 'Rejected':
        return <span className="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>;
      default:
        return <span className="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>;
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            {/* Logo and Navigation */}
            <div className="flex items-center space-x-8">
              <div className="flex items-center">
                <img 
                  src="/management.png" 
                  alt="Company Logo" 
                  className="h-8 w-auto mr-2"
                />
              </div>
              <nav className="flex space-x-4">
                <button 
                  onClick={() => navigate('/employees')}
                  className="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-gray-900"
                >
                  Employees
                </button>
                <button 
                  onClick={() => navigate('/attendance')}
                  className="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-gray-900"
                >
                  Attendance
                </button>
                <button className="px-3 py-2 rounded-md text-sm font-medium text-purple-600 bg-purple-50">
                  Time Off
                </button>
              </nav>
            </div>

            {/* Profile Menu */}
            <div className="relative flex items-center space-x-2">
              {/* Red Dot Indicator */}
              <div className="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center">
                <div className="w-6 h-6 bg-red-600 rounded-full"></div>
              </div>
              
              {/* Profile Avatar */}
              <div className="relative">
                <button
                  onClick={() => setShowProfileMenu(!showProfileMenu)}
                  className="flex items-center justify-center w-10 h-10 rounded-full bg-purple-600 text-white font-semibold hover:bg-purple-700"
                >
                  U
                </button>
                {showProfileMenu && (
                  <div className="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                    <button className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                      My Profile
                    </button>
                    <button className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                      Log Out
                    </button>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Action Bar */}
        <div className="flex justify-between items-center mb-8">
          <h1 className="text-2xl font-bold text-gray-900">Time Off Requests</h1>
          <div className="flex space-x-4">
            <div className="relative">
              <input
                type="text"
                placeholder="Search requests..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
              />
            </div>
            <button
              onClick={() => setShowNewRequestForm(!showNewRequestForm)}
              className="bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-purple-700"
            >
              NEW REQUEST
            </button>
          </div>
        </div>

        {/* New Request Form */}
        {showNewRequestForm && (
          <div className="bg-white shadow-sm rounded-lg p-6 mb-8 border border-gray-200">
            <h2 className="text-lg font-semibold text-gray-900 mb-4">New Time Off Request</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Leave Type</label>
                <select className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                  <option>Annual Leave</option>
                  <option>Sick Leave</option>
                  <option>Personal Leave</option>
                  <option>Maternity Leave</option>
                  <option>Paternity Leave</option>
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                <select className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                  <option>Adept Dinosaur</option>
                  <option>Striking Turkey</option>
                  <option>Diligent Mouse</option>
                  <option>Velvety Meerkat</option>
                  <option>Cultured Ibex</option>
                  <option>Cultivated Wolves</option>
                  <option>Ambitious Seal</option>
                  <option>Maulik Shah</option>
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" />
              </div>
              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                <textarea className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" rows="3" placeholder="Enter reason for leave..."></textarea>
              </div>
            </div>
            <div className="flex justify-end space-x-4 mt-6">
              <button
                onClick={() => setShowNewRequestForm(false)}
                className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                onClick={() => setShowNewRequestForm(false)}
                className="bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-purple-700"
              >
                Submit Request
              </button>
            </div>
          </div>
        )}

        {/* Time Off Table */}
        <div className="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredRequests.map((request) => (
                <tr key={request.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{request.employee}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{request.type}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{request.startDate}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{request.endDate}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{request.days}</td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    {getStatusBadge(request.status)}
                  </td>
                </tr>
              ))}
              {filteredRequests.length === 0 && (
                <tr>
                  <td colSpan="6" className="px-6 py-4 text-center text-gray-500">
                    No time off requests found.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </main>
    </div>
  );
};

export default TimeOff;
