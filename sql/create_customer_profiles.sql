-- Create customer_profiles table to store additional customer information
CREATE TABLE IF NOT EXISTS customer_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    email VARCHAR(100),
    contact_number VARCHAR(20),
    address TEXT,
    age INT,
    country VARCHAR(50),
    preferred_currency VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_profile (user_id)
);

-- Update existing users to have 'customer' role instead of 'user'
UPDATE users SET role = 'customer' WHERE role = 'user';

-- Insert default profiles for existing customer users
INSERT IGNORE INTO customer_profiles (user_id, first_name, last_name, email, country, preferred_currency)
SELECT user_id, first_name, last_name, email, country, 'USD ($)' as preferred_currency
FROM users 
WHERE role = 'customer';
