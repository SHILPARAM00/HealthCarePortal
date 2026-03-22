-- =====================================
-- Database: healthcare_portal
-- =====================================
CREATE DATABASE IF NOT EXISTS healthcare_portal;
USE healthcare_portal;

-- =====================================
-- Table: users
-- Common table for all users (admin, doctor, patient)
-- =====================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'doctor', 'patient') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================
-- Table: doctors
-- Stores doctor-specific info
-- =====================================
CREATE TABLE doctors (
    doctor_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    specialization VARCHAR(100),
    phone VARCHAR(15),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- =====================================
-- Table: patients
-- Stores patient-specific info
-- =====================================
CREATE TABLE patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    phone VARCHAR(15),
    address VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- =====================================
-- Table: appointments
-- Booking info between patients and doctors
-- =====================================
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATETIME NOT NULL,
    status ENUM('pending', 'approved', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE
);

-- =====================================
-- Table: prescriptions
-- Doctor prescriptions for patients
-- =====================================
CREATE TABLE prescriptions (
    prescription_id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    medication TEXT NOT NULL,
    dosage TEXT,
    instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE
);

-- =====================================
-- Table: reports
-- Medical reports uploaded for patients
-- =====================================
CREATE TABLE reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT,
    report_name VARCHAR(255) NOT NULL,
    report_file VARCHAR(255) NOT NULL, -- store filename/path
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE SET NULL
);

-- =====================================
-- Sample Relationships Overview
-- =====================================
-- 1. users (1) -> doctors (1)
-- 2. users (1) -> patients (1)
-- 3. doctors (1) -> appointments (many)
-- 4. patients (1) -> appointments (many)
-- 5. appointments (1) -> prescriptions (1 or many)
-- 6. patients (1) -> reports (many)
-- 7. doctors (optional) -> reports (many)