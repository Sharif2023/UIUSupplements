-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 23, 2025 at 08:13 PM
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
(4, 11221078, 'UPDATE', 'USER', '11111111', 'Updated user: shariful Islam', '2025-12-22 14:33:45');

-- --------------------------------------------------------

--
-- Table structure for table `appointedrooms`
--

CREATE TABLE `appointedrooms` (
  `appointed_room_id` varchar(255) NOT NULL DEFAULT 'NOT NULL',
  `appointed_user_id` int(11) DEFAULT NULL,
  `appointed_user_name` varchar(50) DEFAULT NULL,
  `appointed_user_email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointedrooms`
--

INSERT INTO `appointedrooms` (`appointed_room_id`, `appointed_user_id`, `appointed_user_name`, `appointed_user_email`) VALUES
('uiu111', 11221090, 'Abul Kalam', 'abulkalam@gmail.com'),
('uiu117', 11221369, 'Md Shakib', 'shakib@gmail.com');

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
  `room_rent` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `availablerooms`
--

INSERT INTO `availablerooms` (`serial`, `room_id`, `room_location`, `room_details`, `room_photos`, `available_from`, `available_to`, `status`, `room_rent`) VALUES
(1, 'uiu111', '55/3,syednagar,dhaka-1500', '', 'uploads/room.jpg,uploads/room1.jpg', '2024-07-15', '2024-07-20', 'not-available', 6000),
(2, 'uiu114', '55/4,Family Bazar,dhaka-1501', '', 'uploads/room3.jpg,uploads/room2.jpg,uploads/room4.jpg', '2024-07-17', '2024-08-01', 'available', 6000),
(3, 'uiu115', '55/3,syednagar,dhaka-1500', '', 'uploads/room5.jpg,uploads/room4.jpg', '2024-07-01', '2024-07-31', 'available', 5500),
(4, 'uiu112', '55/2,syednagar,dhaka-1500', '', 'uploads/room4.jpg,uploads/room.jpg', '2024-07-16', '2024-07-27', 'not-available', 5000),
(8, 'uiu113', '12/18,syednagar,dhaka-1100', '', 'uploads/room3.jpg,uploads/room4.jpg', '2024-08-10', '2024-08-31', 'not-available', 5500),
(9, 'uiu116', '54/23, Family Bazar, Dhaka-1300', '', 'uploads/room3.jpg,uploads/room4.jpg', '2024-08-29', '2024-09-30', 'not-available', 7000),
(10, 'uiu117', '12/12, Family Bazar, Dhaka-1220', '', 'uploads/room1.jpg,uploads/room2.jpg', '2024-09-01', '2024-09-30', 'not-available', 4500),
(11, 'uiu118', '10/15,mirpur,dhaka-1200', 'single,attach bathroom, attach kitchen', 'uploads/room2.jpg,uploads/room1.jpg', '2024-08-15', '2024-08-31', 'not-available', 6000),
(13, 'uiu-119', '1/1, uttar badda, dhaka-1320', 'single room with attached bathroom,kitchen and bar', 'uploads/Screenshot (95).png,uploads/Screenshot (96).png,uploads/Screenshot (97).png', '2024-09-01', '2024-09-28', 'available', 6000);

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
(1, 3, 11221079, 11221080, 260.00, 'accepted', 'If possible then inbox.', '2025-12-23 17:06:54', '2025-12-23 17:20:44');

--
-- Triggers `bargains`
--
DELIMITER $$
CREATE TRIGGER `after_bargain_insert` AFTER INSERT ON `bargains` FOR EACH ROW BEGIN
  UPDATE products SET bargain_count = bargain_count + 1 WHERE id = NEW.product_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_bargain_insert_notification` AFTER INSERT ON `bargains` FOR EACH ROW BEGIN
  INSERT INTO notifications (user_id, type, title, message, link)
  SELECT 
    NEW.seller_id,
    'bargain',
    'New Bargain Offer',
    CONCAT('You received a bargain offer of ৳', NEW.bargain_price, ' on your product'),
    CONCAT('myselllist.php?product_id=', NEW.product_id)
  FROM products WHERE id = NEW.product_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_bargain_update_notification` AFTER UPDATE ON `bargains` FOR EACH ROW BEGIN
  IF NEW.status != OLD.status THEN
    -- Notify buyer about status change
    IF NEW.status = 'accepted' THEN
      INSERT INTO notifications (user_id, type, title, message, link)
      VALUES (
        NEW.buyer_id,
        'bargain_accepted',
        'Bargain Accepted!',
        CONCAT('Your bargain offer of ৳', NEW.bargain_price, ' has been accepted'),
        CONCAT('mybargains.php?bargain_id=', NEW.id)
      );
    ELSEIF NEW.status = 'rejected' THEN
      INSERT INTO notifications (user_id, type, title, message, link)
      VALUES (
        NEW.buyer_id,
        'bargain_rejected',
        'Bargain Rejected',
        'Your bargain offer was rejected by the seller',
        CONCAT('mybargains.php?bargain_id=', NEW.id)
      );
    ELSEIF NEW.status = 'countered' THEN
      INSERT INTO notifications (user_id, type, title, message, link)
      VALUES (
        NEW.buyer_id,
        'bargain_countered',
        'Counter Offer Received',
        'The seller has made a counter offer on your bargain',
        CONCAT('mybargains.php?bargain_id=', NEW.id)
      );
    END IF;
  END IF;
END
$$
DELIMITER ;

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
(1, 1, 11221079, 11221080, 'Chat started! Discuss pickup/delivery, payment method (Cash, bKash, Nagad), and meeting details.', 'system', 1, '2025-12-23 19:12:58'),
(2, 1, 11221080, 11221079, 'Where should we meet?', 'text', 0, '2025-12-23 19:13:00'),
(3, 1, 11221080, 11221079, 'hii', 'text', 0, '2025-12-23 19:13:09');

--
-- Triggers `chat_messages`
--
DELIMITER $$
CREATE TRIGGER `after_message_insert` AFTER INSERT ON `chat_messages` FOR EACH ROW BEGIN
  -- Update last message timestamp
  UPDATE deal_chats 
  SET last_message_at = NEW.created_at 
  WHERE id = NEW.chat_id;
  
  -- Increment unread count for receiver
  UPDATE deal_chats 
  SET buyer_unread_count = buyer_unread_count + 1 
  WHERE id = NEW.chat_id AND buyer_id = NEW.receiver_id;
  
  UPDATE deal_chats 
  SET seller_unread_count = seller_unread_count + 1 
  WHERE id = NEW.chat_id AND seller_id = NEW.receiver_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_message_notification` AFTER INSERT ON `chat_messages` FOR EACH ROW BEGIN
  IF NEW.message_type = 'text' THEN
    INSERT INTO notifications (user_id, type, title, message, link)
    SELECT 
      NEW.receiver_id,
      'deal_chat_message',
      'New Deal Message',
      CONCAT('You have a new message about a deal'),
      CONCAT('mydeals.php?chat_id=', NEW.chat_id)
    FROM dual;
  END IF;
END
$$
DELIMITER ;

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
(4, NULL, '011111111', 'dsa@gmail.com', 'contact: 01855255815');

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
-- Triggers `deals`
--
DELIMITER $$
CREATE TRIGGER `after_deal_complete` AFTER UPDATE ON `deals` FOR EACH ROW BEGIN
  IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
    -- Update product status to sold
    UPDATE products SET status = 'sold' WHERE id = NEW.product_id;
    
    -- Notify both parties
    INSERT INTO notifications (user_id, type, title, message, link)
    VALUES 
      (NEW.seller_id, 'deal_completed', 'Deal Completed!', 
       CONCAT('Your product has been sold for ৳', NEW.final_price), 
       CONCAT('deal-details.php?id=', NEW.id)),
      (NEW.buyer_id, 'deal_completed', 'Deal Completed!', 
       CONCAT('You successfully purchased the product for ৳', NEW.final_price), 
       CONCAT('deal-details.php?id=', NEW.id));
  END IF;
END
$$
DELIMITER ;

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
(1, NULL, 1, 11221079, 11221080, 3, '2025-12-23 19:13:09', 2, 0, '2025-12-23 19:12:58');

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
(1, 11111111, 'shariful@gmail.com', 'Others', 'imgOfLost/robert-bye-tG36rvCeqng-unsplash.jpg', '6th floor near room 631.', '2024-10-01 00:00:00', '01855222222', NULL, 1),
(2, 11111112, 'abcd@gmail.com', 'ID card', 'imgOfLost/book2.jpeg', 'Canteen', '2024-10-09 16:04:00', '01700871179', NULL, 1);

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
(4, 11221079, 'bargain_countered', 'Counter Offer Received', 'The seller has made a counter offer on your bargain', 'mybargains.php?bargain_id=1', 0, '2025-12-23 17:15:46'),
(5, 11221079, 'counter_offer', 'Counter Offer Received', 'Seller offered ৳280.00 as counter offer', 'mybargains.php?bargain_id=1', 0, '2025-12-23 17:15:46'),
(6, 11221079, 'counter_offer', 'Counter Offer Received', 'Seller offered ৳260.00 as counter offer', 'mybargains.php?bargain_id=1', 0, '2025-12-23 17:20:18'),
(7, 11221079, 'bargain_accepted', 'Bargain Accepted!', 'Your bargain offer of ৳260.00 has been accepted', 'mybargains.php?bargain_id=1', 0, '2025-12-23 17:20:44'),
(8, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=1', 0, '2025-12-23 18:57:54'),
(9, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=1', 0, '2025-12-23 18:58:40'),
(10, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=1', 0, '2025-12-23 19:04:25'),
(11, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=1', 0, '2025-12-23 19:04:32'),
(12, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=1', 0, '2025-12-23 19:11:00'),
(13, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=1', 0, '2025-12-23 19:13:00'),
(14, 11221079, 'deal_chat_message', 'New Deal Message', 'You have a new message about a deal', 'mydeals.php?chat_id=1', 0, '2025-12-23 19:13:09');

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

--
-- Dumping data for table `offers`
--

INSERT INTO `offers` (`id`, `bargain_id`, `offered_price`, `seller_message`, `status`, `created_at`) VALUES
(1, 1, 280.00, '', 'pending', '2025-12-23 17:15:46'),
(2, 1, 260.00, '', 'pending', '2025-12-23 17:20:18');

--
-- Triggers `offers`
--
DELIMITER $$
CREATE TRIGGER `after_offer_insert_notification` AFTER INSERT ON `offers` FOR EACH ROW BEGIN
  INSERT INTO notifications (user_id, type, title, message, link)
  SELECT 
    b.buyer_id,
    'counter_offer',
    'Counter Offer Received',
    CONCAT('Seller offered ৳', NEW.offered_price, ' as counter offer'),
    CONCAT('mybargains.php?bargain_id=', NEW.bargain_id)
  FROM bargains b WHERE b.id = NEW.bargain_id;
END
$$
DELIMITER ;

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
(1, 'jbl', 'gadget', 5000.00, 'no Scratch ', 'imgOfSell/gadgets2.jpeg', NULL, NULL, 'available', '2025-12-23 15:22:16', '2025-12-23 15:22:16', 0, 0),
(2, 'Intensive English', 'book', 300.00, 'Full Fresh Condition', 'imgOfSell/book1.jpeg', NULL, NULL, 'available', '2025-12-23 15:22:16', '2025-12-23 15:22:16', 0, 0),
(3, 'host', 'other', 300.00, 'per month 300 TK', 'imgOfSell/byte backend.png', NULL, 11221080, 'available', '2025-12-23 17:06:02', '2025-12-23 17:06:54', 0, 1);

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
  `status` varchar(50) DEFAULT 'Pending',
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
('011221212', 'abdus salam', '01900990099', '1234'),
('d1', 'Hasnat', '01700112233', '1234'),
('d2', 'Shafiq Khan', '01700112234', '1234');

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
  `facebook` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uiumentorlist`
--

INSERT INTO `uiumentorlist` (`id`, `photo`, `name`, `bio`, `language`, `response_time`, `industry`, `hourly_rate`, `company`, `country`, `skills`, `email`, `whatsapp`, `linkedin`, `facebook`) VALUES
(6, 'uploads/mentor0.jpg', 'Shariful Islam', 'I am the source of coding', 'Bangla', '6 hours', 'Tech', '0 tk for 10 minutes', 'uiu', 'Bangladesh', 'c++', 'sharifislam0505@gmail.com', '01631223995', 'https://www.linkedin.com/18', 'https://www.facebook.com/sharif2018'),
(7, 'uploads/mentor7.jpg', 'Zamil Khan', 'Learn business from me', 'Bangla', '6 hours', 'Tech', '0 tk for 10 minutes', 'Bkash', 'Bangladesh', 'marketing, promoting, branding', 'sharifislam0505@gmail.com', '01631223995', 'https://www.linkedin.com/18', 'https://www.linkedin.com/18'),
(11, 'uploads/mentor2.jpg', 'Rafi Hasan', 'Learn code from me', 'Bangla', '6 hours', 'Tech', '0 tk for 10 minutes', 'Startech', 'Bangladesh', 'c++,c, java', 'Rafi@gmail.com', '01631223995', 'https://www.linkedin.com/18', 'https://www.facebook.com/sharif2018'),
(12, 'uploads/mentor3.jpg', 'Ashik Khan', 'I am an engineer.', 'Bangla', '6 hours', 'Tech', '0 tk for 10 minutes', 'BrainStorm', 'Bangladesh', 'java,c++,python', 'sharifislam0505@gmail.com', '01631223995', 'https://www.linkedin.com/sharif', 'https://www.facebook.com/sharif'),
(13, 'uploads/mentor4.jpg', 'S.I. Sharif', 'I am a professional Engineer. I want fun.', 'English', '48 hours', 'Tech', '100 tk for 30 minutes', 'UIU', 'Bangladesh', 'c++,gpu,office', 'si@gmail.com', '01632223995', 'https://www.linkedin.com/sharifsi', 'https://www.facebook.com/sharifsi'),
(14, 'uploads/mentor5.jpg', 'AK Rayhan', 'I am a professional Business Man. I want fun.', 'English', '48 hours', 'Tech', '100 tk for 30 minutes', 'Brac', 'Bangladesh', 'c++,gpu,office', 'si@gmail.com', '01632223995', 'https://www.linkedin.com/sharifsi', 'https://www.facebook.com/sharifsi'),
(15, 'uploads/mentor6.jpg', 'Shakib Khan', 'I am a professional Engineer. I want fun.', 'English', '48 hours', 'Tech', '100 tk for 30 minutes', 'United Group', 'Bangladesh', 'c++,gpu,office', 'si@gmail.com', '01632223995', 'https://www.linkedin.com/sharifsi', 'https://www.facebook.com/sharifsi'),
(37, 'uploads/mentor7.jpg', 'AB Mahmud', 'Learn code from me', 'English', '', 'Finance', '', 'uiu', 'Bangladesh', 'marketing, promoting, branding', 'ab@gmail.com', '01800871179', 'https://www.linkedin.com/a', 'https://www.facebook.com/a'),
(38, 'uploads/mentor8.jpg', 'Rana Raiyan', 'Learn Marketing Ideas from me', 'Bangla', '48 hours', 'Tech', '1 hour - 500 tk,2 hours - 1000 tk', 'Nagad', 'Bangladesh', 'networking, Promoting', 'ab@gmail.com', '01800871179', 'https://www.linkedin.com/a', 'https://www.facebook.com/a');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `Gender`, `password_hash`, `mobilenumber`, `created_at`) VALUES
(11221078, 'Shariful Islam', '011221078', 'm', '$2y$10$zAuEsUA/9M0LKmWbBRHL5Oz7n6hFc7uEIoNQtrxaxnXg5F0wKeZvW', '1700871179', '2024-10-03 18:41:30'),
(11221079, 'Mahmudul Hasan', 'mhasan221079@bscse.uiu.ac.bd', 'm', '$2y$10$brpUDs5I6/MM2bFc0ErhCOHuITpTSdY/0gYa2xKbHCEiTWoZDuSRi', '1700221079', '2025-12-23 16:55:43'),
(11221080, 'Ashik Khan', 'akhan221080@bscse.uiu.ac.bd', 'm', '$2y$10$rKJy5xOgZ7f0sV0JOa0bCu9m00XopD.XF5Ex2vUnPGrYKnF6Dhg52', '1700221080', '2025-12-23 17:03:04');

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
-- Indexes for table `appointedrooms`
--
ALTER TABLE `appointedrooms`
  ADD PRIMARY KEY (`appointed_room_id`),
  ADD KEY `fk_appoint_users` (`appointed_user_id`);

--
-- Indexes for table `availablerooms`
--
ALTER TABLE `availablerooms`
  ADD PRIMARY KEY (`serial`),
  ADD UNIQUE KEY `room_id` (`room_id`);

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
  ADD PRIMARY KEY (`id`);

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
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `availablerooms`
--
ALTER TABLE `availablerooms`
  MODIFY `serial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `bargains`
--
ALTER TABLE `bargains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `deals`
--
ALTER TABLE `deals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `deal_chats`
--
ALTER TABLE `deal_chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lost_and_found`
--
ALTER TABLE `lost_and_found`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_views`
--
ALTER TABLE `product_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `request_mentorship_session`
--
ALTER TABLE `request_mentorship_session`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sell_exchange_list`
--
ALTER TABLE `sell_exchange_list`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uiumentorlist`
--
ALTER TABLE `uiumentorlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

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
-- Constraints for table `appointedrooms`
--
ALTER TABLE `appointedrooms`
  ADD CONSTRAINT `fk_appoint_user` FOREIGN KEY (`appointed_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appoint_users` FOREIGN KEY (`appointed_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_room_id` FOREIGN KEY (`appointed_room_id`) REFERENCES `availablerooms` (`room_id`) ON DELETE CASCADE;

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
