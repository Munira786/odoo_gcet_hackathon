DROP TABLE IF EXISTS
salary_history,
salary_details,
leave_requests,
leave_types,
attendance,
employees,
users,
departments,
companies,
roles;


-- HRMS Database Schema (FIXED SEED DATA)
CREATE DATABASE IF NOT EXISTS hrms;
USE hrms;

-- ===============================
-- ROLES
-- ===============================
CREATE TABLE roles (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) UNIQUE NOT NULL,
  description VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===============================
-- COMPANIES
-- ===============================
CREATE TABLE companies (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(20) UNIQUE NOT NULL,
  email VARCHAR(100),
  phone VARCHAR(20),
  address TEXT,
  city VARCHAR(50),
  state VARCHAR(50),
  country VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===============================
-- DEPARTMENTS
-- ===============================
CREATE TABLE departments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  company_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===============================
-- USERS
-- ===============================
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role_id INT NOT NULL,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- ===============================
-- EMPLOYEES
-- ===============================
CREATE TABLE employees (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL UNIQUE,
  company_id INT NOT NULL,
  department_id INT NOT NULL,
  manager_id INT,
  employee_code VARCHAR(50) UNIQUE NOT NULL,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  date_of_birth DATE,
  gender VARCHAR(10),
  phone VARCHAR(20),
  job_position VARCHAR(100),
  join_date DATE NOT NULL,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (company_id) REFERENCES companies(id),
  FOREIGN KEY (department_id) REFERENCES departments(id),
  FOREIGN KEY (manager_id) REFERENCES employees(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ===============================
-- ATTENDANCE
-- ===============================
CREATE TABLE attendance (
  id INT PRIMARY KEY AUTO_INCREMENT,
  employee_id INT NOT NULL,
  check_in_time DATETIME,
  check_out_time DATETIME,
  attendance_date DATE NOT NULL,
  status VARCHAR(20) DEFAULT 'present',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  UNIQUE KEY unique_attendance (employee_id, attendance_date)
) ENGINE=InnoDB;

-- ===============================
-- LEAVE TYPES
-- ===============================
CREATE TABLE leave_types (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50),
  days_per_year INT
) ENGINE=InnoDB;

-- ===============================
-- LEAVE REQUESTS
-- ===============================
CREATE TABLE leave_requests (
  id INT PRIMARY KEY AUTO_INCREMENT,
  employee_id INT NOT NULL,
  leave_type_id INT NOT NULL,
  start_date DATE,
  end_date DATE,
  reason TEXT,
  status VARCHAR(20) DEFAULT 'pending',
  approved_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  FOREIGN KEY (leave_type_id) REFERENCES leave_types(id),
  FOREIGN KEY (approved_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ===============================
-- SALARY DETAILS
-- ===============================
CREATE TABLE salary_details (
  id INT PRIMARY KEY AUTO_INCREMENT,
  employee_id INT NOT NULL UNIQUE,
  basic_salary DECIMAL(12,2),
  hra DECIMAL(12,2),
  allowances DECIMAL(12,2),
  bonus DECIMAL(12,2),
  pf DECIMAL(12,2),
  professional_tax DECIMAL(12,2),
  bank_name VARCHAR(100),
  account_number VARCHAR(50),
  ifsc_code VARCHAR(20),
  pan VARCHAR(20),
  uan VARCHAR(20),
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===============================
-- SEED DATA
-- ===============================

-- ROLES
INSERT INTO roles (name) VALUES ('Admin'), ('HR'), ('Employee');

-- COMPANY
INSERT INTO companies (name, code, city, state, country)
VALUES ('Innovate Tech Solutions', 'OIJODO', 'Bangalore', 'Karnataka', 'India');

-- DEPARTMENTS
INSERT INTO departments (company_id, name) VALUES
(1, 'Engineering'),
(1, 'Human Resources');

-- USERS
INSERT INTO users (email, password, role_id) VALUES
('admin@hrms.com', 'Admin@123', 1),
('hr@hrms.com', 'HR@123', 2),
('john.doe@innovatetech.com', 'Emp@123', 3),
('sarah.smith@innovatetech.com', 'Emp@123', 3);

-- EMPLOYEES (IMPORTANT ORDER)
-- John FIRST (Manager)
INSERT INTO employees
(user_id, company_id, department_id, manager_id, employee_code, first_name, last_name, job_position, join_date)
VALUES
(3, 1, 1, NULL, 'OIJODO20210001', 'John', 'Doe', 'Senior Software Engineer', '2021-01-15');

-- Sarah SECOND (Manager = John.id = 1)
INSERT INTO employees
(user_id, company_id, department_id, manager_id, employee_code, first_name, last_name, job_position, join_date)
VALUES
(4, 1, 1, 1, 'OIJODO20220002', 'Sarah', 'Smith', 'Junior Software Engineer', '2022-03-20');

-- SALARY
INSERT INTO salary_details VALUES
(NULL, 1, 80000, 16000, 8000, 5000, 6400, 800, 'SBI', '1234567890', 'SBIN0001', 'ABCDE1234F', 'UAN123'),
(NULL, 2, 50000, 10000, 5000, 2500, 4000, 500, 'HDFC', '9876543210', 'HDFC0001', 'BCDEF2345G', 'UAN234');

-- ATTENDANCE
INSERT INTO attendance (employee_id, check_in_time, check_out_time, attendance_date)
VALUES
(1, '2025-01-03 09:00:00', '2025-01-03 17:30:00', '2025-01-03'),
(2, '2025-01-03 09:15:00', '2025-01-03 18:00:00', '2025-01-03');

-- LEAVE TYPES
INSERT INTO leave_types VALUES
(1, 'Paid Leave', 20),
(2, 'Sick Leave', 10);

-- LEAVE REQUEST
INSERT INTO leave_requests
(employee_id, leave_type_id, start_date, end_date, reason, status, approved_by)
VALUES
(1, 1, '2025-01-15', '2025-01-17', 'Personal', 'approved', 2);
