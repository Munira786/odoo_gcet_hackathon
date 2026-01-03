import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';

const Attendance = () => {
  const navigate = useNavigate();
  const [showProfileMenu, setShowProfileMenu] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');

  // Dummy attendance data
  const [attendanceLogs] = useState([
    { id: 1, employee: 'Adept Dinosaur', date: '2024-01-15', checkIn: '09:00 AM', checkOut: '06:00 PM', status: 'Present' },
    { id: 2, employee: 'Striking Turkey', date: '2024-01-15', checkIn: '08:45 AM', checkOut: '05:30 PM', status: 'Present' },
    { id: 3, employee: 'Diligent Mouse', date: '2024-01-15', checkIn: '-', checkOut: '-', status: 'Absent' },
    { id: 4, employee: 'Velvety Meerkat', date: '2024-01-15', checkIn: '09:15 AM', checkOut: '-', status: 'Present' },
    { id: 5, employee: 'Cultured Ibex', date: '2024-01-14', checkIn: '08:30 AM', checkOut: '06:15 PM', status: 'Present' },
    { id: 6, employee: 'Cultivated Wolves', date: '2024-01-14', checkIn: '-', checkOut: '-', status: 'On Leave' },
    { id: 7, employee: 'Ambitious Seal', date: '2024-01-14', checkIn: '09:00 AM', checkOut: '05:45 PM', status: 'Present' },
    { id: 8, employee: 'Maulik Shah', date: '2024-01-14', checkIn: '08:50 AM', checkOut: '06:00 PM', status: 'Present' },
  ]);

  const filteredLogs = attendanceLogs.filter(log => 
    log.employee.toLowerCase().includes(searchQuery.toLowerCase()) ||
    log.date.includes(searchQuery)
  );

  const getStatusBadge = (status) => {
    switch (status) {
      case 'Present':
        return <span className="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Present</span>;
      case 'Absent':
        return <span className="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Absent</span>;
      case 'On Leave':
        return <span className="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">On Leave</span>;
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
                <button className="px-3 py-2 rounded-md text-sm font-medium text-purple-600 bg-purple-50">
                  Attendance
                </button>
                <button 
                  onClick={() => navigate('/timeoff')}
                  className="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-gray-900"
                >
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
          <h1 className="text-2xl font-bold text-gray-900">Attendance Log</h1>
          <div className="relative">
            <input
              type="text"
              placeholder="Search by employee or date..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
            />
          </div>
        </div>

        {/* Attendance Table */}
        <div className="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredLogs.map((log) => (
                <tr key={log.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{log.date}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{log.employee}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{log.checkIn}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{log.checkOut}</td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    {getStatusBadge(log.status)}
                  </td>
                </tr>
              ))}
              {filteredLogs.length === 0 && (
                <tr>
                  <td colSpan="5" className="px-6 py-4 text-center text-gray-500">
                    No attendance records found.
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

export default Attendance;
