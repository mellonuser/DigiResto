CREATE DATABASE IF NOT EXISTS DigitalRestaurant CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE DigitalRestaurant;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(30) NOT NULL DEFAULT 'Manajer'
);

CREATE TABLE customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(100) NULL,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tables (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(10) NOT NULL UNIQUE,
  capacity INT NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE menus (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  category VARCHAR(50) NOT NULL DEFAULT 'Makanan',
  price INT NOT NULL
);

CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NULL,
  customer_id INT NOT NULL,
  table_id INT NOT NULL,
  reservation_date DATE NOT NULL,
  reservation_time TIME NOT NULL,
  guests INT NOT NULL,
  status ENUM('pending','confirmed','seated','completed','cancelled') NOT NULL DEFAULT 'pending',
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
  FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE RESTRICT,
  INDEX (reservation_date, status)
);

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reservation_id INT NOT NULL,
  menu_id INT NOT NULL,
  qty INT NOT NULL DEFAULT 1,
  price INT NOT NULL,
  FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
  FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE RESTRICT
);

-- Data awal (akun admin dibuat otomatis saat pertama kali membuka login.php: admin / admin123)
INSERT INTO tables (code, capacity) VALUES
('M1',2),('M2',4),('M3',4),('M4',6),('M5',2),('M6',4),('M7',4),('M8',6),('M9',2),('M10',4),('M11',4),('M12',6);

INSERT INTO menus (name, category, price) VALUES
('Nasi Goreng Spesial','Makanan',35000),('Sate Ayam','Makanan',32000),('Ikan Bakar','Makanan',55000),
('Gado-gado','Makanan',28000),('Es Teh Manis','Minuman',8000),('Jus Alpukat','Minuman',18000),('Kopi Susu','Minuman',20000);

INSERT INTO customers (name, phone, email) VALUES
('Andi Pratama','081234567801','andi@example.com'),('Sarah Wijaya','081234567802','sarah@example.com'),
('Budi Santoso','081234567803',NULL),('Maya Lestari','081234567804',NULL),('Kevin Julian','081234567805',NULL);

INSERT INTO reservations (code, customer_id, table_id, reservation_date, reservation_time, guests, status, notes) VALUES
('RSV-2401',1,8,CURDATE(),'12:30:00',4,'confirmed',NULL),
('RSV-2402',2,3,CURDATE(),'13:00:00',2,'pending','Alergi kacang'),
('RSV-2403',3,12,CURDATE(),'14:15:00',6,'confirmed','Ulang tahun'),
('RSV-2404',4,5,CURDATE(),'18:30:00',3,'pending',NULL),
('RSV-2405',5,10,CURDATE(),'19:00:00',5,'confirmed','Vegetarian');
