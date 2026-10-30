<?php
// =========================================================
// Aroma Cafe - Database Connection File
// Connects PHP backend to MySQL 'coffee' database
// =========================================================

$servername = "localhost";
$username   = "root";
$password   = "7775";
$dbname     = "coffee";

// Establish MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Verify connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// 1. Automatically create cart_items table
$conn->query("CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// 2. Automatically create menu_items table
$conn->query("CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Seed default menu items if table is empty
$checkMenu = $conn->query("SELECT COUNT(*) as total FROM menu_items");
$row = $checkMenu->fetch_assoc();
if ($row['total'] == 0) {
    $conn->query("INSERT INTO menu_items (name, description, price, image) VALUES
        ('Classic Espresso', 'Full-bodied Italian double shot with a velvety crema layer.', 3.50, 'images/menu-espresso.png'),
        ('Velvet Latte', 'Silky steamed micro-foam milk poured over fresh roast espresso.', 4.50, 'images/hero-coffee.png'),
        ('Nitro Cold Brew', 'Slow cold-steeped coffee infused with nitrogen for an ultra-smooth finish.', 5.00, 'images/menu-coldbrew.png'),
        ('Butter Croissant', 'Flaky, crisp golden crust with a soft buttery interior baked daily.', 3.00, 'images/menu-croissant.png')
    ");
}
?>
