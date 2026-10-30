<?php
require_once 'db.php';

// =========================================================
// API Request Handling (JSON Responses for AJAX / Fetch)
// =========================================================
if (isset($_REQUEST['action'])) {
    header('Content-Type: application/json');
    $action = $_REQUEST['action'];

    // 1. Get cart summary and all items
    if ($action === 'get') {
        $result = $conn->query("SELECT * FROM cart_items ORDER BY id DESC");
        $items = [];
        $totalCount = 0;
        $totalPrice = 0.0;

        while ($row = $result->fetch_assoc()) {
            $row['price'] = (float)$row['price'];
            $row['quantity'] = (int)$row['quantity'];
            $row['subtotal'] = round($row['price'] * $row['quantity'], 2);
            $totalCount += $row['quantity'];
            $totalPrice += $row['subtotal'];
            $items[] = $row;
        }

        echo json_encode([
            'success' => true,
            'count'   => $totalCount,
            'total'   => round($totalPrice, 2),
            'items'   => $items
        ]);
        exit;
    }

    // 2. Add an item to the cart (or increment quantity if already added)
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? $_GET['name'] ?? '');
        $price = (float)($_POST['price'] ?? $_GET['price'] ?? 0);

        if ($name !== '' && $price > 0) {
            // Check if item exists in table
            $stmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE item_name = ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($row = $res->fetch_assoc()) {
                $newQty = $row['quantity'] + 1;
                $update = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                $update->bind_param("ii", $newQty, $row['id']);
                $update->execute();
            } else {
                $insert = $conn->prepare("INSERT INTO cart_items (item_name, price, quantity) VALUES (?, ?, 1)");
                $insert->bind_param("sd", $name, $price);
                $insert->execute();
            }
        }

        // Return updated stats
        $stats = $conn->query("SELECT SUM(quantity) as cnt, SUM(price * quantity) as tot FROM cart_items")->fetch_assoc();
        echo json_encode([
            'success' => true,
            'count'   => (int)($stats['cnt'] ?? 0),
            'total'   => round((float)($stats['tot'] ?? 0), 2),
            'message' => "Added {$name} to MySQL cart."
        ]);
        exit;
    }

    // 3. Update quantity (+1 or -1)
    if ($action === 'update_qty') {
        $id = (int)($_REQUEST['id'] ?? 0);
        $delta = (int)($_REQUEST['delta'] ?? 0);

        if ($id > 0 && ($delta === 1 || $delta === -1)) {
            $stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($row = $res->fetch_assoc()) {
                $newQty = $row['quantity'] + $delta;
                if ($newQty <= 0) {
                    $del = $conn->prepare("DELETE FROM cart_items WHERE id = ?");
                    $del->bind_param("i", $id);
                    $del->execute();
                } else {
                    $up = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                    $up->bind_param("ii", $newQty, $id);
                    $up->execute();
                }
            }
        }

        $stats = $conn->query("SELECT SUM(quantity) as cnt, SUM(price * quantity) as tot FROM cart_items")->fetch_assoc();
        echo json_encode([
            'success' => true,
            'count'   => (int)($stats['cnt'] ?? 0),
            'total'   => round((float)($stats['tot'] ?? 0), 2)
        ]);
        exit;
    }

    // 4. Remove a specific item row
    if ($action === 'remove') {
        $id = (int)($_REQUEST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }

        $stats = $conn->query("SELECT SUM(quantity) as cnt, SUM(price * quantity) as tot FROM cart_items")->fetch_assoc();
        echo json_encode([
            'success' => true,
            'count'   => (int)($stats['cnt'] ?? 0),
            'total'   => round((float)($stats['tot'] ?? 0), 2)
        ]);
        exit;
    }

    // 5. Clear all items from cart
    if ($action === 'clear') {
        $conn->query("DELETE FROM cart_items");
        echo json_encode([
            'success' => true,
            'count'   => 0,
            'total'   => 0.00
        ]);
        exit;
    }

    // 6. Complete Checkout
    if ($action === 'checkout') {
        $conn->query("DELETE FROM cart_items");
        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully! Your cart has been completed.'
        ]);
        exit;
    }
}

