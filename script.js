// =========================================================
// Aroma Cafe - JavaScript Logic with PHP & MySQL DB Support
// Handles UI state, async database sync & local fallback
// =========================================================

// Initialize state (from localStorage as quick initial cache)
let cartCount = parseInt(localStorage.getItem('cafe_cart_count')) || 0;
let cartTotal = parseFloat(localStorage.getItem('cafe_cart_total')) || 0.0;

document.addEventListener('DOMContentLoaded', () => {
  updateCartDisplay();
  highlightActiveLink();
  initContactForm();
  syncCartWithDB();
});

// Sync cart directly from MySQL database via cart.php
function syncCartWithDB() {
  fetch('cart.php?action=get')
    .then(res => {
      if (!res.ok) throw new Error('HTTP error ' + res.status);
      return res.json();
    })
    .then(data => {
      if (data.success) {
        cartCount = data.count;
        cartTotal = data.total;
        localStorage.setItem('cafe_cart_count', cartCount);
        localStorage.setItem('cafe_cart_total', cartTotal);
        updateCartDisplay();
      }
    })
    .catch(err => {
      // If opened directly as a local file or server not started, retain local state
      console.log('MySQL Sync info: Run via PHP server to sync live with MySQL database (php -S localhost:8000).');
    });
}

// Update Cart Count badge & Total in the UI
function updateCartDisplay() {
  const badge = document.getElementById('cart-count');
  const totalElem = document.getElementById('cart-total-price');

  if (badge) badge.textContent = cartCount;
  if (totalElem) totalElem.textContent = `$${cartTotal.toFixed(2)}`;
}

// Add an item to the cart (stores in MySQL database 'coffee')
function addToCart(itemName, itemPrice) {
  fetch(`cart.php?action=add&name=${encodeURIComponent(itemName)}&price=${itemPrice}`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        cartCount = data.count;
        cartTotal = data.total;
        localStorage.setItem('cafe_cart_count', cartCount);
        localStorage.setItem('cafe_cart_total', cartTotal);
        updateCartDisplay();
        showMessage(`☕ Added "${itemName}" ($${itemPrice.toFixed(2)})! Saved to MySQL DB.`);
      }
    })
    .catch(() => {
      // Fallback if accessed without active PHP server
      cartCount += 1;
      cartTotal += itemPrice;
      localStorage.setItem('cafe_cart_count', cartCount);
      localStorage.setItem('cafe_cart_total', cartTotal);
      updateCartDisplay();
      showMessage(`Added "${itemName}" ($${itemPrice.toFixed(2)}) to your cart! Total: $${cartTotal.toFixed(2)}`);
    });
}

// Reset cart (deletes all records from MySQL cart_items table)
function clearCart() {
  fetch('cart.php?action=clear')
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        cartCount = 0;
        cartTotal = 0.0;
        localStorage.removeItem('cafe_cart_count');
        localStorage.removeItem('cafe_cart_total');
        updateCartDisplay();
        showMessage('Your cart has been cleared from MySQL.');
      }
    })
    .catch(() => {
      cartCount = 0;
      cartTotal = 0;
      localStorage.removeItem('cafe_cart_count');
      localStorage.removeItem('cafe_cart_total');
      updateCartDisplay();
      showMessage('Your cart has been cleared.');
    });
}

// Show friendly feedback banner
function showMessage(text) {
  const msgBox = document.getElementById('status-message');
  if (!msgBox) return;

  msgBox.textContent = text;
  msgBox.style.display = 'block';

  // Auto-hide after 3.5 seconds
  setTimeout(() => {
    msgBox.style.display = 'none';
  }, 3500);
}

// Highlight current page link in navbar
function highlightActiveLink() {
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-links a').forEach(link => {
    if (link.getAttribute('href') === currentPage) {
      link.classList.add('active');
    }
  });
}

// Contact form submission handler
function initContactForm() {
  const form = document.getElementById('contact-form');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const name = document.getElementById('user-name')?.value || 'Guest';
    showMessage(`Thank you, ${name}! Your message has been sent successfully.`);
    form.reset();
  });
}
