import { LogOut } from 'lucide-react'
import { useState } from 'react'
import api from '../services/api'

export default function Header({ onLogout, toggleSidebar, user }) {
  const [checkInTime, setCheckInTime] = useState(null)
  const [loading, setLoading] = useState(false)

  const handleCheckIn = async () => {
    try {
      setLoading(true)
      const response = await api.post('/attendance/check-in', {})
      setCheckInTime(response.data.check_in_time)
      alert('Checked in successfully!')
    } catch (error) {
      alert(error.response?.data?.error || 'Check-in failed')
    } finally {
      setLoading(false)
    }
  }

  const handleCheckOut = async () => {
    try {
      setLoading(true)
      const response = await api.post('/attendance/check-out', {})
      setCheckInTime(null)
      alert('Checked out successfully!')
    } catch (error) {
      alert(error.response?.data?.error || 'Check-out failed')
    } finally {
      setLoading(false)
    }
  }

  return (
    <header className="bg-white border-b border-gray-200 shadow-sm">
      <div className="flex items-center justify-between px-6 py-4">
        <button 
          onClick={toggleSidebar} 
          className="md:hidden text-gray-600 hover:text-gray-900"
        >
          <Menu size={24} />
        </button>

        <div className="flex-1" />

        {user?.role === 'Employee' && (
          <div className="flex items-center gap-4 mr-6">
            <button
              onClick={handleCheckIn}
              disabled={loading || checkInTime}
              className={`px-4 py-2 rounded-lg font-medium transition-colors ${
                checkInTime
                  ? 'bg-green-100 text-green-700 cursor-not-allowed'
                  : 'bg-blue-600 text-white hover:bg-blue-700'
              }`}
            >
              ✓ Check In
            </button>
            {checkInTime && (
              <button
                onClick={handleCheckOut}
                disabled={loading}
                className="px-4 py-2 bg-orange-600 text-white rounded-lg font-medium hover:bg-orange-700"
              >
                ✗ Check Out
              </button>
            )}
          </div>
        )}

        <div className="flex items-center gap-4">
          <div className="text-right">
            <p className="text-sm font-medium text-gray-900">{user?.name}</p>
            <p className="text-xs text-gray-500">{user?.role}</p>
          </div>
          <button
            onClick={onLogout}
            className="text-gray-600 hover:text-red-600 transition-colors p-2"
            title="Logout"
          >
            <LogOut size={20} />
          </button>
        </div>
      </div>
    </header>
  )
}
