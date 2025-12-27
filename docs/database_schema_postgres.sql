-- ========================================================================================================
-- PostgreSQL Database Schema Migration & Enhancement
-- Project: Interior Design & Task Management System
-- Generated: 2025-12-27
-- Compatible with: PostgreSQL 12+
-- 
-- Import command: psql -U username -d database_name -f database_schema_postgres.sql
-- ========================================================================================================

-- Enable required extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- ========================================================================================================
-- DROP EXISTING TABLES (CASCADE)
-- ========================================================================================================

DROP TABLE IF EXISTS user_sessions CASCADE;
DROP TABLE IF EXISTS user_tokens CASCADE;
DROP TABLE IF EXISTS bills CASCADE;
DROP TABLE IF EXISTS quotes CASCADE;
DROP TABLE IF EXISTS task_attachments CASCADE;
DROP TABLE IF EXISTS task_users CASCADE;
DROP TABLE IF EXISTS task_timelines CASCADE;
DROP TABLE IF EXISTS task_messages CASCADE;
DROP TABLE IF EXISTS task_services CASCADE;
DROP TABLE IF EXISTS measurements CASCADE;
DROP TABLE IF EXISTS tasks CASCADE;
DROP TABLE IF EXISTS service_master CASCADE;
DROP TABLE IF EXISTS designers CASCADE;
DROP TABLE IF EXISTS clients CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS config CASCADE;
DROP TABLE IF EXISTS job_queue CASCADE;

-- Drop existing ENUM types if they exist
DROP TYPE IF EXISTS user_role_enum CASCADE;
DROP TYPE IF EXISTS task_status_enum CASCADE;
DROP TYPE IF EXISTS task_priority_enum CASCADE;
DROP TYPE IF EXISTS bill_status_enum CASCADE;

-- ========================================================================================================
-- CREATE ENUM TYPES
-- ========================================================================================================

-- User role enumeration
CREATE TYPE user_role_enum AS ENUM ('admin', 'salesperson', 'agent');

-- Task status enumeration
CREATE TYPE task_status_enum AS ENUM (
    'Created',
    'Measurement: Done',
    'Quote: Done',
    'Approved',
    'In Progress',
    'Completed',
    'Cancelled'
);

-- Task priority enumeration
CREATE TYPE task_priority_enum AS ENUM ('Low', 'Medium', 'High', 'Urgent');

-- Bill status enumeration
CREATE TYPE bill_status_enum AS ENUM ('Pending', 'Paid', 'Partial', 'Overdue');

-- ========================================================================================================
-- CREATE TABLES
-- ========================================================================================================

-- Table: users
-- Description: User accounts with authentication support
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    contact_no VARCHAR(50),
    address TEXT,
    role user_role_enum NOT NULL DEFAULT 'agent',
    profile_bg_color VARCHAR(20) DEFAULT '#FF5733',
    is_active BOOLEAN DEFAULT TRUE NOT NULL,
    last_login_at TIMESTAMP WITH TIME ZONE,
    email_verified BOOLEAN DEFAULT FALSE NOT NULL,
    phone_verified BOOLEAN DEFAULT FALSE NOT NULL
);

COMMENT ON TABLE users IS 'User accounts with role-based access (admin, salesperson, agent)';
COMMENT ON COLUMN users.role IS 'User role: admin, salesperson, or agent';
COMMENT ON COLUMN users.is_active IS 'Whether the user account is active';
COMMENT ON COLUMN users.email_verified IS 'Whether the email has been verified';
COMMENT ON COLUMN users.phone_verified IS 'Whether the phone number has been verified';

