// Pages that need navigation update (already updated: login.html, register.html, bundles.html, upcoming.html)
const pagesToUpdate = [
    'support.html',
    'new-releases.html', 
    'mystery.html',
    'fantasy-verse.html',
    'discover.html',
    'dashboard.html',
    'categories.html',
    'wishlist.html',
    'orders.html'
];

// Standard navigation HTML to insert
const navigationHTML = `    <!-- Flash Sale Banner -->
    <div class="flash-sale-banner">
        <i class="fas fa-fire"></i> SUMMER SALE - Up to 90% OFF! <i class="fas fa-fire"></i>
    </div>

    <!-- Header -->
    <header class="header">
        <nav class="nav-container">
            <a href="../index.html" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-gamepad"></i>
                </div>
                GAMEHUB
            </a>

            <div class="search-container">
                <input type="search" class="search-bar" placeholder="Search PC, Mac, Linux Games" id="searchInput">
                <button class="search-btn" onclick="performSearch()">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <div class="nav-links">
                <a href="bundles.html" class="nav-link">Bundles</a>
                <a href="upcoming.html" class="nav-link">Upcoming Games</a>
                <a href="new-releases.html" class="nav-link">New Releases</a>
                <a href="support.html" class="nav-link">Support</a>
            </div>

            <div class="user-actions">
                <div class="wishlist-icon">
                    <i class="fas fa-heart"></i>
                    <span class="badge" id="wishlistCount">0</span>
                </div>
                <div class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="badge" id="cartCount">0</span>
                    <span id="cartTotal">$0.00</span>
                </div>
                <a href="login.html" class="login-btn">
                    <i class="fas fa-user"></i>
                    Login
                </a>
            </div>
        </nav>
    </header>

    <!-- Secondary Navigation -->
    <nav class="secondary-nav">
        <div class="secondary-nav-container">
            <a href="discover.html" class="secondary-nav-link">Discover</a>
            <a href="categories.html" class="secondary-nav-link">Categories</a>
            <a href="bundles.html" class="secondary-nav-link">Bundles</a>
            <a href="mystery.html" class="secondary-nav-link">Mystery</a>
            <a href="fantasy-verse.html" class="secondary-nav-link">FantasyVerse</a>
        </div>
    </nav>`;

// Search script to add before closing body tag
const searchScript = `    <script>
        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value;
            if (searchTerm.trim()) {
                window.location.href = \`../index.html?search=\${encodeURIComponent(searchTerm)}\`;
            }
        }

        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    </script>`;

// Instructions for manual update
console.log('Navigation Update Instructions:');
console.log('==============================');
console.log('');
console.log('For each page in the pagesToUpdate array:');
console.log('1. Replace the existing <header> section with the navigationHTML');
console.log('2. Add the searchScript before the closing </body> tag');
console.log('3. Remove any old navigation elements');
console.log('');
console.log('Pages to update:', pagesToUpdate.join(', '));
