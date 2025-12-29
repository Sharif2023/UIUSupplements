-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 29, 2025 at 06:47 PM
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
-- Database: `uiusupplements`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(50) NOT NULL,
  `admin_email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `admin_name`, `admin_email`) VALUES
(11221078, 'Shariful Islam', 'sharifislam0505@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_logs`
--

CREATE TABLE `admin_activity_logs` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_logs`
--

INSERT INTO `admin_activity_logs` (`log_id`, `admin_id`, `action_type`, `target_type`, `target_id`, `description`, `created_at`) VALUES
(1, 11221078, 'UPDATE', 'SESSION', '1', 'Updated session status to: Rejected', '2025-12-22 13:18:44'),
(2, 11221078, 'UPDATE', 'ROOM', 'uiu111', 'Updated room: uiu111', '2025-12-22 14:33:11'),
(3, 11221078, 'UPDATE', 'ROOM', 'uiu111', 'Updated room: uiu111', '2025-12-22 14:33:17'),
(4, 11221078, 'UPDATE', 'USER', '11111111', 'Updated user: shariful Islam', '2025-12-22 14:33:45'),
(5, 11221078, 'DELETE', 'ROOM', 'uiu111', 'Deleted room: uiu111', '2025-12-24 13:03:47'),
(6, 11221078, 'CHECK_EXPIRATION', 'ROOM', 'SYSTEM', 'Checked rental expirations: 1 rooms flagged for relisting', '2025-12-24 13:04:13'),
(7, 11221078, 'CREATE', 'ROOM', 'uiu-1', 'Added new room: uiu-1 at syednagar', '2025-12-25 06:42:13'),
(8, 11221078, 'CREATE', 'ROOM', 'uiu-1', 'Added new room: uiu-1 at syednagar', '2025-12-25 06:50:18'),
(9, 11221078, 'UPDATE', 'ROOM', 'uiu-1', 'Updated room status: uiu-1', '2025-12-25 06:56:04'),
(10, 11221078, 'UPDATE', 'ROOM', 'uiu-1', 'Updated room status: uiu-1', '2025-12-25 06:56:08'),
(11, 11221078, 'CREATE', 'ROOM', 'uiu-2', 'Added new room: uiu-2 at syednagar', '2025-12-25 06:57:19'),
(12, 11221078, 'UPDATE', 'ROOM', 'uiu-2', 'Updated room status: uiu-2', '2025-12-25 07:43:15'),
(13, 11221078, 'UPDATE', 'ROOM', 'uiu-2', 'Updated room status: uiu-2', '2025-12-25 07:43:16'),
(14, 11221078, 'UPDATE', 'ROOM', 'uiu-1', 'Updated room status: uiu-1', '2025-12-25 07:43:17'),
(15, 11221078, 'UPDATE', 'ROOM', 'uiu-1', 'Updated room status: uiu-1', '2025-12-25 07:43:17'),
(16, 11221078, 'DELETE', 'MENTOR', '38', 'Deleted mentor ID: 38', '2025-12-26 17:57:45'),
(17, 11221078, 'DELETE', 'MENTOR', '37', 'Deleted mentor ID: 37', '2025-12-26 17:57:50'),
(18, 11221078, 'DELETE', 'MENTOR', '15', 'Deleted mentor ID: 15', '2025-12-26 17:57:52'),
(19, 11221078, 'DELETE', 'MENTOR', '14', 'Deleted mentor ID: 14', '2025-12-26 17:57:55'),
(20, 11221078, 'DELETE', 'MENTOR', '6', 'Deleted mentor ID: 6', '2025-12-26 17:58:06'),
(21, 11221078, 'DELETE', 'MENTOR', '12', 'Deleted mentor ID: 12', '2025-12-26 17:58:09'),
(22, 11221078, 'DELETE', 'MENTOR', '11', 'Deleted mentor ID: 11', '2025-12-26 17:58:12'),
(23, 11221078, 'DELETE', 'MENTOR', '7', 'Deleted mentor ID: 7', '2025-12-26 17:58:14'),
(24, 11221078, 'DELETE', 'MENTOR', '13', 'Deleted mentor ID: 13', '2025-12-26 17:58:21'),
(25, 11221078, 'DELETE', 'MENTOR', '39', 'Deleted mentor ID: 39', '2025-12-26 17:58:28'),
(26, 11221078, 'DELETE', 'LOST_FOUND', '1', 'Deleted lost & found item', '2025-12-26 17:58:48'),
(27, 11221078, 'DELETE', 'LOST_FOUND', '2', 'Deleted lost & found item', '2025-12-26 17:58:50'),
(28, 11221078, 'DELETE', 'DRIVER', '011221212', 'Deleted driver', '2025-12-26 17:59:58'),
(29, 11221078, 'DELETE', 'DRIVER', 'd1', 'Deleted driver', '2025-12-26 18:00:00'),
(30, 11221078, 'DELETE', 'DRIVER', 'd2', 'Deleted driver', '2025-12-26 18:00:03'),
(31, 11221078, 'DELETE', 'PRODUCT', '1', 'Deleted product', '2025-12-26 18:04:42'),
(32, 11221078, 'DELETE', 'PRODUCT', '2', 'Deleted product', '2025-12-26 18:04:44'),
(33, 11221078, 'DELETE', 'PRODUCT', '3', 'Deleted product', '2025-12-26 18:04:46'),
(34, 11221078, 'DELETE', 'USER', '11221081', 'Deleted user ID: 11221081', '2025-12-26 18:23:36'),
(35, 11221078, 'DELETE', 'ROOM', 'uiu-2', 'Deleted room: uiu-2', '2025-12-27 17:32:08'),
(36, 11221078, 'DELETE', 'ROOM', 'uiu-1', 'Deleted room: uiu-1', '2025-12-27 17:32:12'),
(37, 11221078, 'CREATE', 'ROOM', 'UIU-1', 'Added new room: UIU-1 at syednagar', '2025-12-27 18:20:41'),
(38, 11221078, 'CREATE', 'ROOM', 'UIU-2', 'Added new room: UIU-2 at Family Bazar', '2025-12-27 18:28:54'),
(39, 11221078, 'CREATE', 'ROOM', 'UIU-1', 'Added new room: UIU-1 at syednagar', '2025-12-27 18:35:23'),
(40, 11221078, 'CREATE', 'ROOM', 'UIU-2', 'Added new room: UIU-2 at Family Bazar', '2025-12-27 18:36:44'),
(41, 11221078, 'CREATE', 'ROOM', 'UIU-3', 'Added new room: UIU-3 at 10 Tola', '2025-12-27 18:42:36'),
(42, 11221078, 'CREATE', 'ROOM', 'UIU-4', 'Added new room: UIU-4 at Family Bazar', '2025-12-27 18:44:40'),
(43, 11221078, 'CREATE', 'ROOM', 'UIU-5', 'Added new room: UIU-5 at Notunbazar', '2025-12-27 18:47:00'),
(44, 11221078, 'DELETE', 'LOST_FOUND', '4', 'Deleted lost & found item', '2025-12-29 13:58:13'),
(45, 11221078, 'DELETE', 'LOST_FOUND', '3', 'Deleted lost & found item', '2025-12-29 13:58:17'),
(46, 11221078, 'CREATE', 'DRIVER', 'D01', 'Added new driver: Test', '2025-12-29 17:25:17');

-- --------------------------------------------------------

--
-- Table structure for table `availablerooms`
--

CREATE TABLE `availablerooms` (
  `serial` int(11) NOT NULL,
  `room_id` varchar(255) NOT NULL DEFAULT 'NOT NULL',
  `room_location` varchar(100) NOT NULL,
  `room_details` varchar(50) NOT NULL,
  `room_photos` varchar(1000) DEFAULT NULL,
  `available_from` date NOT NULL,
  `available_to` date DEFAULT NULL,
  `status` enum('available','not-available') NOT NULL,
  `room_rent` int(11) NOT NULL,
  `added_by_admin_id` int(11) DEFAULT NULL COMMENT 'Admin who added this room',
  `rental_rules` text DEFAULT NULL COMMENT 'Rules, regulations, payment procedures from house owner',
  `rented_to_user_id` int(11) DEFAULT NULL COMMENT 'Current tenant user ID',
  `rented_from_date` date DEFAULT NULL COMMENT 'Rental start date',
  `rented_until_date` date DEFAULT NULL COMMENT 'Rental expiry date',
  `is_relisting_pending` tinyint(1) DEFAULT 0 COMMENT 'Flag for expired rentals awaiting admin decision',
  `is_visible_to_students` tinyint(1) DEFAULT 1 COMMENT 'Visibility control for students',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Room creation timestamp',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Last update timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `availablerooms`
--

INSERT INTO `availablerooms` (`serial`, `room_id`, `room_location`, `room_details`, `room_photos`, `available_from`, `available_to`, `status`, `room_rent`, `added_by_admin_id`, `rental_rules`, `rented_to_user_id`, `rented_from_date`, `rented_until_date`, `is_relisting_pending`, `is_visible_to_students`, `created_at`, `updated_at`) VALUES
(6, 'UIU-1', 'syednagar', 'single room, 1 bath, 1 kitchen', 'uploads/rooms/UIU-1_1766860523_71075586.jpg', '2026-01-01', '2026-06-01', 'available', 6500, 11221078, 'Complete Rent till date 5.\r\nContact: 017xxxxxxxxx\r\nNo smoke, No drugs.\r\nEnter house between 11.30 PM.', NULL, NULL, NULL, 0, 1, '2025-12-27 18:35:23', '2025-12-27 18:35:23'),
(7, 'UIU-2', 'Family Bazar', 'double room, 1 coridoor, 1 washroom', 'uploads/rooms/UIU-2_1766860604_ff5af84d.jpg,uploads/rooms/UIU-2_1766860604_464181a4.jpg', '2026-01-01', '2026-06-01', 'not-available', 8500, 11221078, 'Complete Rent till date 5.\r\nContact: 018xxxxxxxxx\r\nNo smoke, No drugs.\r\nEnter house between 11.30 PM.', 11221080, '2025-12-27', '2026-01-27', 0, 0, '2025-12-27 18:36:44', '2025-12-27 18:37:21'),
(8, 'UIU-3', '10 Tola', 'single room, 1 bath, 1 kitchen', 'uploads/rooms/UIU-3_1766860956_d80fef59.jpg,uploads/rooms/UIU-3_1766860956_d60739ac.jpg', '2026-01-01', '2026-06-01', 'available', 7000, 11221078, 'Complete Rent till date 5.\r\nContact: 017xxxxxxxxx\r\nNo smoke, No drugs.\r\nEnter house between 11.30 PM.', NULL, NULL, NULL, 0, 1, '2025-12-27 18:42:36', '2025-12-27 18:42:36'),
(9, 'UIU-4', 'Family Bazar', '1 sublet', 'uploads/rooms/UIU-4_1766861080_5eff361b.jpg,uploads/rooms/UIU-4_1766861080_6b706282.jpg', '2026-02-01', '2026-05-06', 'available', 3500, 11221078, 'Complete Rent till date 5.\r\nContact: 017xxxxxxxxx\r\nNo smoke, No drugs.\r\nEnter house between 11.30 PM.', NULL, NULL, NULL, 0, 1, '2025-12-27 18:44:40', '2025-12-27 18:44:40'),
(10, 'UIU-5', 'Notunbazar', 'single room, 1 bath, 1 kitchen', 'uploads/rooms/UIU-5_1766861220_85cdaf5c.jpg,uploads/rooms/UIU-5_1766861220_553d885e.jpg', '2026-01-01', '2026-12-01', 'available', 5500, 11221078, 'Complete Rent till date 5.\r\nContact: 017xxxxxxxxx\r\nNo smoke, No drugs.\r\nEnter house between 11.30 PM.', NULL, NULL, NULL, 0, 1, '2025-12-27 18:47:00', '2025-12-27 18:47:00');

-- --------------------------------------------------------

--
-- Table structure for table `bargains`
--

CREATE TABLE `bargains` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `bargain_price` decimal(10,2) NOT NULL,
  `status` enum('pending','accepted','rejected','countered','deal_done') DEFAULT 'pending',
  `buyer_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bargains`
--

INSERT INTO `bargains` (`id`, `product_id`, `buyer_id`, `seller_id`, `bargain_price`, `status`, `buyer_message`, `created_at`, `updated_at`) VALUES
(2, 4, 11221080, 11221079, 90.00, 'accepted', '', '2025-12-27 18:10:12', '2025-12-27 18:11:32');

-- Note: Triggers removed for free hosting compatibility
-- Trigger logic is handled in PHP code instead

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `message_type` enum('text','system','payment_info','meeting_info') DEFAULT 'text',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `chat_id`, `sender_id`, `receiver_id`, `message`, `message_type`, `is_read`, `created_at`) VALUES
(6, 2, 11221080, 11221079, 'Chat started! Discuss pickup/delivery, payment method (Cash, bKash, Nagad), and meeting details.', 'system', 1, '2025-12-27 18:11:39'),
(7, 2, 11221079, 11221080, 'Collect it on time', 'text', 1, '2025-12-27 18:11:49'),
(8, 2, 11221080, 11221079, 'ok', 'text', 0, '2025-12-27 18:12:23');

-- Note: chat_messages triggers removed for free hosting compatibility

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `identification_info` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`id`, `item_id`, `user_id`, `email`, `identification_info`) VALUES
(4, NULL, '011111111', 'dsa@gmail.com', 'contact: 01855255815'),
(9, 9, '11221079', 'mhasan221079@bscse.uiu.ac.bd', 'It\'s mine when I was practicing code at there I dropped it.');

-- --------------------------------------------------------

--
-- Table structure for table `deals`
--

CREATE TABLE `deals` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `final_price` decimal(10,2) NOT NULL,
  `bargain_id` int(11) DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `seller_confirmed` tinyint(1) DEFAULT 0,
  `buyer_confirmed` tinyint(1) DEFAULT 0,
  `seller_contact` varchar(255) DEFAULT NULL,
  `buyer_contact` varchar(255) DEFAULT NULL,
  `meeting_location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deals`
--

INSERT INTO `deals` (`id`, `product_id`, `seller_id`, `buyer_id`, `final_price`, `bargain_id`, `status`, `seller_confirmed`, `buyer_confirmed`, `seller_contact`, `buyer_contact`, `meeting_location`, `notes`, `created_at`, `completed_at`) VALUES
(5, 5, 11221079, 11221080, 85.00, NULL, 'pending', 0, 0, NULL, NULL, NULL, NULL, '2025-12-27 18:09:24', NULL);

-- Note: deals triggers removed for free hosting compatibility

-- --------------------------------------------------------

--
-- Table structure for table `deal_chats`
--

CREATE TABLE `deal_chats` (
  `id` int(11) NOT NULL,
  `deal_id` int(11) DEFAULT NULL,
  `bargain_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `buyer_unread_count` int(11) DEFAULT 0,
  `seller_unread_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deal_chats`
