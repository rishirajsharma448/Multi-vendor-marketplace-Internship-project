<footer class="footer-vyaparsetu mt-auto">
  <div class="container-fluid px-lg-5">
    <div class="row g-4 mb-4">
      <div class="col-lg-4 col-md-6">
        <div class="p-2 bg-white rounded d-inline-block mb-3" style="max-width: 160px; box-shadow: var(--shadow-sm);">
          <img src="<?= APP_URL ?>/assets/images/logo.jpg" alt="Vyapar Setu Logo" class="img-fluid" style="max-height: 50px; object-fit: contain;">
        </div>
        <p class="small footer-text mb-3">
          Connecting businesses, creating opportunities. Vyapar Setu is India's premier multi-vendor e-commerce platform connecting verified SME vendors with buyers nationwide.
        </p>
        <div class="d-flex gap-3">
          <a href="#" class="btn btn-sm btn-outline-light rounded-circle"><i class="bi bi-facebook"></i></a>
          <a href="#" class="btn btn-sm btn-outline-light rounded-circle"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="btn btn-sm btn-outline-light rounded-circle"><i class="bi bi-instagram"></i></a>
          <a href="#" class="btn btn-sm btn-outline-light rounded-circle"><i class="bi bi-linkedin"></i></a>
        </div>
      </div>
      
      <div class="col-lg-2 col-md-6">
        <h6 class="text-white fw-bold mb-3">Marketplace</h6>
        <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
          <li><a href="<?= APP_URL ?>/shop.php">All Products</a></li>
          <li><a href="<?= APP_URL ?>/category.php">Categories</a></li>
          <li><a href="<?= APP_URL ?>/shop.php?spotlight=BESTSELLER">Bestsellers</a></li>
          <li><a href="<?= APP_URL ?>/shop.php?spotlight=FEATURED">Featured Items</a></li>
        </ul>
      </div>

      <div class="col-lg-2 col-md-6">
        <h6 class="text-white fw-bold mb-3">Company</h6>
        <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
          <li><a href="<?= APP_URL ?>/about.php">About Us</a></li>
          <li><a href="<?= APP_URL ?>/contact.php">Contact Us</a></li>
          <li><a href="<?= APP_URL ?>/faq.php">Help & FAQ</a></li>
          <li><a href="<?= APP_URL ?>/terms.php">Terms of Service</a></li>
          <li><a href="<?= APP_URL ?>/privacy.php">Privacy Policy</a></li>
        </ul>
      </div>

      <div class="col-lg-4 col-md-6">
        <h6 class="text-white fw-bold mb-3">Sell on Vyapar Setu</h6>
        <p class="small footer-text mb-3">Grow your business by reaching millions of active shoppers nationwide with low commission rates.</p>
        <a href="<?= APP_URL ?>/register.php?role=vendor" class="btn btn-sm btn-warning fw-bold px-3"><i class="bi bi-shop me-1"></i> Become a Vendor</a>
      </div>
    </div>

    <div class="border-top border-secondary pt-3 text-center small text-light opacity-75">
      &copy; <?= date('Y') ?> Vyapar Setu Inc. All rights reserved. Built with PHP, MySQL & Bootstrap 5.
    </div>
  </div>
</footer>
<!-- Chatbot UI -->
<div id="chatbot-container">
    <div id="chatbot-window">
        <div id="chatbot-header">
            <div class="d-flex align-items-center gap-2">
                <div class="chatbot-avatar-badge"><i class="bi bi-robot"></i></div>
                <div>
                    <h5 class="mb-0">Setu AI Assistant</h5>
                    <small class="text-white-50" style="font-size: 11px;">Powered by Live Marketplace Catalog</small>
                </div>
            </div>
            <button id="chatbot-close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div id="chatbot-chips" class="chatbot-chips">
            <span class="chatbot-chip" data-msg="Compare iPhone 17 vs iPhone 18 Pro">⚖️ Compare iPhones</span>
            <span class="chatbot-chip" data-msg="What do customer reviews say about Black T-shirt?">⭐ T-Shirt Reviews</span>
            <span class="chatbot-chip" data-msg="Show me photos and specs of Alpha Tech Active Noise-Cancelling Bluetooth Headphones Pro">📸 Headphones Photos</span>
            <span class="chatbot-chip" data-msg="Compare PlayStation 5 vs Samsung S26 Ultra">🎮 PS5 vs S26</span>
        </div>
        <div id="chatbot-messages">
            <div class="chatbot-message bot">
                👋 <strong>Namaste! I am Setu AI</strong>, your personal shopping assistant.<br><br>
                Ask me for <strong>product comparisons</strong>, <strong>verified customer reviews</strong>, <strong>specifications</strong>, or <strong>photos</strong> of any item in our store!
            </div>
        </div>
        <div id="chatbot-input-area">
            <input type="text" id="chatbot-input" placeholder="Ask about products, orders...">
            <button id="chatbot-send"><i class="bi bi-send-fill"></i></button>
        </div>
    </div>
    <button id="chatbot-toggle">
        <i class="bi bi-chat-dots"></i>
    </button>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- AOS Animations -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<!-- Custom JavaScript -->
<script src="<?= APP_URL ?>/assets/js/app.js"></script>
<script src="<?= APP_URL ?>/assets/js/chatbot.js"></script>
<script>
  if (typeof AOS !== 'undefined') {
    AOS.init({ duration: 600, once: true });
  }
</script>
</body>
</html>
