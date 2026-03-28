-- Stock protection and stock-sync triggers for GearHub.
-- Covers:
-- 1. Preventing negative product stock.
-- 2. Preventing cart quantities above current stock.
-- 3. Deducting stock when order items are created.
-- 4. Rebalancing stock when order items are updated or deleted.
--
-- These triggers also temporarily suppress activity-log side effects
-- while they perform internal product stock updates.

DROP TRIGGER IF EXISTS validate_product_stock_before_insert;
DROP TRIGGER IF EXISTS validate_product_stock_before_update;
DROP TRIGGER IF EXISTS check_stock_before_cart;
DROP TRIGGER IF EXISTS check_stock_before_cart_update;
DROP TRIGGER IF EXISTS check_stock_before_order;
DROP TRIGGER IF EXISTS check_stock_before_order_update;
DROP TRIGGER IF EXISTS deduct_stock_after_order;
DROP TRIGGER IF EXISTS adjust_stock_after_order_update;
DROP TRIGGER IF EXISTS restore_stock_on_order_delete;

DELIMITER $$

CREATE TRIGGER validate_product_stock_before_insert
BEFORE INSERT ON products
FOR EACH ROW
BEGIN
    -- Reject inserts where stock is missing or negative.
    IF NEW.stock IS NULL OR NEW.stock < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Product stock cannot be negative.';
    END IF;
END$$

CREATE TRIGGER validate_product_stock_before_update
BEFORE UPDATE ON products
FOR EACH ROW
BEGIN
    -- Reject updates that would make product stock invalid.
    IF NEW.stock IS NULL OR NEW.stock < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Product stock cannot be negative.';
    END IF;
END$$

CREATE TRIGGER check_stock_before_cart
BEFORE INSERT ON cart_items
FOR EACH ROW
BEGIN
    DECLARE available_stock INT DEFAULT NULL;

    -- Cart quantity must always be positive.
    IF NEW.quantity IS NULL OR NEW.quantity <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cart quantity must be greater than zero.';
    END IF;

    -- Load the current product stock so the cart cannot exceed what is available.
    SELECT stock
      INTO available_stock
      FROM products
     WHERE product_id = NEW.product_id;

    -- Stop the insert if the product does not exist.
    IF available_stock IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Selected product was not found.';
    END IF;

    -- Stop the insert if the requested cart quantity is higher than stock.
    IF NEW.quantity > available_stock THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot add more than available stock to cart.';
    END IF;
END$$

CREATE TRIGGER check_stock_before_cart_update
BEFORE UPDATE ON cart_items
FOR EACH ROW
BEGIN
    DECLARE available_stock INT DEFAULT NULL;

    -- Updated cart quantity must stay positive.
    IF NEW.quantity IS NULL OR NEW.quantity <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cart quantity must be greater than zero.';
    END IF;

    -- Load the latest product stock before allowing the cart update.
    SELECT stock
      INTO available_stock
      FROM products
     WHERE product_id = NEW.product_id;

    -- Stop the update if the product is missing.
    IF available_stock IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Selected product was not found.';
    END IF;

    -- Stop the update if the new quantity would exceed stock.
    IF NEW.quantity > available_stock THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot update cart beyond available stock.';
    END IF;
END$$

CREATE TRIGGER check_stock_before_order
BEFORE INSERT ON order_items
FOR EACH ROW
BEGIN
    DECLARE available_stock INT DEFAULT NULL;

    -- Ordered quantity must always be greater than zero.
    IF NEW.quantity IS NULL OR NEW.quantity <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Order quantity must be greater than zero.';
    END IF;

    -- Read stock from the products table before creating the line item.
    SELECT stock
      INTO available_stock
      FROM products
     WHERE product_id = NEW.product_id;

    -- Reject the insert if the product cannot be found.
    IF available_stock IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Ordered product was not found.';
    END IF;

    -- Reject the insert if there is not enough stock for this order line.
    IF NEW.quantity > available_stock THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Insufficient stock for this product.';
    END IF;
