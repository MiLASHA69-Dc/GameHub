// Main JavaScript file for Fanatical Store
// Handles authentication, navigation, cart, wishlist, and common functionality

class FanaticalStore {
    constructor() {
        this.user = null;
        this.cart = [];
        this.wishlist = [];
        this.products = [];
        
        this.init();
    }

    init() {
        this.loadUserData();
        this.loadCartData();
        this.loadWishlistData();
        this.updateNavigation();
        this.bindEvents();
    }

    // User Authentication
    loadUserData() {
        const userData = localStorage.getItem('user');
        if (userData) {
            this.user = JSON.parse(userData);
        }
    }

    login(userData) {
        this.user = userData;
        localStorage.setItem('user', JSON.stringify(userData));
        this.updateNavigation();
    }

    logout() {
        this.user = null;
        localStorage.removeItem('user');
        this.updateNavigation();
        window.location.href = '/video_game_store/index.html';
    }

    // Cart Management
    loadCartData() {
        const cartData = localStorage.getItem('cart');
        if (cartData) {
            this.cart = JSON.parse(cartData);
        }
        this.updateCartDisplay();
    }

    saveCartData() {
        localStorage.setItem('cart', JSON.stringify(this.cart));
        this.updateCartDisplay();
    }

    addToCart(productId, quantity = 1) {
        const existingItem = this.cart.find(item => item.productId === productId);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.cart.push({
                productId: productId,
                quantity: quantity,
                addedAt: new Date().toISOString()
            });
        }
        
