-- Vyapar Setu Sample Data SQL Script

USE `vyapar_setu`;

-- Password for all seed users is: password123
-- Hash: $2y$10$8v8mQ9vQeXq71X6QYv2PyeX2p8FpY/m8W1/7/8YyJ0o4xKx/7.XgK (or system generated)

-- Seed Users
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `role`, `status`, `email_verified`) VALUES
(1, 'System Administrator', 'admin@vyaparsetu.com', '+91 99000 11223', '$2y$10$fTT5I4/WrKFJhLU87rhezeZFXvqByNvfi0517zAVtOf/pQPw8EPzi', 'admin', 'ACTIVE', 1),
(2, 'Rahul Verma (TechCraft)', 'vendor@vyaparsetu.com', '+91 98765 11111', '$2y$10$fTT5I4/WrKFJhLU87rhezeZFXvqByNvfi0517zAVtOf/pQPw8EPzi', 'vendor', 'ACTIVE', 1),
(3, 'Priya Sharma (Handloom)', 'priya@bharattextiles.in', '+91 98765 22222', '$2y$10$fTT5I4/WrKFJhLU87rhezeZFXvqByNvfi0517zAVtOf/pQPw8EPzi', 'vendor', 'ACTIVE', 1),
(4, 'Amrit Patel (Organic)', 'amrit@swadeshispices.org', '+91 98765 33333', '$2y$10$fTT5I4/WrKFJhLU87rhezeZFXvqByNvfi0517zAVtOf/pQPw8EPzi', 'vendor', 'ACTIVE', 1),
(5, 'Rishiraj Sharma', 'customer@vyaparsetu.com', '+91 98765 43210', '$2y$10$fTT5I4/WrKFJhLU87rhezeZFXvqByNvfi0517zAVtOf/pQPw8EPzi', 'customer', 'ACTIVE', 1),
(6, 'Ananya Sen', 'ananya@gmail.com', '+91 91234 56789', '$2y$10$fTT5I4/WrKFJhLU87rhezeZFXvqByNvfi0517zAVtOf/pQPw8EPzi', 'customer', 'ACTIVE', 1);

-- Seed Vendors
INSERT INTO `vendors` (`id`, `user_id`, `vendor_code`, `store_name`, `owner_name`, `email`, `phone`, `gstin`, `category`, `rating`, `reviews_count`, `status`, `commission_rate`, `address`) VALUES
(1, 2, 'VND-88219', 'TechCraft Electronics', 'Rahul Verma', 'vendor@vyaparsetu.com', '+91 98765 11111', '27AABCT1234H1Z5', 'Electronics', 4.90, 1240, 'APPROVED', '10%', 'Building 4, Tech Park, Mumbai'),
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

-- Seed Products
INSERT INTO `products` (`id`, `sku`, `vendor_id`, `category_id`, `brand_id`, `title`, `description`, `price`, `original_price`, `discount`, `stock`, `spotlight`, `status`, `rating`, `rating_count`) VALUES
(1, 'TC-WLE-01', 1, 1, 1, 'SonicPods Pro Active Noise Cancelling Earbuds', 'Ultra-low latency wireless earbuds featuring Hybrid Active Noise Cancellation (ANC), 30-hour playback with fast charge case, dual ENC mics for crystal clear calls.', 2499.00, 4999.00, 50, 42, 'BESTSELLER', 'ACTIVE', 4.80, 312),
(2, 'TC-SMW-04', 1, 1, 1, 'SmartFit OLED Fitness & SpO2 Smartwatch', 'Sleek aluminum smartwatch with 1.43-inch Always-On AMOLED Display, continuous Heart Rate & SpO2 tracker, 100+ sports modes, and integrated Bluetooth phone calling.', 3999.00, 6999.00, 43, 15, 'FEATURED', 'ACTIVE', 4.90, 184),
(3, 'BT-SLK-09', 2, 2, 2, 'Handcrafted Pure Mulberry Silk Banarasi Saree', 'Woven by master artisans in Varanasi, this pure silk saree features intricate gold zari brocade work and comes with an unstitched matching blouse piece.', 6800.00, 9500.00, 28, 8, 'FEATURED', 'ACTIVE', 4.90, 96),
(4, 'SS-PEP-02', 3, 3, 3, 'Organic Malabar Black Pepper Whole (500g)', 'Directly sourced from organic certified farms in Western Ghats, Kerala. High essential oil content delivering rich aroma and bold pungency.', 650.00, 850.00, 24, 60, 'BESTSELLER', 'ACTIVE', 5.00, 540),
(5, 'SS-KSR-01', 3, 3, 3, 'Kashmiri Grade-1 Mongra Saffron Kesar (2g)', 'Original Pampore Kashmiri Saffron strands known for deep crimson color, exquisite floral fragrance, and natural medicinal properties.', 990.00, 1290.00, 23, 30, 'FEATURED', 'ACTIVE', 4.90, 210),
(6, 'JR-BRS-05', 2, 4, 4, 'Hand-Carved Solid Brass Peacock Diya Idol', 'Exquisite traditional oil lamp handcrafted by Rajasthan brass artisans. Perfect for festive decor, home altars, and heritage gifting.', 1499.00, 1999.00, 25, 12, 'REGULAR', 'ACTIVE', 4.80, 74);

