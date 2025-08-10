-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    shipping_address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_order_number (order_number),
    INDEX idx_customer_id (customer_id),
    INDEX idx_status (status),
    INDEX idx_order_date (order_date)
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_type VARCHAR(50) DEFAULT 'game',
    price DECIMAL(10,2) NOT NULL,
    quantity INT DEFAULT 1,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id)
);

-- Insert sample orders data
INSERT INTO orders (customer_id, order_number, total_amount, status, payment_method, payment_status, shipping_address) VALUES
(1, 'ORD-2024-001', 15.38, 'completed', 'PayPal', 'paid', 'No. 123, Main Street, Colombo, Sri Lanka'),
(2, 'ORD-2024-002', 29.99, 'pending', 'Credit Card', 'pending', 'No. 456, Second Street, Kandy, Sri Lanka'),
(4, 'ORD-2024-003', 59.97, 'completed', 'PayPal', 'paid', '789 Demo Ave, Test City, USA'),
(1, 'ORD-2024-004', 4.99, 'processing', 'Credit Card', 'paid', 'No. 123, Main Street, Colombo, Sri Lanka'),
(2, 'ORD-2024-005', 39.98, 'cancelled', 'PayPal', 'refunded', 'No. 456, Second Street, Kandy, Sri Lanka');

-- Insert sample order items
INSERT INTO order_items (order_id, product_name, price, quantity) VALUES
(1, 'Lords of the Fallen Deluxe', 10.39, 1),
(1, 'Indie Game Bundle', 4.99, 1),
(2, 'Cyberpunk 2077', 29.99, 1),
(3, 'The Elder Scrolls IV: Oblivion', 4.99, 1),
(3, 'Red Dead Redemption 2', 39.99, 1),
(3, 'Action RPG Bundle', 14.99, 1),
(4, 'The Elder Scrolls IV: Oblivion', 4.99, 1),
(5, 'Fantasy Adventure Pack', 19.99, 1),
(5, 'Strategy Game Collection', 19.99, 1);
