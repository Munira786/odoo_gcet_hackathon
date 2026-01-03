import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useAuth } from '../../context/AuthContext';
import { Eye, EyeOff } from 'lucide-react';

const SalaryTab = ({ employeeId }) => {
    const { user } = useAuth();
    const [salary, setSalary] = useState(null);
    const [showValues, setShowValues] = useState(false);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchSalary = async () => {
            try {
                const res = await axios.get(`http://localhost/hrms-backend/api/salary/view.php?user_id=${user.id}&role=${user.role}`);
                setSalary(res.data);
            } catch (err) {
                console.error(err);
            } finally {
                setLoading(false);
            }
        };
        fetchSalary();
    }, [user]);

    if (loading) return <div>Loading Salary Info...</div>;
    if (!salary) return <div>No salary details available. Contact HR.</div>;

    const Value = ({ v }) => (showValues ? <span className="font-mono text-gray-800">${v}</span> : <span className="text-gray-400">••••••</span>);

    return (
        <div className="bg-white p-6 rounded shadow-sm border border-gray-200">
            <div className="flex justify-between items-center mb-6">
                <h3 className="text-lg font-semibold text-gray-800">Salary Structure</h3>
                <button onClick={() => setShowValues(!showValues)} className="text-blue-600 flex items-center gap-2 text-sm">
                    {showValues ? <><EyeOff size={16} /> Hide Values</> : <><Eye size={16} /> Show Values</>}
                </button>
            </div>

            <div className="grid grid-cols-2 gap-x-12 gap-y-6">
                <div className="border-b pb-2">
                    <p className="text-sm text-gray-500">Basic Salary</p>
                    <Value v={salary.basic_salary} />
                </div>
                <div className="border-b pb-2">
                    <p className="text-sm text-gray-500">HRA</p>
                    <Value v={salary.hra} />
                </div>
                <div className="border-b pb-2">
                    <p className="text-sm text-gray-500">Allowances</p>
                    <Value v={salary.allowances} />
                </div>
                <div className="border-b pb-2">
                    <p className="text-sm text-gray-500">Bonus</p>
                    <Value v={salary.bonus} />
                </div>
                <div className="border-b pb-2">
                    <p className="text-sm text-gray-500">Provident Fund (Ded)</p>
                    <Value v={salary.pf} />
                </div>
                <div className="border-b pb-2">
                    <p className="text-sm text-gray-500">Prof. Tax (Ded)</p>
                    <Value v={salary.professional_tax} />
                </div>
            </div>

            <div className="mt-8 pt-4 border-t-2 border-gray-100 flex justify-between items-center bg-gray-50 p-4 rounded">
                <span className="font-bold text-gray-700">Net Salary (In Hand)</span>
                <span className="text-xl font-bold text-green-700"><Value v={salary.net_salary} /></span>
            </div>

            <div className="mt-6 text-xs text-gray-400">
                * Confidential Information. Do not share.
            </div>
        </div>
    );
};

export default SalaryTab;
