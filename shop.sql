-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 25, 2025 at 05:09 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `user_id` char(8) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` varchar(20) NOT NULL,
  `user_id` char(8) NOT NULL,
  `address_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `shipping_courier_id` int(11) NOT NULL,
  `payment_method_id` int(11) NOT NULL,
  `order_status` enum('Pending','Shipped','Out for Delivery','Delivered','Cancelled') DEFAULT 'Pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` varchar(20) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `price_at_purchase` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `payment_id` int(11) NOT NULL,
  `method_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`payment_id`, `method_name`) VALUES
(5, 'Cash on Delivery'),
(3, 'Credit/Debit Card'),
(1, 'Gcash'),
(2, 'Maya'),
(4, 'Paypal');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  `category` enum('keyboard','mouse','accessories','miscellaneous') NOT NULL DEFAULT 'miscellaneous'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `price`, `image`, `stock`, `description`, `category`) VALUES
(2, 'Yunzii B75 Pro', 3899.00, '../uploads/keyboard/1742733264_yunzii_b75pro.webp', 495, 'Enjoy the ultimate combination of style and performance with this coffee-themed keyboard. With its top-notch gasket mount construction featuring 5 layers of cushioning, sound dampening, and pre-lubricated mech switches, it offers smooth, rich typing with a dense, rewarding feel.\r\n\r\nStay in touch your way with tri-mode connectivity—Bluetooth, 2.4GHz wireless, and wired—fueled by a rechargeable 4000mAh battery for uninterrupted work and play.\r\n\r\nTotal hot-swappable, this keyboard accommodates 3-pin as well as 5-pin switches, providing smooth customization and extremely responsive keystrokes. For productivity or gaming, this keyboard brews the best experience.', 'keyboard'),
(7, 'Lofree Flow84', 5295.00, '../uploads/keyboard/1742735830_lofree_flow84.webp', 293, 'Embrace the Flow 84, the ultra-slim mechanical keyboard, where industrial design aesthetic meets smooth typing feel. You can ready to complement your professional and tasteful lifestyle at a moment\'s notice. An all-aluminum base and frame with the low profile switches, perfectly combines professional craftsmanship with sleek aesthetics. \r\n\r\nThe Flow keyboard’s Gasket mount, paired with IXPE and Poron foam enhances each keystroke by isolating switches and reducing vibrations. This results in a quieter, more stable, and precise typing experience, perfect. Experience the difference with every press, where comfort meets accuracy.\r\n\r\nExperience superior touching sensation with our dye-sub PBT keycaps. Designed for the experience of exquisite touch and lasting beauty, these keycaps are resistant and to wear and oil, remaining pristine and vibrant through extensive use. They ensure the colors stay longer and the text clear, for a keyboard that looks and feels as good as new, always.', 'keyboard'),
(8, 'Logitech MX Keys Mini', 4529.00, '../uploads/keyboard/1742733290_mx_master_mini.webp', 379, 'Meet MX Keys Mini – a minimalist keyboard made for creators.\r\nThe same MX high-performance engineered for comfortable, fast, fluid typing, with smart illumination, and programmable keys in a space-saving, form factor. The minimalist form factor of MX Keys Mini aligns your shoulders and results in better posture and improved ergonomics. Type with confidence and speed with spherically-dished keys shaped for your fingertips, minimizing the chances of a mistype, while increased key stability reduces noise.\r\n\r\nThe minimalist form factor aligns your shoulders and allows you to place your mouse closer to your keyboard for less hand reaching – for better posture and improved ergonomics. Backlit keys light up when your hands approach the keyboard and automatically brighten or fade to suit your environment.', 'keyboard'),
(9, 'Akko MU01 Mountain Seclusion', 5949.00, '../uploads/keyboard/1742733298_akko_mountain.webp', 233, 'The Akko MU01 Mountain Seclusion is a 65% mechanical keyboard that harmoniously blends traditional craftsmanship with modern functionality. Encased in a polished walnut wooden frame, it showcases a Mountain Seclusion theme with MOA profile PBT dye-sublimated keycaps, offering a tactile and aesthetic typing experience. ​\r\n\r\nThis keyboard supports Bluetooth 5.0, 2.4GHz wireless (via the included receiver), and wired Type-C connections, allowing seamless switching between multiple devices. Its 4000mAh battery ensures extended usage without frequent recharging. The gasket-mounted design, complemented by an FR4 plate and multiple dampening layers, provides a soft and responsive typing experience. Equipped with Akko\'s V3 Piano Pro or Rosewood linear switches, the MU01 supports hot-swappable functionality, enabling users to customize their typing feel without soldering. Additionally, it features programmable RGB backlighting with various light animation effects, all adjustable through the Akko Cloud Driver software.', 'keyboard'),
(10, 'Attack Shark X68 HE', 1866.00, '../uploads/keyboard/1742733309_atk_shark-x68he.webp', 458, 'The ATTACK SHARK X68 HE is a 60% wired mechanical gaming keyboard designed for users seeking precision and customization. It features adjustable Hall effect magnetic switches, allowing users to set actuation points between 0.1mm and 3.4mm, tailoring the keyboard\'s responsiveness to individual preferences. The rapid trigger function ensures immediate key resets post-actuation, facilitating swift, repeated keystrokes essential for high-paced gaming scenarios. With an 8000Hz polling rate and a scan rate of 128KHz, the X68 HE delivers ultra-low latency of approximately 0.125ms, ensuring swift and accurate key recognition during intense gaming sessions. The keyboard\'s 60% layout offers a compact design that maximizes desk space and enhances typing efficiency. Additionally, it features programmable functions accessible through a web-based driver at qmk.top, enabling key remapping, macro settings, and lighting adjustments without additional software installations. The X68 HE is compatible with both Windows and Mac operating systems, making it a versatile choice for various users.', 'keyboard'),
(11, '8BitDo Retro Keyboard M Edition', 3499.00, '../uploads/keyboard/1742733319_retro_m-edition.webp', 128, 'The 8BitDo Retro Mechanical Keyboard M Edition is a modern mechanical keyboard that draws inspiration from classic designs, notably the IBM Model M. It features an 87-key layout with dye-sublimated PBT keycaps, ensuring durability and a vintage aesthetic. Equipped with hot-swappable Kailh Box White V2 switches, users can customize their typing experience without soldering. The keyboard offers versatile connectivity options, including Bluetooth 5.0, 2.4GHz wireless, and wired USB-C, making it compatible with Windows 10 (1903) and above, as well as Android 9.0 and above devices. A built-in 2000mAh rechargeable battery provides up to 200 hours of usage with a full charge time of approximately four hours. Additional features include programmable Dual Super Buttons and support for n-key rollover, enhancing both functionality and user experience.', 'keyboard'),
(12, 'Keychron K11 Max', 5050.00, '../uploads/keyboard/1742733327_keychron_k11-max.webp', 455, 'The Keychron K11 Max is an ultra-slim, wireless mechanical keyboard that seamlessly blends ergonomic design with advanced functionality. Featuring a 65% Alice layout, it offers a compact and comfortable typing experience by angling and tilting the keys to better fit the natural positioning of the hands. The keyboard supports multiple connectivity options, including 2.4 GHz wireless, Bluetooth 5.1, and wired USB-C, allowing users to connect up to three devices simultaneously. Equipped with hot-swappable Gateron low-profile MX mechanical switches, users can easily customize their typing experience without soldering. The inclusion of LSA (low profile spherical-angled) profile keycaps enhances comfort during extended typing sessions. Additionally, the K11 Max is programmable via QMK firmware and the Keychron Launcher web app, enabling users to remap keys and set macros to suit their workflow. Its sleek design and versatile features make it a suitable choice for both professionals and enthusiasts seeking a portable and efficient keyboard solution.', 'keyboard'),
(13, 'Keychron Q11', 9070.00, '../uploads/keyboard/1742733340_keychron_q11.webp', 322, 'The Keychron Q11 is a customizable mechanical keyboard featuring a unique 75% split design, allowing users to position each half independently for enhanced ergonomics. Crafted from CNC-machined 6063 aluminum, it boasts a robust and premium build quality. The keyboard supports QMK and VIA firmware, enabling extensive key remapping and macro customization. Its hot-swappable sockets are compatible with MX-style mechanical switches, facilitating easy switch modifications without soldering. The Q11 also includes dual rotary knobs, providing additional functionality for tasks like volume control or zoom adjustments. With south-facing RGB LEDs, it ensures optimal illumination for a wide range of keycaps. This combination of flexibility, durability, and advanced features makes the Keychron Q11 a compelling choice for users seeking both ergonomic comfort and customization in their typing experience. ​', 'keyboard'),
(14, 'Lofree Touch PBT Mouse', 2399.00, '../uploads/mouse/1742733387_touchpbt.webp', 368, 'The Lofree Touch PBT Wireless Mouse is a uniquely designed peripheral that combines retro aesthetics with modern functionality, offering users a customizable and efficient experience. One of its standout features is the use of PBT (polybutylene terephthalate) material for the main buttons and upper case, providing a non-glossy, non-sticky, and skin-friendly texture that enhances durability and comfort during extended use.\r\n\r\nA notable aspect of the Lofree Touch is its swappable keycap design, allowing users to personalize the mouse\'s appearance by replacing the main buttons and upper case with different keycap sets. This feature enables a tailored look and feel, aligning the mouse with individual preferences or matching it to other peripherals. ​\r\n\r\nIn terms of performance, the mouse is equipped with the PAW 3805 sensor, offering up to 4,000 DPI and the capability to track on various surfaces, including glass. This ensures precise and responsive cursor movements, catering to both casual and professional use.\r\n\r\nThe Lofree Touch supports tri-mode connectivity, featuring wired USB, low-latency 2.4 GHz RF, and Bluetooth modes, allowing users to connect up to two devices simultaneously. This versatility ensures compatibility with a wide range of devices and operating systems, enhancing flexibility in various environments.', 'mouse'),
(15, 'Logitech POP Mouse', 899.00, '../uploads/mouse/1742733396_pop.webp', 765, 'The Logitech POP Mouse is a compact, wireless mouse designed to add a touch of personality to your workspace while delivering reliable performance. One of its standout features is the customizable top button, which can be programmed to insert your favorite emojis or perform other functions, enhancing your communication and workflow efficiency. The mouse utilizes SilentTouch technology, ensuring quiet clicks that minimize noise without sacrificing responsiveness.\r\n\r\nWeighing approximately 2.8 ounces and measuring 1.3 by 2.3 by 4.1 inches, the POP Mouse is highly portable and suitable for both office and on-the-go use. It offers multi-device connectivity, allowing seamless switching between up to three devices via Bluetooth, making it versatile for various computing needs.', 'mouse'),
(16, 'Razer Viper V3 Pro', 6299.00, '../uploads/mouse/1742733370_viper-v3-pro.webp', 308, 'The Razer Viper V3 Pro is an ultra-lightweight wireless gaming mouse, meticulously engineered for competitive esports enthusiasts seeking peak performance. Weighing approximately 54 grams, it offers exceptional agility and swift responsiveness during intense gaming sessions. A standout feature of the Viper V3 Pro is its Focus Pro 35K Optical Sensor, delivering a maximum sensitivity of 35,000 DPI. This sensor ensures precise tracking and accuracy, accommodating a wide range of gaming styles.\r\n\r\nThe mouse supports a wireless polling rate of up to 8,000Hz, providing ultra-low latency and smooth cursor movements. This high polling rate is particularly advantageous in fast-paced gaming scenarios where split-second reactions are crucial.\r\n\r\nErgonomically designed for right-handed users, the Viper V3 Pro features a refined shape that enhances comfort and control. Its minimalist aesthetic is complemented by Razer\'s Optical Mouse Switches Gen-3, rated for 90 million clicks, ensuring durability and a satisfying tactile response.', 'mouse'),
(17, 'Logitech Lift Vertical Mouse', 4599.00, '../uploads/mouse/1742733407_lift.webp', 347, 'The Logitech Lift Vertical Ergonomic Mouse is designed to promote comfort during extended computer use. Its 57-degree vertical design encourages a natural handshake position, reducing pressure on the wrist and promoting a more relaxed posture in the forearm and upper body. Tailored for small to medium-sized hands, the Lift mouse features a soft rubber grip and a comfortable thumb rest, ensuring a secure and pleasant user experience. It offers versatile wireless connectivity options, including Bluetooth and Logitech\'s Logi Bolt USB receiver, allowing seamless pairing with various devices and operating systems.\r\n\r\nThe mouse includes a SmartWheel for precise and speedy scrolling, enhancing navigation through documents and web pages. With four customizable buttons, users can personalize their workflow to suit individual needs.', 'mouse'),
(18, 'Akko Capybara Mouse', 1299.00, '../uploads/mouse/1742732792_capybara.webp', 321, 'The Akko Capybara Mouse is a 2.4G wireless mouse inspired by the gentle and sociable nature of the capybara, designed to bring a playful touch to your computing experience. Its ergonomic shape ensures a comfortable fit in the palm, promoting a natural grip that enhances comfort during extended use. ​This mouse offers a stable wireless connection up to 33 feet (10 meters), providing flexibility and freedom of movement without the clutter of cords. With a default 1200 DPI setting, it delivers a responsive experience suitable for both work and casual gaming. \r\n\r\nThe Akko Capybara Mouse features plug-and-play functionality; simply insert the USB receiver (conveniently stored within the mouse) into your device, install a AA battery (not included due to shipping regulations), switch it on, and it\'s ready to use without additional setup.', 'mouse'),
(19, 'Razer Naga Pro', 3599.00, '../uploads/mouse/1742733462_naga_pro.webp', 432, 'The Razer Naga Pro is a modular wireless gaming mouse designed to cater to various gaming genres, particularly MMORPGs, MOBAs, and FPS titles. Its standout feature is the inclusion of three interchangeable side plates, offering configurations with 2, 6, or 12 programmable buttons. This design allows gamers to customize their control setup to match specific game requirements, enhancing versatility and user experience. Equipped with Razer\'s Focus+ 20K DPI Optical Sensor, the Naga Pro delivers precise tracking and responsiveness, essential for competitive gaming scenarios. The mouse also features Razer\'s HyperSpeed Wireless technology, ensuring a stable and low-latency connection, which is crucial for maintaining performance during intense gaming sessions. \r\n\r\nThe Naga Pro incorporates optical mouse switches, which provide faster response times and increased durability compared to traditional mechanical switches. These switches are rated for up to 70 million clicks, offering longevity for extensive gaming use.', 'mouse'),
(20, 'WLMOUSE Strider', 7499.00, '../uploads/mouse/1742733474_strider.webp', 220, 'The WLMOUSE Strider is a high-performance gaming mouse designed for gamers seeking precision, speed, and durability. Weighing approximately 45 grams, its ultra-lightweight design is achieved through the use of a premium magnesium alloy shell combined with an ABS plastic bottom, offering a balance between strength and agility. At its core, the Strider features the advanced PAW3950 HS optical sensor, providing up to 30,000 DPI, 750 IPS tracking speed, and 70 G acceleration. This ensures exceptional precision and responsiveness, catering to the demands of competitive gaming.\r\n\r\nOne of the standout features of the Strider is its adjustable wireless polling rate, ranging from 125Hz to an impressive 8,000Hz. This high polling rate delivers ultra-smooth cursor movements and rapid response times, enhancing the overall gaming experience. ​', 'mouse'),
(21, 'Keycap and Switch Puller', 25.00, '../uploads/miscellaneous/1742733515_switch_puller.webp', 998, 'The **Keycap and Switch Puller** is a handy tool for anyone who loves customizing their mechanical keyboard. With a wire puller on one end for easy keycap removal and a sturdy switch puller on the other, it makes swapping out parts quick and hassle-free. The ergonomic handle provides a comfortable grip, so you can work on your board without straining your hands. Whether you\'re upgrading switches or just trying out a new keycap set, this tool is a must-have. Durable and compact, it’s easy to store and perfect for any keyboard setup.', 'miscellaneous'),
(22, 'Cloud Wrist Rest', 120.00, '../uploads/accessories/1742733481_cloud.webp', 689, 'This fluffy wrist rest is designed for ultimate comfort, providing a soft and supportive cushion for your wrists during long typing or gaming sessions. Made with plush memory foam and a smooth, breathable cover, it gently conforms to your wrists for a cloud-like feel. Its ergonomic design helps reduce strain and promote better wrist posture, making it a great addition to any setup. Whether you\'re working or gaming, this wrist rest keeps your hands comfortable and supported. Stylish and durable, it’s the perfect upgrade for your desk.', 'accessories'),
(23, 'Wooden Wrist Rest', 450.00, '../uploads/accessories/1742733490_wooden.webp', 755, 'Made from high-quality, polished wood, it provides a smooth and sturdy surface that promotes better wrist posture during long typing or gaming sessions. Its ergonomic design helps reduce strain while adding a sleek, natural touch to your setup. With its durable build and non-slip base, this wrist rest stays in place for a stable and comfortable experience. Perfect for those who appreciate both style and practicality in their workspace.', 'accessories'),
(24, 'Coffee Cat Keycap Set', 499.00, '../uploads/accessories/1742733498_coffee.webp', 500, 'A coffee-inspired theme set of keycaps, blending warm brown and creamy beige tones for a stylish, café-like aesthetic. The mix of solid and accent keys, along with charming coffee-related icons, adds a unique touch to any mechanical keyboard. Made from high-quality PBT material, these keycaps offer a durable, textured feel that resists shine over time. The Cherry profile ensures a comfortable typing experience, making it perfect for both work and play. Whether you\'re a coffee lover or just want a warm, inviting setup, this keycap set is a great way to personalize your keyboard.', 'accessories'),
(25, 'Yetti Blue Keycap Set', 659.00, '../uploads/accessories/1742733507_yetti.webp', 445, 'This Yeti-inspired keycap set features a stunning gradient of icy blues and crisp whites, creating a cool and refreshing aesthetic for any mechanical keyboard. The smooth transition from white to deep blue mimics the feel of a snowy mountain peak, making it a perfect choice for winter or nature lovers. Made from durable PBT material, these keycaps offer a high-quality, textured finish that resists fading and wear. The Cherry profile ensures a comfortable and ergonomic typing experience, ideal for both work and gaming. If you\'re looking to add a sleek and frosty vibe to your setup, this set is a perfect match!', 'accessories'),
(31, 'Logitech MX Master 3s', 3899.00, '../uploads/mouse/1742734365_mx_master_3s.webp', 245, 'The Logitech MX Master 3S is a wireless mouse designed to enhance productivity and comfort for professionals and power users. Building upon the success of its predecessor, the MX Master 3, this model introduces several notable improvements.​\r\n\r\nOne of the key enhancements is the upgraded 8,000 DPI optical sensor, which allows for precise tracking on various surfaces, including glass. This high-resolution sensor ensures accurate cursor movements, catering to tasks that require meticulous attention to detail.\r\n\r\nIn addition to performance upgrades, the MX Master 3S features \"Quiet Click\" buttons, reducing click noise while maintaining tactile feedback. This design is particularly beneficial in shared workspaces where minimal noise is preferred.', 'mouse');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_couriers`
--

CREATE TABLE `shipping_couriers` (
  `courier_id` int(11) NOT NULL,
  `courier_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_couriers`
--

INSERT INTO `shipping_couriers` (`courier_id`, `courier_name`) VALUES
(4, 'Flash Express'),
(2, 'J&T'),
(1, 'LBC'),
(3, 'Ninja Van');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` char(8) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','client') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` char(8) NOT NULL,
  `address` text NOT NULL,
  `postal_code` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `address_id` (`address_id`),
  ADD KEY `shipping_courier_id` (`shipping_courier_id`),
  ADD KEY `payment_method_id` (`payment_method_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `method_name` (`method_name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `shipping_couriers`
--
ALTER TABLE `shipping_couriers`
  ADD PRIMARY KEY (`courier_id`),
  ADD UNIQUE KEY `courier_name` (`courier_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `shipping_couriers`
--
ALTER TABLE `shipping_couriers`
  MODIFY `courier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `user_addresses` (`address_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`shipping_courier_id`) REFERENCES `shipping_couriers` (`courier_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