END$$

CREATE TRIGGER check_stock_before_order_update
BEFORE UPDATE ON order_items
FOR EACH ROW
BEGIN
    DECLARE available_stock INT DEFAULT NULL;
    DECLARE extra_needed INT DEFAULT 0;

    -- Updated order quantity must stay valid.
    IF NEW.quantity IS NULL OR NEW.quantity <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Order quantity must be greater than zero.';
    END IF;

    IF NEW.product_id = OLD.product_id THEN
        -- Same product: only check the additional quantity being requested.
        SET extra_needed = NEW.quantity - OLD.quantity;

        IF extra_needed > 0 THEN
            SELECT stock
              INTO available_stock
              FROM products
             WHERE product_id = NEW.product_id;

            -- Reject if the product does not exist anymore.
            IF available_stock IS NULL THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Ordered product was not found.';
            END IF;

            -- Reject if the added quantity is larger than the remaining stock.
            IF extra_needed > available_stock THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Insufficient stock for this product.';
            END IF;
        END IF;
    ELSE
        -- Different product: validate the full new quantity against the new product's stock.
        SELECT stock
          INTO available_stock
          FROM products
         WHERE product_id = NEW.product_id;

        IF available_stock IS NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Ordered product was not found.';
        END IF;

        IF NEW.quantity > available_stock THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Insufficient stock for this product.';
        END IF;
    END IF;
END$$

CREATE TRIGGER deduct_stock_after_order
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    DECLARE previous_suppress INT DEFAULT 0;

    -- Temporarily disable logging so this internal stock update does not create duplicate activity logs.
    SET previous_suppress = COALESCE(@suppress_activity_log, 0);
    SET @suppress_activity_log = 1;

    -- Reduce available stock after an order line is inserted.
    UPDATE products
       SET stock = stock - NEW.quantity
     WHERE product_id = NEW.product_id;

    -- Restore the previous logging state for the connection.
    SET @suppress_activity_log = previous_suppress;
END$$

CREATE TRIGGER adjust_stock_after_order_update
AFTER UPDATE ON order_items
FOR EACH ROW
BEGIN
    DECLARE previous_suppress INT DEFAULT 0;
    DECLARE quantity_delta INT DEFAULT 0;

    -- Temporarily disable logging to avoid duplicate logs during stock correction.
    SET previous_suppress = COALESCE(@suppress_activity_log, 0);
    SET @suppress_activity_log = 1;

    IF NEW.product_id = OLD.product_id THEN
        -- Same product: only apply the difference between old and new quantity.
        SET quantity_delta = NEW.quantity - OLD.quantity;

        IF quantity_delta <> 0 THEN
            UPDATE products
               SET stock = stock - quantity_delta
             WHERE product_id = NEW.product_id;
        END IF;
    ELSE
        -- Product changed: return stock to the old product...
        UPDATE products
           SET stock = stock + OLD.quantity
         WHERE product_id = OLD.product_id;

        -- ...then deduct stock from the newly selected product.
        UPDATE products
           SET stock = stock - NEW.quantity
         WHERE product_id = NEW.product_id;
    END IF;

    -- Restore the connection logging flag.
    SET @suppress_activity_log = previous_suppress;
END$$

CREATE TRIGGER restore_stock_on_order_delete
AFTER DELETE ON order_items
FOR EACH ROW
BEGIN
    DECLARE previous_suppress INT DEFAULT 0;

    -- Temporarily disable logging so stock restoration stays internal.
    SET previous_suppress = COALESCE(@suppress_activity_log, 0);
    SET @suppress_activity_log = 1;

    -- Return stock when an order line is removed.
    UPDATE products
       SET stock = stock + OLD.quantity
     WHERE product_id = OLD.product_id;

    -- Restore the previous logging state.
    SET @suppress_activity_log = previous_suppress;
END$$

DELIMITER ;
