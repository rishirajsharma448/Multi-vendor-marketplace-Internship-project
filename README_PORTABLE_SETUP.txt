================================================================================
                    VYAPAR SETU - PORTABLE USB SETUP GUIDE
================================================================================

PROJECT OVERVIEW:
-----------------
Vyapar Setu is a comprehensive, production-grade multi-vendor marketplace platform
featuring buyer storefronts, dedicated vendor dashboards, admin governance,
live product comparisons, verified customer reviews, dynamic rating calculations,
order management, and an integrated AI shopping assistant (Setu AI).

This package has been configured to be 100% PORTABLE. You can copy this entire
folder onto a USB pendrive and run it on another Windows computer without losing
any accounts, products, images, orders, or database records.


================================================================================
1. HOW TO TRANSFER THE PROJECT FOLDER TO A USB PENDRIVE
================================================================================
1. Plug your USB pendrive into the original computer.
2. Copy the entire project folder:
   "Multi_Vendor_Marketplace_final"
   directly to your USB pendrive.
3. Once the copy finishes, safely eject the USB pendrive.
4. Plug the USB pendrive into the destination Windows computer.
5. You can run the website directly from the pendrive or copy the folder
   onto any drive on the destination computer (e.g., C:\, D:\, or Desktop).


================================================================================
2. HOW TO RUN THE WEBSITE ON ANOTHER WINDOWS COMPUTER
================================================================================

METHOD 1: ONE-CLICK QUICK START (AUTOMATIC)
--------------------------------------------
1. Open the transferred project folder.
2. Double-click the file:
   START_VYAPAR_SETU.bat
3. The launcher script will automatically:
   - Detect the PHP runtime on the machine (System PATH, portable folder, or XAMPP).
   - Check the database service (MySQL if running, or auto-fallback to SQLite).
   - Start the local web server on port 8000.
   - Automatically launch your default web browser to:
     http://localhost:8000/
4. When finished, double-click:
   STOP_VYAPAR_SETU.bat
   to cleanly stop the background server process.


METHOD 2: RUNNING WITH XAMPP (TRADITIONAL)
------------------------------------------
If the destination computer has XAMPP installed:
1. Copy the project folder into your XAMPP web root directory:
   C:\xampp\htdocs\Multi_Vendor_Marketplace_final
2. Open XAMPP Control Panel and start "Apache" and "MySQL".
3. Open your web browser and navigate to:
   http://localhost/Multi_Vendor_Marketplace_final/
4. (Optional MySQL Restoration):
   If you wish to import the full MySQL database into phpMyAdmin:
   - Open http://localhost/phpmyadmin/
   - Create a new database named: vyapar_setu
   - Click "Import" -> choose "database/database_backup.sql" -> click "Import".
   - Note: If MySQL is not imported, the system automatically falls back to
     the built-in SQLite database (config/vyapar_setu.sqlite) which already
     contains all 521 records!


METHOD 3: ZERO-INSTALL PORTABLE PHP (100% SELF-CONTAINED USB)
--------------------------------------------------------------
If the destination computer does NOT have PHP or XAMPP installed:
1. Download portable PHP 8.x (Zip) from:
   https://windows.php.net/download/
   (Select VS16 x64 Non Thread Safe or Thread Safe zip)
2. Extract the downloaded zip into a subfolder named "php" inside this project:
   Multi_Vendor_Marketplace_final\php\
3. Inside that php folder, ensure "php.ini-development" is copied to "php.ini",
   and uncomment the following extensions in php.ini:
   extension=pdo_sqlite
   extension=pdo_mysql
   extension=curl
   extension=openssl
   extension=mbstring
4. Double-click START_VYAPAR_SETU.bat.
   It will instantly use the portable PHP runtime and launch the marketplace!


================================================================================
3. SYSTEM REQUIREMENTS & DEPENDENCIES
================================================================================
- Operating System: Windows 10 or Windows 11 (64-bit recommended).
- Web Server      : Apache (via XAMPP) OR PHP Built-in Server (via START_VYAPAR_SETU.bat).
- PHP Version     : PHP 8.0, 8.1, 8.2, or 8.3.
- PHP Extensions  : PDO, pdo_mysql, pdo_sqlite, curl, openssl, mbstring.
- Database Engine : MySQL 5.7+ / MariaDB 10.4+ (Primary)
                    SQLite 3 (Self-Contained Fallback).
- Web Browser     : Google Chrome, Microsoft Edge, Mozilla Firefox, or Brave.


================================================================================
4. DEFAULT LOGIN CREDENTIALS
================================================================================

1. PLATFORM SUPER ADMIN:
   - Login URL : http://localhost:8000/admin_login.php
   - Email     : admin@vyaparsetu.com
   - Password  : admin123
   - Capabilities: Vendor store approval/suspension, commission ledger,
                   order moderation, customer spending overview.

2. REGISTERED CUSTOMER:
   - Login URL : http://localhost:8000/login.php
   - Email     : rishirajsharma448@gmail.com
   - Password  : password123
   - Alternative Customer Accounts:
     * Email: newuser99@gmail.com | Password: password123
     * Email: 9911223344@vyaparsetu.com | Password: password123
   - Capabilities: Browse catalog, filter categories, submit orders, view
                   tracking, manage shipping address, write reviews.

3. REGISTERED VENDOR:
   - Login URL : http://localhost:8000/vendor_login.php
   - Email     : techcraftelectronics@vyaparsetu.com
   - Password  : vendor123
   - Alternative Vendor Accounts:
     * Email: novadigitaltech@vyaparsetu.com | Password: vendor123
     * Email: priya@bharattextiles.in | Password: password123
   - Capabilities: Vendor dashboard, add/edit products, inventory, orders.


================================================================================
5. TROUBLESHOOTING & COMMON ISSUES
================================================================================

Q1: "PHP runtime was not detected on this computer!"
A1: Ensure XAMPP is installed, or add PHP to your Windows Environment Variables
    (System PATH), or extract portable PHP into a "php" folder inside the project.

Q2: Port 8000 is already in use by another application.
A2: Open START_VYAPAR_SETU.bat in Notepad, change "-S localhost:8000" to
    "-S localhost:8080" (or 8888), and update the URL accordingly.

Q3: Database connection fails or says "Access denied for user 'root'@'localhost'".
A3: If running with MySQL, verify that MySQL username is 'root' with blank password.
    If your MySQL setup has a custom password, set it in "includes/config.php"
    (line 23) or set environment variable DB_PASS.
    If MySQL is stopped, Vyapar Setu will automatically switch to the SQLite
    database (config/vyapar_setu.sqlite) without interruption.

Q4: Do product photos require internet access to display?
A4: No. All 45 product photographs and marketplace assets are stored locally
    inside "uploads/product_photos/" and "assets/images/". They load offline.

Q5: Does the AI Shopping Assistant require internet access?
A5: The AI assistant (Setu AI) attempts to connect to Gemini 3.5 Flash-Lite
    via cloud API. If the destination computer is offline or the API is unreachable,
    the built-in local deterministic fallback engine immediately takes over and
    answers comparisons, reviews, and photo requests locally from the database!

================================================================================
Support & Documentation:
Refer to README_DATABASE.txt and PORTABILITY_CHECKLIST.txt for complete schema
specifications and asset verification reports.
================================================================================
