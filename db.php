<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "FuelCrisis";

$conn = new mysqli($servername, $username, $password);

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}


$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if($conn->query($sql) === TRUE){
    $conn->select_db($dbname);
}
else{
    die("Error creating database: " . $conn->error);
}

$conn->query("CREATE TABLE IF NOT EXISTS users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    location VARCHAR(255) DEFAULT NULL,
    location_x DECIMAL(10,2) DEFAULT 0.00,
    location_y DECIMAL(10,2) DEFAULT 0.00,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    status ENUM('Active', 'Banned') DEFAULT 'Active'
)");

$checkStatus = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
if($checkStatus->num_rows == 0){
    $conn->query("ALTER TABLE users ADD COLUMN status ENUM('Active', 'Banned') DEFAULT 'Active'");
}

$checkUserLocation = $conn->query("SHOW COLUMNS FROM users LIKE 'location'");
if($checkUserLocation->num_rows == 0){
    $conn->query("ALTER TABLE users ADD COLUMN location VARCHAR(255) DEFAULT NULL AFTER email");
}

$checkUserLocationX = $conn->query("SHOW COLUMNS FROM users LIKE 'location_x'");
if($checkUserLocationX->num_rows == 0){
    $conn->query("ALTER TABLE users ADD COLUMN location_x DECIMAL(10,2) DEFAULT 0.00 AFTER location");
}

$checkUserLocationY = $conn->query("SHOW COLUMNS FROM users LIKE 'location_y'");
if($checkUserLocationY->num_rows == 0){
    $conn->query("ALTER TABLE users ADD COLUMN location_y DECIMAL(10,2) DEFAULT 0.00 AFTER location_x");
}

