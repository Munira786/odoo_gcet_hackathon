# HRMS Project Folder Structure

## Root Directory
- **HRMS/**: Main project folder.
  - **database.sql**: MySQL database schema and seed data.
  - **README.md**: Setup and usage instructions.
  - **FOLDER_STRUCTURE.md**: This file.

## Backend (PHP)
- **backend/**: logic
  - **config/**
    - `database.php`: MySQL connection class.
  - **api/**
    - **auth/**
      - `login.php`: Authentication endpoint (returns User object).
    - **employees/**
      - `list.php`: Returns list of employees (Admin/HR).
      - `create.php`: Creates new employee with auto-generated ID.
    - **attendance/**
      - `mark.php`: Check-in / Check-out logic.
      - `status.php`: Check current day status.
      - `history.php`: Returns attendance logs.
    - **salary/**
      - `view.php`: View salary details (RBAC protected).
      - `update.php`: Update salary components (Admin/HR).
    - **leave/**
      - `request.php`: Submit leave request.
      - `list.php`: View leaves.
      - `update_status.php`: Approve/Reject leaves.
  - `index.php`: Entry point / Health check.

## Frontend (React)
- **frontend/**
  - **src/**
    - **components/**
      - **layouts/**
        - `MainLayout.jsx`: Sidebar, Header, Navigation.
      - **profile/**
        - `SalaryTab.jsx`: Confidential salary view component.
    - **pages/**
      - `Login.jsx`: Login page.
      - `Dashboard.jsx`: Stats and Employee grid.
      - `MyProfile.jsx`: User profile with Tabs.
    - **context/**
      - `AuthContext.jsx`: Global authentication state management.
    - `App.jsx`: Main Router and Route definitions.
    - `main.jsx`: Entry point.
  - `vite.config.js`: Vite configuration.
  - `tailwind.config.js`: Tailwind styling configuration.
