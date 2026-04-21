-- Comprehensive Subscription Plans System Migration
-- This creates a complete subscription plan system with fee rules integration

-- 1. Create subscription_plans table
CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `billing_cycle` enum('monthly','yearly','lifetime','free') DEFAULT 'monthly',
  `max_events` int(11) DEFAULT NULL COMMENT 'NULL means unlimited',
  `max_contestants_per_event` int(11) DEFAULT NULL COMMENT 'NULL means unlimited',
  `max_votes_per_event` int(11) DEFAULT NULL COMMENT 'NULL means unlimited',
  `features` JSON DEFAULT NULL COMMENT 'Additional features as JSON',
  `fee_rule_id` int(11) DEFAULT NULL COMMENT 'Default fee rule for this plan',
  `is_popular` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(3) DEFAULT 0,
  `trial_days` int(3) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`fee_rule_id`) REFERENCES `fee_rules`(`id`) ON DELETE SET NULL,
  INDEX `idx_active_sort` (`is_active`, `sort_order`),
  INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Create tenant_subscriptions table to track tenant plan assignments
CREATE TABLE `tenant_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `status` enum('active','expired','cancelled','suspended') DEFAULT 'active',
  `started_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT 1,
  `payment_method` varchar(50) DEFAULT NULL,
  `last_payment_at` timestamp NULL DEFAULT NULL,
  `next_payment_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans`(`id`) ON DELETE RESTRICT,
  INDEX `idx_tenant_status` (`tenant_id`, `status`),
  INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Add subscription tracking columns to tenants table
ALTER TABLE `tenants` 
ADD COLUMN `current_plan_id` int(11) DEFAULT NULL AFTER `plan`,
ADD COLUMN `subscription_status` enum('active','trial','expired','cancelled','suspended') DEFAULT 'trial' AFTER `current_plan_id`,
ADD COLUMN `subscription_expires_at` timestamp NULL DEFAULT NULL AFTER `subscription_status`,
ADD COLUMN `trial_ends_at` timestamp NULL DEFAULT NULL AFTER `subscription_expires_at`,
ADD FOREIGN KEY (`current_plan_id`) REFERENCES `subscription_plans`(`id`) ON DELETE SET NULL;

