-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2025 at 11:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jira_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `issue`
--

CREATE TABLE `issue` (
  `id` int(11) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membership`
--

CREATE TABLE `membership` (
  `id` int(100) DEFAULT NULL,
  `pro_id` int(100) DEFAULT NULL,
  `user_id` int(100) DEFAULT NULL,
  `role_id` int(100) NOT NULL,
  `is_active` tinyint(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `membership`
--

INSERT INTO `membership` (`id`, `pro_id`, `user_id`, `role_id`, `is_active`) VALUES
(NULL, 1, 1, 10, 1),
(NULL, 3, 2, 12, 1),
(NULL, 7, 3, 4, 1),
(NULL, 3, 4, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset`
--

CREATE TABLE `password_reset` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `id` int(100) NOT NULL,
  `key_code` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `client` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `Created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `Updated_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project`
--

INSERT INTO `project` (`id`, `key_code`, `name`, `description`, `client`, `start_date`, `end_date`, `Created_at`, `Updated_at`) VALUES
(1, 'PROJ', 'run database', '', 'Vihar joshi', '2025-11-09', '2025-11-29', '2025-11-09 12:14:03.902151', '2025-11-09 12:14:03.902151'),
(3, 'PROJECT3', 'Mechanical', 'qwertyuiopasdfghjklzxcvbnm', 'Vihar joshi', '2025-11-01', '2025-11-15', '2025-11-09 16:06:41.432851', '2025-11-09 16:06:41.432851'),
(4, 'P-1', 'Web Development', 'All Coding Website Will Be here|', 'Priyal_Kansara', '2025-10-01', '2025-11-01', '2025-11-09 17:01:07.200961', '2025-11-09 17:01:07.200961'),
(5, 'P-2', 'Fronted', 'qwewqeq', 'priyal', '2025-11-01', '2025-11-02', '2025-11-09 17:19:23.380637', '2025-11-09 17:19:23.380637'),
(6, 'HJJJ', 'ghg', '', '', '0000-00-00', '0000-00-00', '2025-11-18 08:37:27.692601', '2025-11-18 08:37:27.692601'),
(7, 'PRO123', 'Management', 'hdghjjsdghjg', 'Ronakbhai', '2025-11-01', '2025-11-30', '2025-11-22 15:58:26.557462', '2025-11-22 15:58:26.557462'),
(8, 'PROR1', 'Mechanical', 'hkhkjshdakjhshcbhjbhurgfhdjkfbcjhahiu', 'Ravibhai', '2025-11-25', '2025-12-30', '2025-11-25 08:17:54.790849', '2025-11-25 08:17:54.790849');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `name`) VALUES
(1, 'Admin'),
(2, 'Project Manager'),
(3, 'Developer'),
(4, 'Tester'),
(5, 'Reporter'),
(6, 'Viewer');

-- --------------------------------------------------------

--
-- Table structure for table `task`
--

CREATE TABLE `task` (
  `id` int(11) NOT NULL,
  `pro_id` int(11) NOT NULL,
  `task_key` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('Bug','Task','Story','Improvement') DEFAULT 'Task',
  `priority` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `status` enum('Backlog','To Do','In Progress','In Review','Done','Closed') DEFAULT 'Backlog',
  `reporter_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `due_date` date DEFAULT NULL,
  `estmt_hour` decimal(6,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task`
--

INSERT INTO `task` (`id`, `pro_id`, `task_key`, `title`, `description`, `type`, `priority`, `status`, `reporter_id`, `created_at`, `updated_at`, `due_date`, `estmt_hour`) VALUES
(9, 7, '123', 'hkdsghjh', 'ghgcxg', 'Task', 'Medium', 'Closed', 3, '2025-11-22 15:59:48', '2025-11-22 16:00:33', '2025-11-30', 3.00),
(10, 1, 'PRO1-34599-02ee', 'SQL Query', 'hjsdhahshsgj', 'Task', 'Low', 'In Progress', 3, '2025-11-25 01:36:39', '2025-11-25 01:36:39', '2025-11-27', 4.00),
(11, 8, 'R1', 'jojjdhh', 'hjhjkhckbbjk', 'Task', 'Critical', 'In Progress', 4, '2025-11-25 08:18:55', '2025-11-25 08:18:55', '2025-12-31', 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `task_comments`
--

CREATE TABLE `task_comments` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(100) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `psw` varchar(300) NOT NULL,
  `reset_otp` varchar(6) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `dp_name` varchar(100) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `Created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `Updated_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `Username`, `email`, `psw`, `reset_otp`, `reset_expires`, `dp_name`, `phone`, `Created_at`, `Updated_at`) VALUES
(1, 'Joshi_vihar', 'viharjoshi9678@gmail.com', '$2y$10$Aym2TL/FKx1l7fbapzUhou/knO.zXESgheifaEvH8BQa3AqG0EASu', NULL, NULL, 'Heyyyy Joshiii!!!!!!!!!!', '9687830169', '2025-11-19 15:39:18.200897', '2025-11-19 15:39:18.200897'),
(2, 'Saniya', 'saniyasaiyed9897@gmail.com', '$2y$10$3.Qn/BdwnVchqK3Laao9tuEFZ0lJuEGIZ0lPOy/jfLd1kjzzJ6Vjq', NULL, NULL, 'SANIYA_______', '9999802367', '2025-11-20 05:46:04.812722', '2025-11-20 05:46:04.812722'),
(3, 'Ronak_12', 'ronakpokar464@gmail.com', '$2y$10$tT3mxkzOgppBV6jVi5aQ2.tn7bqyIKwyqebLayOtiY7c70gRrVc1m', NULL, NULL, 'RP', '9876543210', '2025-11-22 15:56:56.348357', '2025-11-22 15:56:56.348357'),
(4, 'Rana Rudra', 'ravirana@gmail.com', '$2y$10$Eri.1l/zG/7SE6HZckdRUOSrBjbJshVCcKDvk7xy/.WdlrjdVnKpO', '841953', '2025-11-25 09:35:12', 'CR', '8866599600', '2025-11-25 08:15:17.521606', '2025-11-25 08:15:17.521606');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `issue`
--
ALTER TABLE `issue`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_issue_user` (`issue_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task`
--
ALTER TABLE `task`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_project_issuekey` (`pro_id`,`task_key`),
  ADD KEY `reporter_id` (`reporter_id`);

--
-- Indexes for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `issue`
--
ALTER TABLE `issue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `task`
--
ALTER TABLE `task`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `task_comments`
--
ALTER TABLE `task_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `issue`
--
ALTER TABLE `issue`
  ADD CONSTRAINT `issue_ibfk_1` FOREIGN KEY (`issue_id`) REFERENCES `issue` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `issue_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD CONSTRAINT `password_reset_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task`
--
ALTER TABLE `task`
  ADD CONSTRAINT `task_ibfk_1` FOREIGN KEY (`pro_id`) REFERENCES `project` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_ibfk_2` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD CONSTRAINT `task_comments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
