<?php
    require 'config.php';
    require 'check_role.php';

    //--- BÖLÜM 1: MANTIKSAL İŞLEMLER (HTML ÇIKTISI YOK) ---
    check_role(['firma_admin']);
    $company_id = $_SESSION['user_company_id'];

    // ID kontrolü
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header("Location: firma_admin_seferler.php");
        exit();
    }
    $trip_id = $_GET['id'];

    // FORM GÖNDERİLDİYSE GÜNCELLEME YAP
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            $stmt = $db->prepare(
                "UPDATE Trips SET departure_city = ?, destination_city = ?, departure_time = ?, arrival_time = ?, price = ?, capacity = ? 
                 WHERE id = ? AND company_id = ?"
            );
            $stmt->execute([
                $_POST['departure_city'], $_POST['destination_city'],
                $_POST['departure_time'], $_POST['arrival_time'],
                $_POST['price'], $_POST['capacity'],
                $trip_id, $company_id
            ]);
            $_SESSION['flash_message'] = "Sefer güncellendi.";
            header("Location: firma_admin_seferler.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "HATA: Güncelleme başarısız. " . $e->getMessage();
            // Hata durumunda aynı sayfaya geri yönlendir
            header("Location: firma_admin_sefer_duzenle.php?id=" . $trip_id);
            exit();
        }
    }

    // SAYFAYI GÖRÜNTÜLEMEK İÇİN GEREKLİ VERİLERİ ÇEK
    $stmt = $db->prepare("SELECT * FROM Trips WHERE id = :id AND company_id = :cid");
    $stmt->execute([':id' => $trip_id, ':cid' => $company_id]);
    $trip = $stmt->fetch();

    if (!$trip) {
        $_SESSION['flash_message'] = "HATA: Yetkiniz olmayan veya bulunamayan bir seferi düzenleyemezsiniz.";
        header("Location: firma_admin_seferler.php");
        exit();
    }

    $message = '';
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }

    //--- BÖLÜM 2: GÖRSEL OLUŞTURMA (HTML BAŞLANGICI) ---
    require 'header.php';
?>

<div class="container">
    <nav class="page-nav">
        <a href="firma_admin_seferler.php">&larr; Sefer Listesine Dön</a>
    </nav>
    <h1>Sefer Düzenle</h1>
    
    <?php if ($message): ?>
        <div class="message error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-section">
        <form action="firma_admin_sefer_duzenle.php?id=<?php echo htmlspecialchars($trip['id']); ?>" method="POST">
            <label>Kalkış Şehri:</label>
            <input type="text" name="departure_city" value="<?php echo htmlspecialchars($trip['departure_city']); ?>" required>
            
            <label>Varış Şehri:</label>
            <input type="text" name="destination_city" value="<?php echo htmlspecialchars($trip['destination_city']); ?>" required>
            
            <label>Kalkış Zamanı:</label>
            <input type="datetime-local" name="departure_time" value="<?php echo date('Y-m-d\TH:i', strtotime($trip['departure_time'])); ?>" required>
            
            <label>Varış Zamanı:</label>
            <input type="datetime-local" name="arrival_time" value="<?php echo date('Y-m-d\TH:i', strtotime($trip['arrival_time'])); ?>" required>
            
            <label>Fiyat:</label>
            <input type="number" name="price" value="<?php echo htmlspecialchars($trip['price']); ?>" required>
            
            <label>Kapasite:</label>
            <input type="number" name="capacity" value="<?php echo htmlspecialchars($trip['capacity']); ?>" required>
            
            <button type="submit">Güncelle</button>
        </form>
    </div>
</div>

<?php
    require 'footer.php';
?>