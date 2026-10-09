-- Database Schema for PHP Basics + MySQL Assignment
-- Database: `myapp`

CREATE DATABASE IF NOT EXISTS `myapp` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `myapp`;

-- Table structure for table `users`
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `age` INT NULL,
    `city` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample seed data
INSERT INTO `users` (`name`, `age`, `city`) VALUES
('Olied Ahmed Chy', 21, 'Sylhet'),
('Iran Ahmed', 21, 'Habiganj'),
('Marzan', 23, 'Beanibazar'),
('Kawser', 24, 'Dhaka'),
('Jakir Hussain', 23, 'Chattogram');
