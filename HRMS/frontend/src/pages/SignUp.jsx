import { useState } from "react";
import { useNavigate } from "react-router-dom";

function SignUp() {
  const navigate = useNavigate();
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [yearOfJoining, setYearOfJoining] = useState('');
  const [serialNumber, setSerialNumber] = useState('0001');
  const [email, setEmail] = useState('');
  const [role, setRole] = useState('Employee');

  // Generate login ID based on the format
  const generateLoginId = () => {
    if (firstName && lastName && yearOfJoining) {
      const companyInitials = 'OI'; // Odoo India
      const nameInitials = firstName.slice(0, 2).toUpperCase() + lastName.slice(0, 2).toUpperCase();
      const serial = serialNumber.padStart(4, '0');
      return `${companyInitials}${nameInitials}${yearOfJoining}${serial}`;
    }
    return '';
  };

  // Generate random password
  const generatePassword = () => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    let password = '';
    for (let i = 0; i < 12; i++) {
      password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return password;
  };

  const generatedLoginId = generateLoginId();
  const generatedPassword = firstName && lastName && yearOfJoining ? generatePassword() : '';

  const handleSignUp = () => {
    // simulate successful signup
    // redirect to login
    navigate("/login");
  };

  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-gray-100">
      <div className="w-full max-w-md bg-white border-2 border-gray-800 rounded-lg p-8">

        <div className="w-full h-20 flex items-center justify-center mb-6">
          <img 
            src="/management.png" 
            alt="App Logo" 
            className="h-16 w-auto"
          />
        </div>

        <h2 className="text-xl font-bold text-center text-gray-800 mb-4">
          HR Admin - Create Employee Account
        </h2>
        <p className="text-center text-gray-600 text-sm mb-6">
          This form is for HR/Admin personnel only to create new employee records
        </p>

        <div className="mb-4">
          <label className="block text-gray-700 text-sm font-medium mb-2">
            First Name:
          </label>
          <input
            className="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="Enter employee's first name"
            value={firstName}
            onChange={(e) => setFirstName(e.target.value)}
          />
        </div>

        <div className="mb-4">
          <label className="block text-gray-700 text-sm font-medium mb-2">
            Last Name:
          </label>
          <input
            className="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="Enter employee's last name"
            value={lastName}
            onChange={(e) => setLastName(e.target.value)}
          />
        </div>

        <div className="mb-4">
          <label className="block text-gray-700 text-sm font-medium mb-2">
            Email:
          </label>
          <input
            className="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="Enter employee's email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
          />
        </div>

        <div className="mb-4">
          <label className="block text-gray-700 text-sm font-medium mb-2">
            Year of Joining:
          </label>
          <input
            className="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-purple-500"
            placeholder="Enter year of joining (e.g., 2022)"
            value={yearOfJoining}
            onChange={(e) => setYearOfJoining(e.target.value)}
          />
        </div>

        <div className="mb-4">
          <label className="block text-gray-700 text-sm font-medium mb-2">
            Role:
          </label>
          <select
            className="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-purple-500"
            value={role}
            onChange={(e) => setRole(e.target.value)}
          >
            <option value="Employee">Employee</option>
            <option value="HR">HR</option>
            <option value="Admin">Admin</option>
          </select>
        </div>

        <div className="mb-4">
          <label className="block text-gray-700 text-sm font-medium mb-2">
            Generated Login ID:
          </label>
          <input
            className="w-full border border-gray-300 px-3 py-2 rounded bg-gray-100 cursor-not-allowed"
            value={generatedLoginId}
            readOnly
            placeholder="Fill the fields above to generate ID"
          />
        </div>

        <div className="mb-6">
          <label className="block text-gray-700 text-sm font-medium mb-2">
            Generated Password:
          </label>
          <div className="relative">
            <input
              className="w-full border border-gray-300 px-3 py-2 rounded bg-gray-100 cursor-not-allowed pr-20"
              value={generatedPassword}
              readOnly
              placeholder="Password will be auto-generated"
            />
            {generatedPassword && (
              <button
                type="button"
                onClick={() => navigator.clipboard.writeText(generatedPassword)}
                className="absolute right-2 top-1/2 transform -translate-y-1/2 bg-purple-600 text-white px-3 py-1 rounded text-xs hover:bg-purple-700"
              >
                Copy
              </button>
            )}
          </div>
          <p className="text-xs text-gray-500 mt-1">
            System-generated password (12 characters with special characters)
          </p>
        </div>

        <button
          onClick={handleSignUp}
          className="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition-colors"
        >
          CREATE EMPLOYEE ACCOUNT
        </button>

        <div className="text-center mt-6">
          <span className="text-gray-600 text-sm">
            <a href="/login" className="text-purple-600 hover:text-purple-700 font-medium">
              Back to Login
            </a>
          </span>
        </div>

      </div>
    </div>
  );
}

export default SignUp;
