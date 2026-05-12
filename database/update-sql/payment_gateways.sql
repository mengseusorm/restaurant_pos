-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 20, 2025 at 06:58 AM
-- Server version: 9.1.0
-- PHP Version: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pos_db_1`
--

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateways`
--

DROP TABLE IF EXISTS `payment_gateways`;
CREATE TABLE IF NOT EXISTS `payment_gateways` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `misc` longtext COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '5' COMMENT '5=Active, 10=Inactive',
  `creator_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creator_id` bigint DEFAULT NULL,
  `editor_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `editor_id` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_gateways`
--

INSERT INTO `payment_gateways` (`id`, `name`, `slug`, `misc`, `status`, `creator_type`, `creator_id`, `editor_type`, `editor_id`, `created_at`, `updated_at`) VALUES
(1, 'Cash On Delivery', 'cash-on-delivery', 'null', 5, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(2, 'Credit', 'credit', 'null', 5, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(3, 'Huione', 'huione', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(4, 'Paypal', 'paypal', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(5, 'Stripe', 'stripe', '{\"input\":[\"stripe.stripeInput.blade.php\"],\"js\":[\"stripe.stripeJs.blade.php\"],\"submit\":true}', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(6, 'Flutterwave', 'flutterwave', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(7, 'Paystack', 'paystack', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(8, 'SslCommerz', 'sslcommerz', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(9, 'Mollie', 'mollie', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(10, 'Senangpay', 'senangpay', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(11, 'Bkash', 'bkash', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(12, 'Paytm', 'paytm', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(13, 'Razorpay', 'razorpay', '{\"input\":[],\"js\":[\"razorpay.razorpayJs.blade.php\"],\"submit\":false}', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(14, 'Mercadopago', 'mercadopago', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(15, 'Cashfree', 'cashfree', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(16, 'Payfast', 'payfast', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(17, 'Skrill', 'skrill', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(18, 'PhonePe', 'phonepe', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(19, 'Telr', 'telr', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(20, 'Iyzico', 'iyzico', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(21, 'Pesapal', 'pesapal', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(22, 'Midtrans', 'midtrans', 'null', 10, NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
