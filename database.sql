CREATE DATABASE IF NOT EXISTS ticketly CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ticketly;

CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    venue VARCHAR(150) NOT NULL,
    event_date DATETIME NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_tickets INT UNSIGNED NOT NULL,
    tickets_remaining INT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_date (event_date)
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference_code VARCHAR(20) NOT NULL UNIQUE,
    event_id INT UNSIGNED NOT NULL,
    customer_name VARCHAR(120) NOT NULL,
    customer_email VARCHAR(190) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_event FOREIGN KEY (event_id) REFERENCES events(id)
);

INSERT INTO events (title, venue, event_date, price, total_tickets, tickets_remaining, description)
SELECT * FROM (
    SELECT 'Neon Nights Festival', 'Constitution Hill, Johannesburg', '2027-02-20 18:00:00', 350.00, 500, 500, 'An electric night of music, lights and local talent.'
    UNION ALL
    SELECT 'Tech Forward Summit', 'Sandton Convention Centre, Johannesburg', '2027-03-12 09:00:00', 499.00, 250, 250, 'Meet developers, designers and technology innovators.'
    UNION ALL
    SELECT 'Comedy Under The Stars', 'Victoria Yards, Johannesburg', '2027-04-03 19:30:00', 180.00, 180, 180, 'A relaxed outdoor comedy show featuring rising stars.'
) AS sample
WHERE NOT EXISTS (SELECT 1 FROM events LIMIT 1);
