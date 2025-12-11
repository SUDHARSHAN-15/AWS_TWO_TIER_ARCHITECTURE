# AWS_TWO_TIER_ARCHITECTURE
# AWS Two-Tier Architecture: Dynamic Restaurant Ordering Web App

A fully functional, responsive restaurant ordering website built using **AWS two-tier architecture**:
- **Web Tier**: EC2 instance running Apache, PHP, and a session-based cart.
- **Database Tier**: RDS MySQL for storing menu items and orders.

**Live Demo** (as of December 2025): http://44.251.29.156/index.php  
*(Note: Public IP changes on EC2 stop/start. Allocate an Elastic IP for a fixed address.)*

## Project Overview

This is a complete end-to-end restaurant web application with the following features:
- Beautiful menu display with high-quality food images
- Dynamic shopping cart using PHP sessions and AJAX (no page reloads)
- Live total price calculation
- Grouped order system (one main order with multiple items and unique order number)
- Professional order history page with expandable details
- Fully responsive design using Bootstrap 5
- Secure SSL connection to RDS MySQL

User Browser
↓ (HTTP/HTTPS)
EC2 Instance (Public Subnet)

Apache Web Server
PHP 8.4
Application Code (index.php, cart.php, action.php, orders.php, config.php)
↓ (MySQL over SSL)
RDS MySQL Instance (Security Group allows only EC2)
menu table
orders table (line items)
order_groups table (main order header)

text## Detailed Setup Instructions

This section provides a step-by-step guide to replicate the project from scratch, starting from VPC configuration to accessing the website. Time estimate: 1-2 hours (assuming AWS account ready).

### 1. Configure VPC and Subnets
- Log in to AWS Console → VPC Dashboard.
- Create a new VPC:
  - Name: my-vpc
  - IPv4 CIDR block: 10.0.0.0/16
  - IPv6: No IPv6 CIDR block
- Create a public subnet:
  - Name: public-subnet
  - VPC: my-vpc
  - Availability Zone: us-west-2a (or your region)
  - IPv4 CIDR: 10.0.0.0/24
- Create an Internet Gateway:
  - Name: my-igw
  - Attach to my-vpc
- Update Route Table for public-subnet:
  - Add route: 0.0.0.0/0 → my-igw

### 2. Configure Security Groups
- Create Web Security Group (for EC2):
  - Name: web-sg
  - VPC: my-vpc
  - Inbound rules:
    - HTTP (80) from 0.0.0.0/0 (anywhere)
    - SSH (22) from your IP (find with "what's my IP")
  - Outbound: All traffic
- Create DB Security Group (for RDS):
  - Name: db-sg
  - VPC: my-vpc
  - Inbound rules:
    - MySQL (3306) from web-sg (search for group name)
  - Outbound: All traffic

### 3. Launch RDS MySQL Instance (Database Tier)
- RDS Dashboard → Create database
- Engine: MySQL
- Template: Free tier
- DB instance identifier: mydb
- Master username: admin
- Master password: Choose a strong one (e.g., Nav12345 — change later!)
- DB instance class: db.t3.micro
- Storage: Default
- VPC: my-vpc
- Subnet group: Create new with public-subnet
- Public access: No (secure)
- Security group: db-sg
- Database name: testdb
- Create — wait 5-10 minutes for "Available"
- Note Endpoint: database-1.cdmeihzpu5cy.us-west-2.rds.amazonaws.com

### 4. Launch EC2 Instance (Web Tier)
- EC2 Dashboard → Launch instance
- Name: mywebserver
- AMI: Amazon Linux 2023
- Instance type: t2.micro
- Key pair: Create new (download .pem)
- Network: my-vpc, public-subnet, auto-assign public IP: Enable
- Security group: web-sg
- Launch — wait 1-2 minutes for "Running"
- Note Public IP: e.g., 44.251.29.156

