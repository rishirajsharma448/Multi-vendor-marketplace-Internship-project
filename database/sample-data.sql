-- Vyapar Setu Sample Data SQL Script

USE `vyapar_setu`;

-- Password for all seed users is: password123
-- Hash: $2y$10$8v8mQ9vQeXq71X6QYv2PyeX2p8FpY/m8W1/7/8YyJ0o4xKx/7.XgK (or system generated)

-- Seed Users
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `role`, `status`, `email_verified`) VALUES
(3, 'Priya Sharma (Handloom)', 'priya@bharattextiles.in', '+91 98765 22222', '$2y$10$fTT5I4/WrKFJhLU87rhezeZFXvqByNvfi0517zAVtOf/pQPw8EPzi', 'vendor', 'ACTIVE', 1),
(4, 'Amrit Patel (Organic)', 'amrit@swadeshispices.org', '+91 98765 33333', '$2y$10$fTT5I4/WrKFJhLU87rhezeZFXvqByNvfi0517zAVtOf/pQPw8EPzi', 'vendor', 'ACTIVE', 1),
(5, 'Ananya Sen', 'ananya@gmail.com', '+91 91234 56789', '$2y$10$fTT5I4/WrKFJhLU87rhezeZFXvqByNvfi0517zAVtOf/pQPw8EPzi', 'customer', 'ACTIVE', 1);

-- Seed Vendors
INSERT INTO `vendors` (`id`, `user_id`, `vendor_code`, `store_name`, `owner_name`, `email`, `phone`, `gstin`, `category`, `rating`, `reviews_count`, `status`, `commission_rate`, `address`) VALUES
(2, 3, 'VND-44102', 'Bharat Handloom Textiles', 'Priya Sharma', 'priya@bharattextiles.in', '+91 98765 22222', '27AABCB5678K1Z2', 'Fashion', 4.80, 850, 'APPROVED', '10%', 'Heritage Weaver Lane, Varanasi'),
(3, 4, 'VND-99301', 'Swadeshi Organic Spices', 'Amrit Patel', 'amrit@swadeshispices.org', '+91 98765 33333', '27AABCS9912M1Z9', 'Spices & Organics', 4.90, 2410, 'APPROVED', '10%', 'Spices Hub, Kochi, Kerala');

-- Seed Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`) VALUES
(1, 'Electronics', 'electronics', 'bi-laptop', 'Gadgets, Smart Devices & Accessories'),
(2, 'Fashion & Apparel', 'fashion', 'bi-bag-heart', 'Ethnic, Western Wear & Handlooms'),
(3, 'Spices & Organics', 'spices-organics', 'bi-flower1', 'Farm Fresh Spices & Organic Herbs'),
(4, 'Home & Living', 'home-living', 'bi-house-door', 'Handicrafts, Décor & Kitchenware'),
(5, 'Beauty & Wellness', 'beauty-wellness', 'bi-heart-pulse', 'Natural Skincare & Herbal Products');

-- Seed Brands
INSERT INTO `brands` (`id`, `name`, `slug`) VALUES
(1, 'TechCraft', 'techcraft'),
(2, 'Bharat Textiles', 'bharat-textiles'),
(3, 'Swadeshi Organics', 'swadeshi-organics'),
(4, 'Jaipur Arts', 'jaipur-arts');



-- Activity Logs
INSERT INTO `activity_logs` (`user_id`, `action_description`, `ip_address`) VALUES
(NULL, 'System initialized with MySQL database schema & sample data.', '127.0.0.1');
