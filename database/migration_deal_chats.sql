-- Migration to add chat functionality for deals
-- Created: 2025-12-24
-- Description: Creates tables for deal chat sessions and messages

-- Table to track chat sessions for each deal
CREATE TABLE IF NOT EXISTS `deal_chats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deal_id` int(11) DEFAULT NULL,
  `bargain_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `buyer_unread_count` int(11) DEFAULT 0,
  `seller_unread_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_bargain_chat` (`bargain_id`),
  KEY `idx_chat_deal` (`deal_id`),
  KEY `idx_chat_buyer` (`buyer_id`),
  KEY `idx_chat_seller` (`seller_id`),
  KEY `idx_chat_product` (`product_id`),
  KEY `idx_last_message` (`last_message_at`),
  CONSTRAINT `fk_chat_bargain` FOREIGN KEY (`bargain_id`) REFERENCES `bargains` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_chat_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_chat_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_chat_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table to store individual chat messages
CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chat_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `message_type` enum('text','system','payment_info','meeting_info') DEFAULT 'text',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_message_chat` (`chat_id`),
  KEY `idx_message_sender` (`sender_id`),
  KEY `idx_message_receiver` (`receiver_id`),
  KEY `idx_message_read` (`is_read`),
  KEY `idx_message_created` (`created_at`),
  CONSTRAINT `fk_message_chat` FOREIGN KEY (`chat_id`) REFERENCES `deal_chats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_message_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_message_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Trigger to update last_message_at and unread count when new message is sent
DELIMITER $$
CREATE TRIGGER `after_message_insert` AFTER INSERT ON `chat_messages` FOR EACH ROW 
BEGIN
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
END$$
DELIMITER ;

-- Trigger to send notification when new message is received
DELIMITER $$
CREATE TRIGGER `after_message_notification` AFTER INSERT ON `chat_messages` FOR EACH ROW 
BEGIN
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
END$$
DELIMITER ;
