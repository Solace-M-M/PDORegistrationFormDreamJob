-- ============================================================
-- Game Developer Job Application System - database schema
-- Import with phpMyAdmin (Import tab) or: mysql -u root < sql/schema.sql
-- ============================================================
CREATE DATABASE IF NOT EXISTS game_dev_applications
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE game_dev_applications;

DROP TABLE IF EXISTS applicants;
CREATE TABLE applicants (
    applicant_id          INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    first_name            VARCHAR(50)   NOT NULL,
    last_name             VARCHAR(50)   NOT NULL,
    email                 VARCHAR(100)  NOT NULL UNIQUE,
    phone_number          VARCHAR(20)   NULL,
    specialization        VARCHAR(50)   NOT NULL,   -- e.g. Gameplay Programmer
    game_engine           VARCHAR(30)   NOT NULL,   -- e.g. Unity, Unreal Engine, Godot
    programming_languages VARCHAR(255)  NOT NULL,   -- e.g. C#, C++
    years_experience      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    portfolio_url         VARCHAR(255)  NULL,
    expected_salary       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    application_status    VARCHAR(20)   NOT NULL DEFAULT 'Pending',
    date_added            DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
