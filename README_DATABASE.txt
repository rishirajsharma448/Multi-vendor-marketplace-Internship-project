================================================================================
                    VYAPAR SETU - DATABASE SPECIFICATION & BACKUP GUIDE
================================================================================

1. DATABASE IDENTIFICATION & ENGINES:
--------------------------------------
- Primary Database Engine : MySQL 5.7+ / MariaDB 10.4+ (Default port 3306)
- Primary Database Name   : vyapar_setu
- Character Set / Collate : utf8mb4 / utf8mb4_unicode_ci & utf8mb4_general_ci
- Default Connection User : root
- Default Connection Pass : (empty string)
- Primary Host            : 127.0.0.1 (or localhost)

- Portable Fallback Engine: SQLite 3
- Fallback Database File  : config/vyapar_setu.sqlite (340 KB)
  * Feature: Automatically engages if MySQL is not installed or not running on
    the destination computer. Both databases are 100% synchronized with the exact
    same 17 tables and 521 records!


================================================================================
2. DATABASE BACKUP FILE DETAILS:
================================================================================
- Backup File Path : database/database_backup.sql
- Format           : Standard UTF-8 SQL Dump (Generated via MariaDB mysqldump)
- File Attributes  : Contains complete DDL table structures, auto-increment values,
                     primary/foreign key constraints, and INSERT statements for
                     every live database record.


================================================================================
3. RESTORATION & IMPORT INSTRUCTIONS:
================================================================================

METHOD A: VIA PHPMYADMIN (GUI):
-------------------------------
1. Open your web browser and go to: http://localhost/phpmyadmin/
2. Click on "New" in the left sidebar to create a database.
3. Database Name: vyapar_setu
   Collation    : utf8mb4_general_ci
   Click "Create".
4. Select the newly created `vyapar_setu` database.
5. Click the "Import" tab at the top.
6. Click "Choose File" and select:
   database/database_backup.sql
7. Click "Import" at the bottom of the page.
8. A green success message will confirm that all 17 tables and records have
   been imported successfully.

METHOD B: VIA MYSQL COMMAND LINE (CLI):
---------------------------------------
If you have MySQL in your PATH or in C:\xampp\mysql\bin:
1. Open Command Prompt (cmd) and navigate to the project directory:
   cd /d "C:\xampp\htdocs\Multi_Vendor_Marketplace_final"
2. Run:
   mysql -u root -e "CREATE DATABASE IF NOT EXISTS vyapar_setu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root vyapar_setu < database\database_backup.sql
3. All tables and records will be instantly restored.

METHOD C: AUTOMATIC ZERO-CONFIG SQLITE MODE:
--------------------------------------------
If no MySQL server is present on the destination machine, you DO NOT need to
import anything! The application automatically detects that MySQL is absent and
runs directly from `config/vyapar_setu.sqlite`.


================================================================================
4. VERIFIED TABLE INVENTORY & RECORD COUNTS (17 TABLES, 521 RECORDS):
================================================================================

+----+--------------------+-------+-------------------------------------------------------+
| #  | TABLE NAME         | ROWS  | DESCRIPTION & PURPOSE                                 |
+----+--------------------+-------+-------------------------------------------------------+
| 1  | users              | 126   | Core credentials (email, password_hash, role, status)  |
| 2  | vendors            | 117   | Business storefronts (store_name, gstin, commission)  |
| 3  | customers          | 7     | Customer delivery profiles, customer codes, addresses |
| 4  | products           | 45    | Marketplace products (title, price, stock, ratings)   |
| 5  | product_images     | 45    | Product primary image URLs and gallery associations   |
| 6  | categories         | 27    | Catalog categories (Electronics, Fashion, etc.)       |
| 7  | brands             | 8     | Brand directory (Apple, Samsung, FashionXFit, etc.)   |
| 8  | reviews            | 96    | Verified customer product ratings & review comments   |
| 9  | orders             | 9     | Order master records with totals & delivery statuses  |
| 10 | order_items        | 8     | Line items linked to orders and products              |
| 11 | cart               | 4     | User shopping cart sessions                           |
| 12 | cart_items         | 0     | Items inside active shopping carts                    |
| 13 | wishlist           | 0     | Saved wishlist items for customers                    |
| 14 | payments           | 0     | Transaction payment records and gateways              |
| 15 | activity_logs      | 29    | Audit log of login, logout, and system activities     |
| 16 | notifications      | 0     | User and vendor system alerts                         |
| 17 | support_tickets    | 0     | Customer support tickets and resolution logs          |
+----+--------------------+-------+-------------------------------------------------------+
|    | TOTAL RECORDS      | 521   | 100% PRESERVED ACROSS BOTH MYSQL & SQLITE             |
+----+--------------------+-------+-------------------------------------------------------+


================================================================================
5. DATABASE ARCHITECTURE: SEPARATION OF CONCERNS
================================================================================

The database strictly separates user security credentials from personal customer
shipping details and vendor business details:

1. `users` Table:
   - Primary Key: `id`
   - Attributes : `name`, `email`, `phone`, `password_hash`, `role` ('customer','vendor','admin'),
                  `status`, `email_verified`, `created_at`, `updated_at`.

2. `customers` Table:
   - Primary Key: `id`
   - Foreign Key: `user_id` -> references `users.id` (1-to-1 relationship)
   - Attributes : `customer_code` (e.g. CUST-38624), `full_name`, `email`, `phone`,
                  `shipping_address`, `city`, `state`, `pincode`, `created_at`.

3. `vendors` Table:
   - Primary Key: `id`
   - Foreign Key: `user_id` -> references `users.id` (1-to-1 relationship)
   - Attributes : `vendor_code` (e.g. VND-00001), `store_name`, `owner_name`, `email`,
                  `phone`, `gstin`, `category`, `store_logo`, `banner`, `rating`,
                  `reviews_count`, `status`, `commission_rate`, `address`.

4. `products` & `product_images` Tables:
   - Foreign Key: `product_images.product_id` -> references `products.id`
   - Rating Auto-Calculation: Handled automatically via review submission triggers
     in product.php and includes/db.php.

All constraints and foreign keys are explicitly defined in database/database_backup.sql
and enforced at runtime.
================================================================================
