-- Trigger-based activity logging for GearHub.
-- PHP should set these MySQL session variables on the same connection before writes:
--   SET @actor_user_id = 123;
--   SET @activity_action = 'updated_product';
--   SET @suppress_activity_log = 0;

DROP TRIGGER IF EXISTS log_order_created;
DROP TRIGGER IF EXISTS log_order_status_change;
DROP TRIGGER IF EXISTS log_product_added;
DROP TRIGGER IF EXISTS log_product_updated;
DROP TRIGGER IF EXISTS log_product_deleted;
DROP TRIGGER IF EXISTS log_user_created;
DROP TRIGGER IF EXISTS log_user_updated;
DROP TRIGGER IF EXISTS log_cart_item_created;
DROP TRIGGER IF EXISTS log_cart_item_updated;
DROP TRIGGER IF EXISTS log_cart_item_deleted;

DELIMITER $$

CREATE TRIGGER log_order_created
AFTER INSERT ON orders
FOR EACH ROW
BEGIN
    -- Record a log entry when a new order is created.
    IF COALESCE(@suppress_activity_log, 0) = 0 THEN
        INSERT INTO activity_logs (user_id, action, table_name, record_id)
        VALUES (
            COALESCE(@actor_user_id, NEW.user_id),
            LEFT(COALESCE(NULLIF(@activity_action, ''), 'placed_order'), 100),
            'orders',
            NEW.order_id
        );
    END IF;
END$$

CREATE TRIGGER log_order_status_change
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    -- Only log when the order status actually changes.
    IF COALESCE(@suppress_activity_log, 0) = 0
       AND COALESCE(OLD.status, '') <> COALESCE(NEW.status, '') THEN
        INSERT INTO activity_logs (user_id, action, table_name, record_id)
        VALUES (
            COALESCE(@actor_user_id, NEW.user_id, OLD.user_id),
            LEFT(COALESCE(NULLIF(@activity_action, ''), CONCAT('updated_order_status_to_', LOWER(NEW.status))), 100),
            'orders',
            NEW.order_id
        );
    END IF;
END$$

CREATE TRIGGER log_product_added
AFTER INSERT ON products
FOR EACH ROW
BEGIN
    -- Record which admin or user created a new product row.
    IF COALESCE(@suppress_activity_log, 0) = 0 THEN
        INSERT INTO activity_logs (user_id, action, table_name, record_id)
        VALUES (
            @actor_user_id,
            LEFT(COALESCE(NULLIF(@activity_action, ''), 'created_product'), 100),
            'products',
            NEW.product_id
        );
    END IF;
END$$

CREATE TRIGGER log_product_updated
AFTER UPDATE ON products
FOR EACH ROW
BEGIN
    -- Log product changes only when meaningful fields were modified.
    IF COALESCE(@suppress_activity_log, 0) = 0
       AND (
            COALESCE(OLD.name, '') <> COALESCE(NEW.name, '')
            OR COALESCE(OLD.description, '') <> COALESCE(NEW.description, '')
            OR COALESCE(OLD.category, '') <> COALESCE(NEW.category, '')
            OR COALESCE(OLD.stock, 0) <> COALESCE(NEW.stock, 0)
            OR COALESCE(OLD.price, 0) <> COALESCE(NEW.price, 0)
       ) THEN
        INSERT INTO activity_logs (user_id, action, table_name, record_id)
        VALUES (
            @actor_user_id,
            LEFT(COALESCE(NULLIF(@activity_action, ''), 'updated_product'), 100),
            'products',
            NEW.product_id
        );
    END IF;
END$$

CREATE TRIGGER log_product_deleted
AFTER DELETE ON products
FOR EACH ROW
BEGIN
    -- Record product deletion in the activity log.
    IF COALESCE(@suppress_activity_log, 0) = 0 THEN
        INSERT INTO activity_logs (user_id, action, table_name, record_id)
        VALUES (
            @actor_user_id,
            LEFT(COALESCE(NULLIF(@activity_action, ''), 'deleted_product'), 100),
            'products',
            OLD.product_id
        );
    END IF;
