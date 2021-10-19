-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th8 23, 2021 lúc 11:46 AM
-- Phiên bản máy phục vụ: 10.4.14-MariaDB
-- Phiên bản PHP: 7.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `bkrm_v2`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `attendances`
--

CREATE TABLE `attendances` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `status` enum('enable','disable') NOT NULL DEFAULT 'enable',
  `created_datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `point` int(11) NOT NULL DEFAULT 0,
  `customer_code` varchar(255) DEFAULT NULL,
  `created_datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `total_sell_price` int(11) NOT NULL,
  `discount` int(11) NOT NULL,
  `created_datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('success','reserved') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `price_id` int(11) NOT NULL,
  `point_ratio` float NOT NULL DEFAULT 0,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `bar_code` varchar(20) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `point_ratio` float DEFAULT NULL,
  `created_datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `item_categories`
--

CREATE TABLE `item_categories` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `point_ratio` float NOT NULL DEFAULT 0,
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `item_exchange_rates`
--

CREATE TABLE `item_exchange_rates` (
  `id` int(11) NOT NULL,
  `from_item` int(11) NOT NULL,
  `to_item` int(11) NOT NULL,
  `rate` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `item_prices`
--

CREATE TABLE `item_prices` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `sell_price` int(11) NOT NULL,
  `change_by` int(11) DEFAULT NULL,
  `start_date` date NOT NULL DEFAULT current_timestamp(),
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `item_quantities`
--

CREATE TABLE `item_quantities` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jwt_info`
--

CREATE TABLE `jwt_info` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` text NOT NULL,
  `is_invalidated` tinyint(1) NOT NULL DEFAULT 0,
  `created_datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `purchased_items`
--

CREATE TABLE `purchased_items` (
  `id` int(11) NOT NULL,
  `purchased_sheet_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `purchase_price` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `purchased_sheets`
--

CREATE TABLE `purchased_sheets` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `purchaser_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `total_purchase_price` int(11) NOT NULL,
  `discount` int(11) NOT NULL DEFAULT 0,
  `deliver_name` varchar(255) DEFAULT NULL,
  `delivery_datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quantity_checking_items`
--

CREATE TABLE `quantity_checking_items` (
  `id` int(11) NOT NULL,
  `quant_checking_sheet_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `changes` varchar(255) NOT NULL,
  `old_quant` int(11) NOT NULL,
  `new_quant` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quantity_checking_sheets`
--

CREATE TABLE `quantity_checking_sheets` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `checker_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `refund_items`
--

CREATE TABLE `refund_items` (
  `id` int(11) NOT NULL,
  `refund_sheet_id` int(11) NOT NULL,
  `invoice_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `refund_sheets`
--

CREATE TABLE `refund_sheets` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `refunder_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `total_refund_price` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `return_purchased_items`
--

CREATE TABLE `return_purchased_items` (
  `id` int(11) NOT NULL,
  `return_sheet_id` int(11) NOT NULL,
  `purchased_item_id` int(11) NOT NULL,
  `old_purchased_price` int(11) NOT NULL,
  `old_quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `return_purchased_sheets`
--

CREATE TABLE `return_purchased_sheets` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `returner_id` int(11) NOT NULL,
  `purchased_sheet_id` int(11) NOT NULL,
  `total_return_money` int(11) NOT NULL DEFAULT 0,
  `created_datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_date` date NOT NULL DEFAULT current_timestamp(),
  `end_date` date DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shifts`
--

CREATE TABLE `shifts` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `monday` tinyint(1) NOT NULL DEFAULT 0,
  `tuesday` tinyint(1) NOT NULL DEFAULT 0,
  `wednesday` tinyint(1) NOT NULL DEFAULT 0,
  `thursday` tinyint(1) NOT NULL DEFAULT 0,
  `friday` tinyint(1) NOT NULL DEFAULT 0,
  `saturday` tinyint(1) NOT NULL DEFAULT 0,
  `sunday` tinyint(1) NOT NULL DEFAULT 0,
  `start_date` date NOT NULL DEFAULT current_timestamp(),
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `stores`
--

CREATE TABLE `stores` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar_url` varchar(255) NOT NULL DEFAULT 'upload/avatar/default_user.png',
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `status` enum('enable','disable') DEFAULT 'enable'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `works`
--

CREATE TABLE `works` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_attendances_schedules` (`schedule_id`);

--
-- Chỉ mục cho bảng `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_branches_stores` (`store_id`);

--
-- Chỉ mục cho bảng `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_customers_branches` (`branch_id`);

--
-- Chỉ mục cho bảng `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_invoices_users` (`seller_id`),
  ADD KEY `fk_invoices_customers` (`customer_id`),
  ADD KEY `fk_invoices_branches` (`branch_id`);

--
-- Chỉ mục cho bảng `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_invoice_items_invoices` (`invoice_id`),
  ADD KEY `fk_invoice_items_item_prices` (`price_id`);

--
-- Chỉ mục cho bảng `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_items_item_categories` (`category_id`);

--
-- Chỉ mục cho bảng `item_categories`
--
ALTER TABLE `item_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_item_categories_branches` (`branch_id`);

--
-- Chỉ mục cho bảng `item_exchange_rates`
--
ALTER TABLE `item_exchange_rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_from_item_items` (`from_item`),
  ADD KEY `fk_to_item_items` (`to_item`);

--
-- Chỉ mục cho bảng `item_prices`
--
ALTER TABLE `item_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_item_prices_items` (`item_id`),
  ADD KEY `fk_item_prices_users` (`change_by`);

--
-- Chỉ mục cho bảng `item_quantities`
--
ALTER TABLE `item_quantities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_item_quantities_branches` (`branch_id`),
  ADD KEY `fk_item_quantities_items` (`item_id`);

--
-- Chỉ mục cho bảng `jwt_info`
--
ALTER TABLE `jwt_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jwt_info_users` (`user_id`);

--
-- Chỉ mục cho bảng `purchased_items`
--
ALTER TABLE `purchased_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_purchased_items_purchased_sheet` (`purchased_sheet_id`),
  ADD KEY `fk_purchased_items_items` (`item_id`);

--
-- Chỉ mục cho bảng `purchased_sheets`
--
ALTER TABLE `purchased_sheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_purchased_sheet_branches` (`branch_id`),
  ADD KEY `fk_purchased_sheet_users` (`purchaser_id`),
  ADD KEY `fk_purchased_sheet_suppliers` (`supplier_id`);

--
-- Chỉ mục cho bảng `quantity_checking_items`
--
ALTER TABLE `quantity_checking_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_quant_checking_items_quant_checking_sheets` (`quant_checking_sheet_id`),
  ADD KEY `fk_quant_checking_items_items` (`item_id`);

--
-- Chỉ mục cho bảng `quantity_checking_sheets`
--
ALTER TABLE `quantity_checking_sheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_quant_checking_sheets_branches` (`branch_id`),
  ADD KEY `fk_quant_checking_sheets_users` (`checker_id`);

--
-- Chỉ mục cho bảng `refund_items`
--
ALTER TABLE `refund_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_refund_items_invoice_items` (`invoice_item_id`),
  ADD KEY `fk_refund_items_refund_sheets` (`refund_sheet_id`);

--
-- Chỉ mục cho bảng `refund_sheets`
--
ALTER TABLE `refund_sheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_refund_sheets_branches` (`branch_id`),
  ADD KEY `fk_refund_sheets_users` (`refunder_id`),
  ADD KEY `fk_refund_sheets_invoices` (`invoice_id`);

--
-- Chỉ mục cho bảng `return_purchased_items`
--
ALTER TABLE `return_purchased_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_return_item_purchased_item` (`purchased_item_id`),
  ADD KEY `fk_return_item_return_sheet_id` (`return_sheet_id`);

--
-- Chỉ mục cho bảng `return_purchased_sheets`
--
ALTER TABLE `return_purchased_sheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_return_branch_id` (`branch_id`),
  ADD KEY `fk_return_returner_id` (`returner_id`),
  ADD KEY `fk_return_purchased_sheet_id` (`purchased_sheet_id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_schedules_shifts` (`shift_id`),
  ADD KEY `fk_schedules_users` (`user_id`);

--
-- Chỉ mục cho bảng `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_shifts_branches` (`branch_id`);

--
-- Chỉ mục cho bảng `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_stores_users` (`owner_id`);

--
-- Chỉ mục cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_suppliers_branches` (`branch_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `works`
--
ALTER TABLE `works`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_works_branches` (`branch_id`),
  ADD KEY `fk_works_users` (`user_id`),
  ADD KEY `fk_works_roles` (`role_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `item_categories`
--
ALTER TABLE `item_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `item_exchange_rates`
--
ALTER TABLE `item_exchange_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `item_prices`
--
ALTER TABLE `item_prices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `item_quantities`
--
ALTER TABLE `item_quantities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `jwt_info`
--
ALTER TABLE `jwt_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `purchased_items`
--
ALTER TABLE `purchased_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `purchased_sheets`
--
ALTER TABLE `purchased_sheets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `quantity_checking_items`
--
ALTER TABLE `quantity_checking_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `quantity_checking_sheets`
--
ALTER TABLE `quantity_checking_sheets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `refund_items`
--
ALTER TABLE `refund_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `refund_sheets`
--
ALTER TABLE `refund_sheets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `return_purchased_items`
--
ALTER TABLE `return_purchased_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `return_purchased_sheets`
--
ALTER TABLE `return_purchased_sheets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `stores`
--
ALTER TABLE `stores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `works`
--
ALTER TABLE `works`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `fk_attendances_schedules` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`);

--
-- Các ràng buộc cho bảng `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `fk_branches_stores` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`);

--
-- Các ràng buộc cho bảng `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `fk_customers_branches` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Các ràng buộc cho bảng `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_branches` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_invoices_customers` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `fk_invoices_users` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `fk_invoice_items_invoices` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  ADD CONSTRAINT `fk_invoice_items_item_prices` FOREIGN KEY (`price_id`) REFERENCES `item_prices` (`id`);

--
-- Các ràng buộc cho bảng `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `fk_items_item_categories` FOREIGN KEY (`category_id`) REFERENCES `item_categories` (`id`);

--
-- Các ràng buộc cho bảng `item_categories`
--
ALTER TABLE `item_categories`
  ADD CONSTRAINT `fk_item_categories_branches` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Các ràng buộc cho bảng `item_exchange_rates`
--
ALTER TABLE `item_exchange_rates`
  ADD CONSTRAINT `fk_from_item_items` FOREIGN KEY (`from_item`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `fk_to_item_items` FOREIGN KEY (`to_item`) REFERENCES `items` (`id`);

--
-- Các ràng buộc cho bảng `item_prices`
--
ALTER TABLE `item_prices`
  ADD CONSTRAINT `fk_item_prices_items` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `fk_item_prices_users` FOREIGN KEY (`change_by`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `item_quantities`
--
ALTER TABLE `item_quantities`
  ADD CONSTRAINT `fk_item_quantities_branches` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_item_quantities_items` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Các ràng buộc cho bảng `jwt_info`
--
ALTER TABLE `jwt_info`
  ADD CONSTRAINT `fk_jwt_info_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `purchased_items`
--
ALTER TABLE `purchased_items`
  ADD CONSTRAINT `fk_purchased_items_items` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `fk_purchased_items_purchased_sheet` FOREIGN KEY (`purchased_sheet_id`) REFERENCES `purchased_sheets` (`id`);

--
-- Các ràng buộc cho bảng `purchased_sheets`
--
ALTER TABLE `purchased_sheets`
  ADD CONSTRAINT `fk_purchased_sheet_branches` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_purchased_sheet_suppliers` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `fk_purchased_sheet_users` FOREIGN KEY (`purchaser_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `quantity_checking_items`
--
ALTER TABLE `quantity_checking_items`
  ADD CONSTRAINT `fk_quant_checking_items_items` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `fk_quant_checking_items_quant_checking_sheets` FOREIGN KEY (`quant_checking_sheet_id`) REFERENCES `quantity_checking_sheets` (`id`);

--
-- Các ràng buộc cho bảng `quantity_checking_sheets`
--
ALTER TABLE `quantity_checking_sheets`
  ADD CONSTRAINT `fk_quant_checking_sheets_branches` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_quant_checking_sheets_users` FOREIGN KEY (`checker_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `refund_items`
--
ALTER TABLE `refund_items`
  ADD CONSTRAINT `fk_refund_items_invoice_items` FOREIGN KEY (`invoice_item_id`) REFERENCES `invoice_items` (`id`),
  ADD CONSTRAINT `fk_refund_items_refund_sheets` FOREIGN KEY (`refund_sheet_id`) REFERENCES `refund_sheets` (`id`);

--
-- Các ràng buộc cho bảng `refund_sheets`
--
ALTER TABLE `refund_sheets`
  ADD CONSTRAINT `fk_refund_sheets_branches` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_refund_sheets_invoices` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  ADD CONSTRAINT `fk_refund_sheets_users` FOREIGN KEY (`refunder_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `return_purchased_items`
--
ALTER TABLE `return_purchased_items`
  ADD CONSTRAINT `fk_return_item_purchased_item` FOREIGN KEY (`purchased_item_id`) REFERENCES `purchased_items` (`id`),
  ADD CONSTRAINT `fk_return_item_return_sheet_id` FOREIGN KEY (`return_sheet_id`) REFERENCES `return_purchased_sheets` (`id`);

--
-- Các ràng buộc cho bảng `return_purchased_sheets`
--
ALTER TABLE `return_purchased_sheets`
  ADD CONSTRAINT `fk_return_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_return_purchased_sheet_id` FOREIGN KEY (`purchased_sheet_id`) REFERENCES `purchased_sheets` (`id`),
  ADD CONSTRAINT `fk_return_returner_id` FOREIGN KEY (`returner_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_schedules_shifts` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`),
  ADD CONSTRAINT `fk_schedules_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `shifts`
--
ALTER TABLE `shifts`
  ADD CONSTRAINT `fk_shifts_branches` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Các ràng buộc cho bảng `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `fk_stores_users` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `fk_suppliers_branches` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Các ràng buộc cho bảng `works`
--
ALTER TABLE `works`
  ADD CONSTRAINT `fk_works_branches` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `fk_works_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `fk_works_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
-- https://stackoverflow.com/questions/4003034/execute-sql-script-to-create-tables-and-rows