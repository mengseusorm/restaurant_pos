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
-- Table structure for table `gateway_options`
--

DROP TABLE IF EXISTS `gateway_options`;
CREATE TABLE IF NOT EXISTS `gateway_options` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `model_id` bigint NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `option` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` tinyint NOT NULL,
  `activities` longtext COLLATE utf8mb4_unicode_ci,
  `creator_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creator_id` bigint DEFAULT NULL,
  `editor_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `editor_id` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gateway_options`
--

INSERT INTO `gateway_options` (`id`, `model_id`, `model_type`, `option`, `value`, `type`, `activities`, `creator_type`, `creator_id`, `editor_type`, `editor_id`, `created_at`, `updated_at`) VALUES
(1, 3, 'App\\Models\\PaymentGateway', 'huione_app_id', '11', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:47:25'),
(2, 3, 'App\\Models\\PaymentGateway', 'huione_secret_key', '11', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:47:30'),
(3, 3, 'App\\Models\\PaymentGateway', 'huione_mode', '5', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:47:18'),
(4, 3, 'App\\Models\\PaymentGateway', 'huione_status', '10', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(5, 4, 'App\\Models\\PaymentGateway', 'paypal_app_id', '1000000', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:29:25'),
(6, 4, 'App\\Models\\PaymentGateway', 'paypal_client_id', NULL, 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:28:36'),
(7, 4, 'App\\Models\\PaymentGateway', 'paypal_client_secret', NULL, 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:28:36'),
(8, 4, 'App\\Models\\PaymentGateway', 'paypal_mode', '5', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:28:36'),
(9, 4, 'App\\Models\\PaymentGateway', 'paypal_status', '10', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(10, 5, 'App\\Models\\PaymentGateway', 'stripe_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(11, 5, 'App\\Models\\PaymentGateway', 'stripe_secret', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(12, 5, 'App\\Models\\PaymentGateway', 'stripe_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(13, 5, 'App\\Models\\PaymentGateway', 'stripe_status', '10', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(14, 6, 'App\\Models\\PaymentGateway', 'flutterwave_public_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(15, 6, 'App\\Models\\PaymentGateway', 'flutterwave_secret_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(16, 6, 'App\\Models\\PaymentGateway', 'flutterwave_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(17, 6, 'App\\Models\\PaymentGateway', 'flutterwave_status', '10', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(18, 7, 'App\\Models\\PaymentGateway', 'paystack_public_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(19, 7, 'App\\Models\\PaymentGateway', 'paystack_secret_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(20, 7, 'App\\Models\\PaymentGateway', 'paystack_payment_url', 'https://api.paystack.co', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(21, 7, 'App\\Models\\PaymentGateway', 'paystack_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(22, 7, 'App\\Models\\PaymentGateway', 'paystack_status', '10', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(23, 8, 'App\\Models\\PaymentGateway', 'sslcommerz_store_name', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(24, 8, 'App\\Models\\PaymentGateway', 'sslcommerz_store_id', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(25, 8, 'App\\Models\\PaymentGateway', 'sslcommerz_store_password', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(26, 8, 'App\\Models\\PaymentGateway', 'sslcommerz_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(27, 8, 'App\\Models\\PaymentGateway', 'sslcommerz_status', '10', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(28, 9, 'App\\Models\\PaymentGateway', 'mollie_api_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(29, 9, 'App\\Models\\PaymentGateway', 'mollie_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(30, 9, 'App\\Models\\PaymentGateway', 'mollie_status', '10', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(31, 10, 'App\\Models\\PaymentGateway', 'senangpay_merchant_id', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(32, 10, 'App\\Models\\PaymentGateway', 'senangpay_secret_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(33, 10, 'App\\Models\\PaymentGateway', 'senangpay_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(34, 10, 'App\\Models\\PaymentGateway', 'senangpay_status', '10', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(35, 11, 'App\\Models\\PaymentGateway', 'bkash_app_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(36, 11, 'App\\Models\\PaymentGateway', 'bkash_app_secret', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(37, 11, 'App\\Models\\PaymentGateway', 'bkash_username', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(38, 11, 'App\\Models\\PaymentGateway', 'bkash_password', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(39, 11, 'App\\Models\\PaymentGateway', 'bkash_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(40, 11, 'App\\Models\\PaymentGateway', 'bkash_status', '', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(41, 12, 'App\\Models\\PaymentGateway', 'paytm_merchant_id', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(42, 12, 'App\\Models\\PaymentGateway', 'paytm_merchant_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(43, 12, 'App\\Models\\PaymentGateway', 'paytm_merchant_website', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(44, 12, 'App\\Models\\PaymentGateway', 'paytm_channel', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(45, 12, 'App\\Models\\PaymentGateway', 'paytm_industry_type', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(46, 12, 'App\\Models\\PaymentGateway', 'paytm_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(47, 12, 'App\\Models\\PaymentGateway', 'paytm_status', '', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(48, 13, 'App\\Models\\PaymentGateway', 'razorpay_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(49, 13, 'App\\Models\\PaymentGateway', 'razorpay_secret', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(50, 13, 'App\\Models\\PaymentGateway', 'razorpay_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(51, 13, 'App\\Models\\PaymentGateway', 'razorpay_status', '', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(52, 14, 'App\\Models\\PaymentGateway', 'mercadopago_client_id', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(53, 14, 'App\\Models\\PaymentGateway', 'mercadopago_client_secret', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(54, 14, 'App\\Models\\PaymentGateway', 'mercadopago_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(55, 14, 'App\\Models\\PaymentGateway', 'mercadopago_status', '10', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(56, 15, 'App\\Models\\PaymentGateway', 'cashfree_app_id', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(57, 15, 'App\\Models\\PaymentGateway', 'cashfree_secret_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(58, 15, 'App\\Models\\PaymentGateway', 'cashfree_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(59, 15, 'App\\Models\\PaymentGateway', 'cashfree_status', '', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(60, 16, 'App\\Models\\PaymentGateway', 'payfast_merchant_id', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(61, 16, 'App\\Models\\PaymentGateway', 'payfast_merchant_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(62, 16, 'App\\Models\\PaymentGateway', 'payfast_passphrase', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(63, 16, 'App\\Models\\PaymentGateway', 'payfast_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(64, 16, 'App\\Models\\PaymentGateway', 'payfast_status', '10', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(65, 17, 'App\\Models\\PaymentGateway', 'skrill_merchant_email', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(66, 17, 'App\\Models\\PaymentGateway', 'skrill_merchant_api_password', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(67, 17, 'App\\Models\\PaymentGateway', 'skrill_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(68, 17, 'App\\Models\\PaymentGateway', 'skrill_status', '10', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(69, 18, 'App\\Models\\PaymentGateway', 'phonepe_merchant_id', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(70, 18, 'App\\Models\\PaymentGateway', 'phonepe_merchant_user_id', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(71, 18, 'App\\Models\\PaymentGateway', 'phonepe_key_index', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(72, 18, 'App\\Models\\PaymentGateway', 'phonepe_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(73, 18, 'App\\Models\\PaymentGateway', 'phonepe_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(74, 18, 'App\\Models\\PaymentGateway', 'phonepe_status', '10', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(75, 19, 'App\\Models\\PaymentGateway', 'telr_store_id', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(76, 19, 'App\\Models\\PaymentGateway', 'telr_store_auth_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(77, 19, 'App\\Models\\PaymentGateway', 'telr_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(78, 19, 'App\\Models\\PaymentGateway', 'telr_status', '10', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(79, 20, 'App\\Models\\PaymentGateway', 'iyzico_api_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(80, 20, 'App\\Models\\PaymentGateway', 'iyzico_secret_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(81, 20, 'App\\Models\\PaymentGateway', 'iyzico_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(82, 20, 'App\\Models\\PaymentGateway', 'iyzico_status', '10', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(83, 21, 'App\\Models\\PaymentGateway', 'pesapal_consumer_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(84, 21, 'App\\Models\\PaymentGateway', 'pesapal_consumer_secret', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(85, 21, 'App\\Models\\PaymentGateway', 'pesapal_ipn_id', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(86, 21, 'App\\Models\\PaymentGateway', 'pesapal_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(87, 21, 'App\\Models\\PaymentGateway', 'pesapal_status', '10', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(88, 22, 'App\\Models\\PaymentGateway', 'midtrans_server_key', '', 5, '\"\"', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(89, 22, 'App\\Models\\PaymentGateway', 'midtrans_mode', '', 10, '{\"5\":\"sandbox\",\"10\":\"live\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01'),
(90, 22, 'App\\Models\\PaymentGateway', 'midtrans_status', '10', 10, '{\"5\":\"enable\",\"10\":\"disable\"}', NULL, NULL, NULL, NULL, '2025-06-20 02:17:01', '2025-06-20 02:17:01');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
