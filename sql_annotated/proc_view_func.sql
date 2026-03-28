-- Remove old copies first so the script can be safely re-run.
DROP PROCEDURE IF EXISTS sp_get_user_orders;
DROP PROCEDURE IF EXISTS sp_get_admin_orders;
DROP PROCEDURE IF EXISTS sp_get_activity_logs;

DROP VIEW IF EXISTS vw_activity_log_details;
DROP VIEW IF EXISTS vw_order_item_details;
DROP VIEW IF EXISTS vw_order_summary;
DROP VIEW IF EXISTS vw_catalog_products;

DROP FUNCTION IF EXISTS fn_stock_status;
DROP FUNCTION IF EXISTS fn_order_item_count;
DROP FUNCTION IF EXISTS fn_order_total;

DELIMITER $$

-- Returns the computed peso total for one order by summing quantity x unit price.
CREATE FUNCTION fn_order_total(p_order_id INT)
RETURNS DECIMAL(10,2)
READS SQL DATA
BEGIN
    DECLARE v_total DECIMAL(10,2);

    SELECT COALESCE(SUM(quantity * unit_price), 0.00)
      INTO v_total
      FROM order_items
     WHERE order_id = p_order_id;

    RETURN v_total;
END$$

-- Returns the total number of items in an order by summing all line quantities.
CREATE FUNCTION fn_order_item_count(p_order_id INT)
RETURNS INT
READS SQL DATA
BEGIN
    DECLARE v_count INT;

    SELECT COALESCE(SUM(quantity), 0)
      INTO v_count
      FROM order_items
     WHERE order_id = p_order_id;

    RETURN v_count;
END$$

-- Converts a raw stock number into a display-friendly label for the UI.
CREATE FUNCTION fn_stock_status(p_stock INT)
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
    IF p_stock IS NULL OR p_stock <= 0 THEN
        RETURN 'Out of Stock';
    END IF;

    IF p_stock <= 5 THEN
        RETURN 'Low Stock';
    END IF;

    RETURN 'In Stock';
END$$

-- Catalog view used by the storefront and admin product listings.
-- It combines product data, a human-friendly stock label, and the product's primary image.
-- Archived products are hidden from this view.
CREATE VIEW vw_catalog_products AS
SELECT
    p.product_id,
    p.name,
    p.description,
    p.price,
    p.date_added,
    p.stock,
    p.category,
    fn_stock_status(p.stock) AS stock_status,
    (
        SELECT pi.image
          FROM product_images pi
         WHERE pi.product_id = p.product_id
         ORDER BY pi.is_primary DESC, pi.image_id ASC
         LIMIT 1
    ) AS image_blob
FROM products p
WHERE p.is_archived = 0$$

-- Order summary view used by profile and admin order pages.
-- It joins users to orders and enriches each order with computed totals and item counts.
CREATE VIEW vw_order_summary AS
SELECT
    o.order_id,
    o.user_id,
    u.username,
    u.email,
    u.address,
    o.status,
    o.created_at,
    fn_order_total(o.order_id) AS total_amount,
    fn_order_item_count(o.order_id) AS item_count
FROM orders o
JOIN users u ON o.user_id = u.user_id$$

-- Order item detail view used when showing the line items inside each order.
-- It joins order items to products so the UI can display the product name.
CREATE VIEW vw_order_item_details AS
SELECT
    oi.item_id,
    oi.order_id,
    oi.product_id,
    p.name AS product_name,
    oi.quantity,
    oi.unit_price,
    (oi.quantity * oi.unit_price) AS subtotal
FROM order_items oi
JOIN products p ON oi.product_id = p.product_id$$

-- Activity log view used by the admin logs page.
-- It joins logs to users so each log entry can show a username and email when available.
CREATE VIEW vw_activity_log_details AS
SELECT
    l.log_id,
    l.user_id,
    l.action,
    l.table_name,
    l.record_id,
    l.logged_at,
    u.username,
    u.email
FROM activity_logs l
LEFT JOIN users u ON l.user_id = u.user_id$$

-- Returns all orders for one specific user, newest first.
CREATE PROCEDURE sp_get_user_orders(IN p_user_id INT)
BEGIN
    SELECT
        order_id,
        user_id,
        username,
        email,
        address,
        status,
        created_at,
        total_amount,
        item_count
    FROM vw_order_summary
    WHERE user_id = p_user_id
    ORDER BY created_at DESC;
END$$

-- Returns admin-facing order rows with optional filters for status and customer search text.
CREATE PROCEDURE sp_get_admin_orders(
    IN p_status VARCHAR(20),
    IN p_customer_search VARCHAR(100)
)
BEGIN
    SELECT
        order_id,
        user_id,
        username,
        email,
        address,
        status,
        created_at,
        total_amount,
        item_count
    FROM vw_order_summary
    WHERE (p_status IS NULL OR p_status = '' OR status = p_status)
      AND (
            p_customer_search IS NULL OR p_customer_search = ''
            OR username LIKE CONCAT('%', p_customer_search, '%')
            OR email LIKE CONCAT('%', p_customer_search, '%')
          )
    ORDER BY created_at DESC;
END$$

-- Returns recent activity logs with optional filtering by table, action text, and user search.
-- The LIMIT protects the admin page from loading an unbounded number of rows.
CREATE PROCEDURE sp_get_activity_logs(
    IN p_table_name VARCHAR(50),
    IN p_action_search VARCHAR(100),
    IN p_user_search VARCHAR(100)
)
BEGIN
    SELECT
        log_id,
        user_id,
        action,
        table_name,
        record_id,
        logged_at,
        username,
        email
    FROM vw_activity_log_details
    WHERE (p_table_name IS NULL OR p_table_name = '' OR table_name = p_table_name)
      AND (p_action_search IS NULL OR p_action_search = '' OR action LIKE CONCAT('%', p_action_search, '%'))
      AND (
            p_user_search IS NULL OR p_user_search = ''
            OR username LIKE CONCAT('%', p_user_search, '%')
            OR email LIKE CONCAT('%', p_user_search, '%')
            OR CAST(user_id AS CHAR) LIKE CONCAT('%', p_user_search, '%')
          )
    ORDER BY logged_at DESC, log_id DESC
    LIMIT 250;
END$$

DELIMITER ;
