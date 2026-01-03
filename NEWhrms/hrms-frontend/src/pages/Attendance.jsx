import { useState, useEffect } from 'react'
import { useAuth } from '../context/AuthContext'
import api from '../services/api'
import { Calendar } from 'lucide-react'

export default function Attendance() {
  const { user } = useAuth()
  const [attendance, setAttendance] = useState([])
  const [summary, setSummary] = useState(null)
  const [selectedMonth, setSelectedMonth] = useState(
    new Date().toISOString().slice(0, 7)
  )
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    loadAttendance()
    loadSummary()
  }, [selectedMonth])

  const loadAttendance = async () => {
    try {
      setLoading(true)
      const response = await api.get(`/attendance?month=${selectedMonth}`)
      setAttendance(response.data.data || [])
    } catch (error) {
      console.error('Failed to load attendance:', error)
    } finally {
      setLoading(false)
    }
  }

  const loadSummary = async () => {
    try {
      const response = await api.get(`/attendance/summary?month=${selectedMonth}`)
      setSummary(response.data.data)
    } catch (error) {
      console.error('Failed to load summary:', error)
    }
  }

  return (
    <div className="space-y-6">
      <h1 className="text-3xl font-bold text-gray-900">Attendance</h1>

      {/* Month Selector */}
      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex items-center gap-4">
          <Calendar className="text-gray-400" size={20} />
          <input
            type="month"
            value={selectedMonth}
            onChange={(e) => setSelectedMonth(e.target.value)}
            className="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-600"
          />
        </div>
      </div>

      {/* Summary Cards */}
      {summary && (
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div className="bg-green-50 rounded-lg shadow p-4">
            <p className="text-gray-600 text-sm">Present Days</p>
            <p className="text-3xl font-bold text-green-600">
              {summary.present_days}
            </p>
          </div>

          <div className="bg-blue-50 rounded-lg shadow p-4">
            <p className="text-gray-600 text-sm">Leave Days</p>
            <p className="text-3xl font-bold text-blue-600">
              {summary.leave_days}
            </p>
          </div>

          <div className="bg-red-50 rounded-lg shadow p-4">
            <p className="text-gray-600 text-sm">Absent Days</p>
            <p className="text-3xl font-bold text-red-600">
              {summary.absent_days}
            </p>
          </div>

          <div className="bg-purple-50 rounded-lg shadow p-4">
            <p className="text-gray-600 text-sm">Total Days</p>
            <p className="text-3xl font-bold text-purple-600">
              {summary.total_days}
            </p>
          </div>
        </div>
      )}

      {/* Attendance Table */}
      <div className="bg-white rounded-lg shadow overflow-hidden">
        <div className="p-6 border-b border-gray-200">
          <h2 className="text-xl font-bold text-gray-900">
            Attendance Records
          </h2>
        </div>

        {loading ? (
          <div className="text-center py-8">
            <div className="inline-block animate-spin">⏳</div>
          </div>
        ) : attendance.length === 0 ? (
          <div className="text-center py-8 text-gray-600">
            No attendance records for this month
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                    Date
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                    Check In
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                    Check Out
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                    Status
                  </th>
                </tr>
              </thead>

              <tbody className="bg-white divide-y divide-gray-200">
                {attendance.map((record, index) => {
                  let statusColor = 'bg-red-100 text-red-700'
                  let statusLabel = 'Absent'

                  if (record.status === 'present') {
                    statusColor = 'bg-green-100 text-green-700'
                    statusLabel = 'Present'
                  } else if (record.status === 'leave') {
                    statusColor = 'bg-blue-100 text-blue-700'
                    statusLabel = 'On Leave'
                  }

                  return (
                    <tr key={index} className="hover:bg-gray-50">
                      <td className="px-6 py-4 text-sm text-gray-900">
                        {record.attendance_date}
                      </td>
                      <td className="px-6 py-4 text-sm text-gray-700">
                        {record.check_in_time
                          ? new Date(
                              record.check_in_time
                            ).toLocaleTimeString()
                          : '--'}
                      </td>
                      <td className="px-6 py-4 text-sm text-gray-700">
                        {record.check_out_time
                          ? new Date(
                              record.check_out_time
                            ).toLocaleTimeString()
                          : '--'}
                      </td>
                      <td className="px-6 py-4">
                        <span
                          className={`px-3 py-1 rounded-full text-xs font-semibold ${statusColor}`}
                        >
                          {statusLabel}
                        </span>
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  )
}