-- Table: user_tokens
-- Description: JWT token management for authentication
CREATE TABLE user_tokens (
    token_id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    access_token TEXT NOT NULL,
    refresh_token TEXT NOT NULL UNIQUE,
    access_token_expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    refresh_token_expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    is_revoked BOOLEAN DEFAULT FALSE NOT NULL,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

COMMENT ON TABLE user_tokens IS 'JWT access and refresh tokens for authentication';
COMMENT ON COLUMN user_tokens.access_token IS 'JWT access token';
COMMENT ON COLUMN user_tokens.refresh_token IS 'JWT refresh token (unique)';
COMMENT ON COLUMN user_tokens.is_revoked IS 'Whether the token has been revoked';

-- Table: user_sessions
-- Description: User login session tracking
CREATE TABLE user_sessions (
    session_id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    token_id UUID REFERENCES user_tokens(token_id) ON DELETE SET NULL,
    login_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    logout_at TIMESTAMP WITH TIME ZONE,
    ip_address INET,
    user_agent TEXT,
    device_info JSONB,
    is_active BOOLEAN DEFAULT TRUE NOT NULL
);

COMMENT ON TABLE user_sessions IS 'User login session tracking';
COMMENT ON COLUMN user_sessions.is_active IS 'Whether the session is currently active';
COMMENT ON COLUMN user_sessions.device_info IS 'JSON metadata about the device';

-- Table: clients
-- Description: Client information
CREATE TABLE clients (
    client_id SERIAL PRIMARY KEY,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    name VARCHAR(255) NOT NULL,
    contact_no VARCHAR(50),
    email VARCHAR(255),
    address TEXT
);

COMMENT ON TABLE clients IS 'Client/customer information';

-- Table: designers
-- Description: Interior designer details
CREATE TABLE designers (
    designer_id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    firm_name VARCHAR(255),
    contact_no VARCHAR(50),
    address TEXT,
    profile_bg_color VARCHAR(20) DEFAULT '#FF5733',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

COMMENT ON TABLE designers IS 'Interior designer information';

-- Table: service_master
-- Description: Service catalog/master list
CREATE TABLE service_master (
    service_master_id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    default_unit_price NUMERIC(10, 2) NOT NULL DEFAULT 0.00,
    unit VARCHAR(50) DEFAULT 'unit',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

COMMENT ON TABLE service_master IS 'Master catalog of available services';

-- Table: tasks
-- Description: Project/task management
CREATE TABLE tasks (
    task_id SERIAL PRIMARY KEY,
    deal_no VARCHAR(100) UNIQUE,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    start_date DATE,
    due_date DATE,
    priority task_priority_enum DEFAULT 'Medium',
    remarks TEXT,
    status task_status_enum DEFAULT 'Created',
    created_by INTEGER REFERENCES users(user_id) ON DELETE SET NULL,
    client_id INTEGER REFERENCES clients(client_id) ON DELETE SET NULL,
    designer_id INTEGER REFERENCES designers(designer_id) ON DELETE SET NULL,
    agency_id INTEGER REFERENCES users(user_id) ON DELETE SET NULL,
    CONSTRAINT chk_task_dates CHECK (start_date IS NULL OR due_date IS NULL OR start_date <= due_date)
);

COMMENT ON TABLE tasks IS 'Project and task management';
COMMENT ON COLUMN tasks.deal_no IS 'Unique deal number (e.g., 0000-0001)';
COMMENT ON COLUMN tasks.agency_id IS 'References user with role=agent';

-- Table: task_users
-- Description: Many-to-many relationship for task assignments
CREATE TABLE task_users (
    task_id INTEGER NOT NULL REFERENCES tasks(task_id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    role_in_task VARCHAR(50),
    PRIMARY KEY (task_id, user_id)
);

COMMENT ON TABLE task_users IS 'Task assignments (many-to-many relationship)';
COMMENT ON COLUMN task_users.role_in_task IS 'User role for this specific task (e.g., Designer, Project Manager)';

-- Table: measurements
-- Description: Task measurements and calculations
CREATE TABLE measurements (
    measurement_id SERIAL PRIMARY KEY,
    task_id INTEGER NOT NULL REFERENCES tasks(task_id) ON DELETE CASCADE,
    location VARCHAR(255),
    width NUMERIC(10, 2),
    height NUMERIC(10, 2),
    area NUMERIC(10, 2) NOT NULL DEFAULT 0.00,
    unit VARCHAR(10) NOT NULL DEFAULT 'm',
    quantity INTEGER NOT NULL DEFAULT 1,
    unit_price NUMERIC(10, 2) NOT NULL DEFAULT 0.00,
    discount NUMERIC(10, 2) NOT NULL DEFAULT 0.00,
    total_price NUMERIC(10, 2) NOT NULL DEFAULT 0.00,
    notes TEXT,
    CONSTRAINT chk_measurement_positive CHECK (
        quantity > 0 AND 
        unit_price >= 0 AND 
        discount >= 0 AND 
        total_price >= 0
    )
);

COMMENT ON TABLE measurements IS 'Measurements for tasks with area calculations';
COMMENT ON COLUMN measurements.area IS 'Calculated as width * height';
COMMENT ON COLUMN measurements.total_price IS 'Calculated as (area * unit_price * quantity) - discount';

-- Table: task_services
-- Description: Services linked to tasks
CREATE TABLE task_services (
    task_service_id SERIAL PRIMARY KEY,
    task_id INTEGER NOT NULL REFERENCES tasks(task_id) ON DELETE CASCADE,
    service_master_id INTEGER NOT NULL REFERENCES service_master(service_master_id) ON DELETE RESTRICT,
    quantity INTEGER NOT NULL,
    unit_price NUMERIC(10, 2) NOT NULL,
    total_amount NUMERIC(10, 2) NOT NULL,
    CONSTRAINT chk_task_service_positive CHECK (
        quantity > 0 AND 
        unit_price >= 0 AND 
        total_amount >= 0
    )
);

COMMENT ON TABLE task_services IS 'Services associated with tasks';
COMMENT ON COLUMN task_services.total_amount IS 'Calculated as quantity * unit_price';

-- Table: task_messages
-- Description: Task communication/messages
CREATE TABLE task_messages (
    message_id SERIAL PRIMARY KEY,
    task_id INTEGER NOT NULL REFERENCES tasks(task_id) ON DELETE CASCADE,
    message TEXT NOT NULL,
    user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

COMMENT ON TABLE task_messages IS 'Messages and communication for tasks';

-- Table: task_timelines
-- Description: Task history and status tracking
CREATE TABLE task_timelines (
    timeline_id SERIAL PRIMARY KEY,
    task_id INTEGER NOT NULL REFERENCES tasks(task_id) ON DELETE CASCADE,
    status VARCHAR(50),
    user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE RESTRICT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

COMMENT ON TABLE task_timelines IS 'Task status change history';

-- Table: task_attachments
-- Description: File uploads for tasks
CREATE TABLE task_attachments (
    attachment_id SERIAL PRIMARY KEY,
    task_id INTEGER NOT NULL REFERENCES tasks(task_id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    attachment_url TEXT NOT NULL,
    uploaded_by INTEGER NOT NULL REFERENCES users(user_id) ON DELETE RESTRICT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

COMMENT ON TABLE task_attachments IS 'File attachments for tasks';

-- Table: quotes
-- Description: Quote documents for tasks
CREATE TABLE quotes (
    quote_id SERIAL PRIMARY KEY,
    task_id INTEGER NOT NULL REFERENCES tasks(task_id) ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    subtotal NUMERIC(10, 2) NOT NULL,
    tax NUMERIC(10, 2) NOT NULL,
    total NUMERIC(10, 2) NOT NULL,
    notes TEXT,
    CONSTRAINT chk_quote_amounts CHECK (
        subtotal >= 0 AND 
        tax >= 0 AND 
        total >= 0
    )
);

COMMENT ON TABLE quotes IS 'Project quotes';
COMMENT ON COLUMN quotes.tax IS 'Typically 18% GST in India';
COMMENT ON COLUMN quotes.total IS 'Calculated as subtotal + tax';

-- Table: bills
-- Description: Billing documents
CREATE TABLE bills (
    bill_id SERIAL PRIMARY KEY,
    task_id INTEGER NOT NULL UNIQUE REFERENCES tasks(task_id) ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    due_date DATE,
    subtotal NUMERIC(10, 2) NOT NULL,
    tax NUMERIC(10, 2) NOT NULL,
    total NUMERIC(10, 2) NOT NULL,
    additional_notes TEXT,
    status bill_status_enum NOT NULL DEFAULT 'Pending',
    CONSTRAINT chk_bill_amounts CHECK (
        subtotal >= 0 AND 
        tax >= 0 AND 
        total >= 0
    )
);

COMMENT ON TABLE bills IS 'Bills and invoices (one per task)';

-- Table: config
-- Description: System configuration key-value store
CREATE TABLE config (
    key VARCHAR(100) PRIMARY KEY,
    value VARCHAR(255) NOT NULL
);

COMMENT ON TABLE config IS 'System configuration settings';

-- Table: job_queue
-- Description: Background job queue
CREATE TABLE job_queue (
    job_id SERIAL PRIMARY KEY,
    job_type VARCHAR(100) NOT NULL,
    payload JSONB,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    processed_at TIMESTAMP WITH TIME ZONE,
    error_message TEXT
);

COMMENT ON TABLE job_queue IS 'Queue for background jobs';

-- ========================================================================================================
-- INSERT DUMMY DATA
-- ========================================================================================================

-- ----------------------------------------
-- Users (12 total: 3 admin, 4 salesperson, 5 agent)
-- ----------------------------------------
INSERT INTO users (name, email, password, contact_no, address, role, profile_bg_color, is_active, email_verified, phone_verified, last_login_at) VALUES
-- Admins (3)
('Admin User', 'admin@company.com', crypt('Admin@123', gen_salt('bf')), '+91-9876543210', '101 Corporate Plaza, Mumbai, MH 400001', 'admin', '#FF5733', TRUE, TRUE, TRUE, '2025-12-20 10:30:00+00'),
('Super Admin', 'superadmin@company.com', crypt('Super@123', gen_salt('bf')), '+91-9876543211', '202 Business Tower, Delhi, DL 110001', 'admin', '#3498DB', TRUE, TRUE, TRUE, '2025-12-22 09:15:00+00'),
('Manager', 'manager@company.com', crypt('Manager@123', gen_salt('bf')), '+91-9876543212', '303 Executive Block, Bangalore, KA 560001', 'admin', '#2ECC71', TRUE, TRUE, TRUE, '2025-12-21 14:45:00+00'),

-- Salespersons (4)
('John Sales', 'john.sales@company.com', crypt('John@123', gen_salt('bf')), '+91-9876543213', '10 Sales Wing, Ahmedabad, GJ 380001', 'salesperson', '#E74C3C', TRUE, TRUE, TRUE, '2025-12-26 08:00:00+00'),
('Sarah Sales', 'sarah.sales@company.com', crypt('Sarah@123', gen_salt('bf')), '+91-9876543214', '20 Commerce Street, Pune, MH 411001', 'salesperson', '#9B59B6', TRUE, TRUE, TRUE, '2025-12-25 11:30:00+00'),
('Mike Sales', 'mike.sales@company.com', crypt('Mike@123', gen_salt('bf')), '+91-9876543215', '30 Market Road, Chennai, TN 600001', 'salesperson', '#F39C12', TRUE, TRUE, FALSE, '2025-12-24 13:20:00+00'),
('Emma Sales', 'emma.sales@company.com', crypt('Emma@123', gen_salt('bf')), '+91-9876543216', '40 Trade Avenue, Hyderabad, TG 500001', 'salesperson', '#1ABC9C', TRUE, TRUE, TRUE, '2025-12-23 16:45:00+00'),

-- Agents (5)
('Alice Agency', 'alice@agency.com', crypt('Alice@123', gen_salt('bf')), '+91-9876543217', '501 Agency Hub, Surat, GJ 395001', 'agent', '#16A085', TRUE, TRUE, TRUE, '2025-12-26 09:30:00+00'),
('Bob Agency', 'bob@agency.com', crypt('Bob@123', gen_salt('bf')), '+91-9876543218', '502 Partner Plaza, Jaipur, RJ 302001', 'agent', '#27AE60', TRUE, TRUE, TRUE, '2025-12-25 10:00:00+00'),
('Charlie Agency', 'charlie@agency.com', crypt('Charlie@123', gen_salt('bf')), '+91-9876543219', '503 Collaboration Center, Lucknow, UP 226001', 'agent', '#2980B9', TRUE, TRUE, FALSE, '2025-12-24 12:00:00+00'),
('Diana Agency', 'diana@agency.com', crypt('Diana@123', gen_salt('bf')), '+91-9876543220', '504 Associate Building, Indore, MP 452001', 'agent', '#8E44AD', TRUE, TRUE, TRUE, '2025-12-23 15:30:00+00'),
('Evan Agency', 'evan@agency.com', crypt('Evan@123', gen_salt('bf')), '+91-9876543221', '505 Affiliate Tower, Chandigarh, CH 160001', 'agent', '#D35400', TRUE, FALSE, FALSE, NULL);

-- ----------------------------------------
-- User Tokens (8 tokens for active users)
-- ----------------------------------------
INSERT INTO user_tokens (user_id, access_token, refresh_token, access_token_expires_at, refresh_token_expires_at, is_revoked, ip_address, user_agent) VALUES
(1, 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxLCJyb2xlIjoiYWRtaW4ifQ.admin_access_token_1', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxLCJ0eXBlIjoicmVmcmVzaCJ9.admin_refresh_token_1', '2025-12-27 10:30:00+00', '2026-01-20 10:30:00+00', FALSE, '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0'),
(2, 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoyLCJyb2xlIjoiYWRtaW4ifQ.superadmin_access_token_2', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoyLCJ0eXBlIjoicmVmcmVzaCJ9.superadmin_refresh_token_2', '2025-12-27 09:15:00+00', '2026-01-22 09:15:00+00', FALSE, '192.168.1.102', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15'),
(4, 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjo0LCJyb2xlIjoic2FsZXNwZXJzb24ifQ.john_access_token_4', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjo0LCJ0eXBlIjoicmVmcmVzaCJ9.john_refresh_token_4', '2025-12-27 08:00:00+00', '2026-01-26 08:00:00+00', FALSE, '192.168.1.104', 'Mozilla/5.0 (X11; Linux x86_64) Firefox/121.0'),
(5, 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjo1LCJyb2xlIjoic2FsZXNwZXJzb24ifQ.sarah_access_token_5', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjo1LCJ0eXBlIjoicmVmcmVzaCJ9.sarah_refresh_token_5', '2025-12-27 11:30:00+00', '2026-01-25 11:30:00+00', FALSE, '192.168.1.105', 'Mozilla/5.0 (iPad; CPU OS 16_0 like Mac OS X) Safari/604.1'),
(8, 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjo4LCJyb2xlIjoiYWdlbnQifQ.alice_access_token_8', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjo4LCJ0eXBlIjoicmVmcmVzaCJ9.alice_refresh_token_8', '2025-12-27 09:30:00+00', '2026-01-26 09:30:00+00', FALSE, '192.168.1.108', 'Mozilla/5.0 (Android 13; Mobile) Chrome/120.0.0.0'),
(9, 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjo5LCJyb2xlIjoiYWdlbnQifQ.bob_access_token_9', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjo5LCJ0eXBlIjoicmVmcmVzaCJ9.bob_refresh_token_9', '2025-12-27 10:00:00+00', '2026-01-25 10:00:00+00', FALSE, '192.168.1.109', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) Safari/605.1.15'),
(3, 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjozLCJyb2xlIjoiYWRtaW4ifQ.manager_access_token_old', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjozLCJ0eXBlIjoicmVmcmVzaCJ9.manager_refresh_token_old', '2025-12-20 14:45:00+00', '2026-01-21 14:45:00+00', TRUE, '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Edge/120.0.0.0'),
(11, 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxMSwicm9sZSI6ImFnZW50In0.diana_access_token_11', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxMSwidHlwZSI6InJlZnJlc2gifQ.diana_refresh_token_11', '2025-12-27 15:30:00+00', '2026-01-23 15:30:00+00', FALSE, '192.168.1.111', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0');

-- ----------------------------------------
-- User Sessions (10 sessions)
-- ----------------------------------------
INSERT INTO user_sessions (user_id, token_id, login_at, logout_at, ip_address, user_agent, device_info, is_active) VALUES
(1, (SELECT token_id FROM user_tokens WHERE user_id = 1 AND is_revoked = FALSE LIMIT 1), '2025-12-20 10:30:00+00', NULL, '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', '{"os": "Windows", "browser": "Chrome", "device": "Desktop"}', TRUE),
(2, (SELECT token_id FROM user_tokens WHERE user_id = 2 AND is_revoked = FALSE LIMIT 1), '2025-12-22 09:15:00+00', NULL, '192.168.1.102', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15', '{"os": "macOS", "browser": "Safari", "device": "Desktop"}', TRUE),
(3, NULL, '2025-12-15 08:00:00+00', '2025-12-15 18:00:00+00', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Edge/120.0.0.0', '{"os": "Windows", "browser": "Edge", "device": "Desktop"}', FALSE),
(4, (SELECT token_id FROM user_tokens WHERE user_id = 4 AND is_revoked = FALSE LIMIT 1), '2025-12-26 08:00:00+00', NULL, '192.168.1.104', 'Mozilla/5.0 (X11; Linux x86_64) Firefox/121.0', '{"os": "Linux", "browser": "Firefox", "device": "Desktop"}', TRUE),
(5, (SELECT token_id FROM user_tokens WHERE user_id = 5 AND is_revoked = FALSE LIMIT 1), '2025-12-25 11:30:00+00', NULL, '192.168.1.105', 'Mozilla/5.0 (iPad; CPU OS 16_0 like Mac OS X) Safari/604.1', '{"os": "iOS", "browser": "Safari", "device": "Tablet"}', TRUE),
(6, NULL, '2025-12-20 12:00:00+00', '2025-12-20 17:30:00+00', '192.168.1.106', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', '{"os": "Windows", "browser": "Chrome", "device": "Desktop"}', FALSE),
(8, (SELECT token_id FROM user_tokens WHERE user_id = 8 AND is_revoked = FALSE LIMIT 1), '2025-12-26 09:30:00+00', NULL, '192.168.1.108', 'Mozilla/5.0 (Android 13; Mobile) Chrome/120.0.0.0', '{"os": "Android", "browser": "Chrome", "device": "Mobile"}', TRUE),
(9, (SELECT token_id FROM user_tokens WHERE user_id = 9 AND is_revoked = FALSE LIMIT 1), '2025-12-25 10:00:00+00', NULL, '192.168.1.109', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) Safari/605.1.15', '{"os": "iOS", "browser": "Safari", "device": "Mobile"}', TRUE),
(10, NULL, '2025-12-18 14:00:00+00', '2025-12-18 16:45:00+00', '192.168.1.110', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Firefox/121.0', '{"os": "Windows", "browser": "Firefox", "device": "Desktop"}', FALSE),
(11, (SELECT token_id FROM user_tokens WHERE user_id = 11 AND is_revoked = FALSE LIMIT 1), '2025-12-23 15:30:00+00', NULL, '192.168.1.111', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0', '{"os": "Windows", "browser": "Chrome", "device": "Desktop"}', TRUE);

-- ----------------------------------------
-- Clients (10)
-- ----------------------------------------
INSERT INTO clients (name, contact_no, email, address, created_at) VALUES
('Rajesh Sharma', '+91-9988776655', 'rajesh.sharma@email.com', 'A-204, Sunshine Apartments, Bodakdev, Ahmedabad, GJ 380054', '2025-01-15 10:30:00+00'),
('Priya Patel', '+91-9988776656', 'priya.patel@email.com', 'B-301, Green Valley, Satellite Road, Ahmedabad, GJ 380015', '2025-02-20 11:45:00+00'),
('Amit Kumar', '+91-9988776657', 'amit.kumar@email.com', 'Villa 12, Prahlad Nagar, Ahmedabad, GJ 380051', '2025-03-10 09:15:00+00'),
('Sneha Desai', '+91-9988776658', 'sneha.desai@email.com', 'C-105, Royal Enclave, SG Highway, Ahmedabad, GJ 380060', '2025-04-05 14:20:00+00'),
('Vikram Singh', '+91-9988776659', 'vikram.singh@email.com', '2nd Floor, Business Hub, CG Road, Ahmedabad, GJ 380009', '2025-05-12 16:30:00+00'),
('Ananya Reddy', '+91-9988776660', 'ananya.reddy@email.com', 'D-202, Lake View Residency, Thaltej, Ahmedabad, GJ 380054', '2025-06-18 10:00:00+00'),
('Karan Malhotra', '+91-9988776661', 'karan.malhotra@email.com', 'Penthouse, Sky Tower, Vastrapur, Ahmedabad, GJ 380015', '2025-07-22 12:45:00+00'),
('Divya Iyer', '+91-9988776662', 'divya.iyer@email.com', 'E-401, Heritage Complex, Navrangpura, Ahmedabad, GJ 380009', '2025-08-30 15:10:00+00'),
('Rohit Joshi', '+91-9988776663', 'rohit.joshi@email.com', 'Bungalow 7, Shilaj Circle, Ahmedabad, GJ 380058', '2025-09-14 09:30:00+00'),
('Kavya Nair', '+91-9988776664', 'kavya.nair@email.com', 'F-303, Palm Springs, Makarba, Ahmedabad, GJ 380051', '2025-10-25 11:00:00+00');

-- ----------------------------------------
-- Designers (8)
-- ----------------------------------------
INSERT INTO designers (name, firm_name, contact_no, address, profile_bg_color, created_at) VALUES
('Rahul Designs', 'Rahul Interior Studio', '+91-9876501234', '15, Design Plaza, Ellis Bridge, Ahmedabad, GJ 380006', '#FF5733', '2025-01-10 10:00:00+00'),
('Meera Interiors', 'Meera Design House', '+91-9876501235', '22, Creative Tower, Ashram Road, Ahmedabad, GJ 380009', '#3498DB', '2025-02-15 11:30:00+00'),
('Arjun Associates', 'Arjun Architect & Designers', '+91-9876501236', '8, Professional Complex, Paldi, Ahmedabad, GJ 380007', '#2ECC71', '2025-03-20 09:45:00+00'),
('Nisha Creative', 'Nisha Design Studio', '+91-9876501237', '30, Innovation Hub, Navrangpura, Ahmedabad, GJ 380009', '#E74C3C', '2025-04-12 14:15:00+00'),
('Sanjay Spaces', 'Sanjay Interior Solutions', '+91-9876501238', '45, Artisan Avenue, CG Road, Ahmedabad, GJ 380009', '#9B59B6', '2025-05-18 10:30:00+00'),
('Pooja Design Lab', 'Pooja Contemporary Designs', '+91-9876501239', '12, Modern Plaza, Vastrapur, Ahmedabad, GJ 380015', '#F39C12', '2025-06-22 12:00:00+00'),
('Karthik Concepts', 'Karthik Design Concepts', '+91-9876501240', '18, Style Street, Satellite, Ahmedabad, GJ 380015', '#1ABC9C', '2025-07-28 15:45:00+00'),
('Lakshmi Living', 'Lakshmi Home Interiors', '+91-9876501241', '25, Elegance Tower, Bodakdev, Ahmedabad, GJ 380054', '#16A085', '2025-08-15 09:00:00+00');

-- ----------------------------------------
-- Service Master (8 services)
-- ----------------------------------------
INSERT INTO service_master (name, description, default_unit_price, unit, created_at) VALUES
('Interior Design Consultation', 'Complete interior design planning and consultation', 5000.00, 'consultation', '2025-01-01 00:00:00+00'),
('3D Visualization', 'Photorealistic 3D renders of interior spaces', 3500.00, 'render', '2025-01-01 00:00:00+00'),
('Site Measurement', 'Detailed site measurement and documentation', 1500.00, 'visit', '2025-01-01 00:00:00+00'),
('Installation Supervision', 'On-site supervision during installation', 300.00, 'day', '2025-01-01 00:00:00+00'),
('Space Planning', 'Optimized space planning and layout design', 2000.00, 'room', '2025-01-01 00:00:00+00'),
('Color Consultation', 'Professional color scheme consultation', 800.00, 'room', '2025-01-01 00:00:00+00'),
('Furniture Design', 'Custom furniture design and specification', 1500.00, 'piece', '2025-01-01 00:00:00+00'),
('Lighting Design', 'Comprehensive lighting design and planning', 1200.00, 'zone', '2025-01-01 00:00:00+00');

-- ----------------------------------------
-- Tasks (20)
-- ----------------------------------------
INSERT INTO tasks (deal_no, name, created_by, client_id, designer_id, agency_id, start_date, due_date, priority, status, remarks, created_at) VALUES
('0000-0001', 'Modern Living Room Makeover', 4, 1, 1, 8, '2025-01-20', '2025-03-15', 'High', 'Completed', 'Client wants contemporary design with neutral tones', '2025-01-20 09:00:00+00'),
('0000-0002', 'Master Bedroom Suite Design', 5, 2, 2, 9, '2025-02-01', '2025-04-10', 'Medium', 'In Progress', 'Luxurious bedroom with walk-in closet', '2025-02-01 10:30:00+00'),
('0000-0003', 'Complete Villa Interior', 4, 3, 3, 8, '2025-02-15', '2025-06-30', 'Urgent', 'In Progress', 'Full villa renovation - 5 bedrooms, 3 bathrooms', '2025-02-15 11:00:00+00'),
('0000-0004', 'Kitchen Renovation Project', 6, 4, 4, 10, '2025-03-01', '2025-04-25', 'High', 'Approved', 'Modern modular kitchen with island', '2025-03-01 09:15:00+00'),
('0000-0005', 'Corporate Office Design', 7, 5, 5, 11, '2025-03-10', '2025-05-20', 'Urgent', 'Quote: Done', 'Professional office space for 50 employees', '2025-03-10 10:00:00+00'),
('0000-0006', 'Kids Room Transformation', 5, 6, 6, 9, '2025-03-20', '2025-05-05', 'Medium', 'Completed', 'Playful and functional design for two kids', '2025-03-20 14:30:00+00'),
('0000-0007', 'Penthouse Luxury Design', 4, 7, 7, 8, '2025-04-01', '2025-07-15', 'Urgent', 'In Progress', 'High-end penthouse with panoramic views', '2025-04-01 09:45:00+00'),
('0000-0008', 'Heritage Home Restoration', 6, 8, 8, 10, '2025-04-15', '2025-08-30', 'High', 'Measurement: Done', 'Restore heritage home while preserving original character', '2025-04-15 11:30:00+00'),
('0000-0009', 'Compact Apartment Design', 7, 9, 1, 11, '2025-05-01', '2025-06-20', 'Medium', 'Approved', 'Space-efficient design for 2BHK apartment', '2025-05-01 10:15:00+00'),
('0000-0010', 'Restaurant Interior Setup', 5, 10, 2, 9, '2025-05-10', '2025-07-25', 'High', 'Quote: Done', 'Contemporary restaurant with 60-seat capacity', '2025-05-10 12:00:00+00'),
('0000-0011', 'Home Office Creation', 4, 1, 3, 8, '2025-06-01', '2025-07-10', 'Low', 'Completed', 'Functional home office in spare bedroom', '2025-06-01 09:30:00+00'),
('0000-0012', 'Bathroom Spa Upgrade', 6, 2, 4, 10, '2025-06-15', '2025-08-05', 'Medium', 'In Progress', 'Luxury spa-like bathroom renovation', '2025-06-15 14:00:00+00'),
('0000-0013', 'Open Plan Living Space', 7, 3, 5, 11, '2025-07-01', '2025-09-15', 'High', 'Measurement: Done', 'Convert closed spaces to open-plan living', '2025-07-01 10:45:00+00'),
('0000-0014', 'Boutique Store Design', 5, 4, 6, 9, '2025-07-15', '2025-09-30', 'Urgent', 'Created', 'Fashion boutique interior with fitting rooms', '2025-07-15 11:15:00+00'),
('0000-0015', 'Guest House Complete Design', 4, 5, 7, 8, '2025-08-01', '2025-10-20', 'Medium', 'Created', 'Independent guest house with 3 rooms', '2025-08-01 09:00:00+00'),
('0000-0016', 'Balcony Garden Design', 6, 6, 8, 10, '2025-08-15', '2025-09-30', 'Low', 'Approved', 'Convert balcony into urban garden retreat', '2025-08-15 13:30:00+00'),
('0000-0017', 'Study Room Enhancement', 7, 7, 1, 11, '2025-09-01', '2025-10-15', 'Medium', 'Quote: Done', 'Quiet study space with custom bookshelves', '2025-09-01 10:00:00+00'),
('0000-0018', 'Basement Recreation Room', 5, 8, 2, 9, '2025-09-15', '2025-11-30', 'High', 'Measurement: Done', 'Entertainment and recreation area in basement', '2025-09-15 14:45:00+00'),
('0000-0019', 'Pooja Room Design', 4, 9, 3, 8, '2025-10-01', '2025-11-15', 'Low', 'Created', 'Traditional pooja room with modern touches', '2025-10-01 11:30:00+00'),
('0000-0020', 'Terrace Lounge Setup', 6, 10, 4, 10, '2025-10-15', '2025-12-31', 'Medium', 'Created', 'Outdoor terrace lounge with seating and lighting', '2025-10-15 12:15:00+00');

-- ----------------------------------------
-- Measurements (15)
-- ----------------------------------------
INSERT INTO measurements (task_id, location, width, height, area, unit, quantity, unit_price, discount, total_price, notes) VALUES
(1, 'Living Room - Main Wall', 5.50, 3.20, 17.60, 'sqm', 1, 450.00, 50.00, 7870.00, 'Feature wall with texture paint'),
(1, 'Living Room - Side Wall', 4.20, 3.20, 13.44, 'sqm', 1, 350.00, 0.00, 4704.00, 'Standard wall painting'),
(2, 'Master Bedroom', 4.80, 3.50, 16.80, 'sqm', 1, 500.00, 100.00, 8300.00, 'Premium wallpaper installation'),
(3, 'Villa - Living Area', 8.00, 4.00, 32.00, 'sqm', 1, 550.00, 200.00, 17400.00, 'Large open living space'),
(3, 'Villa - Master Bedroom', 5.50, 3.80, 20.90, 'sqm', 1, 500.00, 150.00, 10300.00, 'Spacious master suite'),
(3, 'Villa - Kitchen', 6.20, 3.50, 21.70, 'sqm', 1, 600.00, 180.00, 12840.00, 'Modular kitchen area'),
(4, 'Kitchen - Main Area', 4.50, 3.20, 14.40, 'sqm', 1, 650.00, 100.00, 9260.00, 'Kitchen counter and cabinets zone'),
(6, 'Kids Room - Wall 1', 4.00, 3.00, 12.00, 'sqm', 1, 400.00, 50.00, 4750.00, 'Cartoon theme wall'),
(6, 'Kids Room - Wall 2', 3.50, 3.00, 10.50, 'sqm', 1, 400.00, 0.00, 4200.00, 'Study area wall'),
(8, 'Heritage Home - Main Hall', 7.50, 4.50, 33.75, 'sqm', 1, 700.00, 250.00, 23375.00, 'High ceiling heritage room'),
(11, 'Home Office', 3.80, 3.00, 11.40, 'sqm', 1, 420.00, 40.00, 4748.00, 'Compact office space'),
(13, 'Open Living Area', 9.00, 3.80, 34.20, 'sqm', 1, 520.00, 300.00, 17484.00, 'Large open-plan space'),
(16, 'Balcony Garden', 3.20, 2.40, 7.68, 'sqm', 1, 380.00, 30.00, 2888.40, 'Balcony floor area'),
(18, 'Basement Recreation', 6.50, 2.80, 18.20, 'sqm', 1, 480.00, 150.00, 8586.00, 'Below-ground entertainment area'),
(7, 'Penthouse - Living Room', 10.00, 4.20, 42.00, 'sqm', 1, 800.00, 500.00, 33100.00, 'Premium penthouse space with view');

-- ----------------------------------------
-- Task Services (25)
-- ----------------------------------------
INSERT INTO task_services (task_id, service_master_id, quantity, unit_price, total_amount) VALUES
(1, 1, 1, 5000.00, 5000.00),
(1, 2, 3, 3500.00, 10500.00),
(1, 4, 10, 300.00, 3000.00),
(2, 1, 1, 5000.00, 5000.00),
(2, 5, 2, 2000.00, 4000.00),
(2, 7, 5, 1500.00, 7500.00),
(3, 1, 1, 5000.00, 5000.00),
(3, 2, 8, 3500.00, 28000.00),
(3, 5, 5, 2000.00, 10000.00),
(3, 8, 8, 1200.00, 9600.00),
(4, 1, 1, 5000.00, 5000.00),
(4, 5, 1, 2000.00, 2000.00),
(4, 8, 3, 1200.00, 3600.00),
(5, 1, 1, 5000.00, 5000.00),
(5, 2, 5, 3500.00, 17500.00),
(5, 5, 10, 2000.00, 20000.00),
(6, 1, 1, 5000.00, 5000.00),
(6, 6, 2, 800.00, 1600.00),
(6, 7, 3, 1500.00, 4500.00),
(7, 1, 1, 5000.00, 5000.00),
(7, 2, 6, 3500.00, 21000.00),
(7, 8, 10, 1200.00, 12000.00),
(9, 1, 1, 5000.00, 5000.00),
(9, 5, 2, 2000.00, 4000.00),
(10, 1, 1, 5000.00, 5000.00);

-- ----------------------------------------
-- Task Messages (30)
-- ----------------------------------------
INSERT INTO task_messages (task_id, user_id, message, created_at) VALUES
(1, 4, 'Initial consultation completed with client. They prefer modern minimalist design.', '2025-01-20 10:30:00+00'),
(1, 8, 'Measurements taken. Living room dimensions confirmed.', '2025-01-22 14:00:00+00'),
(1, 4, 'Quote approved by client. Starting work next week.', '2025-01-25 11:15:00+00'),
(2, 5, 'Client wants to see 3D visualization before finalizing.', '2025-02-02 09:45:00+00'),
(2, 9, 'Will arrange designer meeting this Friday.', '2025-02-03 10:30:00+00'),
(2, 5, '3D renders shared with client. Awaiting feedback.', '2025-02-08 15:20:00+00'),
(3, 4, 'Large project - full villa design. Multiple rooms to coordinate.', '2025-02-15 12:00:00+00'),
(3, 8, 'Site visit scheduled for measurement. Will take 2 days.', '2025-02-17 09:00:00+00'),
(3, 4, 'Client requesting changes to color scheme in bedrooms.', '2025-02-25 14:30:00+00'),
(4, 6, 'Kitchen renovation starting next month. Modular units ordered.', '2025-03-02 10:15:00+00'),
(4, 10, 'Quote prepared and sent to client for review.', '2025-03-05 11:00:00+00'),
(5, 7, 'Corporate office project - 50 workstations plus conference rooms.', '2025-03-10 09:30:00+00'),
(5, 11, 'Measurements completed. Quote will be ready by Friday.', '2025-03-12 16:00:00+00'),
(6, 5, 'Kids room project completed successfully!', '2025-04-28 10:00:00+00'),
(6, 9, 'Client very happy with the result. Requesting minor touch-ups.', '2025-04-30 14:15:00+00'),
(7, 4, 'Penthouse design - premium materials required for this project.', '2025-04-01 11:00:00+00'),
(7, 8, 'Initial consultation done. Client has specific vision for the space.', '2025-04-03 10:30:00+00'),
(8, 6, 'Heritage restoration requires special care and materials.', '2025-04-15 12:45:00+00'),
(8, 10, 'Measurements complete. Working on design that respects original architecture.', '2025-04-20 09:15:00+00'),
(9, 7, 'Compact apartment - need space-saving solutions.', '2025-05-01 11:30:00+00'),
(9, 11, 'Quote approved! Client excited to start.', '2025-05-08 14:00:00+00'),
(10, 5, 'Restaurant design - need to consider commercial kitchen regulations.', '2025-05-10 13:00:00+00'),
(10, 9, 'Quote prepared including all commercial requirements.', '2025-05-15 10:45:00+00'),
(11, 4, 'Home office completed ahead of schedule!', '2025-06-28 09:00:00+00'),
(12, 6, 'Bathroom renovation in progress. Plumbing work started.', '2025-07-01 11:00:00+00'),
(12, 10, 'Tiles and fixtures delivered. Installation next week.', '2025-07-08 14:30:00+00'),
(13, 7, 'Open plan conversion requires structural assessment first.', '2025-07-01 12:00:00+00'),
(13, 11, 'Structural engineer consulted. Measurements done.', '2025-07-05 10:00:00+00'),
(14, 5, 'New boutique store project received. Meeting with client tomorrow.', '2025-07-16 09:30:00+00'),
(15, 4, 'Guest house design - independent structure with 3 bedrooms.', '2025-08-01 10:15:00+00');

-- ----------------------------------------
-- Task Timelines (25)
-- ----------------------------------------
INSERT INTO task_timelines (task_id, status, user_id, created_at) VALUES
(1, 'Created', 4, '2025-01-20 09:00:00+00'),
(1, 'Measurement: Done', 8, '2025-01-22 14:00:00+00'),
(1, 'Quote: Done', 4, '2025-01-24 10:00:00+00'),
(1, 'Approved', 4, '2025-01-25 11:15:00+00'),
(1, 'In Progress', 4, '2025-01-28 09:00:00+00'),
(1, 'Completed', 4, '2025-03-12 17:00:00+00'),
(2, 'Created', 5, '2025-02-01 10:30:00+00'),
(2, 'Measurement: Done', 9, '2025-02-05 14:00:00+00'),
(2, 'Quote: Done', 5, '2025-02-10 11:00:00+00'),
(2, 'Approved', 5, '2025-02-12 09:30:00+00'),
(2, 'In Progress', 5, '2025-02-15 10:00:00+00'),
(3, 'Created', 4, '2025-02-15 11:00:00+00'),
(3, 'Measurement: Done', 8, '2025-02-20 16:00:00+00'),
(3, 'Quote: Done', 4, '2025-02-25 10:00:00+00'),
(3, 'Approved', 4, '2025-03-01 11:00:00+00'),
(3, 'In Progress', 4, '2025-03-05 09:00:00+00'),
(4, 'Created', 6, '2025-03-01 09:15:00+00'),
(4, 'Measurement: Done', 10, '2025-03-05 14:00:00+00'),
(4, 'Quote: Done', 6, '2025-03-08 10:00:00+00'),
(4, 'Approved', 6, '2025-03-10 11:30:00+00'),
(5, 'Created', 7, '2025-03-10 10:00:00+00'),
(5, 'Measurement: Done', 11, '2025-03-13 15:00:00+00'),
(5, 'Quote: Done', 7, '2025-03-18 10:00:00+00'),
(6, 'Created', 5, '2025-03-20 14:30:00+00'),
(6, 'Measurement: Done', 9, '2025-03-24 10:00:00+00');

-- ----------------------------------------
-- Task Users (20)
-- ----------------------------------------
INSERT INTO task_users (task_id, user_id, role_in_task) VALUES
(1, 4, 'Salesperson'),
(1, 8, 'Agent'),
(2, 5, 'Salesperson'),
(2, 9, 'Agent'),
(3, 4, 'Project Manager'),
(3, 8, 'Site Supervisor'),
(4, 6, 'Salesperson'),
(4, 10, 'Agent'),
(5, 7, 'Salesperson'),
(5, 11, 'Agent'),
(6, 5, 'Salesperson'),
(6, 9, 'Design Coordinator'),
(7, 4, 'Salesperson'),
(7, 8, 'Project Manager'),
(9, 7, 'Salesperson'),
(9, 11, 'Agent'),
(10, 5, 'Salesperson'),
(10, 9, 'Commercial Specialist'),
(11, 4, 'Salesperson'),
(11, 8, 'Agent');

-- ----------------------------------------
-- Task Attachments (12)
-- ----------------------------------------
INSERT INTO task_attachments (task_id, name, attachment_url, uploaded_by, created_at) VALUES
(1, 'floor_plan_v1.pdf', 's3://design-bucket/tasks/0000-0001/floor_plan_v1.pdf', 8, '2025-01-22 15:00:00+00'),
(1, 'client_approval.pdf', 's3://design-bucket/tasks/0000-0001/client_approval.pdf', 4, '2025-01-25 12:00:00+00'),
(2, 'design_mockup_bedroom.jpg', 's3://design-bucket/tasks/0000-0002/design_mockup_bedroom.jpg', 9, '2025-02-08 16:00:00+00'),
(3, 'villa_floor_plans.pdf', 's3://design-bucket/tasks/0000-0003/villa_floor_plans.pdf', 8, '2025-02-20 17:00:00+00'),
(3, 'site_photos_001.jpg', 's3://design-bucket/tasks/0000-0003/site_photos_001.jpg', 8, '2025-02-17 10:00:00+00'),
(4, 'kitchen_layout.pdf', 's3://design-bucket/tasks/0000-0004/kitchen_layout.pdf', 10, '2025-03-05 15:00:00+00'),
(5, 'office_design_v2.pdf', 's3://design-bucket/tasks/0000-0005/office_design_v2.pdf', 11, '2025-03-13 16:30:00+00'),
(6, 'kids_room_render.jpg', 's3://design-bucket/tasks/0000-0006/kids_room_render.jpg', 9, '2025-03-26 11:00:00+00'),
(7, 'penthouse_3d_renders.jpg', 's3://design-bucket/tasks/0000-0007/penthouse_3d_renders.jpg', 8, '2025-04-05 14:00:00+00'),
(8, 'heritage_assessment.pdf', 's3://design-bucket/tasks/0000-0008/heritage_assessment.pdf', 10, '2025-04-22 10:00:00+00'),
(9, 'compact_space_solutions.pdf', 's3://design-bucket/tasks/0000-0009/compact_space_solutions.pdf', 11, '2025-05-05 12:00:00+00'),
(10, 'restaurant_layout_plan.pdf', 's3://design-bucket/tasks/0000-0010/restaurant_layout_plan.pdf', 9, '2025-05-15 11:30:00+00');

-- ----------------------------------------
-- Quotes (10)
-- ----------------------------------------
-- Calculate subtotals from task_services
INSERT INTO quotes (task_id, subtotal, tax, total, notes, created_at) VALUES
(1, 18500.00, 3330.00, 21830.00, 'Quote includes design consultation, 3D visualization, and supervision. Payment terms: 50% advance, 50% on completion. Valid for 30 days.', '2025-01-24 10:00:00+00'),
(2, 16500.00, 2970.00, 19470.00, 'Quote for master bedroom suite design including space planning and custom furniture. 18% GST applicable. Payment in 3 installments.', '2025-02-10 11:00:00+00'),
(3, 52600.00, 9468.00, 62068.00, 'Complete villa interior design package. Includes all consultations, 3D renders, space planning, and lighting design. Payment schedule: 40% advance, 30% mid-project, 30% completion.', '2025-02-25 10:00:00+00'),
(4, 10600.00, 1908.00, 12508.00, 'Kitchen renovation quote with modular design. Includes consultation, space planning, and lighting. 45-day completion timeline.', '2025-03-08 10:00:00+00'),
(5, 42500.00, 7650.00, 50150.00, 'Corporate office interior design for 50-seat capacity. Includes consultation, 3D visualization, and comprehensive space planning. 60-day execution period.', '2025-03-18 10:00:00+00'),
(6, 11100.00, 1998.00, 13098.00, 'Kids room transformation project. Includes design consultation, color consultation, and custom furniture design. Fun and functional space.', '2025-03-26 12:00:00+00'),
(7, 38000.00, 6840.00, 44840.00, 'Luxury penthouse interior design. Premium materials and finishes. Includes consultation, multiple 3D renders, and extensive lighting design. 90-day project timeline.', '2025-04-10 11:00:00+00'),
(9, 9000.00, 1620.00, 10620.00, 'Compact apartment design optimization. Includes consultation and space planning for efficient use of limited space. 30-day completion.', '2025-05-08 10:00:00+00'),
(10, 5000.00, 900.00, 5900.00, 'Restaurant interior design consultation. Preliminary design concepts and layout planning. Follow-up services quoted separately.', '2025-05-15 11:00:00+00'),
(17, 0.00, 0.00, 0.00, 'Quote in preparation - services being finalized with client.', '2025-09-05 10:00:00+00');

-- ----------------------------------------
-- Bills (8)
-- ----------------------------------------
INSERT INTO bills (task_id, subtotal, tax, total, due_date, status, additional_notes, created_at) VALUES
(1, 18500.00, 3330.00, 21830.00, '2025-04-12', 'Paid', 'Invoice #INV-2025-001. Payment received in full. Thank you for your business! Bank: HDFC Bank, Account: 50200012345678, IFSC: HDFC0001234', '2025-03-12 18:00:00+00'),
(6, 11100.00, 1998.00, 13098.00, '2025-06-05', 'Paid', 'Invoice #INV-2025-006. Full payment received. Project completed successfully. Bank details: HDFC Bank, Account: 50200012345678', '2025-05-05 10:00:00+00'),
(11, 8748.00, 1574.64, 10322.64, '2025-08-10', 'Paid', 'Invoice #INV-2025-011. Home office project completed. Payment received. Thank you!', '2025-07-10 09:00:00+00'),
(2, 16500.00, 2970.00, 19470.00, '2026-01-15', 'Partial', 'Invoice #INV-2025-002. Partial payment of ₹10,000 received. Balance ₹9,470 due. Bank: HDFC Bank, Account: 50200012345678', '2025-12-10 11:00:00+00'),
(3, 52600.00, 9468.00, 62068.00, '2026-02-05', 'Pending', 'Invoice #INV-2025-003. Full villa interior project. Payment terms: Final installment due on completion. Bank: HDFC Bank, IFSC: HDFC0001234', '2025-12-20 10:00:00+00'),
(7, 38000.00, 6840.00, 44840.00, '2026-01-20', 'Pending', 'Invoice #INV-2025-007. Penthouse design project ongoing. Final payment due 30 days after completion. Premium project.', '2025-12-15 11:00:00+00'),
(4, 10600.00, 1908.00, 12508.00, '2025-12-10', 'Overdue', 'Invoice #INV-2025-004. Kitchen renovation project. Payment overdue. Please contact accounts department. Late payment charges applicable.', '2025-11-05 10:00:00+00'),
(12, 8500.00, 1530.00, 10030.00, '2026-01-30', 'Pending', 'Invoice #INV-2025-012. Bathroom spa upgrade project. 40% advance received. Balance due on completion. Bank details: HDFC Bank', '2025-12-25 14:00:00+00');

-- ----------------------------------------
-- Config (System Configuration)
-- ----------------------------------------
INSERT INTO config (key, value) VALUES
('latest_deal_no', '0000-0020'),
('gst_rate', '0.18'),
('company_name', 'Interior Design Solutions Pvt Ltd'),
('company_email', 'info@interiordesign.com'),
('company_phone', '+91-9876543200');

-- ========================================================================================================
-- CREATE INDEXES FOR PERFORMANCE OPTIMIZATION
-- ========================================================================================================

-- Users table indexes
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_email_is_active ON users(email, is_active) WHERE email IS NOT NULL;
CREATE INDEX idx_users_is_active ON users(is_active);
CREATE INDEX idx_users_created_at ON users(created_at DESC);

-- User tokens indexes
CREATE INDEX idx_user_tokens_user_id ON user_tokens(user_id);
CREATE INDEX idx_user_tokens_access_token ON user_tokens(access_token);
CREATE INDEX idx_user_tokens_refresh_token ON user_tokens(refresh_token);
CREATE INDEX idx_user_tokens_active ON user_tokens(is_revoked, refresh_token_expires_at);

-- User sessions indexes
CREATE INDEX idx_user_sessions_user_active ON user_sessions(user_id, is_active);
CREATE INDEX idx_user_sessions_token_id ON user_sessions(token_id);

-- Tasks table indexes
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_created_by ON tasks(created_by);
CREATE INDEX idx_tasks_client_id ON tasks(client_id);
CREATE INDEX idx_tasks_designer_id ON tasks(designer_id);
CREATE INDEX idx_tasks_agency_id ON tasks(agency_id);
CREATE INDEX idx_tasks_created_at ON tasks(created_at DESC);
CREATE INDEX idx_tasks_due_date ON tasks(due_date);
CREATE INDEX idx_tasks_deal_no ON tasks(deal_no);
CREATE INDEX idx_tasks_status_created_at ON tasks(status, created_at DESC);
CREATE INDEX idx_tasks_client_created_by ON tasks(client_id, created_by);

-- Task messages indexes
CREATE INDEX idx_task_messages_task_id ON task_messages(task_id);
CREATE INDEX idx_task_messages_user_id ON task_messages(user_id);
CREATE INDEX idx_task_messages_task_created ON task_messages(task_id, created_at DESC);

-- Task timelines indexes
CREATE INDEX idx_task_timelines_task_id ON task_timelines(task_id);
CREATE INDEX idx_task_timelines_user_id ON task_timelines(user_id);
CREATE INDEX idx_task_timelines_task_created ON task_timelines(task_id, created_at DESC);

-- Task attachments indexes
CREATE INDEX idx_task_attachments_task_id ON task_attachments(task_id);
CREATE INDEX idx_task_attachments_uploaded_by ON task_attachments(uploaded_by);

-- Task services indexes
CREATE INDEX idx_task_services_task_id ON task_services(task_id);
CREATE INDEX idx_task_services_service_master_id ON task_services(service_master_id);

-- Task users indexes
CREATE INDEX idx_task_users_user_id ON task_users(user_id);

-- Clients table indexes
CREATE INDEX idx_clients_name ON clients(name);
CREATE INDEX idx_clients_created_at ON clients(created_at DESC);

-- Designers table indexes
CREATE INDEX idx_designers_name ON designers(name);
CREATE INDEX idx_designers_created_at ON designers(created_at DESC);

-- Quotes table indexes
CREATE INDEX idx_quotes_task_id ON quotes(task_id);
CREATE INDEX idx_quotes_created_at ON quotes(created_at DESC);

-- Bills table indexes
CREATE INDEX idx_bills_task_id ON bills(task_id);
CREATE INDEX idx_bills_status ON bills(status);
CREATE INDEX idx_bills_created_at ON bills(created_at DESC);

-- Measurements table indexes
CREATE INDEX idx_measurements_task_id ON measurements(task_id);

-- ========================================================================================================
-- VERIFICATION QUERIES
-- ========================================================================================================

-- Count records in each table
SELECT 'users' as table_name, COUNT(*) as record_count FROM users
UNION ALL
SELECT 'user_tokens', COUNT(*) FROM user_tokens
UNION ALL
SELECT 'user_sessions', COUNT(*) FROM user_sessions
UNION ALL
SELECT 'clients', COUNT(*) FROM clients
UNION ALL
SELECT 'designers', COUNT(*) FROM designers
UNION ALL
SELECT 'service_master', COUNT(*) FROM service_master
UNION ALL
SELECT 'tasks', COUNT(*) FROM tasks
UNION ALL
SELECT 'measurements', COUNT(*) FROM measurements
UNION ALL
SELECT 'task_services', COUNT(*) FROM task_services
UNION ALL
SELECT 'task_messages', COUNT(*) FROM task_messages
UNION ALL
SELECT 'task_timelines', COUNT(*) FROM task_timelines
UNION ALL
SELECT 'task_attachments', COUNT(*) FROM task_attachments
UNION ALL
SELECT 'task_users', COUNT(*) FROM task_users
UNION ALL
SELECT 'quotes', COUNT(*) FROM quotes
UNION ALL
SELECT 'bills', COUNT(*) FROM bills
UNION ALL
SELECT 'config', COUNT(*) FROM config;

-- User distribution by role
SELECT 
    role,
    COUNT(*) as user_count,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as percentage
FROM users
GROUP BY role
ORDER BY user_count DESC;

-- Task distribution by status
SELECT 
    status,
    COUNT(*) as task_count,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as percentage
FROM tasks
GROUP BY status
ORDER BY task_count DESC;

-- Bill status distribution
SELECT 
    status,
    COUNT(*) as bill_count,
    SUM(total) as total_amount
FROM bills
GROUP BY status
ORDER BY bill_count DESC;

-- Active user sessions
SELECT 
    COUNT(*) as active_sessions,
    COUNT(DISTINCT user_id) as unique_users
FROM user_sessions
WHERE is_active = TRUE;

-- Foreign key relationship verification
SELECT 
    'Tasks with valid creators' as check_name,
    COUNT(*) as count
FROM tasks t
WHERE t.created_by IN (SELECT user_id FROM users)
UNION ALL
SELECT 
    'Tasks with valid clients',
    COUNT(*)
FROM tasks t
WHERE t.client_id IN (SELECT client_id FROM clients)
UNION ALL
SELECT 
    'Tasks with valid designers',
    COUNT(*)
FROM tasks t
WHERE t.designer_id IN (SELECT designer_id FROM designers)
UNION ALL
SELECT 
    'Tasks with valid agents',
    COUNT(*)
FROM tasks t
WHERE t.agency_id IN (SELECT user_id FROM users WHERE role = 'agent');

-- ========================================================================================================
-- DATABASE MIGRATION COMPLETED SUCCESSFULLY
-- ========================================================================================================

-- Summary Information
DO $$
BEGIN
    RAISE NOTICE '';
    RAISE NOTICE '========================================================================================================';
    RAISE NOTICE 'DATABASE MIGRATION COMPLETED SUCCESSFULLY';
    RAISE NOTICE '========================================================================================================';
    RAISE NOTICE '';
    RAISE NOTICE 'Database: Interior Design & Task Management System';
    RAISE NOTICE 'PostgreSQL Version: 12+';
    RAISE NOTICE 'Migration Date: 2025-12-27';
    RAISE NOTICE '';
    RAISE NOTICE 'Tables Created: 17';
    RAISE NOTICE 'Enum Types: 4';
    RAISE NOTICE 'Indexes Created: 40+';
    RAISE NOTICE '';
    RAISE NOTICE 'Sample Data Loaded:';
    RAISE NOTICE '  - Users: 12 (3 admin, 4 salesperson, 5 agent)';
    RAISE NOTICE '  - User Tokens: 8';
    RAISE NOTICE '  - User Sessions: 10';
    RAISE NOTICE '  - Clients: 10';
    RAISE NOTICE '  - Designers: 8';
    RAISE NOTICE '  - Services: 8';
    RAISE NOTICE '  - Tasks: 20';
    RAISE NOTICE '  - Measurements: 15';
    RAISE NOTICE '  - Task Services: 25';
    RAISE NOTICE '  - Task Messages: 30';
    RAISE NOTICE '  - Task Timelines: 25';
    RAISE NOTICE '  - Task Users: 20';
    RAISE NOTICE '  - Task Attachments: 12';
    RAISE NOTICE '  - Quotes: 10';
    RAISE NOTICE '  - Bills: 8';
    RAISE NOTICE '';
    RAISE NOTICE 'Features Implemented:';
    RAISE NOTICE '  ✓ JWT Token Authentication (access + refresh tokens)';
    RAISE NOTICE '  ✓ User Session Tracking';
    RAISE NOTICE '  ✓ Role-based Access (admin, salesperson, agent)';
    RAISE NOTICE '  ✓ Comprehensive Indexing for Performance';
    RAISE NOTICE '  ✓ Foreign Key Constraints with Cascading';
    RAISE NOTICE '  ✓ Realistic Dummy Data with Proper Relationships';
    RAISE NOTICE '  ✓ Check Constraints for Data Integrity';
    RAISE NOTICE '  ✓ PostgreSQL-specific Optimizations';
    RAISE NOTICE '';
    RAISE NOTICE 'Default User Credentials (password format: Name@123):';
    RAISE NOTICE '  Admin: admin@company.com / Admin@123';
    RAISE NOTICE '  Salesperson: john.sales@company.com / John@123';
    RAISE NOTICE '  Agent: alice@agency.com / Alice@123';
    RAISE NOTICE '';
    RAISE NOTICE 'All passwords are bcrypt hashed for security.';
    RAISE NOTICE '';
    RAISE NOTICE '========================================================================================================';
END $$;
