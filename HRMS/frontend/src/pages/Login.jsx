import { useAuth } from "../context/AuthContext";
import { useNavigate } from "react-router-dom";

function Login() {
  const { login } = useAuth();
  const navigate = useNavigate();

  const handleLogin = () => {
    // simulate successful login
    login({ name: "Admin", role: "Admin" });

    // redirect to dashboard
    navigate("/dashboard");
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-300">
      <div className="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">

        <h1 className="text-3xl font-bold text-center text-slate-800 mb-2">
          HRMS Login
        </h1>
        <p className="text-center text-slate-500 mb-6">
          Sign in to continue
        </p>

        <input
          className="w-full border p-2 mb-3 rounded"
          placeholder="Email"
        />

        <input
          type="password"
          className="w-full border p-2 mb-4 rounded"
          placeholder="Password"
        />

        <button
          onClick={handleLogin}
          className="w-full bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700"
        >
          Sign In
        </button>

      </div>
    </div>
  );
}

export default Login;