-- 4. Create plan_features table for detailed feature management
CREATE TABLE `plan_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL,
  `feature_key` varchar(100) NOT NULL,
  `feature_name` varchar(255) NOT NULL,
  `feature_value` varchar(255) DEFAULT NULL,
  `is_boolean` tinyint(1) DEFAULT 0 COMMENT '1 if feature is yes/no, 0 if has value',
  `sort_order` int(3) DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_plan_feature` (`plan_id`, `feature_key`),
  INDEX `idx_plan_sort` (`plan_id`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Insert default subscription plans
INSERT INTO `subscription_plans` (`name`, `slug`, `description`, `price`, `billing_cycle`, `max_events`, `max_contestants_per_event`, `max_votes_per_event`, `features`, `fee_rule_id`, `is_popular`, `sort_order`, `trial_days`) VALUES
('Free Starter', 'free', 'Perfect for trying out the platform', 0.00, 'free', 1, 10, 1000, '{"custom_branding": false, "analytics": "basic", "support": "community"}', 3, 0, 1, 14),
('Basic Plan', 'basic', 'Great for small organizations and events', 29.99, 'monthly', 5, 50, 10000, '{"custom_branding": true, "analytics": "standard", "support": "email", "api_access": false}', 2, 0, 2, 7),
('Professional', 'professional', 'Perfect for growing organizations', 99.99, 'monthly', NULL, NULL, NULL, '{"custom_branding": true, "analytics": "advanced", "support": "priority", "api_access": true, "webhooks": true}', 2, 1, 3, 7),
('Enterprise', 'enterprise', 'For large organizations with unlimited needs', 299.99, 'monthly', NULL, NULL, NULL, '{"custom_branding": true, "analytics": "premium", "support": "dedicated", "api_access": true, "webhooks": true, "white_label": true}', 1, 0, 4, 14);

-- 6. Insert plan features for better feature management
INSERT INTO `plan_features` (`plan_id`, `feature_key`, `feature_name`, `feature_value`, `is_boolean`, `sort_order`) VALUES
-- Free Plan Features
(1, 'events', 'Events', '1', 0, 1),
(1, 'contestants', 'Contestants per Event', '10', 0, 2),
(1, 'votes', 'Votes per Event', '1,000', 0, 3),
(1, 'custom_branding', 'Custom Branding', NULL, 1, 4),
(1, 'analytics', 'Analytics', 'Basic', 0, 5),
(1, 'support', 'Support', 'Community', 0, 6),

-- Basic Plan Features  
(2, 'events', 'Events', '5', 0, 1),
(2, 'contestants', 'Contestants per Event', '50', 0, 2),
(2, 'votes', 'Votes per Event', '10,000', 0, 3),
(2, 'custom_branding', 'Custom Branding', '1', 1, 4),
(2, 'analytics', 'Analytics', 'Standard', 0, 5),
(2, 'support', 'Support', 'Email', 0, 6),
(2, 'api_access', 'API Access', NULL, 1, 7),

-- Professional Plan Features
(3, 'events', 'Events', 'Unlimited', 0, 1),
(3, 'contestants', 'Contestants per Event', 'Unlimited', 0, 2),
(3, 'votes', 'Votes per Event', 'Unlimited', 0, 3),
(3, 'custom_branding', 'Custom Branding', '1', 1, 4),
(3, 'analytics', 'Analytics', 'Advanced', 0, 5),
(3, 'support', 'Support', 'Priority', 0, 6),
(3, 'api_access', 'API Access', '1', 1, 7),
(3, 'webhooks', 'Webhooks', '1', 1, 8),

-- Enterprise Plan Features
(4, 'events', 'Events', 'Unlimited', 0, 1),
(4, 'contestants', 'Contestants per Event', 'Unlimited', 0, 2),
(4, 'votes', 'Votes per Event', 'Unlimited', 0, 3),
(4, 'custom_branding', 'Custom Branding', '1', 1, 4),
(4, 'analytics', 'Analytics', 'Premium', 0, 5),
(4, 'support', 'Support', 'Dedicated', 0, 6),
(4, 'api_access', 'API Access', '1', 1, 7),
(4, 'webhooks', 'Webhooks', '1', 1, 8),
(4, 'white_label', 'White Label', '1', 1, 9);

-- 7. Update existing tenants to have a default plan (Basic)
UPDATE `tenants` SET 
    `current_plan_id` = 2,
    `subscription_status` = 'active',
    `subscription_expires_at` = DATE_ADD(NOW(), INTERVAL 1 YEAR)
WHERE `active` = 1;

-- 8. Create tenant subscriptions for existing tenants
INSERT INTO `tenant_subscriptions` (`tenant_id`, `plan_id`, `status`, `expires_at`, `next_payment_at`)
SELECT 
    `id` as tenant_id,
    2 as plan_id,
    'active' as status,
    DATE_ADD(NOW(), INTERVAL 1 YEAR) as expires_at,
    DATE_ADD(NOW(), INTERVAL 1 MONTH) as next_payment_at
FROM `tenants` 
WHERE `active` = 1;

-- 9. Create tenant_plan_history table for tracking plan changes
CREATE TABLE `tenant_plan_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `old_plan_id` int(11) DEFAULT NULL,
  `new_plan_id` int(11) NOT NULL,
  `changed_by` int(11) DEFAULT NULL COMMENT 'User ID who made the change',
  `change_reason` varchar(255) DEFAULT NULL,
  `effective_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`old_plan_id`) REFERENCES `subscription_plans`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`new_plan_id`) REFERENCES `subscription_plans`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`changed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_tenant_date` (`tenant_id`, `effective_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 10. Show results
SELECT 'Subscription plans created:' as info;
SELECT * FROM subscription_plans ORDER BY sort_order;

SELECT 'Plan features created:' as info;
SELECT sp.name as plan_name, pf.feature_name, pf.feature_value, pf.is_boolean 
FROM plan_features pf 
INNER JOIN subscription_plans sp ON pf.plan_id = sp.id 
ORDER BY sp.sort_order, pf.sort_order;

SELECT 'Tenant subscriptions created:' as info;
SELECT t.name as tenant_name, sp.name as plan_name, ts.status, ts.expires_at
FROM tenant_subscriptions ts
INNER JOIN tenants t ON ts.tenant_id = t.id
INNER JOIN subscription_plans sp ON ts.plan_id = sp.id;

SELECT 'Migration completed successfully!' as result;
