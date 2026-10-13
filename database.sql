-- SkillHub Marketplace Database
-- Import this file via phpMyAdmin (create a database named "skillhub" first, then Import this file)

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('client','freelancer','admin') NOT NULL DEFAULT 'client',
    profile_pic VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active','blocked') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS gigs (
    gig_id INT AUTO_INCREMENT PRIMARY KEY,
    freelancer_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    FOREIGN KEY (freelancer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS gig_packages (
    package_id INT AUTO_INCREMENT PRIMARY KEY,
    gig_id INT NOT NULL,
    package_type ENUM('basic','standard','premium') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    delivery_days INT NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (gig_id) REFERENCES gigs(gig_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    gig_id INT NOT NULL,
    package_id INT NOT NULL,
    client_id INT NOT NULL,
    freelancer_id INT NOT NULL,
    status ENUM('Pending','In Progress','Delivered','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    deadline DATE DEFAULT NULL,
    FOREIGN KEY (gig_id) REFERENCES gigs(gig_id),
    FOREIGN KEY (package_id) REFERENCES gig_packages(package_id),
    FOREIGN KEY (client_id) REFERENCES users(user_id),
    FOREIGN KEY (freelancer_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('Pending','Paid') NOT NULL DEFAULT 'Pending',
    payment_date DATETIME DEFAULT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    sender_id INT NOT NULL,
    message_text TEXT NOT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    client_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS deliveries (
    delivery_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================== SEED DATA =====================

-- Categories
INSERT INTO categories (category_name, icon) VALUES
('Web Development', 'code'),
('Graphic Design', 'design'),
('Content Writing', 'write'),
('Digital Marketing', 'marketing');

-- Demo users (passwords are set by running setup.php once after import - see README)
INSERT INTO users (name, email, password, role, bio, status) VALUES
('Admin', 'admin@skillhub.com', 'PLACEHOLDER', 'admin', 'Platform administrator', 'active'),
('Rahul Sharma', 'rahul@skillhub.com', 'PLACEHOLDER', 'freelancer', 'Full-stack web developer with 3 years of freelance experience in PHP and JavaScript.', 'active'),
('Priya Patel', 'priya@skillhub.com', 'PLACEHOLDER', 'freelancer', 'Graphic designer specialising in logos, posters and social media creatives.', 'active'),
('Aman Verma', 'aman@skillhub.com', 'PLACEHOLDER', 'client', 'Small business owner looking for freelance help.', 'active');

-- Demo gigs
INSERT INTO gigs (freelancer_id, category_id, title, description, status) VALUES
(2, 1, 'I will build a responsive website using HTML CSS and JS', 'I will design and build a clean, mobile-friendly website for your business or portfolio using HTML, CSS and JavaScript. Includes basic SEO-friendly structure and cross-browser testing.', 'active'),
(2, 1, 'I will fix bugs in your PHP or MySQL project', 'Got errors in your PHP/MySQL college project or small application? I will debug and fix logic errors, SQL issues, and broken forms quickly.', 'active'),
(3, 2, 'I will design a modern logo for your brand', 'I will create 3 unique logo concepts for your business along with source files, based on your brand style and preferences.', 'active'),
(3, 2, 'I will design social media posts and banners', 'A set of 5 clean, professional social media posts/banners designed to match your brand colours and message.', 'active');

-- Packages for gig 1
INSERT INTO gig_packages (gig_id, package_type, price, delivery_days, description) VALUES
(1, 'basic', 999.00, 5, 'Single page responsive website'),
(1, 'standard', 2499.00, 8, 'Up to 5 pages, responsive, contact form'),
(1, 'premium', 4999.00, 12, 'Up to 10 pages, responsive, contact form, basic animations'),
-- Packages for gig 2
(2, 'basic', 499.00, 2, 'Fix up to 3 bugs'),
(2, 'standard', 999.00, 3, 'Fix up to 8 bugs + code review'),
(2, 'premium', 1799.00, 5, 'Unlimited bug fixes for one project + documentation'),
-- Packages for gig 3
(3, 'basic', 799.00, 3, '1 logo concept, 2 revisions'),
(3, 'standard', 1499.00, 5, '3 logo concepts, 5 revisions, source files'),
(3, 'premium', 2499.00, 7, '5 logo concepts, unlimited revisions, full brand kit'),
-- Packages for gig 4
(4, 'basic', 599.00, 3, '5 static posts'),
(4, 'standard', 1099.00, 5, '10 static posts + 2 banners'),
(4, 'premium', 1899.00, 7, '15 posts + 3 banners + 2 animated posts');
