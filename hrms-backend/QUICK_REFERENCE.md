# HRMS Backend - Quick Reference

## 📁 Complete File Structure

```
hrms-backend/
├── api/
│   ├── auth/                    [4 files] 🔐 Authentication
│   │   ├── login.php           ✅ POST - User login with session
│   │   ├── logout.php          ✅ POST - Destroy session
│   │   ├── register.php        ✅ POST - Register new user
│   │   └── me.php              ✅ GET  - Get current user
│   │
│   ├── employees/               [5 files] 👥 Employee Management
│   │   ├── list.php            ✅ GET    - List all employees
│   │   ├── detail.php          ✅ GET    - Get employee by ID
│   │   ├── create.php          ✅ POST   - Create employee
│   │   ├── update.php          ✅ PUT    - Update employee
│   │   └── delete.php          ✅ DELETE - Delete employee
│   │
│   ├── attendance/              [4 files] ⏰ Attendance Tracking
│   │   ├── mark.php            ✅ POST - Check-in/Check-out
│   │   ├── history.php         ✅ GET  - Attendance history
│   │   ├── status.php          ✅ GET  - Today's status
│   │   └── list.php            ✅ GET  - Admin attendance list
│   │
│   ├── leave/                   [3 files] 🏖️ Leave Management
│   │   ├── request.php         ✅ POST - Submit leave request
│   │   ├── list.php            ✅ GET  - List leave requests
│   │   └── update_status.php   ✅ POST - Approve/Reject leave
│   │
│   ├── salary/                  [2 files] 💰 Salary Management
│   │   ├── view.php            ✅ GET  - View salary details
│   │   └── update.php          ✅ POST - Update salary
│   │
│   └── dashboard/               [2 files] 📊 Dashboard
│       ├── stats.php           ✅ GET - Dashboard statistics
│       └── team.php            ✅ GET - Team status
│
├── config/
│   └── database.php            ✅ Database connection class
│
├── index.php                   ⚠️  Basic entry point
└── README.md                   ✅ Complete API documentation
```

**Total: 20 API endpoints** across 6 modules

---

## 🚀 Quick Start

### 1. Setup
```bash
# Copy to XAMPP
Copy hrms-backend to C:\xampp\htdocs\

# Start services in XAMPP Control Panel
- Apache ✓
- MySQL ✓

# Import database
Open phpMyAdmin → Create 'hrms_db' → Import HRMS/database.sql
```

### 2. Test
```bash
# Test login
http://localhost/hrms-backend/api/auth/login.php

# Credentials
Email: admin@hrms.com
Password: admin123
```

### 3. Frontend Integration
```javascript
// Frontend already configured for:
const API_BASE = 'http://localhost/hrms-backend/api';

// Example: Login
axios.post(`${API_BASE}/auth/login.php`, {
  email: 'admin@hrms.com',
  password: 'admin123'
}, { withCredentials: true });
```

---

## 🔑 Key Features

### Security
- ✅ Bcrypt password hashing
- ✅ Session-based authentication
- ✅ Role-based authorization (Admin/HR/Employee)
- ✅ SQL injection prevention (PDO)
- ✅ CORS configured for localhost:5173

### Smart Features
- 🎯 Auto-generated employee codes
- 🎯 Auto-calculated net salary
- 🎯 Leave-attendance integration
- 🎯 Role-based data filtering

### Code Quality
- ✅ Consistent structure across all endpoints
- ✅ Proper HTTP status codes
- ✅ JSON responses
- ✅ Error handling with try-catch
- ✅ Database transactions for data integrity

---

## 📋 API Quick Reference

### Authentication
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/auth/login.php` | POST | Login user |
| `/auth/logout.php` | POST | Logout user |
| `/auth/register.php` | POST | Register new user |
| `/auth/me.php` | GET | Get current user |

### Employees
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/employees/list.php` | GET | List all employees |
| `/employees/detail.php?id=1` | GET | Get employee details |
| `/employees/create.php` | POST | Create employee |
| `/employees/update.php` | PUT | Update employee |
| `/employees/delete.php?id=1` | DELETE | Delete employee |

