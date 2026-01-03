import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useAuth } from '../context/AuthContext';

const Leaves = () => {
    const { user } = useAuth();
    const [leaves, setLeaves] = useState([]);
    const [showModal, setShowModal] = useState(false);

    // Form State
    const [leaveType, setLeaveType] = useState('Paid');
    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');
    const [reason, setReason] = useState('');

    const fetchLeaves = async () => {
        try {
            const res = await axios.get(`http://localhost/hrms-backend/api/leave/list.php?user_id=${user.id}&role=${user.role}`);
            if (res.data.records) setLeaves(res.data.records);
        } catch (err) {
            console.error(err);
        }
    };

    useEffect(() => {
        fetchLeaves();
    }, [user]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            await axios.post('http://localhost/hrms-backend/api/leave/request.php', {
                user_id: user.id,
                leave_type: leaveType,
                start_date: startDate,
                end_date: endDate,
                reason
            });
            setShowModal(false);
            fetchLeaves();
            alert("Leave request submitted!");
        } catch (err) {
            alert("Error submitting request.");
        }
    };

    const handleAction = async (id, status) => {
        try {
            await axios.post('http://localhost/hrms-backend/api/leave/update_status.php', {
                leave_id: id,
                status
            });
            fetchLeaves();
        } catch (err) {
            alert("Error updating status.");
        }
    };

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <h2 className="text-2xl font-bold text-gray-800">Leave Management</h2>
                <button
                    onClick={() => setShowModal(true)}
                    className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition-colors"
                >
                    + Apply for Leave
                </button>
            </div>

            {/* List */}
            <div className="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            {(user.role === 'Admin' || user.role === 'HR') && <th className="px-6 py-3 text-right">Actions</th>}
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {leaves.map((leave) => (
                            <tr key={leave.id}>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {leave.first_name ? `${leave.first_name} ${leave.last_name}` : 'Me'}
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{leave.leave_type}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {leave.start_date} to {leave.end_date}
                                </td>
                                <td className="px-6 py-4 text-sm text-gray-500 truncate max-w-xs">{leave.reason}</td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    ${leave.status === 'Approved' ? 'bg-green-100 text-green-800' :
                                            leave.status === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'}`}>
                                        {leave.status}
                                    </span>
                                </td>
                                {(user.role === 'Admin' || user.role === 'HR') && leave.status === 'Pending' && (
                                    <td className="px-6 py-4 text-right text-sm font-medium space-x-2">
                                        <button onClick={() => handleAction(leave.id, 'Approved')} className="text-green-600 hover:text-green-900">Approve</button>
                                        <button onClick={() => handleAction(leave.id, 'Rejected')} className="text-red-600 hover:text-red-900">Reject</button>
                                    </td>
                                )}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Modal */}
            {showModal && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
                    <div className="bg-white rounded-lg max-w-md w-full p-6">
                        <h3 className="text-lg font-bold mb-4">Apply for Leave</h3>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Leave Type</label>
                                <select value={leaveType} onChange={e => setLeaveType(e.target.value)} className="w-full border rounded p-2 mt-1">
                                    <option>Paid</option>
                                    <option>Sick</option>
                                    <option>Unpaid</option>
                                </select>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Start Date</label>
                                    <input type="date" required value={startDate} onChange={e => setStartDate(e.target.value)} className="w-full border rounded p-2 mt-1" />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">End Date</label>
                                    <input type="date" required value={endDate} onChange={e => setEndDate(e.target.value)} className="w-full border rounded p-2 mt-1" />
                                </div>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Reason</label>
                                <textarea required value={reason} onChange={e => setReason(e.target.value)} className="w-full border rounded p-2 mt-1" rows="3"></textarea>
                            </div>
                            <div className="flex justify-end gap-2 mt-4">
                                <button type="button" onClick={() => setShowModal(false)} className="px-4 py-2 text-gray-600">Cancel</button>
                                <button type="submit" className="px-4 py-2 bg-blue-600 text-white rounded">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
};

export default Leaves;
