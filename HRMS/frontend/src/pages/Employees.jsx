import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useAuth } from '../context/AuthContext';

const Employees = () => {
    const { user } = useAuth();
    const [employees, setEmployees] = useState([]);

    useEffect(() => {
        // Only Admin/HR should access this generally, but backend list filters or returns all
        const fetchEmployees = async () => {
            try {
                const res = await axios.get('http://localhost/hrms-backend/api/employees/list.php');
                if (res.data.records) setEmployees(res.data.records);
            } catch (err) {
                console.error(err);
            }
        };
        fetchEmployees();
    }, []);

    return (
        <div className="space-y-6">
            <h2 className="text-2xl font-bold text-gray-800">Employee Directory</h2>
            <div className="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {employees.map((emp) => (
                            <tr key={emp.id} className="hover:bg-gray-50">
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <div className="flex items-center">
                                        <div className="h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 font-bold shrink-0">
                                            {emp.name[0]}
                                        </div>
                                        <div className="ml-4">
                                            <div className="text-sm font-medium text-gray-900">{emp.name}</div>
                                            <div className="text-sm text-gray-500">{emp.email}</div>
                                        </div>
                                    </div>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{emp.role}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{emp.department}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500">{emp.employee_code}</td>
                            </tr>
                        ))}
                        {employees.length === 0 && (
                            <tr><td colSpan="4" className="px-6 py-4 text-center text-gray-500">No employees found.</td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default Employees;
