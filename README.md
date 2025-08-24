# FGDCW BookBridge: Library Management System

BookBridge is a comprehensive, web-based Library Management System developed as a Final Year Project for FG Degree College (W), Kharian. Built using PHP, MySQL, HTML, CSS, and JavaScript, this system is designed to automate and streamline all major library operations, making them more efficient for both librarians and students.

The primary goal of this project is to provide a user-friendly digital platform for managing books, tracking issues and returns, and handling fines in a modern, automated way.

## Core Features

This system is divided into two main panels with role-specific features:

### Librarian Panel
- **Dashboard:** An overview of key library statistics.
- **Book Management:** Add, update, and manage the complete book catalog.
- **Issue & Return Management:** A streamlined process to issue books to students and mark them as returned.
- **Automated Fine Calculation:** The system automatically calculates fines when a book is returned after its due date.
- **User Management:** Manage student and faculty accounts.
- **Reporting:** View reports on issued books, fines, and payments.

### Student Panel
- **Dashboard:** A personalized dashboard for students.
- **Book Catalog:** Browse and search for available books in the library.
- **My Returns:** Track currently borrowed books and view their due dates.
- **Fine Management:** View any pending or paid fines.
- **Dual Payment System:**
    - **💳 Online Payment:** Pay fines instantly and securely using the integrated **PayFast** payment gateway.
    - **📄 Bank Challan:** Generate and download a PDF bank challan to pay the fine manually at the designated bank branch.

## Technologies Used

- **Backend:** PHP
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Payment Gateway:** PayFast (Sandbox for testing)
- **PDF Generation:** Dompdf library
- **Development Environment:** XAMPP Server

## How to Set Up and Run This Project

To set up and run this project on your local machine, follow these steps:

1.  **Clone the Repository:**
    ```bash
    git clone https://github.com/your-username/your-repository-name.git
    ```

2.  **Move to `htdocs`:**
    -   Move the cloned project folder into the `htdocs` directory of your XAMPP installation (e.g., `C:\xampp\htdocs\`).

3.  **Database Setup:**
    -   Open XAMPP Control Panel and start the **Apache** and **MySQL** services.
    -   Open your web browser and go to `http://localhost/phpmyadmin/`.
    -   Create a new database and name it `fgdcw_bookbridge`.
    -   The required tables will be created automatically when you first run the project, thanks to the code in `includes/config.php`.

4.  **Install Dependencies:**
    -   You need [Composer](https://getcomposer.org/) installed on your machine.
    -   Open a terminal inside the project's root directory and run the following command to install the `dompdf` library:
        ```bash
        composer install
        ```

5.  **Configure PayFast:**
    -   Open the `includes/config.php` file.
    -   Find the following lines and replace the placeholder values with your own PayFast Sandbox Merchant ID and Key.
        ```php
        define('PAYFAST_MERCHANT_ID', 'YOUR_MERCHANT_ID');
        define('PAYFAST_MERCHANT_KEY', 'YOUR_MERCHANT_KEY');
        ```

6.  **Run the Project:**
    -   You are all set! Open your browser and navigate to `http://localhost/your-project-folder-name/` to see the project live.
