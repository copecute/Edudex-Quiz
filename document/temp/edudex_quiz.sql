-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 15, 2025 at 09:56 PM
-- Server version: 8.0.30
-- PHP Version: 8.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `edudex_quiz`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên người dùng',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Địa chỉ email',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mật khẩu',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` tinyint NOT NULL DEFAULT '0' COMMENT '0: CBCT, 1: Giáo viên, 2: Admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `account_infos`
--

CREATE TABLE `account_infos` (
  `id` bigint UNSIGNED NOT NULL,
  `account_id` bigint UNSIGNED NOT NULL,
  `fullName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Họ và tên=',
  `birthday` date DEFAULT NULL COMMENT 'Ngày sinh',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ảnh đại diện',
  `gender` tinyint(1) NOT NULL COMMENT 'Giới tính',
  `phoneNumber` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Số điện thoại',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Địa chỉ',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `answers`
--

CREATE TABLE `answers` (
  `id` bigint UNSIGNED NOT NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nội dung đáp án',
  `link_media` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Link media (hình ảnh/video)',
  `is_correct` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Là đáp án đúng',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `duration` int NOT NULL COMMENT 'Thời gian làm bài (phút)',
  `total_questions` int NOT NULL,
  `subject_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `easy_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `medium_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `hard_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_periods`
--

CREATE TABLE `exam_periods` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên kỳ thi',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả',
  `start_time` datetime NOT NULL COMMENT 'Thời gian bắt đầu',
  `end_time` datetime NOT NULL COMMENT 'Thời gian kết thúc',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Trạng thái',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_period_proctors`
--

CREATE TABLE `exam_period_proctors` (
  `id` bigint UNSIGNED NOT NULL,
  `exam_period_id` bigint UNSIGNED NOT NULL,
  `account_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_period_rooms`
--

CREATE TABLE `exam_period_rooms` (
  `id` bigint UNSIGNED NOT NULL,
  `exam_period_id` bigint UNSIGNED NOT NULL,
  `room_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_period_room_students`
--

CREATE TABLE `exam_period_room_students` (
  `id` bigint UNSIGNED NOT NULL,
  `exam_period_id` bigint UNSIGNED NOT NULL,
  `exam_period_subject_id` bigint UNSIGNED NOT NULL,
  `exam_period_subject_student_id` bigint UNSIGNED NOT NULL,
  `exam_period_room_id` bigint UNSIGNED NOT NULL,
  `exam_shift_id` bigint UNSIGNED NOT NULL,
  `seat_number` int NOT NULL COMMENT 'Số ghế trong phòng',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_period_subjects`
--

CREATE TABLE `exam_period_subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `exam_period_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `exam_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_period_subject_shifts`
--

CREATE TABLE `exam_period_subject_shifts` (
  `id` bigint UNSIGNED NOT NULL,
  `exam_period_subject_id` bigint UNSIGNED NOT NULL,
  `exam_shift_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_period_subject_students`
--

CREATE TABLE `exam_period_subject_students` (
  `id` bigint UNSIGNED NOT NULL,
  `exam_period_id` bigint UNSIGNED NOT NULL,
  `exam_period_subject_id` bigint UNSIGNED NOT NULL,
  `exam_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Số báo danh',
  `student_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã sinh viên',
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Họ và tên thí sinh',
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Số điện thoại',
  `address` text COLLATE utf8mb4_unicode_ci COMMENT 'Địa chỉ',
  `birthday` date DEFAULT NULL COMMENT 'Ngày sinh',
  `gender` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'true: Nam, false: Nữ',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

CREATE TABLE `exam_results` (
  `id` bigint UNSIGNED NOT NULL,
  `exam_period_id` bigint UNSIGNED NOT NULL,
  `exam_shift_id` bigint UNSIGNED NOT NULL,
  `exam_period_subject_id` bigint UNSIGNED NOT NULL,
  `exam_id` bigint UNSIGNED NOT NULL,
  `exam_period_room_id` bigint UNSIGNED NOT NULL,
  `exam_period_proctor_id` bigint UNSIGNED NOT NULL,
  `exam_period_subject_student_id` bigint UNSIGNED NOT NULL,
  `exam_period_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã kỳ thi',
  `exam_shift_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã ca thi',
  `exam_subject_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã môn thi',
  `exam_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã đề thi',
  `room_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã phòng',
  `proctor_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã người coi thi',
  `student_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã sinh viên',
  `correct_answers` int NOT NULL COMMENT 'Số câu trả lời đúng',
  `correct_answers_after_review` int DEFAULT NULL COMMENT 'Số câu đúng sau phúc khảo',
  `total_questions` int NOT NULL COMMENT 'Tổng số câu hỏi',
  `score` decimal(5,2) NOT NULL COMMENT 'Điểm số',
  `score_after_review` decimal(5,2) DEFAULT NULL COMMENT 'Điểm sau phúc khảo',
  `note` text COLLATE utf8mb4_unicode_ci COMMENT 'Ghi chú',
  `review_note` text COLLATE utf8mb4_unicode_ci,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint UNSIGNED DEFAULT NULL,
  `log_file` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'File log .edudex base64',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_shifts`
--

CREATE TABLE `exam_shifts` (
  `id` bigint UNSIGNED NOT NULL,
  `exam_period_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên ca thi',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả',
  `start_time` datetime NOT NULL COMMENT 'Thời gian bắt đầu',
  `end_time` datetime NOT NULL COMMENT 'Thời gian kết thúc',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Trạng thái',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_shift_rooms`
--

CREATE TABLE `exam_shift_rooms` (
  `id` bigint UNSIGNED NOT NULL,
  `exam_shift_id` bigint UNSIGNED NOT NULL,
  `exam_period_room_id` bigint UNSIGNED NOT NULL,
  `exam_period_subject_id` bigint UNSIGNED DEFAULT NULL,
  `exam_period_proctor_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_tags`
--

CREATE TABLE `exam_tags` (
  `id` bigint UNSIGNED NOT NULL,
  `exam_id` bigint UNSIGNED NOT NULL,
  `tag_id` bigint UNSIGNED NOT NULL,
  `num_questions` int NOT NULL,
  `easy_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `medium_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `hard_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculties`
--

CREATE TABLE `faculties` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã khoa',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên khoa',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `majors`
--

CREATE TABLE `majors` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã ngành',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên ngành',
  `faculty_id` bigint UNSIGNED NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` bigint UNSIGNED NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nội dung câu hỏi',
  `link_media` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Link media (hình ảnh/video)',
  `subject_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `difficulty` enum('easy','medium','hard') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Độ khó',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_tag`
--

CREATE TABLE `question_tag` (
  `question_id` bigint UNSIGNED NOT NULL,
  `tag_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `facility_id` bigint UNSIGNED NOT NULL,
  `capacity` int NOT NULL DEFAULT '20',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã môn học',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên môn học',
  `credits` int NOT NULL COMMENT 'Số tín chỉ',
  `major_id` bigint UNSIGNED NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `accounts_username_unique` (`username`),
  ADD UNIQUE KEY `accounts_email_unique` (`email`);

--
-- Indexes for table `account_infos`
--
ALTER TABLE `account_infos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_infos_account_id_foreign` (`account_id`);

--
-- Indexes for table `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `answers_question_id_foreign` (`question_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exams_subject_code_foreign` (`subject_code`);

--
-- Indexes for table `exam_periods`
--
ALTER TABLE `exam_periods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exam_period_proctors`
--
ALTER TABLE `exam_period_proctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_period_proctors_exam_period_id_account_id_unique` (`exam_period_id`,`account_id`),
  ADD KEY `exam_period_proctors_account_id_foreign` (`account_id`);

--
-- Indexes for table `exam_period_rooms`
--
ALTER TABLE `exam_period_rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_period_rooms_exam_period_id_room_id_unique` (`exam_period_id`,`room_id`),
  ADD KEY `exam_period_rooms_room_id_foreign` (`room_id`);

--
-- Indexes for table `exam_period_room_students`
--
ALTER TABLE `exam_period_room_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_shift` (`exam_shift_id`,`exam_period_subject_student_id`),
  ADD UNIQUE KEY `unique_seat_room_shift` (`exam_period_room_id`,`seat_number`,`exam_shift_id`),
  ADD UNIQUE KEY `unique_student_subject_exam` (`exam_period_id`,`exam_period_subject_id`,`exam_period_subject_student_id`),
  ADD KEY `exam_period_room_students_exam_period_subject_id_foreign` (`exam_period_subject_id`),
  ADD KEY `exam_period_room_students_exam_period_subject_student_id_foreign` (`exam_period_subject_student_id`);

--
-- Indexes for table `exam_period_subjects`
--
ALTER TABLE `exam_period_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_period_subjects_exam_period_id_subject_id_unique` (`exam_period_id`,`subject_id`),
  ADD KEY `exam_period_subjects_subject_id_foreign` (`subject_id`),
  ADD KEY `exam_period_subjects_exam_id_foreign` (`exam_id`);

--
-- Indexes for table `exam_period_subject_shifts`
--
ALTER TABLE `exam_period_subject_shifts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_subject_shift` (`exam_period_subject_id`,`exam_shift_id`),
  ADD KEY `exam_period_subject_shifts_exam_shift_id_foreign` (`exam_shift_id`);

--
-- Indexes for table `exam_period_subject_students`
--
ALTER TABLE `exam_period_subject_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_period_subject_students_exam_code_unique` (`exam_code`),
  ADD KEY `exam_period_subject_students_exam_period_subject_id_foreign` (`exam_period_subject_id`),
  ADD KEY `exam_period_subject_students_exam_code_index` (`exam_code`),
  ADD KEY `exam_period_subject_students_student_code_index` (`student_code`),
  ADD KEY `exam_period_subject_students_full_name_index` (`full_name`),
  ADD KEY `exam_period_subject_students_exam_period_id_index` (`exam_period_id`);

--
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_subject_result` (`exam_period_id`,`exam_period_subject_id`,`exam_period_subject_student_id`),
  ADD KEY `exam_results_exam_shift_id_foreign` (`exam_shift_id`),
  ADD KEY `exam_results_exam_period_subject_id_foreign` (`exam_period_subject_id`),
  ADD KEY `exam_results_exam_id_foreign` (`exam_id`),
  ADD KEY `exam_results_exam_period_room_id_foreign` (`exam_period_room_id`),
  ADD KEY `exam_results_exam_period_proctor_id_foreign` (`exam_period_proctor_id`),
  ADD KEY `exam_results_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `exam_results_exam_period_id_exam_shift_id_index` (`exam_period_id`,`exam_shift_id`),
  ADD KEY `exam_results_exam_period_subject_student_id_index` (`exam_period_subject_student_id`),
  ADD KEY `exam_results_exam_period_code_index` (`exam_period_code`),
  ADD KEY `exam_results_student_code_index` (`student_code`);

--
-- Indexes for table `exam_shifts`
--
ALTER TABLE `exam_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_shifts_exam_period_id_foreign` (`exam_period_id`);

--
-- Indexes for table `exam_shift_rooms`
--
ALTER TABLE `exam_shift_rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_shift_room` (`exam_shift_id`,`exam_period_room_id`),
  ADD UNIQUE KEY `unique_proctor_per_shift` (`exam_shift_id`,`exam_period_proctor_id`),
  ADD KEY `exam_shift_rooms_exam_period_room_id_foreign` (`exam_period_room_id`),
  ADD KEY `exam_shift_rooms_exam_period_subject_id_foreign` (`exam_period_subject_id`),
  ADD KEY `exam_shift_rooms_exam_period_proctor_id_foreign` (`exam_period_proctor_id`);

--
-- Indexes for table `exam_tags`
--
ALTER TABLE `exam_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_tags_exam_id_foreign` (`exam_id`),
  ADD KEY `exam_tags_tag_id_foreign` (`tag_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `facilities_code_unique` (`code`);

--
-- Indexes for table `faculties`
--
ALTER TABLE `faculties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `faculties_code_unique` (`code`);

--
-- Indexes for table `majors`
--
ALTER TABLE `majors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `majors_code_unique` (`code`),
  ADD KEY `majors_faculty_id_foreign` (`faculty_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `questions_subject_code_foreign` (`subject_code`);

--
-- Indexes for table `question_tag`
--
ALTER TABLE `question_tag`
  ADD PRIMARY KEY (`question_id`,`tag_id`),
  ADD KEY `question_tag_tag_id_foreign` (`tag_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rooms_code_unique` (`code`),
  ADD KEY `rooms_facility_id_foreign` (`facility_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subjects_code_unique` (`code`),
  ADD KEY `subjects_major_id_foreign` (`major_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tags_name_subject_code_unique` (`name`,`subject_code`),
  ADD KEY `tags_subject_code_foreign` (`subject_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `account_infos`
--
ALTER TABLE `account_infos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `answers`
--
ALTER TABLE `answers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_periods`
--
ALTER TABLE `exam_periods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_period_proctors`
--
ALTER TABLE `exam_period_proctors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_period_rooms`
--
ALTER TABLE `exam_period_rooms`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_period_room_students`
--
ALTER TABLE `exam_period_room_students`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_period_subjects`
--
ALTER TABLE `exam_period_subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_period_subject_shifts`
--
ALTER TABLE `exam_period_subject_shifts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_period_subject_students`
--
ALTER TABLE `exam_period_subject_students`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_shifts`
--
ALTER TABLE `exam_shifts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_shift_rooms`
--
ALTER TABLE `exam_shift_rooms`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_tags`
--
ALTER TABLE `exam_tags`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faculties`
--
ALTER TABLE `faculties`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `majors`
--
ALTER TABLE `majors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account_infos`
--
ALTER TABLE `account_infos`
  ADD CONSTRAINT `account_infos_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `answers`
--
ALTER TABLE `answers`
  ADD CONSTRAINT `answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_subject_code_foreign` FOREIGN KEY (`subject_code`) REFERENCES `subjects` (`code`);

--
-- Constraints for table `exam_period_proctors`
--
ALTER TABLE `exam_period_proctors`
  ADD CONSTRAINT `exam_period_proctors_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_period_proctors_exam_period_id_foreign` FOREIGN KEY (`exam_period_id`) REFERENCES `exam_periods` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_period_rooms`
--
ALTER TABLE `exam_period_rooms`
  ADD CONSTRAINT `exam_period_rooms_exam_period_id_foreign` FOREIGN KEY (`exam_period_id`) REFERENCES `exam_periods` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_period_rooms_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_period_room_students`
--
ALTER TABLE `exam_period_room_students`
  ADD CONSTRAINT `exam_period_room_students_exam_period_id_foreign` FOREIGN KEY (`exam_period_id`) REFERENCES `exam_periods` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_period_room_students_exam_period_room_id_foreign` FOREIGN KEY (`exam_period_room_id`) REFERENCES `exam_period_rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_period_room_students_exam_period_subject_id_foreign` FOREIGN KEY (`exam_period_subject_id`) REFERENCES `exam_period_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_period_room_students_exam_period_subject_student_id_foreign` FOREIGN KEY (`exam_period_subject_student_id`) REFERENCES `exam_period_subject_students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_period_room_students_exam_shift_id_foreign` FOREIGN KEY (`exam_shift_id`) REFERENCES `exam_shifts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_period_subjects`
--
ALTER TABLE `exam_period_subjects`
  ADD CONSTRAINT `exam_period_subjects_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `exam_period_subjects_exam_period_id_foreign` FOREIGN KEY (`exam_period_id`) REFERENCES `exam_periods` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_period_subjects_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_period_subject_shifts`
--
ALTER TABLE `exam_period_subject_shifts`
  ADD CONSTRAINT `exam_period_subject_shifts_exam_period_subject_id_foreign` FOREIGN KEY (`exam_period_subject_id`) REFERENCES `exam_period_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_period_subject_shifts_exam_shift_id_foreign` FOREIGN KEY (`exam_shift_id`) REFERENCES `exam_shifts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_period_subject_students`
--
ALTER TABLE `exam_period_subject_students`
  ADD CONSTRAINT `exam_period_subject_students_exam_period_id_foreign` FOREIGN KEY (`exam_period_id`) REFERENCES `exam_periods` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_period_subject_students_exam_period_subject_id_foreign` FOREIGN KEY (`exam_period_subject_id`) REFERENCES `exam_period_subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD CONSTRAINT `exam_results_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`),
  ADD CONSTRAINT `exam_results_exam_period_id_foreign` FOREIGN KEY (`exam_period_id`) REFERENCES `exam_periods` (`id`),
  ADD CONSTRAINT `exam_results_exam_period_proctor_id_foreign` FOREIGN KEY (`exam_period_proctor_id`) REFERENCES `exam_period_proctors` (`id`),
  ADD CONSTRAINT `exam_results_exam_period_room_id_foreign` FOREIGN KEY (`exam_period_room_id`) REFERENCES `exam_period_rooms` (`id`),
  ADD CONSTRAINT `exam_results_exam_period_subject_id_foreign` FOREIGN KEY (`exam_period_subject_id`) REFERENCES `exam_period_subjects` (`id`),
  ADD CONSTRAINT `exam_results_exam_period_subject_student_id_foreign` FOREIGN KEY (`exam_period_subject_student_id`) REFERENCES `exam_period_subject_students` (`id`),
  ADD CONSTRAINT `exam_results_exam_shift_id_foreign` FOREIGN KEY (`exam_shift_id`) REFERENCES `exam_shifts` (`id`),
  ADD CONSTRAINT `exam_results_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `accounts` (`id`);

--
-- Constraints for table `exam_shifts`
--
ALTER TABLE `exam_shifts`
  ADD CONSTRAINT `exam_shifts_exam_period_id_foreign` FOREIGN KEY (`exam_period_id`) REFERENCES `exam_periods` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_shift_rooms`
--
ALTER TABLE `exam_shift_rooms`
  ADD CONSTRAINT `exam_shift_rooms_exam_period_proctor_id_foreign` FOREIGN KEY (`exam_period_proctor_id`) REFERENCES `exam_period_proctors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `exam_shift_rooms_exam_period_room_id_foreign` FOREIGN KEY (`exam_period_room_id`) REFERENCES `exam_period_rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_shift_rooms_exam_period_subject_id_foreign` FOREIGN KEY (`exam_period_subject_id`) REFERENCES `exam_period_subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `exam_shift_rooms_exam_shift_id_foreign` FOREIGN KEY (`exam_shift_id`) REFERENCES `exam_shifts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_tags`
--
ALTER TABLE `exam_tags`
  ADD CONSTRAINT `exam_tags_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_tags_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `majors`
--
ALTER TABLE `majors`
  ADD CONSTRAINT `majors_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_subject_code_foreign` FOREIGN KEY (`subject_code`) REFERENCES `subjects` (`code`) ON DELETE CASCADE;

--
-- Constraints for table `question_tag`
--
ALTER TABLE `question_tag`
  ADD CONSTRAINT `question_tag_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_major_id_foreign` FOREIGN KEY (`major_id`) REFERENCES `majors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tags`
--
ALTER TABLE `tags`
  ADD CONSTRAINT `tags_subject_code_foreign` FOREIGN KEY (`subject_code`) REFERENCES `subjects` (`code`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