### 5. Connect to EC2 and Install Software
- SSH from your local machine:
  ```bash
  ssh -i "your-key.pem" ec2-user@44.251.29.156

Update and install:Bashsudo dnf update -y
sudo dnf install httpd php php-mysqlnd mariadb105 -y
sudo systemctl start httpd
sudo systemctl enable httpd

6. Download RDS CA Bundle for SSL Connection

On EC2:Bashcd /var/www/html
sudo wget https://truststore.pki.rds.amazonaws.com/global/global-bundle.pem
sudo chown apache:apache global-bundle.pem
sudo chmod 644 global-bundle.pem

7. Setup Database Tables

On EC2, connect to RDS:Bashmysql -h database-1.cdmeihzpu5cy.us-west-2.rds.amazonaws.com -u admin -pEnter password.
Create tables:SQLUSE testdb;

CREATE TABLE menu (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  description TEXT,
  image_url VARCHAR(255)
);

INSERT INTO menu (name, price, description, image_url) VALUES
('Margherita Pizza', 12.99, 'Classic pizza with tomato, mozzarella, and basil', 'https://cookingitalians.com/wp-content/uploads/2024/11/Margherita-Pizza.jpg'),
('Cheese Burger', 9.99, 'Juicy beef patty with cheese, lettuce, and tomato', 'https://andershusa.com/wp-content/uploads/2022/09/feature-guide-the-best-burgers-in-los-angeles-our-ten-favorite-cheeseburgers-la-food-foodie-eat-eating-out-dining-tips-recommendations-guide-1.jpg'),
('Caesar Salad', 8.99, 'Fresh romaine lettuce, parmesan, croutons, Caesar dressing', 'https://media.istockphoto.com/id/534139231/photo/healthy-grilled-chicken-caesar-salad.jpg?s=612x612&w=0&k=20&c=TR_sE5S5ChmjFywg3dh_J5V_ha-BcwgTU26BvsgbsjY='),
('Pasta Carbonara', 11.99, 'Creamy pasta with bacon, egg, and parmesan', 'https://static01.nyt.com/images/2021/02/14/dining/carbonara-horizontal/carbonara-horizontal-threeByTwoMediumAt2X-v2.jpg'),
('Chocolate Cake', 6.99, 'Rich chocolate cake with ganache', 'https://media.istockphoto.com/id/1326149453/photo/dark-chocolate-cake-slice.jpg?s=612x612&w=0&k=20&c=KaZDGCl6ROSRiQfXNUd_AinfvWlv8K5bvPBSqPUXJfA='),
('Lemonade', 3.99, 'Freshly squeezed lemonade', 'https://media.gettyimages.com/id/466723416/photo/fresh-water-with-lemon-and-mint.jpg?s=612x612&w=gi&k=20&c=7F-4vdETkgAAnW2l_TEecTKhQ3OCh7LUuSJdIoZp-Tk=');

CREATE TABLE order_groups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(50) UNIQUE NOT NULL,
  customer_name VARCHAR(255) NOT NULL,
  customer_email VARCHAR(255) NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_group_id INT NOT NULL,
  customer_name VARCHAR(255) NOT NULL,
  customer_email VARCHAR(255) NOT NULL,
  menu_id INT NOT NULL,
  quantity INT NOT NULL,
  total_price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_group_id) REFERENCES order_groups(id),
  FOREIGN KEY (menu_id) REFERENCES menu(id)
);

EXIT;

8. Deploy the Code

Copy the PHP files (config.php, index.php, cart.php, action.php, orders.php) to /var/www/html/.
Update config.php with your RDS endpoint and password.
Set permissions:Bashsudo chown -R apache:apache /var/www/html
sudo chmod -R 644 /var/www/html/*.php
sudo systemctl restart httpd

9. Access the Website

Open the EC2 public IP in your browser: http://44.251.29.156/index.php
Add items to cart, place order, view orders.
Test queries in command line (e.g., SELECT * FROM order_groups;).

Technologies Used

AWS Services:
EC2 (Amazon Linux 2023)
RDS (MySQL 8.0)
VPC, Security Groups, Internet Gateway
