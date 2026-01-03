import { Link, useLocation } from 'react-router-dom'
import { Menu, X } from 'lucide-react'

export default function Sidebar({ open, setOpen, userRole }) {
  const location = useLocation()

  const menuItems = [
    { label: 'Dashboard', path: '/dashboard', icon: '📊' },
    { label: 'Attendance', path: '/attendance', icon: '✓' },
    { label: 'Leave', path: '/leave', icon: '✈️' },
    { label: 'Profile', path: '/profile', icon: '👤' },
    { label: 'Salary', path: '/salary', icon: '💰' },
    ...(userRole === 'Admin' || userRole === 'HR' ? [{ label: 'Employees', path: '/employees', icon: '👥' }] : [])
  ]

  return (
    <>
      {open && (
        <div 
          className="fixed inset-0 bg-black/50 md:hidden z-40"
          onClick={() => setOpen(false)}
        />
      )}

      <aside className={`fixed md:static w-64 h-screen bg-gradient-to-b from-blue-900 to-blue-800 text-white shadow-lg transition-transform z-50 md:z-0 ${open ? 'translate-x-0' : '-translate-x-full md:translate-x-0'}`}>
        <div className="p-6 border-b border-blue-700">
          <div className="flex items-center justify-between">
            <h1 className="text-xl font-bold">HRMS</h1>
            <button 
              onClick={() => setOpen(false)} 
              className="md:hidden text-white hover:bg-blue-700 p-2 rounded-lg"
            >
              <X size={20} />
            </button>
          </div>
        </div>

        <nav className="p-4 space-y-2">
          {menuItems.map((item) => (
            <Link
              key={item.path}
              to={item.path}
              onClick={() => setOpen(false)}
              className={`block px-4 py-3 rounded-lg transition-colors ${
                location.pathname === item.path
                  ? 'bg-blue-700 font-semibold'
                  : 'hover:bg-blue-700'
              }`}
            >
              <span className="mr-3">{item.icon}</span>
              {item.label}
            </Link>
          ))}
        </nav>
      </aside>
    </>
  )
}
