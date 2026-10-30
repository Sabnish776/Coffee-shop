// =========================================================
// Aroma Cafe - React 18 Custom Brew Component
// A simple, zero-build React component with interactive state
// =========================================================

(function () {
  const { useState, createElement: h } = React;

  function CoffeeCustomizer() {
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

    return h('div', { className: 'customizer-card' },
      h('div', { className: 'customizer-header' },
        h('h3', null, '☕ Quick Brew Customizer')
      ),
      h('p', { className: 'customizer-desc' }, 'Customize your roast & size with real-time price calculation:'),

      // Drink selection
      h('div', { className: 'customizer-row' },
        h('label', null, 'Drink Type:'),
        h('div', { className: 'pill-group' },
          Object.keys(basePrices).map(d =>
            h('button', {
              key: d,
              type: 'button',
              className: `pill-btn ${drink === d ? 'active' : ''}`,
              onClick: () => setDrink(d)
            }, `${d} ($${basePrices[d].toFixed(2)})`)
          )
        )
      ),

      // Size selection
      h('div', { className: 'customizer-row' },
        h('label', null, 'Size:'),
        h('div', { className: 'pill-group' },
          Object.keys(sizeAddons).map(s =>
            h('button', {
              key: s,
              type: 'button',
              className: `pill-btn ${size === s ? 'active' : ''}`,
              onClick: () => setSize(s)
            }, `${s} ${sizeAddons[s] > 0 ? '(+$' + sizeAddons[s].toFixed(2) + ')' : ''}`)
          )
        )
      ),

      // Extra shot toggle
      h('div', { className: 'customizer-row checkbox-row' },
        h('label', { className: 'checkbox-label' },
          h('input', {
            type: 'checkbox',
            checked: extraShot,
            onChange: e => setExtraShot(e.target.checked)
          }),
          ' Add Extra Espresso Shot (+$0.75)'
        )
      ),

      // Footer: Total & Add to Cart button
      h('div', { className: 'customizer-footer' },
        h('div', { className: 'customizer-total' },
          h('span', { className: 'total-label' }, 'Total: '),
          h('span', { className: 'total-price' }, `$${totalPrice.toFixed(2)}`)
        ),
        h('button', {
          type: 'button',
          className: 'btn btn-primary',
          onClick: handleOrder
        }, added ? '✓ Added to Cart!' : 'Add to Order')
      )
    );
  }

  // Mount component into #coffee-customizer-root
  function mountCustomizer() {
    const container = document.getElementById('coffee-customizer-root');
    if (container && window.ReactDOM && window.React) {
      const root = ReactDOM.createRoot(container);
      root.render(h(CoffeeCustomizer));
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountCustomizer);
  } else {
    mountCustomizer();
  }
})();
