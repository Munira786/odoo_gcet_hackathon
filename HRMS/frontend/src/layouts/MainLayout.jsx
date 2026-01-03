import { Outlet, Link } from "react-router-dom";

function MainLayout() {
  return (
    <div className="min-h-screen bg-gray-100">
      <header className="bg-white shadow px-6 py-4 flex gap-6">
        <Link to="/dashboard">Dashboard</Link>
        <Link to="/attendance">Attendance</Link>
        <Link to="/timeoff">Time Off</Link>
        <Link to="/profile">Profile</Link>
      </header>

      <main className="p-6">
        <Outlet />
      </main>
    </div>
  );
}

export default MainLayout;
