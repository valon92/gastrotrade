-- PLOTËSUES: përdore këtë skedar VETËM nëse ke fshirë vetëm tabelat products dhe product_images
-- (nuk fshin users, categories, clients, orders). Kategoritë (1–7) duhet të ekzistojnë.

USE `aronqbxm_arontrade`;

SET NAMES utf8mb4;

-- ========== Krijimi i tabelave products dhe product_images nëse mungojnë ==========
CREATE TABLE IF NOT EXISTS `products` (
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

CREATE TABLE IF NOT EXISTS `product_images` (
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

-- ========== INSERT: të gjitha 35 produktet ==========
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

-- ========== PRODUCT IMAGES (1 për çdo produkt) ==========
INSERT INTO `product_images` (`product_id`,`file_name`,`file_path`,`is_featured`,`sort_order`,`created_at`,`updated_at`)
SELECT `id`, SUBSTRING_INDEX(`image_path`,'/',-1), `image_path`, 1, 1, NOW(), NOW() FROM `products`;

-- ========== BARCODE & SPECS: Kese ==========
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

-- ========== BARCODE & SPECS: Gota Letre ==========
UPDATE `products` SET `size`='2.5oz', `barcode`='GT-GL-2.5oz' WHERE `slug`='gota-leter-per-kafe-2-5oz';
UPDATE `products` SET `size`='3oz',   `barcode`='GT-GL-3oz'   WHERE `slug`='gota-leter-per-kafe-3oz';
UPDATE `products` SET `size`='4oz',   `barcode`='GT-GL-4oz'   WHERE `slug`='gota-leter-per-kafe-4oz';
UPDATE `products` SET `size`='7oz',   `barcode`='GT-GL-7oz'   WHERE `slug`='gota-leter-per-kafe-7oz';

-- ========== BARCODE: për pjesën tjetër ==========
UPDATE `products` SET `barcode` = CONCAT('GT-', UPPER(`slug`)) WHERE `barcode` IS NULL OR `barcode` = '';
