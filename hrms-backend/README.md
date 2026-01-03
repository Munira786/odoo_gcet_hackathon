# HRMS Backend API Documentation

PHP-based REST API for Human Resource Management System running on XAMPP.

## Base URL
```
http://localhost/hrms-backend/api
```

## Authentication
Session-based authentication. Login endpoint returns session ID as token.

---

## API Endpoints

### Authentication

#### POST `/auth/login.php`
Login user and create session.

**Request Body:**
```json
{
  "email": "admin@hrms.com",
  "password": "admin123"
}
```

**Response:**
```json
{
  "message": "Login successful.",
  "user": {
    "id": 1,
    "employee_id": 1,
    "email": "admin@hrms.com",
    "role": "Admin",
    "name": "Super Admin",
    "first_name": "Super",
    "last_name": "Admin",
    "employee_code": "ADM20230001",
    "profile_picture": null
  },
  "token": "session_id_here"
}
```

#### POST `/auth/logout.php`
Logout user and destroy session.

#### GET `/auth/me.php`
Get current authenticated user details.

#### POST `/auth/register.php`
Register new user with auto-generated employee code.

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123",
  "first_name": "John",
  "last_name": "Doe",
  "role_id": 3,
  "job_position": "Developer",
  "department": "IT",
  "phone": "1234567890"
}
```

---

### Employees

#### GET `/employees/list.php`
Get all employees.

**Response:**
```json
{
  "records": [
    {
      "id": 1,
      "employee_code": "ADM20230001",
      "name": "Super Admin",
      "first_name": "Super",
      "last_name": "Admin",
      "email": "admin@hrms.com",
      "role": "Admin",
      "department": "IT",
      "position": "System Administrator",
      "phone": null,
      "joining_date": "2023-01-01",
      "status": "Active",
      "profile_picture": null
    }
  ]
}
```

#### GET `/employees/detail.php?id={employee_id}`
Get single employee details.

#### POST `/employees/create.php`
Create new employee.

**Request Body:**
```json
{
  "first_name": "Jane",
  "last_name": "Smith",
  "email": "jane@example.com",
  "role_id": 3,
  "job_position": "Designer",
  "department": "Design"
}
```

#### PUT `/employees/update.php`
Update employee information.

**Request Body:**
```json
{
  "id": 1,
  "first_name": "John",
  "last_name": "Doe",
  "job_position": "Senior Developer",
  "department": "IT",
  "phone": "9876543210",
  "address": "123 Main St",
  "manager_id": null
}
```

#### DELETE `/employees/delete.php?id={employee_id}`
Delete employee.

---

### Attendance

#### POST `/attendance/mark.php`
Mark check-in or check-out.

**Request Body:**
```json
{
  "user_id": 1
}
```

**Response (Check-in):**
```json
{
  "message": "Checked In successfully.",
  "status": "Checked In",
  "time": "2024-01-15 09:00:00"
}
```

#### GET `/attendance/history.php?user_id={user_id}&role={role}`
Get attendance history.
- Admin/HR: See all attendance records
- Employee: See only own records

#### GET `/attendance/status.php?user_id={user_id}`
Get today's attendance status for user.

#### GET `/attendance/list.php?start_date={date}&end_date={date}`
Get attendance list for admin (with date range filter).

---

### Leave Management

#### POST `/leave/request.php`
Submit leave request.

**Request Body:**
```json
{
  "user_id": 1,
  "leave_type": "Paid",
  "start_date": "2024-01-20",
  "end_date": "2024-01-22",
  "reason": "Family vacation"
}
```

#### GET `/leave/list.php?user_id={user_id}&role={role}`
Get leave requests.
- Admin/HR: See all leave requests
- Employee: See only own requests

**Response:**
```json
{
  "records": [
    {
      "id": 1,
      "employee_id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "leave_type": "Paid",
      "start_date": "2024-01-20",
      "end_date": "2024-01-22",
      "reason": "Family vacation",
      "status": "Pending",
      "created_at": "2024-01-15 10:00:00"
    }
  ]
}
```

#### POST `/leave/update_status.php`
Approve or reject leave request (Admin/HR only).

**Request Body:**
```json
{
  "leave_id": 1,
  "status": "Approved",
  "admin_remark": "Approved for vacation"
}
```

---

### Salary

#### GET `/salary/view.php?user_id={user_id}&role={role}&employee_id={employee_id}`
View salary details.
- Employee: Can only view own salary
- Admin/HR: Can view any employee's salary

**Response:**
```json
{
  "id": 1,
  "employee_id": 1,
  "basic_salary": "50000.00",
  "hra": "10000.00",
  "allowances": "5000.00",
  "bonus": "2000.00",
  "pf": "6000.00",
  "professional_tax": "200.00",
  "net_salary": "60800.00",
  "bank_name": "ABC Bank",
  "account_number": "1234567890",
  "ifsc_code": "ABC0001234",
  "pan_number": "ABCDE1234F",
  "uan_number": "123456789012"
}
```

#### POST `/salary/update.php`
Update salary details (Admin/HR only).

**Request Body:**
```json
{
  "employee_id": 1,
  "basic_salary": 50000,
  "hra": 10000,
  "allowances": 5000,
  "bonus": 2000,
  "pf": 6000,
  "professional_tax": 200,
  "bank_name": "ABC Bank",
  "account_number": "1234567890",
  "ifsc_code": "ABC0001234",
  "pan_number": "ABCDE1234F",
  "uan_number": "123456789012"
}
```

---

### Dashboard

#### GET `/dashboard/stats.php`
Get dashboard statistics.

**Response:**
```json
{
  "total_employees": 124,
  "present_today": 112,
  "on_leave": 5,
  "absent": 7,
  "pending_leaves": 3
}
```

#### GET `/dashboard/team.php`
Get team status with today's attendance.

**Response:**
```json
{
  "records": [
    {
      "id": 1,
      "name": "John Doe",
      "employee_code": "HRM JD20240001",
      "job_position": "Developer",
      "department": "IT",
      "status": "Present",
      "check_in": "2024-01-15 09:00:00",
      "check_out": null
    }
  ]
}
```

---

## CORS Configuration

All endpoints are configured with:
- **Origin**: `http://localhost:5173` (React frontend)
- **Credentials**: Enabled (for session cookies)
- **Methods**: GET, POST, PUT, DELETE, OPTIONS
- **Headers**: Content-Type, Authorization, X-Requested-With

