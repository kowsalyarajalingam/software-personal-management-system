-- ============================================================
--  EMS Portal — Full Database Schema
--  Database: personnel_management
-- ============================================================

CREATE DATABASE IF NOT EXISTS personnel_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE personnel_management;

-- ── USERS ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    fullname          VARCHAR(150) NOT NULL,
    email             VARCHAR(150) NOT NULL UNIQUE,
    password          VARCHAR(255) NOT NULL,
    role              ENUM('admin','employee') NOT NULL DEFAULT 'employee',
    employee_id       VARCHAR(50)  DEFAULT NULL,
    dept_name         VARCHAR(100) DEFAULT NULL,
    designation       VARCHAR(100) DEFAULT NULL,
    phone             VARCHAR(20)  DEFAULT NULL,
    gender            ENUM('Male','Female','Other') DEFAULT NULL,
    dob               DATE         DEFAULT NULL,
    join_date         DATE         DEFAULT NULL,
    basic_salary      DECIMAL(10,2) DEFAULT 0.00,
    created_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- ── ATTENDANCE ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS attendance (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    user_id  INT NOT NULL,
    date     DATE NOT NULL,
    status   ENUM('present','absent','late') NOT NULL DEFAULT 'present',
    UNIQUE KEY uq_user_date (user_id, date),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── LEAVE APPLICATIONS ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS leave_applications (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    leave_type  VARCHAR(100) NOT NULL,
    from_date   DATE NOT NULL,
    to_date     DATE NOT NULL,
    reason      TEXT,
    status      ENUM('pending','Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    applied_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── SALARIES ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS salaries (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    user_id              INT NOT NULL,
    salary_month         VARCHAR(7) NOT NULL COMMENT 'Format: YYYY-MM',
    basic_salary         DECIMAL(10,2) DEFAULT 0.00,
    hra                  DECIMAL(10,2) DEFAULT 0.00,
    transport_allowance  DECIMAL(10,2) DEFAULT 0.00,
    deductions           DECIMAL(10,2) DEFAULT 0.00,
    net_salary           DECIMAL(10,2) DEFAULT 0.00,
    status               ENUM('pending','paid') DEFAULT 'pending',
    processed_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_month (user_id, salary_month),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── SAMPLE ADMIN ACCOUNT ─────────────────────────────────────
-- Password: admin123
INSERT IGNORE INTO users (fullname, email, password, role, employee_id)
VALUES (
    'Admin User',
    'admin@ems.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'ADM001'
);

-- Note: The default password hash above is for 'password' (Laravel default).
-- To create a proper admin, register via signup.html and select Admin role.
-- Or run: php -r "echo password_hash('admin123', PASSWORD_DEFAULT);"
-- and update the hash above.
