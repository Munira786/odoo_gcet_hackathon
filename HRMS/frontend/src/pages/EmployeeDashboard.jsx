import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';

const EmployeeDashboard = () => {
  const navigate = useNavigate();
  const [showProfileMenu, setShowProfileMenu] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');

  // Dummy employee data
  const [employees, setEmployees] = useState([
    { id: 1, name: 'Adept Dinosaur', status: 'present', checkInTime: '09:00 AM' },
    { id: 2, name: 'Striking Turkey', status: 'on_leave' },
    { id: 3, name: 'Diligent Mouse', status: 'remote' },
    { id: 4, name: 'Velvety Meerkat', status: 'present', checkInTime: '09:30 AM' },
    { id: 5, name: 'Cultured Ibex', status: 'not_checked_in' },
    { id: 6, name: 'Cultivated Wolves', status: 'remote' },
    { id: 7, name: 'Ambitious Seal', status: 'on_leave' },
    { id: 8, name: 'Maulik Shah', status: 'present', checkInTime: '10:00 AM' },
  ]);

  const getStatusIndicator = (status) => {
    switch (status) {
      case 'present':
        return <div className="w-3 h-3 bg-green-500 rounded-full absolute top-2 right-2"></div>; // Green dot for present
      case 'on_leave':
        return <div className="absolute top-1 right-2 text-lg">✈️</div>; // Airplane emoji for on leave
      case 'remote':
      case 'not_checked_in':
        return <div className="w-3 h-3 bg-red-500 rounded-full absolute top-2 right-2"></div>; // Red dot for not present (remote or not checked in)
      default:
        return null;
    }
  };

  const handleCheckIn = (employeeId) => {
    setEmployees(employees.map(emp => 
      emp.id === employeeId 
        ? { ...emp, status: 'present', checkInTime: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) }
        : emp
    ));
  };

  const handleCheckOut = (employeeId) => {
    setEmployees(employees.map(emp => 
      emp.id === employeeId 
        ? { ...emp, status: 'not_checked_in', checkInTime: null }
        : emp
    ));
  };

  const filteredEmployees = employees.filter(emp => 
    emp.name.toLowerCase().includes(searchQuery.toLowerCase())
  );

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
                <button className="px-3 py-2 rounded-md text-sm font-medium text-purple-600 bg-purple-50">
                  Employees
                </button>
                <button 
                  onClick={() => navigate('/attendance')}
                  className="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-gray-900"
                >
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
          <button className="bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-purple-700">
            NEW
          </button>
          <div className="relative">
            <input
              type="text"
              placeholder="Search employees..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
            />
          </div>
        </div>

        {/* Employee Cards Grid */}
        <div className="overflow-y-auto">
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            {filteredEmployees.map((employee) => (
              <div key={employee.id} className="bg-white border border-gray-300 rounded-lg shadow-sm p-4 relative">
                {/* Status Indicator */}
                {getStatusIndicator(employee.status)}
                
                {/* Profile Picture */}
                <div className="flex justify-center mb-2">
                  <div className="w-16 h-16 rounded-full bg-blue-200 flex items-center justify-center text-blue-800 font-semibold text-lg overflow-hidden">
                    {/* Placeholder for profile picture as per image */}
                    <svg className="w-full h-full text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                  </div>
                </div>

                {/* Employee Name */}
                <h3 className="text-center font-medium text-gray-900 mb-4">
                  {employee.name}
                </h3>

                {/* Check In/Out Button */}
                {employee.status === 'present' ? (
                  <div className="text-center">
                    <button
                      onClick={() => handleCheckOut(employee.id)}
                      className="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 text-sm"
                    >
                      Check Out →
                    </button>
                    <p className="text-xs text-gray-500 mt-2">
                      Since {employee.checkInTime}
                    </p>
                  </div>
                ) : (
                  <div className="text-center">
                    <button
                      onClick={() => handleCheckIn(employee.id)}
                      className="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-sm"
                    >
                      Check IN →
                    </button>
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      </main>
    </div>
  );
};

export default EmployeeDashboard;
