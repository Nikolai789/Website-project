-- Create a dedicated MySQL account for the GearHub application.
create user 'Niko'@'localhost'
identified by 'Niko123';

-- Allow the app account to read and modify application data inside gearhubDB.
grant 
select, insert, update, delete 
on gearhubDB.*
to 'Niko'@'localhost';

-- Allow the app account to execute stored procedures and functions in this database.
GRANT EXECUTE ON gearhubDB.* TO 'Niko'@'localhost';

-- These procedure-specific grants are extra explicit access for the three procedures used by the app.
GRANT EXECUTE ON PROCEDURE gearhubDB.sp_get_admin_orders TO 'Niko'@'localhost';
GRANT EXECUTE ON PROCEDURE gearhubDB.sp_get_activity_logs TO 'Niko'@'localhost';
GRANT EXECUTE ON PROCEDURE gearhubDB.sp_get_user_orders TO 'Niko'@'localhost';

-- Reload grant tables so the permission changes take effect immediately.
FLUSH PRIVILEGES;
