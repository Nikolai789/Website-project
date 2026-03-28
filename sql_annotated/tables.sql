-- Create the main database used by the GearHub project.
create database gearhubDB;

-- Switch the current MySQL session to that database.
use gearhubDB;

-- Enables MySQL scheduled events.
-- This project does not currently create events here, but this allows timed jobs later if needed.
set global event_scheduler = true;

-- Stores all registered accounts.
-- Each row is one person who can log in, either as a regular user or an admin.
create table users(
	user_id int primary key auto_increment,
	username varchar(20) NOT NULL,
	email varchar(255) unique,
	password varchar(255),
	address varchar(255),
	user_role enum('admin','user') default 'user',
    created_at timestamp default CURRENT_TIMESTAMP);

-- Stores the products shown in the shop.
-- `is_archived` hides products from the catalog without deleting them from order history.
create table products(
	product_id int primary key auto_increment,
	name varchar(100) NOT NULL,
	description text NOT NULL,
	price decimal(10,2)NOT NULL,
	date_added date NOT NULL Default (current_date),
	stock int NOT NULL, 
	category ENUM('Keyboard', 'Mouse', 'Headphone') NOT NULL,
    is_archived TINYINT(1) NOT NULL DEFAULT 0
);

-- Stores one or more images for each product.
-- If a product is fully deleted, its images are automatically deleted too.
CREATE TABLE product_images (
    image_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image LONGBLOB NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Stores the order header or summary record created at checkout.
-- One user can have many orders.
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10,2),
    status ENUM('pending', 'shipped', 'paid/delivered') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Stores the individual line items inside an order.
-- This connects an order to the products that were purchased and preserves the unit price used at checkout.
CREATE TABLE order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT,
    unit_price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Stores the user's temporary shopping cart before checkout.
-- If a user or product is deleted, related cart rows are automatically removed.
CREATE TABLE cart_items( 
	cart_item_id INT PRIMARY KEY AUTO_INCREMENT, 
	user_id INT, product_id INT, quantity INT NOT NULL, 
	added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE, 
	FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE 
);

-- Stores the audit trail for important actions in the system.
-- Logs can still remain even if the related user is removed because the foreign key becomes NULL.
CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100),
    table_name VARCHAR(50),
    record_id INT,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);
