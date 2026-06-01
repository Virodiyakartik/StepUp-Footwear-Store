-- 1. Products Table (Catalog management)
CREATE TABLE IF NOT EXISTS products (
    p_id INT AUTO_INCREMENT PRIMARY KEY,
    product_title VARCHAR(255) NOT NULL,
    product_price VARCHAR(50) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    brand_name VARCHAR(100)
);

-- 2. Orders Table (Checkout & Tracking)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cust_name VARCHAR(100),
    cust_email VARCHAR(150),
    product_name VARCHAR(255),
    price VARCHAR(50),
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Wishlist Table (User favorites)
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255) NOT NULL,
    product_id INT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(p_id) ON DELETE CASCADE
);

-- 4. Reviews Table (Feedback management)
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    rating INT,
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


FeatureQueryFetch All ItemsSELECT * FROM products ORDER BY p_id DESC;Add to WishlistINSERT INTO wishlist (user_email, product_id) VALUES (?, ?);Place OrderINSERT INTO orders (cust_name, cust_email, product_name, price) VALUES (?, ?, ?, ?);Update Order StatusUPDATE orders SET status = ? WHERE id = ?;Get Unique BrandsSELECT DISTINCT brand_name FROM products WHERE brand_name != '' ORDER BY brand_name ASC;Get Wishlist ItemsSELECT p.* FROM products p JOIN wishlist w ON p.p_id = w.product_id WHERE w.user_email = ?;Track OrderSELECT status FROM orders WHERE id = ?;