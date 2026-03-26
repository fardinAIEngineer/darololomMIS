CREATE DATABASE IF NOT EXISTS darololom_php CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE darololom_php;

CREATE TABLE IF NOT EXISTS study_levels (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS semesters (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    number TINYINT UNSIGNED NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS course_periods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    number TINYINT UNSIGNED NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS school_classes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    level_id INT UNSIGNED NULL,
    semester_id INT UNSIGNED NULL,
    period_id INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_school_classes_level FOREIGN KEY (level_id) REFERENCES study_levels(id) ON DELETE SET NULL,
    CONSTRAINT fk_school_classes_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL,
    CONSTRAINT fk_school_classes_period FOREIGN KEY (period_id) REFERENCES course_periods(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS subjects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    level_id INT UNSIGNED NULL,
    semester TINYINT UNSIGNED NOT NULL DEFAULT 1,
    period_id INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_subjects_level FOREIGN KEY (level_id) REFERENCES study_levels(id) ON DELETE SET NULL,
    CONSTRAINT fk_subjects_period FOREIGN KEY (period_id) REFERENCES course_periods(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teachers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    father_name VARCHAR(255) NULL,
    birth_date DATE NULL,
    permanent_address TEXT NULL,
    current_address TEXT NULL,
    village VARCHAR(150) NULL,
    district VARCHAR(150) NULL,
    area VARCHAR(150) NULL,
    gender ENUM('male', 'female') NOT NULL DEFAULT 'male',
    education_level ENUM('p', 'b', 'm', 'd') NOT NULL DEFAULT 'p',
    id_number VARCHAR(100) NULL,
    image_path VARCHAR(255) NULL,
    plan_file VARCHAR(255) NULL,
    education_document VARCHAR(255) NULL,
    experience_document VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teacher_class (
    teacher_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (teacher_id, class_id),
    CONSTRAINT fk_teacher_class_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    CONSTRAINT fk_teacher_class_class FOREIGN KEY (class_id) REFERENCES school_classes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teacher_subject (
    teacher_id INT UNSIGNED NOT NULL,
    subject_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (teacher_id, subject_id),
    CONSTRAINT fk_teacher_subject_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    CONSTRAINT fk_teacher_subject_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teacher_level (
    teacher_id INT UNSIGNED NOT NULL,
    level_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (teacher_id, level_id),
    CONSTRAINT fk_teacher_level_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    CONSTRAINT fk_teacher_level_level FOREIGN KEY (level_id) REFERENCES study_levels(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teacher_semester (
    teacher_id INT UNSIGNED NOT NULL,
    semester_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (teacher_id, semester_id),
    CONSTRAINT fk_teacher_semester_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    CONSTRAINT fk_teacher_semester_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teacher_period (
    teacher_id INT UNSIGNED NOT NULL,
    period_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (teacher_id, period_id),
    CONSTRAINT fk_teacher_period_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    CONSTRAINT fk_teacher_period_period FOREIGN KEY (period_id) REFERENCES course_periods(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teacher_contracts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT UNSIGNED NOT NULL UNIQUE,
    contract_number VARCHAR(100) NOT NULL UNIQUE,
    contract_date DATE NULL,
    monthly_salary VARCHAR(100) NULL,
    position VARCHAR(150) NULL,
    notes LONGTEXT NULL,
    signed_file VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_teacher_contract_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS students (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    father_name VARCHAR(255) NULL,
    grandfather_name VARCHAR(255) NULL,
    birth_date DATE NULL,
    id_number VARCHAR(100) NULL,
    exam_number VARCHAR(100) NULL,
    gender ENUM('male', 'female') NOT NULL DEFAULT 'male',
    current_address TEXT NULL,
    village VARCHAR(150) NULL,
    district VARCHAR(150) NULL,
    area VARCHAR(150) NULL,
    time_start TIME NULL,
    time_end TIME NULL,
    permanent_address TEXT NULL,
    school_class_id INT UNSIGNED NULL,
    mobile_number VARCHAR(20) NULL,
    image_path VARCHAR(255) NULL,
    certificate_file VARCHAR(255) NULL,
    level_id INT UNSIGNED NULL,
    is_grade12_graduate TINYINT(1) NOT NULL DEFAULT 0,
    is_graduated TINYINT(1) NOT NULL DEFAULT 0,
    certificate_number VARCHAR(50) NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_students_class FOREIGN KEY (school_class_id) REFERENCES school_classes(id) ON DELETE SET NULL,
    CONSTRAINT fk_students_level FOREIGN KEY (level_id) REFERENCES study_levels(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_semester (
    student_id INT UNSIGNED NOT NULL,
    semester_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (student_id, semester_id),
    CONSTRAINT fk_student_semester_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_student_semester_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_period (
    student_id INT UNSIGNED NOT NULL,
    period_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (student_id, period_id),
    CONSTRAINT fk_student_period_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_student_period_period FOREIGN KEY (period_id) REFERENCES course_periods(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_scores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    subject_id INT UNSIGNED NOT NULL,
    score TINYINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_student_subject (student_id, subject_id),
    CONSTRAINT fk_student_scores_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_student_scores_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_behaviors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    entry_type ENUM('violation', 'merit') NOT NULL,
    note VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_student_behaviors_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teacher_behaviors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT UNSIGNED NOT NULL,
    entry_type ENUM('violation', 'merit') NOT NULL,
    note VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_teacher_behaviors_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO study_levels (code, name) VALUES
    ('aali', 'عالی'),
    ('moteseta', 'متوسطه'),
    ('ebtedai', 'ابتداییه')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO semesters (number) VALUES (1), (2), (3), (4)
ON DUPLICATE KEY UPDATE number = VALUES(number);

INSERT INTO course_periods (number) VALUES (1), (2), (3), (4), (5), (6)
ON DUPLICATE KEY UPDATE number = VALUES(number);