--

INSERT INTO `deal_chats` (`id`, `deal_id`, `bargain_id`, `buyer_id`, `seller_id`, `product_id`, `last_message_at`, `buyer_unread_count`, `seller_unread_count`, `created_at`) VALUES
(2, NULL, 2, 11221080, 11221079, 4, '2025-12-27 18:12:23', 0, 1, '2025-12-27 18:11:39');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `event_name` varchar(100) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_location` varchar(100) DEFAULT NULL,
  `event_description` text DEFAULT NULL,
  `event_status` enum('upcoming','ongoing','completed') DEFAULT 'upcoming',
  `organizer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `job_type` enum('part-time','full-time','contract','internship') DEFAULT 'part-time',
  `category` varchar(100) NOT NULL,
  `salary` varchar(100) DEFAULT NULL,
  `days_per_week` int(11) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `posted_by_user_id` int(11) DEFAULT NULL,
  `posted_by_admin_id` int(11) DEFAULT NULL,
  `status` enum('active','closed','expired') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `description`, `company`, `location`, `job_type`, `category`, `salary`, `days_per_week`, `requirements`, `contact_email`, `contact_phone`, `posted_by_user_id`, `posted_by_admin_id`, `status`, `created_at`, `expires_at`) VALUES
(7, 'Teaching Assistant – SPL', 'Assist faculty members in conducting lab classes, evaluating assignments, and supporting students during lab hours. The role also includes helping prepare course materials and guiding junior students in programming fundamentals.', 'United International University (UIU)', 'UIU Campus, Dhaka', 'part-time', 'Teaching', '8,000 – 12,000 BDT / month', 3, '1. Studying or completed BSc in CSE\\n2. Good communication skills\\n3. Prior TA experience preferred\\n4. Strong knowledge of C/C++ or Python', 'hr@uiu.edu.bd', '017XXXXXXXX', NULL, 11221078, 'active', '2025-12-29 12:58:30', '2026-12-30'),
(8, 'Junior Web Developer', 'Develop and maintain responsive websites and web applications. Work closely with senior developers to implement UI features and fix bugs.', 'TechSpark Solutions', 'Remote', 'part-time', 'IT', '15,000 – 20,000 BDT / month', 5, '1. Knowledge of HTML, CSS, JavaScript\\n2. Basic understanding of React or Vue\\n3. Git & GitHub experience\\n4. Portfolio or GitHub profile preferred', 'careers@techspark.com', '018XXXXXXXX', 11221080, NULL, 'active', '2025-12-29 13:08:37', '2027-12-01'),
(9, 'Research Assistant – Electrical & Electronic Engineering', 'Assist in research related to renewable energy systems, data collection, simulation, and technical report writing.', 'Smart Energy Research Lab', 'Dhaka', 'part-time', 'Research', '10,000 – 15,000 BDT / month', 4, '1. BSc in EEE (running or completed)\\n2. Knowledge of MATLAB/Simulink\\n3. Basic research and documentation skills', 'research@smartenergy.org', '016XXXXXXXX', NULL, 11221078, 'active', '2025-12-29 13:10:32', '2027-12-01'),
(10, 'Digital Marketing Intern', 'Support digital marketing campaigns, manage social media posts, assist with content creation, and analyze engagement performance.', 'GrowthLab Agency', 'Remote', 'internship', 'Marketing', '6,000 – 8,000 BDT / month', 5, '1. Basic knowledge of social media marketing\\n2. Familiarity with Canva or similar tools\\n3. Good communication skills', 'hr@growthlab.com', '019XXXXXXXX', 11221080, NULL, 'active', '2025-12-29 13:12:28', '2027-12-01');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cover_letter` text DEFAULT NULL,
  `cv_path` varchar(500) DEFAULT NULL,
  `status` enum('pending','reviewed','accepted','rejected') DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lost_and_found`