// =========================================================
// HTML Page Rendering (When loaded directly in browser)
// =========================================================
$query = "SELECT * FROM cart_items ORDER BY id DESC";
$result = $conn->query($query);
$items = [];
$totalPrice = 0.0;
$totalCount = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['price'] = (float)$row['price'];
        $row['quantity'] = (int)$row['quantity'];
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $totalPrice += $row['subtotal'];
        $totalCount += $row['quantity'];
        $items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aroma Cafe | Shopping Cart (MySQL + PHP)</title>
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
          <li><a href="menu.php">Menu</a></li>
          <li><a href="contact.html">Contact</a></li>
          <li><a href="cart.php" class="btn btn-primary">Cart <span id="cart-count" class="cart-badge"><?php echo $totalCount; ?></span></a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main class="container">
    <!-- Status Message -->
    <div id="status-message"></div>

    <div class="section-title">
      <h2>Your Coffee Cart</h2>
      <p>Review your selected roasts and proceed to checkout</p>
    </div>

    <div class="cart-content-box">
      <?php if (empty($items)): ?>
        <div class="cart-empty-state">
          <div style="font-size: 3rem; margin-bottom: 10px;">🛒</div>
          <h3>Your cart is currently empty</h3>
          <p>Explore our handcrafted menu and add your favorite brews to the cart.</p>
          <a href="menu.php" class="btn btn-primary" style="margin-top: 15px;">Browse Menu</a>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="cart-table">
            <thead>
              <tr>
                <th>Item</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $item): ?>
                <tr id="row-<?php echo $item['id']; ?>">
                  <td>
                    <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                  </td>
                  <td class="price">$<?php echo number_format($item['price'], 2); ?></td>
                  <td>
                    <div class="qty-control">
                      <button class="qty-btn" onclick="modifyQty(<?php echo $item['id']; ?>, -1)">-</button>
                      <span class="qty-val" id="qty-<?php echo $item['id']; ?>"><?php echo $item['quantity']; ?></span>
                      <button class="qty-btn" onclick="modifyQty(<?php echo $item['id']; ?>, 1)">+</button>
                    </div>
                  </td>
                  <td class="price" id="subtotal-<?php echo $item['id']; ?>">$<?php echo number_format($item['subtotal'], 2); ?></td>
                  <td>
                    <button class="btn-delete" onclick="deleteItem(<?php echo $item['id']; ?>)">✕ Remove</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Cart Summary Actions -->
        <div class="cart-checkout-bar">
          <div class="cart-actions-left">
            <button class="btn btn-secondary" onclick="emptyCart()">Empty Cart</button>
            <a href="menu.php" class="btn btn-secondary">← Add More Items</a>
          </div>
          <div class="cart-actions-right">
            <div class="total-display">
              <span>Grand Total:</span>
              <span id="cart-grand-total" class="price" style="font-size: 1.6rem;">$<?php echo number_format($totalPrice, 2); ?></span>
            </div>
            <button class="btn btn-primary" onclick="proceedCheckout()">Confirm & Place Order</button>
          </div>
        </div>
      <?php endif; ?>
    </div>

  </main>

  <!-- Simple Footer -->
  <footer>
    <div class="container">
      <p>&copy; 2026 Aroma Cafe. Simple HTML, CSS, JS, PHP & MySQL Demonstration.</p>
      <p>
        <a href="index.html">Home</a> | 
        <a href="menu.html">Menu</a> | 
        <a href="contact.html">Contact</a> |
        <a href="cart.php">Cart</a>
      </p>
    </div>
  </footer>

  <script src="script.js"></script>
  <script>
    // Specific handlers for cart.php view
    function modifyQty(id, delta) {
      fetch(`cart.php?action=update_qty&id=${id}&delta=${delta}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            window.location.reload();
          }
        })
        .catch(err => console.error(err));
    }

    function deleteItem(id) {
      if (!confirm('Are you sure you want to remove this item?')) return;
      fetch(`cart.php?action=remove&id=${id}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            window.location.reload();
          }
        })
        .catch(err => console.error(err));
    }

    function emptyCart() {
      if (!confirm('Clear all items from your cart?')) return;
      fetch('cart.php?action=clear')
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            window.location.reload();
          }
        })
        .catch(err => console.error(err));
    }

    function proceedCheckout() {
      fetch('cart.php?action=checkout')
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert('🎉 ' + data.message);
            window.location.href = 'index.html';
          }
        })
        .catch(err => console.error(err));
    }
  </script>
</body>
</html>