END$$

CREATE TRIGGER log_user_created
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    -- Record a new account registration.
    IF COALESCE(@suppress_activity_log, 0) = 0 THEN
        INSERT INTO activity_logs (user_id, action, table_name, record_id)
        VALUES (
            COALESCE(@actor_user_id, NEW.user_id),
            LEFT(COALESCE(NULLIF(@activity_action, ''), 'registered_account'), 100),
            'users',
            NEW.user_id
        );
    END IF;
END$$

CREATE TRIGGER log_user_updated
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    DECLARE resolved_action VARCHAR(100);

    -- Only log if profile details or password actually changed.
    IF COALESCE(@suppress_activity_log, 0) = 0
       AND (
            COALESCE(OLD.username, '') <> COALESCE(NEW.username, '')
            OR COALESCE(OLD.email, '') <> COALESCE(NEW.email, '')
            OR COALESCE(OLD.address, '') <> COALESCE(NEW.address, '')
            OR COALESCE(OLD.password, '') <> COALESCE(NEW.password, '')
       ) THEN
        -- If PHP did not provide a custom action, infer whether this was a password change or profile edit.
        SET resolved_action = NULLIF(@activity_action, '');

        IF resolved_action IS NULL THEN
            IF COALESCE(OLD.password, '') <> COALESCE(NEW.password, '') THEN
                SET resolved_action = 'changed_password';
            ELSE
                SET resolved_action = 'updated_profile';
            END IF;
        END IF;

        INSERT INTO activity_logs (user_id, action, table_name, record_id)
        VALUES (
            COALESCE(@actor_user_id, NEW.user_id, OLD.user_id),
            LEFT(resolved_action, 100),
            'users',
            NEW.user_id
        );
    END IF;
END$$

CREATE TRIGGER log_cart_item_created
AFTER INSERT ON cart_items
FOR EACH ROW
BEGIN
    -- Record when a product is added to a shopping cart.
    IF COALESCE(@suppress_activity_log, 0) = 0 THEN
        INSERT INTO activity_logs (user_id, action, table_name, record_id)
        VALUES (
            COALESCE(@actor_user_id, NEW.user_id),
            LEFT(COALESCE(NULLIF(@activity_action, ''), 'added_to_cart'), 100),
            'cart_items',
            NEW.cart_item_id
        );
    END IF;
END$$

CREATE TRIGGER log_cart_item_updated
AFTER UPDATE ON cart_items
FOR EACH ROW
BEGIN
    -- Log cart updates only when the quantity changed.
    IF COALESCE(@suppress_activity_log, 0) = 0
       AND COALESCE(OLD.quantity, 0) <> COALESCE(NEW.quantity, 0) THEN
        INSERT INTO activity_logs (user_id, action, table_name, record_id)
        VALUES (
            COALESCE(@actor_user_id, NEW.user_id, OLD.user_id),
            LEFT(
                COALESCE(
                    NULLIF(@activity_action, ''),
                    IF(NEW.quantity > OLD.quantity, 'increased_cart_quantity', 'updated_cart_quantity')
                ),
                100
            ),
            'cart_items',
            NEW.cart_item_id
        );
    END IF;
END$$

CREATE TRIGGER log_cart_item_deleted
AFTER DELETE ON cart_items
FOR EACH ROW
BEGIN
    -- Record when an item is removed from the shopping cart.
    IF COALESCE(@suppress_activity_log, 0) = 0 THEN
        INSERT INTO activity_logs (user_id, action, table_name, record_id)
        VALUES (
            COALESCE(@actor_user_id, OLD.user_id),
            LEFT(COALESCE(NULLIF(@activity_action, ''), 'removed_cart_item'), 100),
            'cart_items',
            OLD.cart_item_id
        );
    END IF;
END$$

DELIMITER ;