--

CREATE TABLE `lost_and_found` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `foundPlace` varchar(255) DEFAULT NULL,
  `date_time` datetime DEFAULT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `where_now` varchar(255) DEFAULT NULL,
  `claim_status` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lost_and_found`
--

INSERT INTO `lost_and_found` (`id`, `user_id`, `email`, `category`, `image_path`, `foundPlace`, `date_time`, `contact_info`, `where_now`, `claim_status`) VALUES
(5, 11221079, 'mhasan221079@bscse.uiu.ac.bd', 'id_card', 'LostandFound/imgOfLost/lost_6952895242e220.93532329.jpg', 'Library', '2025-12-29 16:04:00', NULL, 'Room 101', 0),
(6, 11221075, 'rislam221075@bscse.uiu.ac.bd', 'others', 'LostandFound/imgOfLost/lost_695289cfd35724.71602510.webp', 'Canteen', '2025-12-28 15:16:00', NULL, 'With Me', 0),
(7, 11221075, 'rislam221075@bscse.uiu.ac.bd', 'others', 'LostandFound/imgOfLost/lost_69528a1d86cce4.84855733.webp', 'Room 631', '2025-12-27 09:07:00', NULL, 'With Me', 0),
(8, 11221075, 'rislam221075@bscse.uiu.ac.bd', 'gadgets', 'LostandFound/imgOfLost/lost_69528c6a18af75.16512528.jpg', 'Lab room 325', '2025-12-29 13:17:00', NULL, 'With Me', 0),
(9, 11221075, 'rislam221075@bscse.uiu.ac.bd', 'gadgets', 'LostandFound/imgOfLost/lost_69528cad8a17b1.11318981.jpg', 'Lab room 325', '2025-12-29 09:13:00', NULL, 'attendent', 2);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `created_at`) VALUES
(1, 11221122, 11221369, 'hii', 1, '2025-12-20 11:45:02'),
(2, 11221369, 11221122, 'hello', 1, '2025-12-20 11:46:00'),
(3, 11221122, 11221078, 'hii', 0, '2025-12-21 19:26:59');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 11221369, 'message', 'New message from Test', 'hii', 'chat.php?user=11221122', 1, '2025-12-20 11:45:02'),
(2, 11221122, 'message', 'New message from Md Shakib', 'hello', 'chat.php?user=11221369', 1, '2025-12-20 11:46:00'),
(3, 11221080, 'bargain', 'New Bargain Offer', 'You received a bargain offer of ৳250.00 on your product', 'myselllist.php?product_id=3', 1, '2025-12-23 17:06:54'),
(4, 11221079, 'bargain_countered', 'Counter Offer Received', 'The seller has made a counter offer on your bargain', 'mybargains.php?bargain_id=1', 1, '2025-12-23 17:15:46'),
(5, 11221079, 'counter_offer', 'Counter Offer Received', 'Seller offered ৳280.00 as counter offer', 'mybargains.php?bargain_id=1', 1, '2025-12-23 17:15:46'),
(6, 11221079, 'counter_offer', 'Counter Offer Received', 'Seller offered ৳260.00 as counter offer', 'mybargains.php?bargain_id=1', 1, '2025-12-23 17:20:18'),
(7, 11221079, 'bargain_accepted', 'Bargain Accepted!', 'Your bargain offer of ৳260.00 has been accepted', 'mybargains.php?bargain_id=1', 1, '2025-12-23 17:20:44'),
(8, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=1', 1, '2025-12-23 18:57:54'),
(9, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=1', 1, '2025-12-23 18:58:40'),
(10, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=1', 1, '2025-12-23 19:04:25'),
(11, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=1', 1, '2025-12-23 19:04:32'),
(12, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=1', 1, '2025-12-23 19:11:00'),
(13, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=1', 1, '2025-12-23 19:13:00'),
(14, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=1', 1, '2025-12-23 19:13:09'),
(15, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=1', 1, '2025-12-23 19:15:28'),
(16, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=1', 1, '2025-12-23 19:15:33'),
(17, 11221079, 'session_accepted', 'Session Accepted!', 'Your mentorship session with Ashiq Khan has been accepted! Check the meeting link.', 'mymentors.php', 1, '2025-12-26 13:34:27'),
(18, 11221079, 'bargain', 'New Bargain Offer', 'You received a bargain offer of ৳90.00 on your product', 'myselllist.php?product_id=4', 1, '2025-12-27 18:10:12'),
(19, 11221080, 'bargain_accepted', 'Bargain Accepted!', 'Your bargain offer of ৳90.00 has been accepted', 'mybargains.php?bargain_id=2', 1, '2025-12-27 18:11:32'),
(20, 11221080, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=2', 1, '2025-12-27 18:11:49'),
(21, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=2', 1, '2025-12-27 18:12:23'),
(22, 11221079, 'claim_approved', 'Claim Approved!', 'Your claim for the gadgets has been approved! Please contact the finder to collect your item.', 'lostandfound.php', 1, '2025-12-29 14:33:22');

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `bargain_id` int(11) NOT NULL,
  `offered_price` decimal(10,2) NOT NULL,
  `seller_message` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Note: offers triggers removed for free hosting compatibility

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `bargain_price` decimal(10,2) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('available','sold','pending') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `view_count` int(11) DEFAULT 0,
  `bargain_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `category`, `price`, `description`, `image_path`, `bargain_price`, `user_id`, `status`, `created_at`, `updated_at`, `view_count`, `bargain_count`) VALUES