### Attendance
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/attendance/mark.php` | POST | Check-in/out |
| `/attendance/history.php` | GET | Attendance history |
| `/attendance/status.php` | GET | Today's status |
| `/attendance/list.php` | GET | Admin list |

### Leave
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/leave/request.php` | POST | Submit leave |
| `/leave/list.php` | GET | List leaves |
| `/leave/update_status.php` | POST | Approve/Reject |

### Salary
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/salary/view.php` | GET | View salary |
| `/salary/update.php` | POST | Update salary |

### Dashboard
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/dashboard/stats.php` | GET | Statistics |
| `/dashboard/team.php` | GET | Team status |

---

## 🎯 Role-Based Access

| Feature | Employee | HR | Admin |
|---------|----------|-----|-------|
| View own profile | ✅ | ✅ | ✅ |
| View all employees | ❌ | ✅ | ✅ |
| Create employee | ❌ | ✅ | ✅ |
| Mark attendance | ✅ | ✅ | ✅ |
| View own attendance | ✅ | ✅ | ✅ |
| View all attendance | ❌ | ✅ | ✅ |
| Request leave | ✅ | ✅ | ✅ |
| Approve/Reject leave | ❌ | ✅ | ✅ |
| View own salary | ✅ | ✅ | ✅ |
| View/Update any salary | ❌ | ✅ | ✅ |
| Dashboard access | ❌ | ✅ | ✅ |

---

## 🔧 Configuration

### CORS Settings
```php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
```

### Database Connection
```php
// config/database.php
Host: localhost
Database: hrms_db
Username: root
Password: (empty)
```

---

## 📊 Database Schema

```
users (id, email, password, role_id)
  ↓
employees (id, user_id, employee_code, first_name, last_name, ...)
  ↓
  ├── attendance (id, employee_id, date, check_in, check_out, status)
  ├── leave_requests (id, employee_id, leave_type, start_date, end_date, status)
  └── salary_details (id, employee_id, basic_salary, hra, net_salary, ...)

roles (id, name)
  ↑
  └── users.role_id
```

---

## ✅ Testing Checklist

- [ ] Login with admin credentials
- [ ] Create new employee
- [ ] View employee list
- [ ] Mark attendance (check-in)
- [ ] Mark attendance (check-out)
- [ ] Submit leave request
- [ ] Approve leave (as admin)
- [ ] View salary details
- [ ] Update salary (as admin)
- [ ] View dashboard stats

---

## 🎨 Frontend Integration Status

The existing React frontend (`HRMS/frontend/`) is already calling these endpoints:

✅ **Employees.jsx** → `api/employees/list.php`
✅ **Leaves.jsx** → `api/leave/list.php`, `api/leave/request.php`, `api/leave/update_status.php`
✅ **SalaryTab.jsx** → `api/salary/view.php`
⚠️ **Login.jsx** → Currently using mock data, ready to integrate with `api/auth/login.php`

---

## 📝 Next Steps

### Immediate
1. Update frontend `Login.jsx` to use `api/auth/login.php`
2. Update `AuthContext.jsx` to handle real authentication
3. Test complete user flow

### Enhancements
1. Add authentication middleware
2. Implement leave balance tracking
3. Add input validation utilities
4. Create error logging system
5. Add API rate limiting

---

## 📞 Support

For detailed API documentation, see:
- [README.md](file:///c:/Users/lenovo/OneDrive/Documents/odoo_gcet_hackathon/hrms-backend/README.md)
- [Walkthrough](file:///C:/Users/lenovo/.gemini/antigravity/brain/de7a68b9-817d-4ccd-9597-916803c81048/walkthrough.md)

---

**Status**: ✅ Backend Complete - Ready for Frontend Integration
