import { useState, useEffect } from 'react'
import { useAuth } from '../context/AuthContext'
import api from '../services/api'
import { Search } from 'lucide-react'

export default function Dashboard() {
  const { user } = useAuth()
  const [employees, setEmployees] = useState([])
  const [search, setSearch] = useState('')
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    loadEmployees()
  }, [])

  const loadEmployees = async () => {
    try {
      setLoading(true)
      const response = await api.get('/employees')
      setEmployees(response.data.data)
    } catch (error) {
      console.error('Failed to load employees:', error)
    } finally {
      setLoading(false)
    }
  }

  const getStatusBadge = (status) => {
    const badges = {
      'present': { bg: 'bg-green-100', text: 'text-green-700', label: '🟢 Present' },
      'on_leave': { bg: 'bg-blue-100', text: 'text-blue-700', label: '✈️ On Leave' },
      'absent': { bg: 'bg-red-100', text: 'text-red-700', label: '🔴 Absent' },
      'not_checked': { bg: 'bg-yellow-100', text: 'text-yellow-700', label: '🟡 Not Checked In' }
    }
    return badges[status] || badges['absent']
  }

  const filteredEmployees = employees.filter(emp =>
    emp.first_name.toLowerCase().includes(search.toLowerCase()) ||
    emp.last_name.toLowerCase().includes(search.toLowerCase()) ||
    emp.employee_code.toLowerCase().includes(search.toLowerCase())
  )

  if (user?.role === 'Employee') {
    return (
      <div className="space-y-6">
        <h1 className="text-3xl font-bold text-gray-900">Dashboard</h1>
        
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="bg-white rounded-lg shadow p-6">
            <p className="text-gray-600 text-sm">Employee Code</p>
            <p className="text-2xl font-bold text-gray-900">{user?.employee_code}</p>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <p className="text-gray-600 text-sm">Name</p>
            <p className="text-2xl font-bold text-gray-900">{user?.name}</p>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <p className="text-gray-600 text-sm">Role</p>
            <p className="text-2xl font-bold text-gray-900">{user?.role}</p>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-xl font-bold text-gray-900 mb-4">Quick Links</h2>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="/attendance" className="p-4 bg-blue-50 rounded-lg text-center hover:bg-blue-100 transition">
              <p className="text-2xl mb-2">✓</p>
              <p className="font-semibold text-gray-900">Attendance</p>
            </a>
            <a href="/leave" className="p-4 bg-blue-50 rounded-lg text-center hover:bg-blue-100 transition">
              <p className="text-2xl mb-2">✈️</p>
              <p className="font-semibold text-gray-900">Leave</p>
            </a>
            <a href="/salary" className="p-4 bg-blue-50 rounded-lg text-center hover:bg-blue-100 transition">
              <p className="text-2xl mb-2">💰</p>
              <p className="font-semibold text-gray-900">Salary</p>
            </a>
            <a href="/profile" className="p-4 bg-blue-50 rounded-lg text-center hover:bg-blue-100 transition">
              <p className="text-2xl mb-2">👤</p>
              <p className="font-semibold text-gray-900">Profile</p>
            </a>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-gray-900 mb-2">Dashboard</h1>
        <p className="text-gray-600">Employee Overview</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="bg-white rounded-lg shadow p-4">
          <p className="text-gray-600 text-sm">Total Employees</p>
          <p className="text-3xl font-bold text-blue-600">{employees.length}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4">
          <p className="text-gray-600 text-sm">Present Today</p>
          <p className="text-3xl font-bold text-green-600">{employees.filter(e => e.status === 'present').length}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4">
          <p className="text-gray-600 text-sm">On Leave</p>
          <p className="text-3xl font-bold text-blue-600">{employees.filter(e => e.status === 'on_leave').length}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4">
          <p className="text-gray-600 text-sm">Absent</p>
          <p className="text-3xl font-bold text-red-600">{employees.filter(e => e.status === 'absent').length}</p>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex items-center mb-6">
          <Search className="text-gray-400 mr-3" size={20} />
          <input
            type="text"
            placeholder="Search by name or employee code..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="flex-1 border-0 outline-none text-gray-900"
          />
        </div>

        {loading ? (
          <div className="text-center py-8">
            <div className="inline-block animate-spin">⏳</div>
          </div>
        ) : filteredEmployees.length === 0 ? (
          <p className="text-gray-600 text-center py-8">No employees found</p>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {filteredEmployees.map((emp) => {
              const badge = getStatusBadge(emp.status)
              return (
                <div key={emp.id} className="border border-gray-200 rounded-lg p-4 hover:shadow-md transition cursor-pointer">
                  <div className="flex justify-between items-start mb-2">
                    <div>
                      <h3 className="font-bold text-gray-900">{emp.first_name} {emp.last_name}</h3>
                      <p className="text-xs text-gray-500">{emp.employee_code}</p>
                    </div>
                    <span className={`px-3 py-1 rounded-full text-xs font-semibold ${badge.bg} ${badge.text}`}>
                      {badge.label}
                    </span>
                  </div>
                  <p className="text-sm text-gray-600 mb-2">{emp.job_position}</p>
                  <p className="text-xs text-gray-500">{emp.department}</p>
                </div>
              )
            })}
          </div>
        )}
      </div>
    </div>
  )
}