(4, 'Chicken Biriyani', 'other', 100.00, 'Meet at Shuttle Line.\r\nTime: 11AM-2PM', 'imgOfSell/bir.jpg', NULL, 11221079, 'available', '2025-12-27 17:50:22', '2025-12-27 18:10:12', 0, 1),
(5, 'Bhuna Khichuri', 'other', 85.00, 'Meet at Shuttle Line between 11AM to 1PM', 'imgOfSell/Bhuna-khichuri-recipe.jpg', NULL, 11221079, 'pending', '2025-12-27 17:55:02', '2025-12-27 18:09:24', 0, 0),
(6, 'Bhuna Khichuri', 'other', 85.00, 'Collect at canteen between 11.30AM to 1.30PM', 'imgOfSell/Bhuna-khichuri-recipe.jpg', NULL, 11221079, 'available', '2025-12-28 06:58:53', '2025-12-28 06:58:53', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_views`
--

CREATE TABLE `product_views` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rentedrooms`
--

CREATE TABLE `rentedrooms` (
  `rented_room_id` varchar(255) NOT NULL DEFAULT 'NOT NULL',
  `rented_user_id` int(11) DEFAULT NULL,
  `rented_user_name` varchar(50) DEFAULT NULL,
  `rented_user_email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rentedrooms`
--

INSERT INTO `rentedrooms` (`rented_room_id`, `rented_user_id`, `rented_user_name`, `rented_user_email`) VALUES
('UIU-2', 11221080, 'Ashik Khan', 'akhan221080@bscse.uiu.ac.bd');

-- --------------------------------------------------------

--
-- Table structure for table `request_mentorship_session`
--

CREATE TABLE `request_mentorship_session` (
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mentor_id` int(11) NOT NULL,
  `session_time` varchar(255) NOT NULL,
  `session_price` varchar(255) NOT NULL,
  `communication_method` varchar(255) NOT NULL,
  `session_date` date NOT NULL,
  `problem_description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `mentor_message` text DEFAULT NULL COMMENT 'Message from mentor to student',
  `meeting_link` varchar(500) DEFAULT NULL COMMENT 'Zoom/Meet link for session',
  `responded_at` timestamp NULL DEFAULT NULL COMMENT 'When mentor responded',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sell_exchange_list`
--

CREATE TABLE `sell_exchange_list` (
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `item_photo` varchar(255) DEFAULT NULL,
  `item_status` enum('available','sold','exchanged') DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shuttle_driver`
--

CREATE TABLE `shuttle_driver` (
  `d_id` varchar(20) NOT NULL,
  `d_name` varchar(255) DEFAULT NULL,
  `d_contactNo` varchar(255) DEFAULT NULL,
  `d_password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shuttle_driver`
--

INSERT INTO `shuttle_driver` (`d_id`, `d_name`, `d_contactNo`, `d_password`) VALUES
('D01', 'Test', '+8801711111111', '123456');

-- --------------------------------------------------------

--
-- Table structure for table `total_trip`
--

CREATE TABLE `total_trip` (
  `driver_id` varchar(20) NOT NULL,
  `trip_count` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uiumentorlist`
--

CREATE TABLE `uiumentorlist` (
  `id` int(11) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `bio` text NOT NULL,
  `language` enum('Bangla','English') NOT NULL,
  `response_time` enum('6 hours','12 hours','24 hours','48 hours','72 hours') NOT NULL,
  `industry` enum('Tech','Finance','Healthcare','Marketing','Other') NOT NULL,
  `hourly_rate` varchar(50) NOT NULL,
  `company` varchar(100) DEFAULT NULL,
  `country` enum('Bangladesh','USA','UK','India','Canada') NOT NULL,
  `skills` text DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `linked_user_id` int(11) DEFAULT NULL COMMENT 'Linked user account for profile switching'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uiumentorlist`
--

INSERT INTO `uiumentorlist` (`id`, `photo`, `name`, `bio`, `language`, `response_time`, `industry`, `hourly_rate`, `company`, `country`, `skills`, `email`, `whatsapp`, `linkedin`, `facebook`, `linked_user_id`) VALUES
(40, 'uploads/mentors/mentor1767010724_m1.jpg', 'Naimur Rahman', 'Business strategist with 10+ years of experience in corporate management, startup mentoring, and SME growth planning. Specializes in marketing analytics and business development.', 'Bangla', '6 hours', 'Tech', '30 Min - 350 tk,1 Hour - 600 tk,2 Hour - 1000 tk', '', 'Bangladesh', 'Business Strategy,Marketing Management,Financial Analysis,Entrepreneurship,Team Leadership', 'naimur.bba@example.com', '+880-1000-000001', 'https://linkedin.com/in/naimur-bba', 'https://facebook.com/in/naimur-bba', 11221081),
(41, 'uploads/mentors/mentor1767011953_m2.jpg', 'Naimul Rahman', 'Software engineer and coding mentor with expertise in full-stack development and competitive programming. Passionate about mentoring students for industry readiness.', 'Bangla', '24 hours', 'Tech', '30 Min - 400 tk,1 Hour - 750 tk,2 Hour - 1000 tk,3', 'StarTech', 'Bangladesh', 'Python, Java, C++,Web Development (React, Node.js),Data Structures & Algorithms,Git & DevOps Basics', 'naimul.cse@example.com', '+880-1000-000002', 'https://linkedin.com/in/naimul-bba', 'https://facebook.com/in/naimul-bba', 11221082),
(42, 'uploads/mentors/mentor1767012215_m3.jpg', 'Mehedi Hasan', 'Electrical engineer with strong experience in power systems, renewable energy, and industrial automation. Works with final-year students on practical engineering projects.', 'English', '24 hours', 'Tech', '2 Hour - 800 tk,4 Hour - 1500 tk', '', 'Bangladesh', 'Power System Analysis,Renewable Energy Systems,MATLAB & Simulink,Electrical Machines', 'mehedi.cse@gmail.com', '+880-1000-000003', 'https://linkedin.com/in/mehedi-bba', 'https://facebook.com/in/mehedi-bba', 11221083),
(43, 'uploads/mentors/mentor1767012362_m4.jpg', 'Rasel Mia', 'HR professional and career coach focused on talent management, organizational behavior, and leadership development.', 'Bangla', '24 hours', 'Tech', '30 Min - 250 tk,1 Hour - 450 tk', '', 'Bangladesh', 'Human Resource Management,Recruitment & Training,Organizational Psychology,Corporate Communication', 'rasel.hr@gmail.com', '+880-1000-000004', 'https://linkedin.com/in/rasel-hr', 'https://facebook.com/in/rasel-hr', 11221084),
(44, 'uploads/mentors/mentor1767012518_m5.webp', 'Rafiul Islam', 'AI enthusiast and data analyst working with machine learning models and real-world datasets. Guides students in research and project-based learning.', 'Bangla', '6 hours', 'Tech', '45 Min - 300 tk,1.5 Hours - 500 tk', '', 'Bangladesh', 'Machine Learning,Python & TensorFlow,Data Analysis,Research Methodology', 'rafiul.ai@gmail.com', '+880-1000-000005', 'https://linkedin.com/in/rafiul-ai', 'https://facebook.com/in/rafiul-ai', 11221085);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `Gender` enum('m','f','o') NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `mobilenumber` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_mentor` tinyint(1) DEFAULT 0 COMMENT 'Whether user is an approved mentor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `Gender`, `password_hash`, `mobilenumber`, `created_at`, `is_mentor`) VALUES
(11221075, 'Rafiul Islam', 'rislam221075@bscse.uiu.ac.bd', 'm', '$2y$10$6tshsprijyOBFgNBbE5Rx.YxuoFz/SeR.7nx9.9CAsyZiFC6Gmlq6', '1700221075', '2025-12-28 07:10:40', 0),
(11221078, 'Shariful Islam', '011221078', 'm', '$2y$10$zAuEsUA/9M0LKmWbBRHL5Oz7n6hFc7uEIoNQtrxaxnXg5F0wKeZvW', '1700871179', '2024-10-03 18:41:30', 0),
(11221079, 'Mahmudul Hasan', 'mhasan221079@bscse.uiu.ac.bd', 'm', '$2y$10$oYCyRZNWe7mmxQFMsuYLuuxPlkMJsshQMhrUO7UjxmMgBuuGK5bvm', '1700221079', '2025-12-23 16:55:43', 0),
(11221080, 'Ashik Khan', 'akhan221080@bscse.uiu.ac.bd', 'm', '$2y$10$rKJy5xOgZ7f0sV0JOa0bCu9m00XopD.XF5Ex2vUnPGrYKnF6Dhg52', '1700221080', '2025-12-23 17:03:04', 0),
(11221081, 'Naimur Rahman', 'nrahman221081@bscse.uiu.ac.bd', 'm', '$2y$10$rypcY7wE7shYr.lrPbXrLOyxfg6Jb0OcSyGcVqR39vMHB4F3h4G26', '1700221081', '2025-12-28 07:01:17', 1),
(11221082, 'Naimul Rahman', 'nrahman221082@bscse.uiu.ac.bd', 'm', '$2y$10$MOqp8MyaVsUg1TgChB/GKeWDm41S.BgegE37OU88QNUZurH0Hf9Qe', '1700221082', '2025-12-28 07:02:19', 1),
(11221083, 'Mehedi Hasan', 'mhasan221083@bscse.uiu.ac.bd', 'm', '$2y$10$FUJqSDkNRyOyaqvsnTtdyOwJPRhAfLoEUAWTkFcJxMvEhp3gWZfrm', '1700221083', '2025-12-28 07:03:13', 1),
(11221084, 'Rasel Mia', 'rmia221084@bscse.uiu.ac.bd', 'm', '$2y$10$BJmQSu5EwI9m0Gcjlgsz9uF/rjyWGkD2M1TD4H.4IZDYdoHGomUby', '1700221084', '2025-12-28 07:04:32', 1),
(11221085, 'Ziaur Rahman', 'zrahman221085@bscse.uiu.ac.bd', 'm', '$2y$10$2kg8kXwKkbPxI1G0OgO6H.65JK4eWA4MLP1XG8cd5cGCON9uRogUe', '1700221085', '2025-12-28 07:12:47', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `user_id` int(11) NOT NULL,
  `user_photo` varchar(255) DEFAULT NULL,
  `user_bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`user_id`, `user_photo`, `user_bio`) VALUES
(11221079, 'uploads/1766906322_hasan.jpg', 'I\'m a photographer'),
(11221122, 'uploads/1766232983_IMG_99950 (1).jpg', 'I\'m Shakib'),
(11221369, 'uploads/1766233021_IMG_99950 (1).jpg', 'Hello I\'m Shakib');

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  `push_notifications` tinyint(1) DEFAULT 1,
  `profile_visibility` enum('public','private') DEFAULT 'public',
  `two_factor_auth` tinyint(1) DEFAULT 0,
  `marketing_emails` tinyint(1) DEFAULT 0,
  `show_online_status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_settings`
--

INSERT INTO `user_settings` (`user_id`, `email_notifications`, `push_notifications`, `profile_visibility`, `two_factor_auth`, `marketing_emails`, `show_online_status`) VALUES
(11221079, 1, 1, 'public', 0, 0, 1),
(11221122, 1, 1, 'public', 0, 0, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_admin_logs_admin` (`admin_id`),
  ADD KEY `idx_admin_logs_date` (`created_at`);

--
-- Indexes for table `availablerooms`
--
ALTER TABLE `availablerooms`
  ADD PRIMARY KEY (`serial`),
  ADD UNIQUE KEY `room_id` (`room_id`),
  ADD KEY `idx_admin_id` (`added_by_admin_id`),
  ADD KEY `idx_rented_user` (`rented_to_user_id`),
  ADD KEY `idx_visibility` (`is_visible_to_students`),
  ADD KEY `idx_relisting` (`is_relisting_pending`),
  ADD KEY `idx_rental_expiry` (`rented_until_date`);

--
-- Indexes for table `bargains`
--
ALTER TABLE `bargains`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `idx_bargain_status` (`status`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message_chat` (`chat_id`),
  ADD KEY `idx_message_sender` (`sender_id`),
  ADD KEY `idx_message_receiver` (`receiver_id`),
  ADD KEY `idx_message_read` (`is_read`),
  ADD KEY `idx_message_created` (`created_at`);

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `deals`
--
ALTER TABLE `deals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `bargain_id` (`bargain_id`),
  ADD KEY `idx_deal_status` (`status`);

--
-- Indexes for table `deal_chats`
--
ALTER TABLE `deal_chats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_bargain_chat` (`bargain_id`),
  ADD KEY `idx_chat_deal` (`deal_id`),
  ADD KEY `idx_chat_buyer` (`buyer_id`),
  ADD KEY `idx_chat_seller` (`seller_id`),
  ADD KEY `idx_chat_product` (`product_id`),
  ADD KEY `idx_last_message` (`last_message_at`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `organizer_id` (`organizer_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_posted_by_user` (`posted_by_user_id`),
  ADD KEY `idx_posted_by_admin` (`posted_by_admin_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_application` (`job_id`,`user_id`),
  ADD KEY `idx_job_id` (`job_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `lost_and_found`
--
ALTER TABLE `lost_and_found`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lost_found_claim` (`claim_status`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_messages_sender` (`sender_id`),
  ADD KEY `idx_messages_receiver` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`,`is_read`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bargain_id` (`bargain_id`),
  ADD KEY `idx_offer_status` (`status`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_user_id` (`user_id`),
  ADD KEY `idx_products_status` (`status`);

--
-- Indexes for table `product_views`
--
ALTER TABLE `product_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rentedrooms`
--
ALTER TABLE `rentedrooms`
  ADD PRIMARY KEY (`rented_room_id`),
  ADD KEY `fk_appoint_users` (`rented_user_id`);

--
-- Indexes for table `request_mentorship_session`
--
ALTER TABLE `request_mentorship_session`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `mentor_id` (`mentor_id`);

--
-- Indexes for table `sell_exchange_list`
--
ALTER TABLE `sell_exchange_list`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shuttle_driver`
--
ALTER TABLE `shuttle_driver`
  ADD PRIMARY KEY (`d_id`);

--
-- Indexes for table `total_trip`
--
ALTER TABLE `total_trip`
  ADD PRIMARY KEY (`driver_id`);

--
-- Indexes for table `uiumentorlist`
--
ALTER TABLE `uiumentorlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_linked_user` (`linked_user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `availablerooms`
--
ALTER TABLE `availablerooms`
  MODIFY `serial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `bargains`
--
ALTER TABLE `bargains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `deals`
--
ALTER TABLE `deals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `deal_chats`
--
ALTER TABLE `deal_chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lost_and_found`
--
ALTER TABLE `lost_and_found`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_views`
--
ALTER TABLE `product_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `request_mentorship_session`
--
ALTER TABLE `request_mentorship_session`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sell_exchange_list`
--
ALTER TABLE `sell_exchange_list`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uiumentorlist`
--
ALTER TABLE `uiumentorlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admin_user` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD CONSTRAINT `admin_activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`);

--
-- Constraints for table `availablerooms`
--
ALTER TABLE `availablerooms`
  ADD CONSTRAINT `fk_room_admin` FOREIGN KEY (`added_by_admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_room_tenant` FOREIGN KEY (`rented_to_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bargains`
--
ALTER TABLE `bargains`
  ADD CONSTRAINT `bargains_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bargains_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bargains_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `fk_message_chat` FOREIGN KEY (`chat_id`) REFERENCES `deal_chats` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_message_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_message_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `lost_and_found` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `deals`
--
ALTER TABLE `deals`
  ADD CONSTRAINT `deals_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deals_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deals_ibfk_3` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deals_ibfk_4` FOREIGN KEY (`bargain_id`) REFERENCES `bargains` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deal_chats`
--
ALTER TABLE `deal_chats`
  ADD CONSTRAINT `fk_chat_bargain` FOREIGN KEY (`bargain_id`) REFERENCES `bargains` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_chat_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_chat_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_chat_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `fk_application_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_application_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `offers`
--
ALTER TABLE `offers`
  ADD CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`bargain_id`) REFERENCES `bargains` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_views`
--
ALTER TABLE `product_views`
  ADD CONSTRAINT `product_views_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_views_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `rentedrooms`
--
ALTER TABLE `rentedrooms`
  ADD CONSTRAINT `fk_appoint_user` FOREIGN KEY (`rented_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rented_users` FOREIGN KEY (`rented_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_room_id` FOREIGN KEY (`rented_room_id`) REFERENCES `availablerooms` (`room_id`) ON DELETE CASCADE;

--
-- Constraints for table `request_mentorship_session`
--
ALTER TABLE `request_mentorship_session`
  ADD CONSTRAINT `request_mentorship_session_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_mentorship_session_ibfk_2` FOREIGN KEY (`mentor_id`) REFERENCES `uiumentorlist` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sell_exchange_list`
--
ALTER TABLE `sell_exchange_list`
  ADD CONSTRAINT `sell_exchange_list_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `total_trip`
--
ALTER TABLE `total_trip`
  ADD CONSTRAINT `fk_driver` FOREIGN KEY (`driver_id`) REFERENCES `shuttle_driver` (`d_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
