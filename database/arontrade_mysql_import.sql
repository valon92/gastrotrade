-- AronTrade - MySQL/MariaDB import (për phpMyAdmin)
-- Përdorim: Import në databazën aronqbxm_arontrade. Mos përdor dump nga SQLite (PRAGMA).

USE `aronqbxm_arontrade`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ========== DROP (nëse ekzistojnë) ==========
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `client_prices`;
DROP TABLE IF EXISTS `client_locations`;
DROP TABLE IF EXISTS `clients`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `suppliers`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `personal_access_tokens`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `migrations`;

-- ========== USERS & AUTH ==========
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `role` varchar(255) NOT NULL DEFAULT 'order_manager',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== CACHE & JOBS ==========
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== CATEGORIES ==========
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== SUPPLIERS ==========
CREATE TABLE `suppliers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `notes` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== PRODUCTS ==========
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `size` varchar(50) DEFAULT NULL,
  `liters` varchar(20) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `cost_price` decimal(10,2) DEFAULT NULL,
  `min_stock_level` int NOT NULL DEFAULT '0',
  `image_path` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `sold_by_package` tinyint(1) NOT NULL DEFAULT '0',
  `pieces_per_package` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_supplier_id_foreign` (`supplier_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== PRODUCT IMAGES ==========
CREATE TABLE `product_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_foreign` (`product_id`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== CLIENTS ==========
CREATE TABLE `clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `store_name` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `street_number` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `viber` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `fiscal_number` varchar(255) DEFAULT NULL,
  `notes` text,
  `password` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `allow_piece_sales` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clients_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== CLIENT PRICES ==========
CREATE TABLE `client_prices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `notes` text,
  `allow_piece_sales` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_prices_client_id_product_id_unique` (`client_id`,`product_id`),
  KEY `client_prices_product_id_foreign` (`product_id`),
  CONSTRAINT `client_prices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `client_prices_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== CLIENT LOCATIONS ==========
CREATE TABLE `client_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint unsigned NOT NULL,
  `unit_name` varchar(255) NOT NULL,
  `street_number` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `viber` varchar(255) DEFAULT NULL,
  `notes` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_locations_client_id_foreign` (`client_id`),
  CONSTRAINT `client_locations_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== ORDERS ==========
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint unsigned DEFAULT NULL,
  `client_location_id` bigint unsigned DEFAULT NULL,
  `order_number` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `business_name` varchar(255) NOT NULL,
  `fiscal_number` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `viber` varchar(255) DEFAULT NULL,
  `total_items` int unsigned NOT NULL DEFAULT '0',
  `total_amount` decimal(12,2) DEFAULT NULL,
  `has_vat` tinyint(1) NOT NULL DEFAULT '0',
  `vat_amount` decimal(10,2) DEFAULT NULL,
  `amount_before_vat` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT NULL,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_type` varchar(255) DEFAULT NULL,
  `discount_value` decimal(12,2) DEFAULT NULL,
  `summary` json DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'ruajtur',
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `paid_at` timestamp NULL DEFAULT NULL,
  `location_unit_name` varchar(255) DEFAULT NULL,
  `location_street_number` varchar(255) DEFAULT NULL,
  `location_phone` varchar(255) DEFAULT NULL,
  `location_viber` varchar(255) DEFAULT NULL,
  `location_city` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_client_id_foreign` (`client_id`),
  KEY `orders_client_location_id_foreign` (`client_location_id`),
  CONSTRAINT `orders_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_client_location_id_foreign` FOREIGN KEY (`client_location_id`) REFERENCES `client_locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== ORDER ITEMS ==========
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int unsigned NOT NULL,
  `sold_by_package` tinyint(1) NOT NULL DEFAULT '0',
  `pieces_per_package` int unsigned DEFAULT NULL,
  `unit_type` varchar(255) DEFAULT NULL,
  `unit_price` decimal(12,2) DEFAULT NULL,
  `total_price` decimal(12,2) DEFAULT NULL,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_type` varchar(255) DEFAULT NULL,
  `discount_value` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== PERSONAL ACCESS TOKENS ==========
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== MIGRATIONS (Laravel) ==========
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ========== SEED: CATEGORIES ==========
INSERT INTO `categories` (`name`,`slug`,`description`,`created_at`,`updated_at`) VALUES
('Pipëza','pipeza','Pipëza plastike për pije të ndryshme',NOW(),NOW()),
('Përzierës','perzieres','Përzierës kafeje dhe shkopinj druri',NOW(),NOW()),
('Alumin/Fletë','alumin-flete','Folie alumini dhe fletë të tjera',NOW(),NOW()),
('Peceta/Letër','peceta-leter','Peceta kuzhine dhe letër të pastër',NOW(),NOW()),
('Mbeturina','mbeturina','Kese mbeturinash dhe kontejnerë',NOW(),NOW()),
('Të Lagura','te-lagura','Peceta të lagura dhe baby wipes',NOW(),NOW()),
('Të Tjera','te-tjera','Produkte të tjera për kuzhinë',NOW(),NOW());

-- ========== SEED: ADMIN USER ==========
-- Email: svalon95@gmail.com  |  Password: Valon123
INSERT INTO `users` (`name`,`email`,`password`,`is_admin`,`role`,`created_at`,`updated_at`) VALUES
('Admin','svalon95@gmail.com','$2y$12$pCzTTr3C/wOmbzilbcUewe368Se1YC5fc5MmnqDZJIStIz1bR4DNq',1,'admin',NOW(),NOW());

-- ========== SEED: KLIENTË TEST (kyçje në faqe) ==========
-- svalon95@gmail.com / Valon123  |  klient1@test.com / password  |  klient2@test.com / password
INSERT INTO `clients` (`name`,`store_name`,`email`,`password`,`is_active`,`created_at`,`updated_at`) VALUES
('Valon Test','Dyqani Valon','svalon95@gmail.com','$2y$12$pCzTTr3C/wOmbzilbcUewe368Se1YC5fc5MmnqDZJIStIz1bR4DNq',1,NOW(),NOW()),
('Klient Test 1','Biznesi 1','klient1@test.com','$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NOW(),NOW()),
('Klient Test 2','Biznesi 2','klient2@test.com','$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,NOW(),NOW());

-- ========== SEED: PRODUCTS (të gjitha si në ProductSeeder) ==========
INSERT INTO `products` (`category_id`,`name`,`slug`,`description`,`image_path`,`is_featured`,`sort_order`,`sold_by_package`,`pieces_per_package`,`created_at`,`updated_at`) VALUES
(2,'Luga Kafe E Bardh 10cp Kompleti','luga-kafe-e-bardh-10cp-kompleti','Luga kafe e bardhë profesionale 10cp për përzierjen e kafesë. Materiali i sigurt dhe i qëndrueshëm. 1 kompleti = 10 copa.','/images/lugakafe.jpeg',0,0,1,10,NOW(),NOW()),
(1,'Pipëza Plastike të Zeza','pipeza-plastike-te-zeza','Pipëza plastike të zeza të cilësisë së lartë. 100cp, 200cp, 500cp. 1 kompleti = 20 copa.','/images/IMG_6821.jpeg',1,0,1,20,NOW(),NOW()),
(3,'Folie Alumini 9 Micron','folie-alumini-9-micron','Folie alumini profesionale 9 micron për ruajtjen dhe mbrojtjen e ushqimit.','/images/IMG_6814.jpeg',1,0,0,NULL,NOW(),NOW()),
(4,'Letër Kuzhine NUSH 2 Shtresa','leter-kuzhine-nush-2-shtresa','Letër kuzhine NUSH 2 shtresa, 100% celulozë. 1 kompleti = 12 copa.','/images/Pallomat/XL/foto1.png',1,0,1,12,NOW(),NOW()),
(4,'Letër Salvete','leter-salvete','Salvete letre për tavolina dhe nevoja të ndryshme.','/images/Pallomat/Salvete/foto1.jpg',1,0,0,NULL,NOW(),NOW()),
(6,'Baby Wipes Canéa Aqua Pure','baby-wipes-canea-aqua-pure','Për fëmijë dhe kuzhinë.','/images/IMG_6818.jpeg',1,0,0,NULL,NOW(),NOW()),
(1,'Pipëza Transparente për Pije','pipeza-transparente-per-pije','Pipëza transparente për pije. 1 kompleti = 20 copa.','/images/IMG_6819.jpeg',0,0,1,20,NOW(),NOW()),
(5,'Kese Mbeturinash 40L','kese-mbeturinash-40l','Kese mbeturinash 40L të forta. 1 kompleti = 20 copa.','/images/IMG_6815.jpeg',1,1,1,20,NOW(),NOW()),
(5,'Kese Mbeturinash 70L','kese-mbeturinash-70l','Kese mbeturinash 70L të forta. 1 kompleti = 20 copa.','/images/IMG_6815.jpeg',1,2,1,20,NOW(),NOW()),
(5,'Kese Mbeturinash 120L','kese-mbeturinash-120l','Kese mbeturinash 120L të forta. 1 kompleti = 20 copa.','/images/IMG_6815.jpeg',1,3,1,20,NOW(),NOW()),
(5,'Kese Mbeturinash 150L','kese-mbeturinash-150l','Kese mbeturinash 150L të forta. 1 kompleti = 20 copa.','/images/IMG_6815.jpeg',1,4,1,20,NOW(),NOW()),
(5,'Kese Mbeturinash 170L','kese-mbeturinash-170l','Kese mbeturinash 170L të forta. 1 kompleti = 20 copa.','/images/IMG_6815.jpeg',1,5,1,20,NOW(),NOW()),
(5,'Kese Mbeturinash 200L','kese-mbeturinash-200l','Kese mbeturinash 200L të forta. 1 kompleti = 20 copa.','/images/IMG_6815.jpeg',1,6,1,20,NOW(),NOW()),
(5,'Kese Mbeturinash 240L','kese-mbeturinash-240l','Kese mbeturinash 240L të forta. 1 kompleti = 20 copa.','/images/IMG_6815.jpeg',1,7,1,20,NOW(),NOW()),
(5,'Kese Mbeturinash 270L','kese-mbeturinash-270l','Kese mbeturinash 270L të forta. 1 kompleti = 20 copa.','/images/IMG_6815.jpeg',1,8,1,20,NOW(),NOW()),
(5,'Kese Mbeturinash 300L','kese-mbeturinash-300l','Kese mbeturinash 300L të forta. 1 kompleti = 20 copa.','/images/IMG_6815.jpeg',1,9,1,20,NOW(),NOW()),
(5,'Kese Mbeturinash 70L ME LIDHSE','kese-mbeturinash-70l-me-lidhse','Kese mbeturinash 70L me lidhse. 1 kompleti = 20 copa.','/images/IMG_6815.jpeg',1,10,1,20,NOW(),NOW()),
(5,'Kese Mbeturinash 120L ME LIDHSE','kese-mbeturinash-120l-me-lidhse','Kese mbeturinash 120L me lidhse. 1 kompleti = 20 copa.','/images/IMG_6815.jpeg',1,11,1,20,NOW(),NOW()),
(5,'Kese Mbeturinash 200L ME LIDHSE','kese-mbeturinash-200l-me-lidhse','Kese mbeturinash 200L me lidhse. 1 kompleti = 20 copa.','/images/IMG_6815.jpeg',1,12,1,20,NOW(),NOW()),
(5,'Kese Mbeturinash 220L ME LIDHSE','kese-mbeturinash-220l-me-lidhse','Kese mbeturinash 220L me lidhse. 1 kompleti = 20 copa.','/images/IMG_6815.jpeg',1,13,1,20,NOW(),NOW()),
(1,'Pipëza me Ngjyra të Ndryshme','pipeza-me-ngjyra-te-ndryshme','Pipëza me ngjyra për pije. 1 kompleti = 20 copa.','/images/IMG_6816.jpeg',0,0,1,20,NOW(),NOW()),
(7,'Mbulesë Tavoline LDP','mbulese-tavoline-ldp','Mbulesë tavoline LDP për mbrojtjen e tavolinave.','/images/IMG_6817.jpeg',0,0,0,NULL,NOW(),NOW()),
(7,'Gota Letre Per Kafe 2.5oz','gota-leter-per-kafe-2-5oz','Gota letre 2.5oz për kafe. 1 kompleti = 40 copa.','/images/IMG_6822.jpeg',1,0,1,40,NOW(),NOW()),
(7,'Gota Letre Per Kafe 3oz','gota-leter-per-kafe-3oz','Gota letre 3oz për kafe. 1 kompleti = 20 copa.','/images/IMG_6823.jpeg',1,0,1,20,NOW(),NOW()),
(7,'Gota Letre Per Kafe 4oz','gota-leter-per-kafe-4oz','Gota letre 4oz për kafe. 1 kompleti = 20 copa.','/images/IMG_6824.jpeg',1,0,1,20,NOW(),NOW()),
(7,'Gota Letre Per Kafe 7oz','gota-leter-per-kafe-7oz','Gota letre 7oz për kafe. 1 kompleti = 20 copa.','/images/IMG_6825.jpeg',1,0,1,20,NOW(),NOW()),
(7,'Gota Plastike','gota-plastike','Gota plastike për pije të nxehta dhe të ftohta.','/images/Gota Plastike/foto1.jpg',1,0,0,NULL,NOW(),NOW()),
(7,'Produkt Shtesë 1','produkt-shtese-1','Produkt shtesë i cilësisë së lartë për kuzhinë.','/images/IMG_6821.jpeg',0,0,0,NULL,NOW(),NOW()),
(7,'Produkt Shtesë 2','produkt-shtese-2','Produkt shtesë profesional për kuzhinë.','/images/IMG_6821.jpeg',0,0,0,NULL,NOW(),NOW()),
(7,'Produkt Shtesë 3','produkt-shtese-3','Produkt shtesë i cilësisë së lartë për kuzhinë.','/images/IMG_6821.jpeg',0,0,0,NULL,NOW(),NOW());

-- ========== SEED: PRODUCT IMAGES (1 për produkt) ==========
INSERT INTO `product_images` (`product_id`,`file_name`,`file_path`,`is_featured`,`sort_order`,`created_at`,`updated_at`)
SELECT `id`, SUBSTRING_INDEX(`image_path`,'/',-1), `image_path`, 1, 1, NOW(), NOW() FROM `products`;

-- ========== BARCODE & SPECS: Kese Mbeturinash (size, liters, barcode) ==========
UPDATE `products` SET `size`='45x55',  `liters`='40L',  `barcode`='GT-KM-40L'   WHERE `slug`='kese-mbeturinash-40l';
UPDATE `products` SET `size`='55x75',  `liters`='70L',  `barcode`='GT-KM-70L'   WHERE `slug`='kese-mbeturinash-70l';
UPDATE `products` SET `size`='65x110', `liters`='120L', `barcode`='GT-KM-120L'  WHERE `slug`='kese-mbeturinash-120l';
UPDATE `products` SET `size`='70x115', `liters`='150L', `barcode`='GT-KM-150L'  WHERE `slug`='kese-mbeturinash-150l';
UPDATE `products` SET `size`='75x120', `liters`='170L', `barcode`='GT-KM-170L'  WHERE `slug`='kese-mbeturinash-170l';
UPDATE `products` SET `size`='80x125', `liters`='200L', `barcode`='GT-KM-200L'  WHERE `slug`='kese-mbeturinash-200l';
UPDATE `products` SET `size`='85x130', `liters`='240L', `barcode`='GT-KM-240L'  WHERE `slug`='kese-mbeturinash-240l';
UPDATE `products` SET `size`='90x130', `liters`='270L', `barcode`='GT-KM-270L'  WHERE `slug`='kese-mbeturinash-270l';
UPDATE `products` SET `size`='95x140', `liters`='300L', `barcode`='GT-KM-300L'  WHERE `slug`='kese-mbeturinash-300l';
UPDATE `products` SET `size`='55x75',  `liters`='70L',  `barcode`='GT-KM-70L-ML'  WHERE `slug`='kese-mbeturinash-70l-me-lidhse';
UPDATE `products` SET `size`='65x110', `liters`='120L', `barcode`='GT-KM-120L-ML' WHERE `slug`='kese-mbeturinash-120l-me-lidhse';
UPDATE `products` SET `size`='80x125', `liters`='200L', `barcode`='GT-KM-200L-ML' WHERE `slug`='kese-mbeturinash-200l-me-lidhse';
UPDATE `products` SET `size`='82x128', `liters`='220L', `barcode`='GT-KM-220L-ML' WHERE `slug`='kese-mbeturinash-220l-me-lidhse';

-- ========== BARCODE & SPECS: Gota Letre (size, barcode) ==========
UPDATE `products` SET `size`='2.5oz', `barcode`='GT-GL-2.5oz' WHERE `slug`='gota-leter-per-kafe-2-5oz';
UPDATE `products` SET `size`='3oz',   `barcode`='GT-GL-3oz'   WHERE `slug`='gota-leter-per-kafe-3oz';
UPDATE `products` SET `size`='4oz',   `barcode`='GT-GL-4oz'   WHERE `slug`='gota-leter-per-kafe-4oz';
UPDATE `products` SET `size`='7oz',   `barcode`='GT-GL-7oz'   WHERE `slug`='gota-leter-per-kafe-7oz';

-- ========== BARCODE: për produktet e tjera (GT-SLUG) ==========
UPDATE `products` SET `barcode` = CONCAT('GT-', UPPER(`slug`)) WHERE `barcode` IS NULL OR `barcode` = '';

-- ========== MIGRATIONS TABLE (Laravel - batch 1) ==========
INSERT INTO `migrations` (`migration`,`batch`) VALUES
('0001_01_01_000000_create_users_table',1),
('0001_01_01_000001_create_cache_table',1),
('0001_01_01_000002_create_jobs_table',1),
('2025_10_27_174917_create_categories_table',1),
('2025_10_27_174920_create_products_table',1),
('2025_10_27_174922_create_product_images_table',1),
('2025_11_24_080307_add_sort_order_to_products_table',1),
('2025_11_26_043015_create_clients_table',1),
('2025_11_26_043017_create_client_prices_table',1),
('2025_11_26_043140_add_is_admin_to_users_table',1),
('2025_11_26_044306_create_personal_access_tokens_table',1),
('2025_11_26_051605_add_package_info_to_products_table',1),
('2025_11_26_130000_create_orders_table',1),
('2025_11_26_130100_create_order_items_table',1),
('2025_11_26_140500_add_fiscal_number_to_clients_table',1),
('2025_11_26_150000_update_clients_table_business_name_required',1),
('2025_11_26_160000_add_allow_piece_sales_to_clients_table',1),
('2025_11_26_170000_add_payment_fields_to_orders_table',1),
('2025_11_26_180000_make_phone_nullable_in_orders_table',1),
('2025_11_26_190000_add_discount_fields_to_order_items_table',1),
('2025_11_26_191000_add_discount_fields_to_orders_table',1),
('2025_11_27_075420_add_fiscal_number_to_orders_table',1),
('2025_11_28_060714_make_description_nullable_in_products_table',1),
('2025_11_28_082116_create_suppliers_table',1),
('2025_11_28_082119_add_stock_fields_to_products_table',1),
('2025_11_28_063459_add_has_vat_to_orders_table',1),
('2025_11_28_162553_add_deleted_at_to_orders_table',1),
('2025_11_28_162615_add_deleted_at_to_clients_table',1),
('2025_11_28_162616_add_deleted_at_to_products_table',1),
('2025_11_28_170259_add_allow_piece_sales_to_client_prices_table',1),
('2025_12_03_055304_add_unit_type_to_order_items_table',1),
('2025_12_04_083935_add_street_number_and_unit_to_clients_table',1),
('2025_12_04_090000_create_client_locations_table',1),
('2025_12_05_100000_add_client_location_id_to_orders_table',1),
('2025_12_05_110000_add_location_fields_to_orders_table',1),
('2025_12_07_120000_add_role_to_users_table',1),
('2026_02_19_044602_add_specs_and_barcode_to_products_table',1),
('2026_02_21_120000_add_password_to_clients_table',1),
('2026_03_01_120000_clients_email_login_and_nullable_store_name',1);
