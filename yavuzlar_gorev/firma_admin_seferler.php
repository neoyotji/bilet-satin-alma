<?php
    require 'config.php';
    require 'check_role.php';

    //--- BÖLUM 1: MANTIKSAL İŞLEMLER (HTML'den Önce) ---
    check_role(['firma_admin']);
    $company_id = $_SESSION['user_company_id'];

    if (empty($company_id)) {
        die("HATA: Bir firmaya atanmamışsınız.");
    }

    // YENİ SEFER EKLEME
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_trip'])) {
        try {
            $id = uniqid('T');
            $stmt = $db->prepare("INSERT INTO Trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity) VALUES (:id, :cid, :dep_city, :dest_city, :dep_time, :arr_time, :price, :cap)");
            $stmt->execute([
                ':id' => $id,
                ':cid' => $company_id,
                ':dep_city' => $_POST['departure_city'],
                ':dest_city' => $_POST['destination_city'],
                ':dep_time' => $_POST['departure_time'],
                ':arr_time' => $_POST['arrival_time'],
                ':price' => $_POST['price'],
                ':cap' => $_POST['capacity']
            ]);
            $_SESSION['flash_message'] = "Yeni sefer başarıyla eklendi.";
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "HATA: Sefer eklenemedi. " . $e->getMessage();
        }
        header("Location: firma_admin_seferler.php");
        exit();
    }

    // SEFER SİLME
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_trip'])) {
        $trip_id = $_POST['trip_id'];
        try {
            $stmt = $db->prepare("DELETE FROM Trips WHERE id = :id AND company_id = :cid");
            $stmt->execute([':id' => $trip_id, ':cid' => $company_id]);
            $_SESSION['flash_message'] = "Sefer silindi.";
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "HATA: Sefer silinemedi.";
        }
        header("Location: firma_admin_seferler.php");
        exit();
    }

    // SAYFAYI GÖSTERMEK İÇİN VERİLERİ HAZIRLA
    $message = '';
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }
    $stmt = $db->prepare("SELECT * FROM Trips WHERE company_id = :cid ORDER BY departure_time DESC");
    $stmt->execute([':cid' => $company_id]);
    $trips = $stmt->fetchAll();

    //--- BÖLÜM 2: GÖRSEL OLUŞTURMA (HTML Başlangıcı) ---
    require 'header.php';
?>

<div class="container">
    <nav class="page-nav">
        <a href="firma_admin_panel.php">&larr; Firma Paneline Dön</a>
    </nav>
    <h1>Sefer Yönetimi</h1>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-section">
        <h2>Yeni Sefer Ekle</h2>
        <form action="firma_admin_seferler.php" method="POST">
            <input type="text" name="departure_city" placeholder="Kalkış Şehri" required>
            <input type="text" name="destination_city" placeholder="Varış Şehri" required>
            <label>Kalkış Zamanı:</label>
            <input type="datetime-local" name="departure_time" required>
            <label>Varış Zamanı:</label>
            <input type="datetime-local" name="arrival_time" required>
            <input type="number" name="price" placeholder="Fiyat" required>
            <input type="number" name="capacity" placeholder="Kapasite" required>
            <button type="submit" name="add_trip">Yeni Sefer Ekle</button>
        </form>
    </div>

    <h2>Mevcut Seferler</h2>
    <table>
        <thead>
            <tr>
                <th>Güzergah</th>
                <th>Kalkış Zamanı</th>
                <th>Fiyat</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($trips as $trip): ?>
                <tr>
                    <td><?php echo htmlspecialchars($trip['departure_city']); ?> &rarr; <?php echo htmlspecialchars($trip['destination_city']); ?></td>
                    <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($trip['departure_time']))); ?></td>
                    <td><?php echo htmlspecialchars($trip['price']); ?> TL</td>
                    <td class="action-links">
                        <a href="firma_admin_sefer_duzenle.php?id=<?php echo $trip['id']; ?>">Düzenle</a>
                        <form action="firma_admin_seferler.php" method="POST" style="display:inline;" onsubmit="return confirm('Bu seferi silmek istediğinizden emin misiniz?');">
                            <input type="hidden" name="trip_id" value="<?php echo $trip['id']; ?>">
                            <button type="submit" name="delete_trip">Sil</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
    require 'footer.php';
?>