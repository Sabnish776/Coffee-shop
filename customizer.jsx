// =========================================================
// Aroma Cafe - React 18 Custom Brew Component (JSX Version)
// Standard React JSX syntax with Hooks (useState)
// =========================================================

import React, { useState } from 'react';

export default function CoffeeCustomizer() {
  const [drink, setDrink] = useState('Latte');
  const [size, setSize] = useState('Medium');
  const [extraShot, setExtraShot] = useState(false);
  const [added, setAdded] = useState(false);

  const basePrices = {
    'Espresso': 3.50,
    'Latte': 4.25,
    'Cold Brew': 4.50,
    'Mocha': 4.75
  };

  const sizeAddons = {
    'Small': 0.00,
    'Medium': 0.50,
    'Large': 1.00
  };

  const totalPrice = basePrices[drink] + sizeAddons[size] + (extraShot ? 0.75 : 0.00);

  const handleOrder = () => {
    const itemName = `${size} ${drink}${extraShot ? ' (+Extra Shot)' : ''}`;
    if (typeof addToCart === 'function') {
      addToCart(itemName, totalPrice);
    }
    setAdded(true);
    setTimeout(() => setAdded(false), 2500);
  };

  return (
    <div className="customizer-card">
      <div className="customizer-header">
        <h3>☕ Quick Brew Customizer</h3>
      </div>
      <p className="customizer-desc">
        Customize your roast & size with real-time price calculation:
      </p>

      {/* Drink Selection */}
      <div className="customizer-row">
        <label>Drink Type:</label>
        <div className="pill-group">
          {Object.keys(basePrices).map((d) => (
            <button
              key={d}
              type="button"
              className={`pill-btn ${drink === d ? 'active' : ''}`}
              onClick={() => setDrink(d)}
            >
              {d} (${basePrices[d].toFixed(2)})
            </button>
          ))}
        </div>
      </div>

      {/* Size Selection */}
      <div className="customizer-row">
        <label>Size:</label>
        <div className="pill-group">
          {Object.keys(sizeAddons).map((s) => (
            <button
              key={s}
              type="button"
              className={`pill-btn ${size === s ? 'active' : ''}`}
              onClick={() => setSize(s)}
            >
              {s} {sizeAddons[s] > 0 ? `(+$${sizeAddons[s].toFixed(2)})` : ''}
            </button>
          ))}
        </div>
      </div>

      {/* Extra Shot Option */}
      <div className="customizer-row checkbox-row">
        <label className="checkbox-label">
          <input
            type="checkbox"
            checked={extraShot}
            onChange={(e) => setExtraShot(e.target.checked)}
          />
          Add Extra Espresso Shot (+$0.75)
        </label>
      </div>

      {/* Total & Action Button */}
      <div className="customizer-footer">
        <div className="customizer-total">
          <span className="total-label">Total: </span>
          <span className="total-price">${totalPrice.toFixed(2)}</span>
        </div>
        <button
          type="button"
          className="btn btn-primary"
          onClick={handleOrder}
        >
          {added ? '✓ Added to Cart!' : 'Add to Order'}
        </button>
      </div>
    </div>
  );
}
