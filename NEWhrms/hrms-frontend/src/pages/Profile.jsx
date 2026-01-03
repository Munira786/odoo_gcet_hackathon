import { useAuth } from "../context/AuthContext"

export default function Profile() {
  const { user } = useAuth()

  return (
    <div className="space-y-6">
      <h1 className="text-3xl font-bold">My Profile</h1>

      <div className="bg-white rounded-lg shadow p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <p className="text-gray-500">Name</p>
          <p className="font-semibold">{user?.name}</p>
        </div>

        <div>
          <p className="text-gray-500">Employee Code</p>
          <p className="font-semibold">{user?.employee_code}</p>
        </div>

        <div>
          <p className="text-gray-500">Role</p>
          <p className="font-semibold">{user?.role}</p>
        </div>

        <div>
          <p className="text-gray-500">Email</p>
          <p className="font-semibold">{user?.email}</p>
        </div>

        <div>
          <p className="text-gray-500">Department</p>
          <p className="font-semibold">{user?.department}</p>
        </div>

        <div>
          <p className="text-gray-500">Job Position</p>
          <p className="font-semibold">{user?.job_position}</p>
        </div>
      </div>
    </div>
  )
}