-- Seed Product Images
INSERT INTO `product_images` (`product_id`, `image_url`, `is_primary`) VALUES
(1, 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=600&q=80', 1),
(2, 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?auto=format&fit=crop&w=600&q=80', 1),
(3, 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=600&q=80', 1),
(4, 'https://images.unsplash.com/photo-1599940824399-b87987ceb72a?auto=format&fit=crop&w=600&q=80', 1),
(5, 'https://images.unsplash.com/photo-1601004890684-d8cbf643f5f2?auto=format&fit=crop&w=600&q=80', 1),
(6, 'https://images.unsplash.com/photo-1605648916361-9bc12ad6a569?auto=format&fit=crop&w=600&q=80', 1);

-- Seed Orders
INSERT INTO `orders` (`id`, `order_number`, `user_id`, `customer_name`, `email`, `phone`, `address`, `payment_method`, `payment_status`, `subtotal`, `discount`, `total_amount`, `status`) VALUES
(1, 'ORD-99012', 5, 'Rishiraj Sharma', 'customer@vyaparsetu.com', '+91 98765 43210', 'Flat 402, G.V. Acharya Campus Road, Shelu, Maharashtra - 410101', 'UPI / Razorpay', 'Paid', 2499.00, 250.00, 2249.00, 'Shipped'),
(2, 'ORD-99013', 6, 'Ananya Sen', 'ananya@gmail.com', '+91 91234 56789', 'Suite 12, Tech Park Avenue, Pune, Maharashtra', 'Cash on Delivery', 'Pending', 1300.00, 0.00, 1300.00, 'Delivered');

-- Seed Order Items
INSERT INTO `order_items` (`order_id`, `product_id`, `vendor_id`, `product_title`, `price`, `quantity`, `status`) VALUES
(1, 1, 1, 'SonicPods Pro Active Noise Cancelling Earbuds', 2499.00, 1, 'Shipped'),
(2, 4, 3, 'Organic Malabar Black Pepper Whole (500g)', 650.00, 2, 'Delivered');

-- Seed Reviews
INSERT INTO `reviews` (`product_id`, `user_id`, `customer_name`, `rating`, `comment`, `vendor_response`) VALUES
(1, 5, 'Rishiraj Sharma', 5, 'Crystal clear sound quality and great ANC capabilities. Highly recommended!', 'Thank you for your review! We are glad you love the SonicPods.'),
(4, 6, 'Ananya Sen', 5, 'Extremely aromatic pepper. Top quality organic product!', 'Thank you Ananya!');

-- Activity Logs
INSERT INTO `activity_logs` (`user_id`, `action_description`, `ip_address`) VALUES
(1, 'System initialized with MySQL database schema & sample data.', '127.0.0.1'),
(2, 'Vendor login successful for TechCraft Electronics.', '127.0.0.1'),
(5, 'Customer order ORD-99012 placed successfully.', '127.0.0.1');
