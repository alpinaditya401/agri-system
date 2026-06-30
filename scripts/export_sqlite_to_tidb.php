<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$sqlitePath = $root . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sqlite';
$exportDir = $root . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'exports';

if (! is_file($sqlitePath)) {
    fwrite(STDERR, "SQLite database not found: {$sqlitePath}\n");
    exit(1);
}

if (! is_dir($exportDir) && ! mkdir($exportDir, 0777, true) && ! is_dir($exportDir)) {
    fwrite(STDERR, "Cannot create export directory: {$exportDir}\n");
    exit(1);
}

$schema = <<<'SQL'
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `chat_messages`;
DROP TABLE IF EXISTS `articles`;
DROP TABLE IF EXISTS `bps_fetch_logs`;
DROP TABLE IF EXISTS `commodity_prices`;
DROP TABLE IF EXISTS `fertilizer_transactions`;
DROP TABLE IF EXISTS `fertilizer_quotas`;
DROP TABLE IF EXISTS `fertilizer_stocks`;
DROP TABLE IF EXISTS `fertilizer_types`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `carts`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `product_categories`;
DROP TABLE IF EXISTS `distributor_profiles`;
DROP TABLE IF EXISTS `farmer_profiles`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL,
  `password` varchar(255) NOT NULL,
  `profile_photo_path` varchar(255) NULL,
  `phone` varchar(20) NULL,
  `address` text NULL,
  `latitude` decimal(10,8) NULL,
  `longitude` decimal(11,8) NULL,
  `province` varchar(255) NULL,
  `district` varchar(255) NULL,
  `sub_district` varchar(255) NULL,
  `village` varchar(255) NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  `deleted_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_is_active_index` (`role_id`, `is_active`),
  KEY `users_latitude_longitude_index` (`latitude`, `longitude`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `farmer_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `nik` varchar(16) NULL,
  `farmer_group_id` varchar(255) NULL,
  `farmer_group_name` varchar(255) NULL,
  `land_area_hectares` decimal(8,2) NULL,
  `main_commodity` varchar(255) NULL,
  `verification_status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `rejection_reason` text NULL,
  `verified_at` timestamp NULL,
  `verified_by` bigint unsigned NULL,
  `urea_quota_kg` int NOT NULL DEFAULT 0,
  `npk_quota_kg` int NOT NULL DEFAULT 0,
  `urea_used_kg` int NOT NULL DEFAULT 0,
  `npk_used_kg` int NOT NULL DEFAULT 0,
  `quota_year` year NULL,
  `quota_season` varchar(255) NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `farmer_profiles_user_id_unique` (`user_id`),
  UNIQUE KEY `farmer_profiles_nik_unique` (`nik`),
  KEY `farmer_profiles_farmer_group_id_index` (`farmer_group_id`),
  KEY `farmer_profiles_verification_status_index` (`verification_status`),
  KEY `farmer_profiles_verified_by_foreign` (`verified_by`),
  CONSTRAINT `farmer_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `farmer_profiles_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `distributor_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `company_name` varchar(255) NULL,
  `license_number` varchar(255) NULL,
  `storage_capacity_kg` int NOT NULL DEFAULT 0,
  `verification_status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `distributor_profiles_user_id_unique` (`user_id`),
  CONSTRAINT `distributor_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NULL,
  `icon` varchar(255) NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `farmer_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NULL,
  `price_per_unit` decimal(12,2) NOT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'kg',
  `stock_quantity` int NOT NULL DEFAULT 0,
  `minimum_order` int NOT NULL DEFAULT 1,
  `main_image` varchar(255) NULL,
  `origin_province` varchar(255) NULL,
  `origin_district` varchar(255) NULL,
  `origin_lat` decimal(10,8) NULL,
  `origin_lng` decimal(11,8) NULL,
  `status` enum('draft','active','inactive','sold_out') NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  `deleted_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_farmer_id_status_index` (`farmer_id`, `status`),
  KEY `products_category_id_status_index` (`category_id`, `status`),
  CONSTRAINT `products_farmer_id_foreign` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int NOT NULL DEFAULT 0,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_foreign` (`product_id`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `buyer_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `carts_buyer_id_product_id_unique` (`buyer_id`, `product_id`),
  KEY `carts_product_id_foreign` (`product_id`),
  CONSTRAINT `carts_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `carts_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) NOT NULL,
  `buyer_id` bigint unsigned NOT NULL,
  `farmer_id` bigint unsigned NOT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `shipping_cost` decimal(10,2) NOT NULL DEFAULT 0,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0,
  `total_amount` decimal(14,2) NOT NULL,
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `order_status` enum('pending','confirmed','processing','shipped','delivered','completed','cancelled','disputed') NOT NULL DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `shipping_method` varchar(255) NULL,
  `tracking_number` varchar(255) NULL,
  `shipped_at` timestamp NULL,
  `delivered_at` timestamp NULL,
  `payment_method` varchar(255) NULL,
  `payment_reference` varchar(255) NULL,
  `payment_gateway` varchar(255) NULL,
  `payment_token` varchar(255) NULL,
  `payment_checkout_url` text NULL,
  `payment_expires_at` timestamp NULL,
  `paid_at` timestamp NULL,
  `buyer_notes` text NULL,
  `farmer_notes` text NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  `deleted_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_buyer_id_order_status_index` (`buyer_id`, `order_status`),
  KEY `orders_farmer_id_order_status_index` (`farmer_id`, `order_status`),
  CONSTRAINT `orders_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `orders_farmer_id_foreign` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price_per_unit` decimal(12,2) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `quantity` int NOT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fertilizer_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `subsidy_price_per_kg` decimal(10,2) NOT NULL,
  `market_price_per_kg` decimal(10,2) NULL,
  `description` text NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fertilizer_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fertilizer_stocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `distributor_id` bigint unsigned NOT NULL,
  `fertilizer_type_id` bigint unsigned NOT NULL,
  `stock_kg` int NOT NULL DEFAULT 0,
  `reserved_kg` int NOT NULL DEFAULT 0,
  `batch_number` varchar(255) NULL,
  `received_date` date NULL,
  `expiry_date` date NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  KEY `fertilizer_stocks_distributor_id_fertilizer_type_id_index` (`distributor_id`, `fertilizer_type_id`),
  KEY `fertilizer_stocks_fertilizer_type_id_foreign` (`fertilizer_type_id`),
  CONSTRAINT `fertilizer_stocks_distributor_id_foreign` FOREIGN KEY (`distributor_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fertilizer_stocks_fertilizer_type_id_foreign` FOREIGN KEY (`fertilizer_type_id`) REFERENCES `fertilizer_types` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fertilizer_quotas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `farmer_id` bigint unsigned NOT NULL,
  `fertilizer_type_id` bigint unsigned NOT NULL,
  `year` year NOT NULL,
  `season` varchar(10) NOT NULL,
  `allocated_kg` int NOT NULL,
  `used_kg` int NOT NULL DEFAULT 0,
  `remaining_kg` int GENERATED ALWAYS AS (`allocated_kg` - `used_kg`) STORED,
  `quota_expires_at` timestamp NULL,
  `allocated_by` bigint unsigned NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  KEY `fertilizer_quotas_farmer_id_year_season_index` (`farmer_id`, `year`, `season`),
  KEY `fertilizer_quotas_fertilizer_type_id_foreign` (`fertilizer_type_id`),
  KEY `fertilizer_quotas_allocated_by_foreign` (`allocated_by`),
  CONSTRAINT `fertilizer_quotas_farmer_id_foreign` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fertilizer_quotas_fertilizer_type_id_foreign` FOREIGN KEY (`fertilizer_type_id`) REFERENCES `fertilizer_types` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fertilizer_quotas_allocated_by_foreign` FOREIGN KEY (`allocated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fertilizer_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_number` varchar(255) NOT NULL,
  `farmer_id` bigint unsigned NOT NULL,
  `distributor_id` bigint unsigned NOT NULL,
  `fertilizer_type_id` bigint unsigned NOT NULL,
  `fertilizer_quota_id` bigint unsigned NOT NULL,
  `requested_kg` int NOT NULL,
  `approved_kg` int NULL,
  `price_per_kg` decimal(10,2) NOT NULL,
  `total_amount` decimal(12,2) NULL,
  `status` enum('pending','approved','rejected','dispensed','cancelled') NOT NULL DEFAULT 'pending',
  `rejection_reason` text NULL,
  `approved_at` timestamp NULL,
  `dispensed_at` timestamp NULL,
  `processed_by` bigint unsigned NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fertilizer_transactions_transaction_number_unique` (`transaction_number`),
  KEY `fertilizer_transactions_farmer_id_status_index` (`farmer_id`, `status`),
  KEY `fertilizer_transactions_distributor_id_status_index` (`distributor_id`, `status`),
  KEY `fertilizer_transactions_fertilizer_type_id_foreign` (`fertilizer_type_id`),
  KEY `fertilizer_transactions_fertilizer_quota_id_foreign` (`fertilizer_quota_id`),
  KEY `fertilizer_transactions_processed_by_foreign` (`processed_by`),
  CONSTRAINT `fertilizer_transactions_farmer_id_foreign` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fertilizer_transactions_distributor_id_foreign` FOREIGN KEY (`distributor_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fertilizer_transactions_fertilizer_type_id_foreign` FOREIGN KEY (`fertilizer_type_id`) REFERENCES `fertilizer_types` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fertilizer_transactions_fertilizer_quota_id_foreign` FOREIGN KEY (`fertilizer_quota_id`) REFERENCES `fertilizer_quotas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fertilizer_transactions_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `commodity_prices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `commodity_name` varchar(255) NOT NULL,
  `commodity_code` varchar(255) NULL,
  `category` varchar(255) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'kg',
  `region` varchar(255) NULL,
  `region_code` varchar(255) NULL,
  `source` varchar(255) NOT NULL DEFAULT 'BPS',
  `price_date` date NOT NULL,
  `raw_data` json NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  KEY `commodity_prices_commodity_name_price_date_index` (`commodity_name`, `price_date`),
  KEY `commodity_prices_category_price_date_index` (`category`, `price_date`),
  KEY `commodity_prices_price_date_index` (`price_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bps_fetch_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `endpoint` varchar(255) NOT NULL,
  `status` enum('success','failed','partial') NOT NULL,
  `records_fetched` int NOT NULL DEFAULT 0,
  `error_message` text NULL,
  `response_time_ms` int NULL,
  `fetched_at` timestamp NOT NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `articles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `author_id` bigint unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text NULL,
  `content` longtext NOT NULL,
  `cover_image` varchar(255) NULL,
  `category` varchar(255) NULL,
  `tags` json NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL,
  `view_count` int NOT NULL DEFAULT 0,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  `deleted_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articles_slug_unique` (`slug`),
  KEY `articles_status_published_at_index` (`status`, `published_at`),
  KEY `articles_author_id_foreign` (`author_id`),
  CONSTRAINT `articles_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `chat_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `from_id` bigint unsigned NOT NULL,
  `to_id` bigint unsigned NOT NULL,
  `pesan` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  KEY `chat_messages_from_id_to_id_index` (`from_id`, `to_id`),
  KEY `chat_messages_to_id_is_read_index` (`to_id`, `is_read`),
  CONSTRAINT `chat_messages_from_id_foreign` FOREIGN KEY (`from_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_to_id_foreign` FOREIGN KEY (`to_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `tipe` enum('arrived','price','low_stock','chat','info','alert') NOT NULL DEFAULT 'info',
  `judul` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `link` varchar(255) NULL,
  `dibaca` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_dibaca_index` (`user_id`, `dibaca`),
  KEY `notifications_user_id_tipe_index` (`user_id`, `tipe`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned NULL,
  `ip_address` varchar(45) NULL,
  `user_agent` text NULL,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
SQL;

$pdo = new PDO('sqlite:' . $sqlitePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$exportTables = [
    'migrations',
    'roles',
    'users',
    'farmer_profiles',
    'distributor_profiles',
    'product_categories',
    'products',
    'product_images',
    'carts',
    'orders',
    'order_items',
    'fertilizer_types',
    'fertilizer_stocks',
    'fertilizer_quotas',
    'fertilizer_transactions',
    'commodity_prices',
    'bps_fetch_logs',
    'articles',
    'chat_messages',
    'notifications',
];

$postMigrateTables = array_values(array_diff($exportTables, ['migrations']));

function q(string $identifier): string
{
    return '`' . str_replace('`', '``', $identifier) . '`';
}

function sqlValue(mixed $value): string
{
    if ($value === null) {
        return 'NULL';
    }

    if (is_bool($value)) {
        return $value ? '1' : '0';
    }

    if (is_int($value) || is_float($value)) {
        return (string) $value;
    }

    return "'" . str_replace(["\\", "'", "\r", "\n", "\0"], ["\\\\", "''", "\\r", "\\n", "\\0"], (string) $value) . "'";
}

function dataDump(PDO $pdo, array $tables): string
{
    $sql = "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tables as $table) {
        $columns = $pdo->query('PRAGMA table_info("' . str_replace('"', '""', $table) . '")')->fetchAll(PDO::FETCH_ASSOC);
        if ($columns === []) {
            continue;
        }

        $columnNames = array_map(static fn(array $column): string => $column['name'], $columns);
        $rows = $pdo->query('SELECT * FROM "' . str_replace('"', '""', $table) . '"')->fetchAll(PDO::FETCH_ASSOC);

        if ($rows === []) {
            continue;
        }

        $sql .= "-- Data for {$table}\n";
        $sql .= 'INSERT INTO ' . q($table) . ' (' . implode(', ', array_map('q', $columnNames)) . ") VALUES\n";
        $values = [];

        foreach ($rows as $row) {
            $values[] = '(' . implode(', ', array_map(static fn(string $column): string => sqlValue($row[$column]), $columnNames)) . ')';
        }

        $sql .= implode(",\n", $values) . ";\n\n";
    }

    return $sql . "SET FOREIGN_KEY_CHECKS=1;\n";
}

$schemaPath = $exportDir . DIRECTORY_SEPARATOR . 'agri_system_tidb_schema.sql';
$dataPath = $exportDir . DIRECTORY_SEPARATOR . 'agri_system_tidb_data_after_migrate.sql';
$fullPath = $exportDir . DIRECTORY_SEPARATOR . 'agri_system_tidb_full.sql';

file_put_contents($schemaPath, $schema . "\n");
file_put_contents($dataPath, dataDump($pdo, $postMigrateTables));
file_put_contents($fullPath, $schema . "\n\n" . dataDump($pdo, $exportTables));

echo "Created:\n";
echo " - {$schemaPath}\n";
echo " - {$dataPath}\n";
echo " - {$fullPath}\n";
