import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useAuth } from '../context/AuthContext';

const Attendance = () => {
    const { user } = useAuth();
    const [logs, setLogs] = useState([]);

    const fetchLogs = async () => {
        try {
            const res = await axios.get(`http://localhost/hrms-backend/api/attendance/history.php?user_id=${user.id}&role=${user.role}`);
            if (res.data.records) setLogs(res.data.records);
        } catch (err) {
            console.error(err);
        }
    };

    useEffect(() => {
        fetchLogs();
    }, [user]);

    const handleMarkAttendance = async () => {
        // Re-using the same endpoint logic for Check-In/Out
        try {
            const res = await axios.post('http://localhost/hrms-backend/api/attendance/mark.php', { user_id: user.id });
            alert(res.data.message);
            fetchLogs(); // Refresh
        } catch (err) {
            alert(err.response?.data?.message || 'Error marking attendance');
        }
    };

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <h2 className="text-2xl font-bold text-gray-800">Attendance Log</h2>
                <button
                    onClick={handleMarkAttendance}
                    className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow transition-colors"
                >
                    Mark Attendance (Today)
                </button>
            </div>

            <div className="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            {(user.role === 'Admin' || user.role === 'HR') && (
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            )}
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {logs.map((log) => (
                            <tr key={log.id} className="hover:bg-gray-50">
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{log.date}</td>
                                {(user.role === 'Admin' || user.role === 'HR') && (
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {log.first_name} {log.last_name}
                                    </td>
                                )}
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{log.check_in || '-'}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{log.check_out || '-'}</td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    ${log.status === 'Present' ? 'bg-green-100 text-green-800' :
                                            log.status === 'Absent' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'}`}>
                                        {log.status}
                                    </span>
                                </td>
                            </tr>
                        ))}
                        {logs.length === 0 && <tr><td colSpan="5" className="px-6 py-4 text-center">No existing records.</td></tr>}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default Attendance;
