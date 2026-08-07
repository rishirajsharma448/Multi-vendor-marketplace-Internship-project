/**
 * Vyapar Setu Main Interactive JavaScript Framework
 */

document.addEventListener('DOMContentLoaded', () => {
  initTooltips();
  initCartAndWishlistButtons();
  initSearchAutocomplete();
});

// Toast notification helper
function showToast(message, type = 'success') {
  const toastContainer = document.getElementById('toastContainer') || createToastContainer();
  const toastId = 'toast-' + Date.now();
  
  const bgClass = type === 'success' ? 'bg-success text-white' : (type === 'danger' ? 'bg-danger text-white' : 'bg-primary text-white');
  const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
  
  const toastHTML = `
    <div id="${toastId}" class="toast align-items-center ${bgClass} border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center gap-2">
          <i class="${icon} fs-5"></i>
          <span>${message}</span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  `;
  
  toastContainer.insertAdjacentHTML('beforeend', toastHTML);
  const toastEl = document.getElementById(toastId);
  const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });
  bsToast.show();
  
  toastEl.addEventListener('hidden.bs.toast', () => {
    toastEl.remove();
  });
}

function createToastContainer() {
  const container = document.createElement('div');
  container.id = 'toastContainer';
  container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
  container.style.zIndex = '9999';
  document.body.appendChild(container);
  return container;
}

function initTooltips() {
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
}

function initCartAndWishlistButtons() {
  document.addEventListener('click', (e) => {
    const cartBtn = e.target.closest('.btn-add-cart');
    if (cartBtn) {
      e.preventDefault();
      const productId = cartBtn.dataset.productId;
      const qtyInput = document.getElementById('productQuantity');
      const quantity = qtyInput ? qtyInput.value : (cartBtn.dataset.quantity || 1);
      
      addToCartAJAX(productId, quantity);
    }
    
    const buyBtn = e.target.closest('.btn-buy-now');
    if (buyBtn) {
      e.preventDefault();
      const productId = buyBtn.dataset.productId;
      const qtyInput = document.getElementById('productQuantity');
      const quantity = qtyInput ? qtyInput.value : 1;
      
      buyNowAJAX(productId, quantity);
    }

    const wishBtn = e.target.closest('.btn-toggle-wishlist');
    if (wishBtn) {
      e.preventDefault();
      const productId = wishBtn.dataset.productId;
      toggleWishlistAJAX(productId, wishBtn);
    }
  });
}

function getAjaxUrl(path) {
  return (window.APP_URL || '') + '/' + path;
}

function addToCartAJAX(productId, quantity) {
  fetch(getAjaxUrl('ajax/add-cart.php'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `product_id=${productId}&quantity=${quantity}`
  })
  .then(res => res.text().then(text => {
    try {
      return JSON.parse(text);
    } catch (e) {
      throw new Error('Server error when adding item to cart.');
    }
  }))
  .then(data => {
    if (data.status === 'success') {
      showToast(data.message || 'Item added to shopping cart!', 'success');
      const badge = document.querySelector('.cart-badge');
      if (badge && data.cart_count !== undefined) {
        badge.textContent = data.cart_count;
        badge.classList.remove('d-none');
      }
    } else if (data.status === 'redirect') {
      showToast(data.message || 'Please login to add items to cart.', 'info');
      setTimeout(() => {
        window.location.href = data.url;
      }, 800);
    } else {
      showToast(data.message || 'Error updating cart', 'danger');
    }
  })
  .catch(err => {
    console.error('Cart AJAX error:', err);
    showToast(err.message || 'Failed to add item to cart.', 'danger');
  });
}

function buyNowAJAX(productId, quantity) {
  fetch(getAjaxUrl('ajax/add-cart.php'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `product_id=${productId}&quantity=${quantity}`
  })
  .then(res => res.text().then(text => {
    try {
      return JSON.parse(text);
    } catch (e) {
      throw new Error('Server error processing request.');
    }
  }))
  .then(data => {
    if (data.status === 'success') {
      window.location.href = window.APP_URL + '/customer/cart.php';
    } else if (data.status === 'redirect') {
      window.location.href = data.url;
    } else {
      showToast(data.message || 'Error processing request', 'danger');
    }
  })
  .catch(err => {
    console.error('Buy Now AJAX error:', err);
    showToast(err.message || 'Failed to process Buy Now.', 'danger');
  });
}

function toggleWishlistAJAX(productId, buttonEl) {
  fetch(getAjaxUrl('ajax/wishlist.php'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `product_id=${productId}`
  })
  .then(res => res.text().then(text => {
    try {
      return JSON.parse(text);
    } catch (e) {
      throw new Error('Server error updating wishlist.');
    }
  }))
  .then(data => {
    if (data.status === 'success') {
      showToast(data.message, 'success');
      const icon = buttonEl.querySelector('i');
      if (icon) {
        if (data.added) {
          icon.className = 'bi bi-heart-fill text-danger';
        } else {
          icon.className = 'bi bi-heart';
        }
      }
      const badge = document.querySelector('.wishlist-badge');
      if (badge && data.wishlist_count !== undefined) {
        badge.textContent = data.wishlist_count;
        badge.classList.remove('d-none');
      }
    } else if (data.status === 'redirect') {
      showToast(data.message || 'Please login to save items to wishlist.', 'info');
      setTimeout(() => {
        window.location.href = data.url;
      }, 800);
    } else {
      showToast(data.message || 'Error updating wishlist', 'danger');
    }
  })
  .catch(err => {
    console.error('Wishlist AJAX error:', err);
    showToast(err.message || 'Failed to update wishlist.', 'danger');
  });
}

function initSearchAutocomplete() {
  const searchInput = document.getElementById('globalSearchInput');
  if (!searchInput) return;
  
  let timeout = null;
  searchInput.addEventListener('input', function() {
    clearTimeout(timeout);
    const query = this.value.trim();
    if (query.length < 2) return;
    
    timeout = setTimeout(() => {
      fetch(`ajax/search.php?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
          // Can render autocomplete dropdown if container present
        }).catch(err => {});
    }, 300);
  });
}
