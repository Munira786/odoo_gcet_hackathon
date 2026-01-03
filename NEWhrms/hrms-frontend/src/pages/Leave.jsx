import { useEffect, useState } from "react"
import { useAuth } from "../context/AuthContext"
import api from "../services/api"

export default function Leave() {
  const { user } = useAuth()
  const [leaves, setLeaves] = useState([])
  const [balance, setBalance] = useState([])
  const [form, setForm] = useState({
    leave_type_id: "",
    start_date: "",
    end_date: "",
    reason: ""
  })
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    fetchLeaves()
    if (user?.role === "Employee") fetchBalance()
  }, [])

  const fetchLeaves = async () => {
    const res = await api.get("/leave")
    setLeaves(res.data.data)
  }

  const fetchBalance = async () => {
    const res = await api.get("/leave/balance")
    setBalance(res.data.data)
  }

  const submitLeave = async (e) => {
    e.preventDefault()
    try {
      setLoading(true)
      await api.post("/leave/apply", form)
      alert("Leave request submitted")
      setForm({ leave_type_id: "", start_date: "", end_date: "", reason: "" })
      fetchLeaves()
      fetchBalance()
    } catch (err) {
      alert(err.response?.data?.error || "Failed")
    } finally {
      setLoading(false)
    }
  }

  const approve = async (id) => {
    await api.post(`/leave/approve/${id}`)
    fetchLeaves()
  }

  const reject = async (id) => {
    await api.post(`/leave/reject/${id}`, { rejection_reason: "Rejected" })
    fetchLeaves()
  }

  return (
    <div className="space-y-8">
      <h1 className="text-3xl font-bold">Leave Management</h1>

      {user?.role === "Employee" && (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          {balance.map((b) => (
            <div key={b.id} className="bg-blue-50 p-4 rounded-lg shadow">
              <p className="font-semibold">{b.name}</p>
              <p className="text-sm text-gray-600">
                Remaining: {b.remaining_days} / {b.days_per_year}
              </p>
            </div>
          ))}
        </div>
      )}

      {user?.role === "Employee" && (
        <form onSubmit={submitLeave} className="bg-white p-6 rounded-lg shadow space-y-4">
          <h2 className="text-xl font-semibold">Apply for Leave</h2>

          <select
            required
            className="border p-2 w-full rounded"
            value={form.leave_type_id}
            onChange={(e) => setForm({ ...form, leave_type_id: e.target.value })}
          >
            <option value="">Select Leave Type</option>
            <option value="1">Casual Leave</option>
            <option value="2">Sick Leave</option>
            <option value="3">Paid Leave</option>
          </select>

          <div className="grid grid-cols-2 gap-4">
            <input type="date" required className="border p-2 rounded"
              value={form.start_date}
              onChange={(e) => setForm({ ...form, start_date: e.target.value })}
            />
            <input type="date" required className="border p-2 rounded"
              value={form.end_date}
              onChange={(e) => setForm({ ...form, end_date: e.target.value })}
            />
          </div>

          <textarea
            required
            className="border p-2 w-full rounded"
            placeholder="Reason"
            value={form.reason}
            onChange={(e) => setForm({ ...form, reason: e.target.value })}
          />

          <button
            disabled={loading}
            className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
          >
            Apply
          </button>
        </form>
      )}

      <div className="bg-white rounded-lg shadow overflow-hidden">
        <table className="min-w-full">
          <thead className="bg-gray-100">
            <tr>
              <th className="p-3 text-left">Employee</th>
              <th className="p-3">Type</th>
              <th className="p-3">Dates</th>
              <th className="p-3">Status</th>
              {(user?.role === "HR" || user?.role === "Admin") && <th className="p-3">Action</th>}
            </tr>
          </thead>
          <tbody>
            {leaves.map((l) => (
              <tr key={l.id} className="border-t">
                <td className="p-3">{l.first_name} {l.last_name}</td>
                <td className="p-3">{l.leave_type}</td>
                <td className="p-3">{l.start_date} → {l.end_date}</td>
                <td className="p-3">{l.status}</td>
                {(user?.role === "HR" || user?.role === "Admin") && (
                  <td className="p-3 space-x-2">
                    {l.status === "pending" && (
                      <>
                        <button onClick={() => approve(l.id)} className="text-green-600">Approve</button>
                        <button onClick={() => reject(l.id)} className="text-red-600">Reject</button>
                      </>
                    )}
                  </td>
                )}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
