StepUp Footwear E-Commerce Platform
StepUp is a modern, feature-rich e-commerce web application designed for a premium sneaker retail experience. Built with a robust PHP and MySQL backend, it provides a seamless user journey from product discovery to secure order placement.

🚀 Key Benefits (Advantages)
Seamless UX: Multi-step checkout wizard provides a professional and intuitive shopping flow.

Instant Search & Filter: Real-time hybrid search (Name + Brand) and price filtering ensure users find products quickly without server lag.

Interactive Shopping: Includes advanced features like wishlist management, product reviews, and live order tracking.

Comprehensive Admin Control: An integrated dashboard allows for inventory management, sales tracking, and order status updates.

Professional Reporting: Built-in PDF invoice generation for customer convenience.

AI-Ready Assistance: Features a floating AI-powered chatbot to handle customer inquiries 24/7.

🛠️ Limitations (Disadvantages)
Server Dependency: Being a PHP/MySQL application, it requires a local server environment like XAMPP to function.

Security Constraints: As a custom-built solution, it lacks the out-of-the-box advanced security protocols found in large-scale enterprise platforms like Shopify or Magento.

Scalability: Requires manual optimization for handling extremely high volumes of concurrent traffic compared to cloud-native architectures.

📋 How to Use (Setup Guide)
Prerequisites: Install XAMPP or any local development server supporting PHP and MySQL.

Setup Database:

Open phpMyAdmin and create a new database.

Import the provided SQL file to create the necessary tables (products, orders, wishlist, etc.).

Configure Connection:

Open db.php in your project folder.

Update the database credentials (hostname, username, password, and database name) to match your local setup.

Deploy: Move the project folder into your htdocs directory.

Launch: Open your browser and navigate to http://localhost/your-project-folder/.

Admin Access: Use the admin login to manage products and track order status updates.

💻 Tech Stack
Frontend: HTML5, CSS3, JavaScript (AJAX)

Backend: PHP

Database: MySQL
