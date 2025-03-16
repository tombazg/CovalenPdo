<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "streamflix_db";

// Create a connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// SQL query to create users table
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    date_of_birth DATE,
    phone_number VARCHAR(15),
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    dt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    active_date TIMESTAMP NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB;";

// SQL query to create movies table
$sql_movies = "CREATE TABLE IF NOT EXISTS movies (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    category VARCHAR(50),
    release_date YEAR,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rating FLOAT NOT NULL DEFAULT '0',
    price FLOAT NOT NULL DEFAULT '0',
    genres VARCHAR(128) NOT NULL DEFAULT '',
    actors VARCHAR(128) NOT NULL DEFAULT '',
    duration FLOAT NOT NULL DEFAULT '0',
    trailers TEXT NOT NULL,
    seen INT UNSIGNED NOT NULL DEFAULT '0',
    predelete TINYINT(1) NOT NULL DEFAULT '0',
    weekly_trending TINYINT(1) DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB;";

// SQL query to create bookings table
$sql_bookings = "CREATE TABLE IF NOT EXISTS bookings (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    dt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id INT UNSIGNED,
    movie_id INT UNSIGNED,
    booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Confirmed', 'Cancelled'),
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (movie_id) REFERENCES movies(id)
) ENGINE=InnoDB;";

// SQL query to create booking_seats table
$sql_booking_seats = "CREATE TABLE IF NOT EXISTS booking_seats (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_id INT UNSIGNED NOT NULL,
    seat_number VARCHAR(10),
    date DATE,
    time TIME,
    PRIMARY KEY (id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
) ENGINE=InnoDB;";

// Execute the queries for these tables first
$initial_tables = [$sql_users, $sql_movies, $sql_bookings, $sql_booking_seats];

foreach ($initial_tables as $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Table created successfully.<br>";
    } else {
        echo "Error creating table: " . mysqli_error($conn) . "<br>";
    }
}

// Adding the foreign key constraint for booking_id in booking_seats table if it doesn't already exist
$sql_check_foreign_key = "SELECT CONSTRAINT_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_NAME = 'booking_seats' AND COLUMN_NAME = 'booking_id'";

$result = mysqli_query($conn, $sql_check_foreign_key);
if (mysqli_num_rows($result) == 0) {
    $sql_add_foreign_key = "ALTER TABLE booking_seats 
        ADD CONSTRAINT fk_booking_id FOREIGN KEY (booking_id) REFERENCES bookings(id);";

    if (mysqli_query($conn, $sql_add_foreign_key)) {
        echo "Foreign key added successfully.<br>";
    } else {
        echo "Error adding foreign key: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Foreign key constraint fk_booking_id already exists.<br>";
}

// SQL query to create system_logs table
$sql_system_logs = "CREATE TABLE IF NOT EXISTS system_logs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED,
    action TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;";

// SQL query to create watchlist table
$sql_watchlist = "CREATE TABLE IF NOT EXISTS watchlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    movie_id INT UNSIGNED NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
) ENGINE=InnoDB;";

// SQL query to create cinemas table
$sql_cinemas = "CREATE TABLE IF NOT EXISTS cinemas (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100),
    location VARCHAR(255),
    number_of_screens INT,
    PRIMARY KEY (id)
) ENGINE=InnoDB;";

// SQL query to create cinema_movies table
$sql_cinema_movies = "CREATE TABLE IF NOT EXISTS cinema_movies (
    cinema_id INT UNSIGNED,
    movie_id INT UNSIGNED,
    showtimes TEXT,
    PRIMARY KEY (cinema_id, movie_id),
    FOREIGN KEY (cinema_id) REFERENCES cinemas(id),
    FOREIGN KEY (movie_id) REFERENCES movies(id)
) ENGINE=InnoDB;";

// Execute the remaining table creation queries
$remaining_tables = [$sql_system_logs, $sql_watchlist, $sql_cinemas, $sql_cinema_movies];

foreach ($remaining_tables as $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Table created successfully.<br>";
    } else {
        echo "Error creating table: " . mysqli_error($conn) . "<br>";
    }
}

// Close the connection
mysqli_close($conn);

?>
