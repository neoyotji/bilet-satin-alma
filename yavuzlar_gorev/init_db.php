<?php
    require 'config.php'; 
    require 'check_role.php';
    require 'header.php';
    try {
        echo "Veritabanı kurulumu başlıyor...\n";

        $sql = file_get_contents('yavuzlar.sql');
        if ($sql === false) {
            throw new Exception("yavuzlar.sql dosyası okunamadı.");
        }

        $db_path = getenv('DATABASE_PATH') ?: __DIR__ . '/bilet.db';
        if (file_exists($db_path)) {
            unlink($db_path);
            echo "Mevcut veritabanı dosyası silindi.\n";
        }
        
        $db = new PDO("sqlite:$db_path");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);


        $db->exec($sql);
        echo "Tüm tablolar başarıyla oluşturuldu.\n";

        echo "Test verileri ekleniyor...\n";

        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $firmaAdminPassword = password_hash('firmaA123', PASSWORD_DEFAULT);
        $userPassword = password_hash('yolcu123', PASSWORD_DEFAULT);

        $db->exec("INSERT INTO Bus_Company (id, name) VALUES ('C01', 'Yavuzlar Turizm');");
        $db->exec("INSERT INTO Bus_Company (id, name) VALUES ('C02', 'Metro Turizm');");

        $db->exec("INSERT INTO User (id, full_name, email, role, password) VALUES ('U01', 'Admin Yönetici', 'admin@bilet.com', 'admin', '$adminPassword');");
        $db->exec("INSERT INTO User (id, full_name, email, role, password, company_id) VALUES ('U02', 'Ali Veli', 'firma_a@bilet.com', 'firma_admin', '$firmaAdminPassword', 'C01');");
        $db->exec("INSERT INTO User (id, full_name, email, role, password, balance) VALUES ('U03', 'Ayşe Yılmaz', 'yolcu@bilet.com', 'user', '$userPassword', 1500.0);");
        
        $db->exec("INSERT INTO Trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity) VALUES ('T01', 'C01', 'İstanbul', 'Ankara', '2025-10-20 23:00:00', '2025-10-21 06:00:00', 450, 40);");
        $db->exec("INSERT INTO Trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity) VALUES ('T02', 'C01', 'Ankara', 'İzmir', '2025-10-22 10:00:00', '2025-10-22 18:00:00', 600, 40);");
        $db->exec("INSERT INTO Trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity) VALUES ('T03', 'C02', 'İzmir', 'Antalya', '2025-11-05 12:30:00', '2025-11-05 19:00:00', 550, 42);");

        $db->exec("INSERT INTO Coupons (id, code, discount, company_id, expire_date) VALUES ('CP01', 'YAVUZLAR10', 10.0, 'C01', '2025-12-31');"); // Firmaya özel
        $db->exec("INSERT INTO Coupons (id, code, discount, expire_date) VALUES ('CP02', 'HOSGELDIN15', 15.0, '2026-01-31');"); // Genel kupon (company_id = NULL)


        echo "Test verileri başarıyla eklendi.\n";
        echo "\nKurulum tamamlandı!\n";
        echo "Kullanabileceğiniz hesaplar:\n";
        echo "Admin: admin@bilet.com / admin123\n";
        echo "Firma Admin: firma_a@bilet.com / firmaA123\n";
        echo "Yolcu: yolcu@bilet.com / yolcu123\n";


    } catch (Exception $e) {
        die("HATA: " . $e->getMessage());
    }
?>