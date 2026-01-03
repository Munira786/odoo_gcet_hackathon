import { useEffect, useState } from "react"
import { useAuth } from "../context/AuthContext"
import api from "../services/api"

export default function Salary() {
  const { user } = useAuth()
  const [salary, setSalary] = useState(null)
  const [month, setMonth] = useState(new Date().toISOString().slice(0, 7))

  useEffect(() => {
    if (user?.employee_id) fetchSalary()
  }, [month])

  const fetchSalary = async () => {
    const res = await api.get(`/salary/slip/${user.employee_id}?month=${month}`)
    setSalary(res.data.data)
  }

  if (!salary) return <p>Loading salary...</p>

  return (
    <div className="space-y-6">
      <h1 className="text-3xl font-bold">Salary Slip</h1>

      <input
        type="month"
        value={month}
        onChange={(e) => setMonth(e.target.value)}
        className="border p-2 rounded"
      />

      <div className="bg-white rounded-lg shadow p-6 space-y-4">
        <h2 className="text-xl font-semibold">{salary.employee_name}</h2>
        <p>{salary.employee_code} • {salary.job_position}</p>

        <div className="grid grid-cols-2 gap-4">
          <div>
            <h3 className="font-semibold">Earnings</h3>
            <p>Basic: ₹{salary.earnings.basic}</p>
            <p>HRA: ₹{salary.earnings.hra}</p>
            <p>Allowances: ₹{salary.earnings.allowances}</p>
            <p>Bonus: ₹{salary.earnings.bonus}</p>
          </div>

          <div>
            <h3 className="font-semibold">Deductions</h3>
            <p>PF: ₹{salary.deductions.pf}</p>
            <p>Professional Tax: ₹{salary.deductions.professional_tax}</p>
          </div>
        </div>

        <div className="text-right text-xl font-bold text-green-600">
          Net Salary: ₹{salary.net_salary}
        </div>
      </div>
    </div>
  )
}
