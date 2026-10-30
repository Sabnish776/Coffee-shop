-- =========================================================
-- Aroma Cafe - MySQL Database Schema
-- Database: coffee
-- =========================================================

-- Step 1: Create Database if not exists
CREATE DATABASE IF NOT EXISTS coffee;
USE coffee;

-- Step 2: Create Menu Items Table
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed default menu items
INSERT INTO menu_items (name, description, price, image) VALUES
('Classic Espresso', 'Full-bodied Italian double shot with a velvety crema layer.', 3.50, 'images/menu-espresso.png'),
('Velvet Latte', 'Silky steamed micro-foam milk poured over fresh roast espresso.', 4.50, 'images/hero-coffee.png'),
('Nitro Cold Brew', 'Slow cold-steeped coffee infused with nitrogen for an ultra-smooth finish.', 5.00, 'images/menu-coldbrew.png'),
('Butter Croissant', 'Flaky, crisp golden crust with a soft buttery interior baked daily.', 3.00, 'images/menu-croissant.png');

-- Step 3: Create Cart Items Table
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
