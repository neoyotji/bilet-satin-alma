CREATE TABLE User (
    id TEXT PRIMARY KEY NOT NULL,
    full_name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    role TEXT NOT NULL, -- 'user', 'company_admin', 'admin'
    password TEXT NOT NULL,
    company_id TEXT,
    balance REAL DEFAULT 0.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Bus_Company (
    id TEXT PRIMARY KEY NOT NULL,
    name TEXT UNIQUE NOT NULL,
    logo_path TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Coupons (
    id TEXT PRIMARY KEY NOT NULL,
    code TEXT UNIQUE NOT NULL,
    discount REAL NOT NULL,
    company_id TEXT,
    usage_limit INTEGER,
    expire_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE User_Coupons (
    id TEXT PRIMARY KEY NOT NULL,
    coupon_id TEXT NOT NULL,
    user_id TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES Coupons(id),
    FOREIGN KEY (user_id) REFERENCES User(id)
);

CREATE TABLE Trips (
    id TEXT PRIMARY KEY NOT NULL,
    company_id TEXT NOT NULL,
    destination_city TEXT NOT NULL,
    arrival_time DATETIME NOT NULL,
    departure_time DATETIME NOT NULL,
    departure_city TEXT NOT NULL,
    price INTEGER NOT NULL,
    capacity INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES Bus_Company(id)
);

CREATE TABLE Tickets (
    id TEXT PRIMARY KEY NOT NULL,
    trip_id TEXT NOT NULL,
    user_id TEXT NOT NULL,
    status TEXT DEFAULT 'active' NOT NULL, -- 'active', 'canceled', 'expired'
    total_price REAL NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES Trips(id),
    FOREIGN KEY (user_id) REFERENCES User(id)
);

CREATE TABLE Booked_Seats (
    id TEXT PRIMARY KEY NOT NULL,
    ticket_id TEXT NOT NULL,
    seat_number INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES Tickets(id)
);