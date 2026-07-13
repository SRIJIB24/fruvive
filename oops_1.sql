-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 05, 2026 at 01:23 PM
-- Server version: 10.1.29-MariaDB
-- PHP Version: 7.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `oops_1`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `userid` int(11) DEFAULT NULL,
  `productid` int(11) DEFAULT NULL,
  `pname` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `userid`, `productid`, `pname`, `price`, `qty`, `created_at`) VALUES
(1, 3, 1, 'Banana(Robusta)', '50.00', 3, '2026-03-05 12:09:00'),
(2, 3, 10, 'Mango(Himsagar)', '50.00', 9, '2026-03-05 12:09:07'),
(3, 3, 30, 'Almonds(california)', '450.00', 1, '2026-03-05 12:09:16');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(4) NOT NULL,
  `cname` varchar(40) NOT NULL,
  `edtm` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `cname`, `edtm`) VALUES
(1, ' Daily Fruits', '2026-02-13 04:51:13'),
(2, 'Seasonal Fruits', '2026-02-13 05:14:28'),
(3, 'Dry Fruits', '2026-02-13 04:54:36'),
(4, 'Cut Fruit Cups', '2026-02-13 05:14:38'),
(5, 'Gift Baskets', '2026-02-13 05:14:42');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `proid` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `expect_date` date NOT NULL,
  `delivery_date` date NOT NULL,
  `pack_quant` int(11) NOT NULL,
  `pack_price` decimal(10,2) NOT NULL,
  `total_packs` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `assign_man_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `edtm` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(4) NOT NULL,
  `pname` varchar(100) NOT NULL,
  `cid` int(4) NOT NULL,
  `quant` varchar(100) NOT NULL,
  `edtm` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `pname`, `cid`, `quant`, `edtm`) VALUES
(1, 'Banana(Robusta)', 1, '12 Pcs', '2026-02-13 10:29:08'),
(2, 'Banana(Martaman)', 1, '12 Pcs', '2026-02-13 10:31:54'),
(3, 'Apple(Red Delicious)', 1, '1 Kg', '2026-02-13 10:40:09'),
(4, 'Royal Gala', 1, '1 kg', '2026-02-13 10:40:29'),
(5, 'Apple(Fuji)', 1, '1 Kg', '2026-02-13 10:40:49'),
(6, 'Papaya', 1, '1 Kg', '2026-02-13 10:41:08'),
(7, 'Lemon', 1, '1 Kg', '2026-02-13 10:41:20'),
(8, 'Guava', 1, '1 Kg', '2026-02-13 10:41:53'),
(9, 'Pomegranate(Bedana)', 1, '1 Kg', '2026-02-13 10:43:19'),
(10, 'Mango(Himsagar)', 2, '1 Kg', '2026-02-13 10:47:21'),
(11, 'Mango(Langra)', 2, '1 Kg', '2026-02-13 10:47:44'),
(12, 'Mango(Chausa)', 2, '1 Kg', '2026-02-13 10:48:21'),
(13, 'Mango(Amrapali)', 2, '1 Kg', '2026-02-13 10:48:41'),
(14, 'Mango(Fazli)', 2, '1 Kg', '2026-02-13 10:49:06'),
(15, 'Lichi', 2, '1 Kg', '2026-02-13 10:49:49'),
(16, 'Watermelon', 2, '1 Kg', '2026-02-13 10:50:10'),
(17, 'Grapes(Green)', 2, '1 Kg', '2026-02-13 10:50:56'),
(18, 'Grapes(Black/Red)', 2, '1 Kg', '2026-02-13 10:51:27'),
(19, 'Custard Apple(Ata)', 2, '1 Kg', '2026-02-13 10:51:51'),
(20, 'Black Plum(Jamun)', 2, '500 g', '2026-02-13 12:05:56'),
(21, 'Naspati', 2, '1 Kg', '2026-02-13 10:52:29'),
(22, 'Pineapple', 2, '1 Pcs', '2026-02-13 10:53:17'),
(23, 'Jujube(Kul Narkeli)', 2, '1 Kg', '2026-02-13 10:55:03'),
(24, 'Jujube(Kul Topa)', 2, '1 Kg', '2026-02-13 10:55:30'),
(25, 'Green Coconut(Daab)', 1, '1 Pcs', '2026-02-13 10:56:48'),
(26, 'Darjeeling Orange', 2, '1 Kg', '2026-02-13 10:59:57'),
(27, 'Nagpur Orange', 2, '1 Kg', '2026-02-13 11:00:11'),
(28, 'Mosambi', 1, '1 Kg', '2026-02-13 11:01:23'),
(29, 'Malta Orange', 1, '1 Kg', '2026-02-13 11:02:52'),
(30, 'Almonds(california)', 3, '500 g', '2026-02-13 11:12:10'),
(31, 'Cashews', 3, '500 g', '2026-02-13 11:12:28'),
(32, 'Walnuts', 3, '250 g', '2026-02-13 11:26:54'),
(33, 'Raisins', 3, '250 g', '2026-02-13 11:13:10'),
(34, 'Dates', 3, '500 g', '2026-02-13 11:13:42'),
(35, 'Makhana', 3, '250 g', '2026-02-13 11:14:03'),
(36, 'Figs(Anjeer)', 3, '250 g', '2026-02-13 11:14:38');

-- --------------------------------------------------------

--
-- Table structure for table `stock_in`
--

CREATE TABLE `stock_in` (
  `id` int(11) NOT NULL,
  `catid` int(11) NOT NULL,
  `proid` int(11) NOT NULL,
  `date` date NOT NULL,
  `pack_quant` varchar(11) NOT NULL,
  `pack_price` decimal(10,2) NOT NULL,
  `total_quant` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `edtm` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `stock_in`
--

INSERT INTO `stock_in` (`id`, `catid`, `proid`, `date`, `pack_quant`, `pack_price`, `total_quant`, `total_price`, `edtm`) VALUES
(1, 3, 30, '2026-02-12', '500 g', '450.00', 30, '13500.00', '2026-02-13 11:42:32'),
(2, 3, 31, '2026-02-12', '500 g', '500.00', 30, '15000.00', '2026-02-13 11:42:43'),
(3, 3, 32, '2026-02-12', '250 g', '350.00', 20, '7000.00', '2026-02-13 11:42:52'),
(4, 3, 33, '2026-02-12', '250 g', '150.00', 30, '4500.00', '2026-02-13 11:32:11'),
(5, 3, 34, '2026-02-12', '500 g', '120.00', 50, '6000.00', '2026-02-13 11:32:44'),
(6, 3, 36, '2026-02-12', '250 g', '250.00', 20, '5000.00', '2026-02-13 11:35:25'),
(7, 3, 35, '2026-02-12', '250 g', '150.00', 40, '6000.00', '2026-02-13 11:35:12'),
(8, 1, 1, '2026-02-12', '12 Pcs', '50.00', 60, '3000.00', '2026-02-13 11:37:53'),
(9, 1, 2, '2026-02-12', '12 Pcs', '36.00', 60, '2160.00', '2026-02-13 11:38:19'),
(10, 1, 3, '2026-02-12', '1 Kg', '120.00', 40, '4800.00', '2026-02-13 11:43:27'),
(11, 1, 4, '2026-02-12', '1 kg', '160.00', 20, '3200.00', '2026-02-13 11:39:02'),
(12, 1, 5, '2026-02-12', '1 Kg', '200.00', 20, '4000.00', '2026-02-13 11:43:08'),
(13, 1, 6, '2026-02-12', '1 Kg', '40.00', 20, '800.00', '2026-02-13 11:40:10'),
(14, 1, 7, '2026-02-12', '1 Kg', '120.00', 40, '4800.00', '2026-02-13 11:40:39'),
(15, 1, 8, '2026-02-12', '1 Kg', '50.00', 25, '1250.00', '2026-02-13 11:41:00'),
(16, 1, 9, '2026-02-12', '1 Kg', '160.00', 30, '4800.00', '2026-02-13 11:41:19'),
(17, 1, 25, '2026-02-12', '1 Pcs', '50.00', 60, '3000.00', '2026-02-13 11:41:43'),
(18, 1, 28, '2026-02-12', '1 Kg', '80.00', 30, '2400.00', '2026-02-13 11:42:01'),
(19, 1, 29, '2026-02-12', '1 Kg', '140.00', 40, '5600.00', '2026-02-13 11:42:16'),
(20, 2, 10, '2026-02-12', '1 Kg', '50.00', 40, '2000.00', '2026-02-13 12:02:54'),
(21, 2, 11, '2026-02-12', '1 Kg', '55.00', 40, '2200.00', '2026-02-13 12:03:13'),
(22, 2, 12, '2026-02-12', '1 Kg', '80.00', 30, '2400.00', '2026-02-13 12:03:36'),
(23, 2, 13, '2026-02-12', '1 Kg', '30.00', 50, '1500.00', '2026-02-13 12:03:55'),
(24, 2, 14, '2026-02-12', '1 Kg', '35.00', 40, '1400.00', '2026-02-13 12:04:12'),
(25, 2, 16, '2026-02-12', '1 Kg', '20.00', 80, '1600.00', '2026-02-13 12:04:33'),
(26, 2, 15, '2026-02-12', '1 Kg', '120.00', 100, '12000.00', '2026-02-13 12:04:57'),
(27, 2, 20, '2026-02-12', '500 g', '80.00', 40, '3200.00', '2026-02-13 12:07:00'),
(28, 2, 19, '2026-02-12', '1 Kg', '140.00', 20, '2800.00', '2026-02-13 12:07:27'),
(29, 2, 21, '2026-02-12', '1 Kg', '40.00', 40, '1600.00', '2026-02-13 12:07:48'),
(30, 2, 23, '2026-02-12', '1 Kg', '40.00', 20, '800.00', '2026-02-13 12:08:10'),
(31, 2, 24, '2026-02-12', '1 Kg', '25.00', 15, '375.00', '2026-02-13 12:08:32'),
(32, 2, 26, '2026-02-12', '1 Kg', '120.00', 40, '4800.00', '2026-02-13 12:09:28'),
(33, 2, 27, '2026-02-12', '1 Kg', '80.00', 50, '4000.00', '2026-02-13 12:09:47'),
(34, 2, 17, '2026-02-12', '1 Kg', '80.00', 20, '1600.00', '2026-02-13 12:10:05'),
(35, 2, 18, '2026-02-12', '1 Kg', '125.00', 20, '2500.00', '2026-02-13 12:10:25'),
(36, 2, 22, '2026-02-12', '1 Pcs', '40.00', 20, '800.00', '2026-02-13 12:15:51');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `pass` varchar(255) DEFAULT NULL,
  `userlevel` int(11) NOT NULL,
  `lastlogin` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `pass`, `userlevel`, `lastlogin`) VALUES
(1, 'admin', 'admin@gmail.com ', '0000', -1, '2026-02-14 10:20:34'),
(2, 'srijib pal', 'srijib@gmail.com', 'srijib12', 10, '2026-02-12 10:20:15'),
(3, 'xyz', 'xyz@gmail.com', '123', 10, '2026-03-05 12:59:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_in`
--
ALTER TABLE `stock_in`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `stock_in`
--
ALTER TABLE `stock_in`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