$conn->query("CREATE TABLE IF NOT EXISTS stations(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    location_x DECIMAL(10,2) DEFAULT 0.00,
    location_y DECIMAL(10,2) DEFAULT 0.00,
    on_off_status ENUM('on', 'off') DEFAULT 'on',
    map_link TEXT,
    img_url TEXT,
    petrol_stock FLOAT DEFAULT 0,
    diesel_stock FLOAT DEFAULT 0,
    status ENUM('AVAILABLE', 'NO STOCK') DEFAULT 'AVAILABLE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$checkStationLocationX = $conn->query("SHOW COLUMNS FROM stations LIKE 'location_x'");
if($checkStationLocationX->num_rows == 0){
    $conn->query("ALTER TABLE stations ADD COLUMN location_x DECIMAL(10,2) DEFAULT 0.00 AFTER location");
}

$checkStationLocationY = $conn->query("SHOW COLUMNS FROM stations LIKE 'location_y'");
if($checkStationLocationY->num_rows == 0){
    $conn->query("ALTER TABLE stations ADD COLUMN location_y DECIMAL(10,2) DEFAULT 0.00 AFTER location_x");
}

$checkStationOnOffStatus = $conn->query("SHOW COLUMNS FROM stations LIKE 'on_off_status'");
if($checkStationOnOffStatus->num_rows == 0){
    $conn->query("ALTER TABLE stations ADD COLUMN on_off_status ENUM('on', 'off') DEFAULT 'on' AFTER location_y");
}

$checkStationHasPetrol = $conn->query("SHOW COLUMNS FROM stations LIKE 'has_petrol'");
if($checkStationHasPetrol->num_rows > 0){
    $conn->query("ALTER TABLE stations DROP COLUMN has_petrol");
}

$checkStationHasDiesel = $conn->query("SHOW COLUMNS FROM stations LIKE 'has_diesel'");
if($checkStationHasDiesel->num_rows > 0){
    $conn->query("ALTER TABLE stations DROP COLUMN has_diesel");
}

$conn->query("CREATE TABLE IF NOT EXISTS vehicle_daily_limits(
    vehicle_type ENUM('bike', 'car') PRIMARY KEY,
    daily_limit DECIMAL(8,2) NOT NULL
)");
$conn->query("INSERT IGNORE INTO vehicle_daily_limits (vehicle_type, daily_limit) VALUES ('bike', 50.00), ('car', 100.00)");

$conn->query("CREATE TABLE IF NOT EXISTS cars(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    car_type VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_car_id (user_id, car_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

$conn->query("CREATE TABLE IF NOT EXISTS tokens(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    station_id INT NOT NULL,
    car_id INT NULL,
    vehicle_type ENUM('bike', 'car') NOT NULL DEFAULT 'car',
    fuel_type VARCHAR(50) NOT NULL,
    liters FLOAT NOT NULL,
    serial_number INT NOT NULL,
    status ENUM('Pending', 'Called', 'Completed') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_global_vehicle_daily_usage (user_id, vehicle_type, created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (station_id) REFERENCES stations(id) ON DELETE CASCADE,
    KEY idx_user_car (user_id, car_id),
    CONSTRAINT fk_tokens_car FOREIGN KEY (user_id, car_id) REFERENCES cars(user_id, car_id) ON DELETE RESTRICT ON UPDATE CASCADE
)");

$vehicleTypeCheck = $conn->query("SHOW COLUMNS FROM tokens LIKE 'vehicle_type'");
if($vehicleTypeCheck->num_rows == 0){
    $conn->query("ALTER TABLE tokens ADD COLUMN vehicle_type ENUM('bike', 'car') NOT NULL DEFAULT 'car' AFTER station_id");
}

$carIdCheck = $conn->query("SHOW COLUMNS FROM tokens LIKE 'car_id'");
if($carIdCheck->num_rows == 0){
    $conn->query("ALTER TABLE tokens ADD COLUMN car_id INT NULL AFTER station_id");
}

$globalQuotaIndex = $conn->query("SHOW INDEX FROM tokens WHERE Key_name='idx_global_vehicle_daily_usage'");
if($globalQuotaIndex->num_rows == 0){
    $conn->query("ALTER TABLE tokens ADD INDEX idx_global_vehicle_daily_usage (user_id, vehicle_type, created_at)");
}

$carFkCheck = $conn->query("SHOW CREATE TABLE tokens");
if($carFkCheck && $carFkCheck->num_rows > 0){
    $createSql = $carFkCheck->fetch_assoc()['Create Table'];
    if(strpos($createSql, 'fk_tokens_car') !== false && stripos($createSql, 'ON DELETE SET NULL') !== false){
        $conn->query("ALTER TABLE tokens DROP FOREIGN KEY fk_tokens_car");
        $createSql = '';
    }
    if(strpos($createSql, 'fk_tokens_car') === false){
        $conn->query("ALTER TABLE tokens ADD CONSTRAINT fk_tokens_car FOREIGN KEY (user_id, car_id) REFERENCES cars(user_id, car_id) ON DELETE RESTRICT ON UPDATE CASCADE");
    }
}


$conn->query("CREATE TABLE IF NOT EXISTS reviews(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    station_id INT NOT NULL,
    comment TEXT NOT NULL,
    rating TINYINT(1) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (station_id) REFERENCES stations(id) ON DELETE CASCADE
)");

$reviewRatingCheck = $conn->query("SHOW COLUMNS FROM reviews LIKE 'rating'");
if($reviewRatingCheck->num_rows == 0){
    $conn->query("ALTER TABLE reviews ADD COLUMN rating TINYINT(1) DEFAULT NULL AFTER comment");
}


$conn->query("CREATE TABLE IF NOT EXISTS logs(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)");


$conn->query("CREATE TABLE IF NOT EXISTS fuel_market_prices(
    id INT AUTO_INCREMENT PRIMARY KEY,
    fuel_type VARCHAR(50) NOT NULL UNIQUE,
    price_per_liter FLOAT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");


$conn->query("INSERT INTO users (full_name, email, password, role, location_x, location_y)
    VALUES ('Admin User', 'admin@gmail.com', 'admin', 'admin', 12.50, 18.00)
    ON DUPLICATE KEY UPDATE location_x = VALUES(location_x), location_y = VALUES(location_y)");

$conn->query("INSERT INTO users (full_name, email, password, role, location_x, location_y)
    VALUES ('Staff User', 'staff@gmail.com', 'staff', 'staff', 15.75, 21.00)
    ON DUPLICATE KEY UPDATE location_x = VALUES(location_x), location_y = VALUES(location_y)");

$conn->query("INSERT IGNORE INTO users (full_name, email, password, role, location_x, location_y)
VALUES
    ('Maimun', 'maimun@gmail.com', '12345', 'user', 9.25, 11.50),
    ('Maruf', 'maruf@gmail.com', '1234', 'user', 18.30, 14.75),
    ('Jamal', 'jamal@gmail.com', 'jamal123', 'user', 22.00, 27.50),
    ('Nadia', 'nadia@gmail.com', 'nadia123', 'user', 6.80, 19.20),
    ('Rafi', 'rafi@gmail.com', 'rafi123', 'user', 28.40, 10.60)");

$checkPrices = $conn->query("SELECT id FROM fuel_market_prices");
if($checkPrices->num_rows == 0){
    $conn->query("INSERT INTO fuel_market_prices (fuel_type, price_per_liter) VALUES ('petrol', 120.00), ('diesel', 100.00)");
}


$checkStations = $conn->query("SELECT id FROM stations");
if($checkStations->num_rows == 0){
    $conn->query("INSERT INTO stations (name, location, location_x, location_y, on_off_status, map_link, img_url, petrol_stock, diesel_stock, status) VALUES 
    ('Shell Petrol Station', '123 Main Street, Cityville', 12.00, 18.00, 'on', '#', '../../Assets/card-im.png', 5000, 0, 'AVAILABLE'),
    ('City Center Station', 'Downtown, Metro City', 18.50, 22.00, 'on', '#', '../../Assets/card-im.png', 2000, 3000, 'AVAILABLE'),
    ('Badda Fuel Point', 'Badda, Link Road', 14.40, 16.80, 'on', '#', '../../Assets/card-im.png', 4200, 2800, 'AVAILABLE'),
    ('Gulshan Service Station', 'Gulshan Avenue', 21.20, 25.10, 'on', '#', '../../Assets/card-im.png', 3500, 2600, 'AVAILABLE'),
    ('Mirpur Fuel Hub', 'Mirpur-10', 9.10, 12.60, 'on', '#', '../../Assets/card-im.png', 3100, 2400, 'AVAILABLE'),
    ('Uttara CNG Station', 'Uttara', 27.80, 19.40, 'on', '#', '../../Assets/card-im.png', 2900, 2200, 'AVAILABLE')");
}

$conn->query("UPDATE stations SET location_x = 12.50, location_y = 18.00 WHERE name = 'S T Power Limited CNG' AND (location_x = 0 OR location_y = 0)");
$conn->query("UPDATE stations SET location_x = 14.00, location_y = 16.75 WHERE name = 'Makka CNG' AND (location_x = 0 OR location_y = 0)");
$conn->query("UPDATE stations SET location_x = 21.20, location_y = 25.00 WHERE name = 'Gulshan Service Station' AND (location_x = 0 OR location_y = 0)");
$conn->query("UPDATE stations SET location_x = 9.00, location_y = 12.50 WHERE name = 'Dhaka CNG Ltd.' AND (location_x = 0 OR location_y = 0)");
$conn->query("UPDATE stations SET location_x = 27.80, location_y = 19.30 WHERE name = 'Cosmo Filling Station' AND (location_x = 0 OR location_y = 0)");
$conn->query("UPDATE stations SET location_x = 12.00, location_y = 18.00 WHERE name = 'Shell Petrol Station' AND (location_x = 0 OR location_y = 0)");
$conn->query("UPDATE stations SET location_x = 18.50, location_y = 22.00 WHERE name = 'City Center Station' AND (location_x = 0 OR location_y = 0)");
$conn->query("UPDATE stations SET location_x = 14.40, location_y = 16.80 WHERE name = 'Badda Fuel Point' AND (location_x = 0 OR location_y = 0)");
$conn->query("UPDATE stations SET location_x = 9.10, location_y = 12.60 WHERE name = 'Mirpur Fuel Hub' AND (location_x = 0 OR location_y = 0)");
$conn->query("UPDATE stations SET location_x = 27.80, location_y = 19.40 WHERE name = 'Uttara CNG Station' AND (location_x = 0 OR location_y = 0)");

$conn->query("UPDATE users SET location_x = 12.50, location_y = 18.00 WHERE email='admin@gmail.com' AND (location_x = 0 OR location_y = 0)");
$conn->query("UPDATE users SET location_x = 15.75, location_y = 21.00 WHERE email='staff@gmail.com' AND (location_x = 0 OR location_y = 0)");
$conn->query("UPDATE users SET location_x = 9.25, location_y = 11.50 WHERE email='maimun@gmail.com' AND (location_x = 0 OR location_y = 0)");
$conn->query("UPDATE users SET location_x = 18.30, location_y = 14.75 WHERE email='maruf@gmail.com' AND (location_x = 0 OR location_y = 0)");
?>