        this.saveCartData();
        this.showNotification('Item added to cart!', 'success');
    }

    removeFromCart(productId) {
        this.cart = this.cart.filter(item => item.productId !== productId);
        this.saveCartData();
        this.showNotification('Item removed from cart!', 'info');
    }

    updateCartQuantity(productId, quantity) {
        const item = this.cart.find(item => item.productId === productId);
        if (item) {
            if (quantity <= 0) {
                this.removeFromCart(productId);
            } else {
                item.quantity = quantity;
                this.saveCartData();
            }
        }
    }

    getCartTotal() {
        return this.cart.reduce((total, item) => {
            const product = this.getProduct(item.productId);
            return total + (product ? product.price * item.quantity : 0);
        }, 0);
    }

    updateCartDisplay() {
        const cartCountElement = document.getElementById('cartCount');
        const cartTotalElement = document.getElementById('cartTotal');
        
        if (cartCountElement) {
            cartCountElement.textContent = this.cart.length;
        }
        
        if (cartTotalElement) {
            cartTotalElement.textContent = `$${this.getCartTotal().toFixed(2)}`;
        }
    }

    // Wishlist Management
    loadWishlistData() {
        const wishlistData = localStorage.getItem('wishlist');
        if (wishlistData) {
            this.wishlist = JSON.parse(wishlistData);
        }
        this.updateWishlistDisplay();
    }

    saveWishlistData() {
        localStorage.setItem('wishlist', JSON.stringify(this.wishlist));
        this.updateWishlistDisplay();
    }

    addToWishlist(productId) {
        if (!this.wishlist.includes(productId)) {
            this.wishlist.push(productId);
            this.saveWishlistData();
            this.showNotification('Added to wishlist!', 'success');
        } else {
            this.showNotification('Already in wishlist!', 'info');
        }
    }

    removeFromWishlist(productId) {
        this.wishlist = this.wishlist.filter(id => id !== productId);
        this.saveWishlistData();
        this.showNotification('Removed from wishlist!', 'info');
    }

    toggleWishlist(productId) {
        if (this.wishlist.includes(productId)) {
            this.removeFromWishlist(productId);
        } else {
            this.addToWishlist(productId);
        }
    }

    updateWishlistDisplay() {
        const wishlistCountElement = document.getElementById('wishlistCount');
        if (wishlistCountElement) {
            wishlistCountElement.textContent = this.wishlist.length;
        }
    }

    // Navigation Updates
    updateNavigation() {
        const accountText = document.getElementById('accountText');
        const accountMenu = document.getElementById('accountMenu');
        
        if (this.user && this.user.isLoggedIn) {
            if (accountText) {
                accountText.textContent = `Welcome, ${this.user.name || 'User'}!`;
            }
            
            if (accountMenu) {
                accountMenu.innerHTML = `
                    <a href="${this.user.role === 'admin' ? '/video_game_store/admin/dashboard.html' : '/video_game_store/pages/dashboard.html'}">Dashboard</a>
                    <a href="/video_game_store/pages/orders.html">My Orders</a>
                    <a href="/video_game_store/pages/wishlist.html">Wishlist</a>
                    <a href="#" onclick="store.logout()">Logout</a>
                `;
            }
        } else {
            if (accountText) {
                accountText.textContent = 'My Account';
            }
            
            if (accountMenu) {
                accountMenu.innerHTML = `
                    <a href="/video_game_store/pages/login.html">Login</a>
                    <a href="/video_game_store/pages/register.html">Register</a>
                    <a href="/video_game_store/admin/login.html">Admin Login</a>
                `;
            }
        }
    }

    // Product Management
    getProduct(productId) {
        return this.products.find(product => product.id === productId);
    }

    // Search Functionality
    searchProducts(query, filters = {}) {
        let results = this.products;
        
        // Text search
        if (query) {
            results = results.filter(product =>
                product.title.toLowerCase().includes(query.toLowerCase()) ||
                product.description?.toLowerCase().includes(query.toLowerCase())
            );
        }
        
        // Price filters
        if (filters.minPrice) {
            results = results.filter(product => product.price >= filters.minPrice);
        }
        
        if (filters.maxPrice) {
            results = results.filter(product => product.price <= filters.maxPrice);
        }
        
        // Category filter
        if (filters.category) {
            results = results.filter(product => product.category === filters.category);
        }
        
        // Platform filter
        if (filters.platform) {
            results = results.filter(product => product.platform === filters.platform);
        }
        
        // On sale filter
        if (filters.onSale) {
            results = results.filter(product => product.discount > 0);
        }
        
        return results;
    }

    // Notifications
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        // Hide notification
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // Event Binding
    bindEvents() {
        // Account menu toggle
        const accountBtn = document.querySelector('.account-btn');
        if (accountBtn) {
            accountBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleAccountMenu();
            });
        }

        // Close account menu when clicking outside
        document.addEventListener('click', () => {
            const accountMenu = document.getElementById('accountMenu');
            if (accountMenu) {
                accountMenu.classList.add('hidden');
            }
        });

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.querySelector('.search-btn');
        
        if (searchInput && searchBtn) {
            searchBtn.addEventListener('click', () => {
                this.performSearch(searchInput.value);
            });
            
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.performSearch(searchInput.value);
                }
            });
        }

        // Modal functionality
        this.bindModalEvents();
    }

    toggleAccountMenu() {
        const accountMenu = document.getElementById('accountMenu');
        if (accountMenu) {
            accountMenu.classList.toggle('hidden');
        }
    }

    performSearch(query) {
        if (query.trim()) {
            // Redirect to search results page or filter current products
            window.location.href = `/video_game_store/pages/search.html?q=${encodeURIComponent(query)}`;
        }
    }

    // Modal Management
    bindModalEvents() {
        // Close modal when clicking outside
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
            }
        });

        // Close modal with escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal[style*="display: flex"]');
                if (openModal) {
                    openModal.style.display = 'none';
                }
            }
        });
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Utility Functions
    formatPrice(price) {
        return `$${price.toFixed(2)}`;
    }

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString();
    }

    // API Calls (placeholder for future implementation)
    async apiCall(endpoint, options = {}) {
        try {
            const response = await fetch(`/video_game_store/api/${endpoint}`, {
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API call failed:', error);
            throw error;
        }
    }
}

// Initialize the store
const store = new FanaticalStore();

// Global functions for backwards compatibility
function addToCart(productId, quantity = 1) {
    store.addToCart(productId, quantity);
}

function addToWishlist(productId) {
    store.addToWishlist(productId);
}

function toggleWishlist(productId) {
    store.toggleWishlist(productId);
}

function toggleAccountMenu() {
    store.toggleAccountMenu();
}

function performSearch() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        store.performSearch(searchInput.value);
    }
}

function openModal(modalId) {
    store.openModal(modalId);
}

function closeModal(modalId) {
    store.closeModal(modalId);
}

function logout() {
    store.logout();
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FanaticalStore;
}
