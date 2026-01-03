import { useAuth } from "../context/AuthContext";
import { useNavigate } from "react-router-dom";

function Login() {
  const { login } = useAuth();
  const navigate = useNavigate();

  const handleLogin = () => {
    // simulate successful login
    login({ name: "Admin", role: "Admin" });

    // redirect to employee dashboard
    navigate("/employees");
  };

  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-gray-100">
      <div className="w-full max-w-md bg-white border-2 border-gray-800 rounded-lg p-8">

        <div className="w-full h-20 flex items-center justify-center mb-6">
          <img 
            src="/management.png" 
            alt="App Logo" 
            className="h-20 w-auto"
          />
        </div>

        <div className="mb-4">
          <label className="block text-gray-700 text-sm font-medium mb-2">
            Login Id/Email:
          </label>
          <input
            className="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="Enter your login ID or email"
          />
        </div>

        <div className="mb-6">
          <label className="block text-gray-700 text-sm font-medium mb-2">
            Password:
          </label>
          <input
            type="password"
            className="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="Enter your password"
          />
        </div>

        <button
          onClick={handleLogin}
          className="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition-colors"
        >
          SIGN IN
        </button>

      </div>
    </div>
  );
}

export default Login;
