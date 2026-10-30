<?php
require_once 'db.php';

// Fetch all menu items from MySQL database
$menuQuery = "SELECT * FROM menu_items ORDER BY id ASC";
$menuResult = $conn->query($menuQuery);
$menuCount = $menuResult ? $menuResult->num_rows : 0;

// Fetch cart count for initial display
$cartStats = $conn->query("SELECT SUM(quantity) as total_qty, SUM(price * quantity) as total_price FROM cart_items")->fetch_assoc();
$cartCount = (int)($cartStats['total_qty'] ?? 0);
$cartTotal = (float)($cartStats['total_price'] ?? 0.0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aroma Cafe | Handcrafted Menu (MySQL + PHP)</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Navigation Bar -->
  <header>
    <div class="container">
      <nav>
        <a href="index.html" class="logo">☕ Aroma Cafe</a>
        <ul class="nav-links">
          <li><a href="index.html">Home</a></li>
          <li><a href="menu.php" class="active">Menu</a></li>
          <li><a href="contact.html">Contact</a></li>
          <li><a href="cart.php" class="btn btn-primary">Cart <span id="cart-count" class="cart-badge"><?php echo $cartCount; ?></span></a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main class="container">
    <!-- Dynamic JS Status Message -->
    <div id="status-message"></div>

    <div class="section-title">
      <h2>Our Handcrafted Menu</h2>
      <p>Select your favorite drink or pastry and add it to your order</p>
    </div>


    <!-- Live Cart Summary Display -->
    <div class="cart-summary-box">
      <div>
        <strong>Current Order Total: </strong>
        <span id="cart-total-price" class="price">$<?php echo number_format($cartTotal, 2); ?></span>
      </div>
      <div>
        <button class="btn btn-secondary" onclick="clearCart()">Clear Cart</button>
        <a href="cart.php" class="btn btn-primary">View Cart & Checkout</a>
      </div>
    </div>

    <!-- Dynamic Menu Items Grid from MySQL Database -->
    <div class="cards-grid">
      <?php if ($menuResult && $menuResult->num_rows > 0): ?>
        <?php while ($item = $menuResult->fetch_assoc()): ?>
          <div class="card">
            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
            <div class="card-body">
              <h3><?php echo htmlspecialchars($item['name']); ?></h3>
              <p><?php echo htmlspecialchars($item['description']); ?></p>
              <div class="card-footer">
                <span class="price">$<?php echo number_format($item['price'], 2); ?></span>
                <button class="btn btn-primary" onclick="addToCart('<?php echo addslashes($item['name']); ?>', <?php echo $item['price']; ?>)">+ Add to Cart</button>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="grid-column: 1 / -1; text-align: center; color: #b5a99f;">No menu items found in MySQL database table <code>menu_items</code>.</p>
      <?php endif; ?>
    </div>

    <!-- Hyperlink Callout to Contact Page -->
    <div style="text-align: center; margin: 40px 0;">
      <p style="margin-bottom: 15px; color: #b5a99f;">Need customized brewing or catering for events?</p>
      <a href="contact.html" class="btn btn-secondary">Get in Touch with Our Baristas</a>
    </div>
  </main>

  <!-- Simple Footer -->
  <footer>
    <div class="container">
      <p>&copy; 2026 Aroma Cafe. Simple HTML, CSS, JS, PHP & MySQL Demonstration.</p>
      <p>
        <a href="index.html">Home</a> | 
        <a href="menu.php">Menu</a> | 
        <a href="contact.html">Contact</a> |
        <a href="cart.php">Cart</a>
      </p>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
