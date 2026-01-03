// src/pages/Employees.jsx
import { useState, useEffect } from 'react'
import api from '../services/api'
import { Search, Plus } from 'lucide-react'

export default function Employees() {
  const [employees, setEmployees] = useState([])
  const [search, setSearch] = useState('')
  const [showForm, setShowForm] = useState(false)
  const [loading, setLoading] = useState(false)
  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    phone: '',
    job_position: '',
    company_id: 1,
    department_id: 1,
    basic_salary: ''
  })

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

  const handleSubmit = async (e) => {
    e.preventDefault()
    try {
      setLoading(true)
      await api.post('/employees', formData)
      alert('Employee created successfully!')
      setFormData({
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        phone: '',
        job_position: '',
        company_id: 1,
        department_id: 1,
        basic_salary: ''
      })
      setShowForm(false)
      loadEmployees()
    } catch (error) {
      alert(error.response?.data?.error || 'Failed to create employee')
    } finally {
      setLoading(false)
    }
  }

  const filteredEmployees = employees.filter(emp =>
    emp.first_name.toLowerCase().includes(search.toLowerCase()) ||
    emp.last_name.toLowerCase().includes(search.toLowerCase()) ||
    emp.employee_code.toLowerCase().includes(search.toLowerCase())
  )

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-3xl font-bold text-gray-900">Employees</h1>
        {!showForm && (
          <button
            onClick={() => setShowForm(true)}
            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
          >
            <Plus size={18} /> Add Employee
          </button>
        )}
      </div>

      {/* Add Form */}
      {showForm && (
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-xl font-bold text-gray-900 mb-4">Add New Employee</h2>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                <input
                  type="text"
                  value={formData.first_name}
                  onChange={(e) => setFormData({ ...formData, first_name: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-600"
                  required
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                <input
                  type="text"
                  value={formData.last_name}
                  onChange={(e) => setFormData({ ...formData, last_name: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-600"
                  required
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input
                  type="email"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-600"
                  required
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input
                  type="password"
                  value={formData.password}
                  onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-600"
                  required
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input
                  type="tel"
                  value={formData.phone}
                  onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-600"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Job Position</label>
                <input
                  type="text"
                  value={formData.job_position}
                  onChange={(e) => setFormData({ ...formData, job_position: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-600"
                  required
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Department</label>
                <select
                  value={formData.department_id}
                  onChange={(e) => setFormData({ ...formData, department_id: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-600"
                >
                  <option value="1">Engineering</option>
                  <option value="2">HR</option>
                  <option value="3">Sales</option>
                  <option value="4">Finance</option>
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Basic Salary</label>
                <input
                  type="number"
                  value={formData.basic_salary}
                  onChange={(e) => setFormData({ ...formData, basic_salary: e.target.value })}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-600"
                  required
                />
              </div>
            </div>
            <div className="flex gap-3">
              <button
                type="submit"
                disabled={loading}
                className="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                {loading ? 'Creating...' : 'Create Employee'}
              </button>
              <button
                type="button"
                onClick={() => setShowForm(false)}
                className="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      )}

      {/* Search */}
      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex items-center">
          <Search className="text-gray-400 mr-3" size={20} />
          <input
            type="text"
            placeholder="Search by name or employee code..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="flex-1 border-0 outline-none text-gray-900"
          />
        </div>
      </div>

      {/* Employee List */}
      <div className="bg-white rounded-lg shadow overflow-hidden">
        {loading ? (
          <div className="text-center py-8">Loading...</div>
        ) : filteredEmployees.length === 0 ? (
          <div className="text-center py-8 text-gray-600">No employees found</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50 border-b">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-semibold text-gray-700">Code</th>
                  <th className="px-6 py-3 text-left text-xs font-semibold text-gray-700">Name</th>
                  <th className="px-6 py-3 text-left text-xs font-semibold text-gray-700">Position</th>
                  <th className="px-6 py-3 text-left text-xs font-semibold text-gray-700">Department</th>
                  <th className="px-6 py-3 text-left text-xs font-semibold text-gray-700">Email</th>
                  <th className="px-6 py-3 text-left text-xs font-semibold text-gray-700">Phone</th>
                </tr>
              </thead>
              <tbody className="divide-y">
                {filteredEmployees.map((emp) => (
                  <tr key={emp.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 text-sm font-medium text-gray-900">{emp.employee_code}</td>
                    <td className="px-6 py-4 text-sm text-gray-900">{emp.first_name} {emp.last_name}</td>
                    <td className="px-6 py-4 text-sm text-gray-600">{emp.job_position}</td>
                    <td className="px-6 py-4 text-sm text-gray-600">{emp.department}</td>
                    <td className="px-6 py-4 text-sm text-gray-600">{emp.email}</td>
                    <td className="px-6 py-4 text-sm text-gray-600">{emp.phone || '-'}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  )
}