---

## Database Schema

### Tables
- `users` - User authentication
- `roles` - User roles (Admin, HR, Employee)
- `employees` - Employee details
- `attendance` - Daily attendance records
- `leave_requests` - Leave applications
- `salary_details` - Salary information

### Default Credentials
- **Email**: admin@hrms.com
- **Password**: admin123

---

## Setup Instructions

1. **Copy to XAMPP**
   ```
   Copy hrms-backend folder to C:\xampp\htdocs\
   ```

2. **Start Services**
   - Start Apache and MySQL in XAMPP Control Panel

3. **Import Database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create database: `hrms_db`
   - Import: `HRMS/database.sql`

4. **Test API**
   ```
   http://localhost/hrms-backend/api/auth/login.php
   ```

---

## Error Codes

- **200**: Success
- **201**: Created
- **400**: Bad Request (Missing/Invalid data)
- **401**: Unauthorized
- **404**: Not Found
- **405**: Method Not Allowed
- **409**: Conflict (Duplicate entry)
- **500**: Internal Server Error
- **503**: Service Unavailable

---

## Security Features

- ✅ Bcrypt password hashing
- ✅ Session-based authentication
- ✅ Role-based authorization
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ CORS protection
- ✅ Input validation

---

## Notes

- All dates in `Y-m-d` format (2024-01-15)
- All timestamps in `Y-m-d H:i:s` format
- Employee codes auto-generated on creation
- Net salary auto-calculated on salary update
- Approved leaves automatically create attendance records
