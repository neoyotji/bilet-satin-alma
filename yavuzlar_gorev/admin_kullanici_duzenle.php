<?php
    require 'config.php';
    require 'check_role.php';

    //--- BÖLÜM 1: MANTIKSAL İŞLEMLER (HTML ÇIKTISI YOK) ---
    check_role(['admin']);

    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header("Location: admin_kullanicilar.php");
        exit();
    }
    $userId = $_GET['id'];

    // FORM GÖNDERİLDİYSE GÜNCELLEME İŞLEMİNİ YAP
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $fullName = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $companyId = $_POST['company_id'];
        $password = $_POST['password'];

        if (empty($fullName) || empty($email) || empty($companyId)) {
            $_SESSION['flash_message'] = "HATA: Ad, e-posta ve firma alanları zorunludur.";
        } else {
            try {
                if (!empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE User SET full_name = :fn, email = :em, company_id = :cid, password = :pw WHERE id = :id");
                    $stmt->bindParam(':pw', $hashedPassword);
                } else {
                    $stmt = $db->prepare("UPDATE User SET full_name = :fn, email = :em, company_id = :cid WHERE id = :id");
                }
                $stmt->bindParam(':fn', $fullName);
                $stmt->bindParam(':em', $email);
                $stmt->bindParam(':cid', $companyId);
                $stmt->bindParam(':id', $userId);
                $stmt->execute();
                
                $_SESSION['flash_message'] = "Kullanıcı bilgileri güncellendi.";
                header("Location: admin_kullanicilar.php");
                exit();
            } catch (PDOException $e) {
                $_SESSION['flash_message'] = "HATA: " . $e->getMessage();
            }
        }
        // Hata durumunda aynı sayfaya geri yönlendir
        header("Location: admin_kullanici_duzenle.php?id=" . $userId);
        exit();
    }

    // SAYFAYI GÖRÜNTÜLEMEK İÇİN GEREKLİ VERİLERİ ÇEK
    $user = $db->prepare("SELECT * FROM User WHERE id = :id AND role = 'firma_admin'");
    $user->execute([':id' => $userId]);
    $user = $user->fetch();

    if (!$user) {
        $_SESSION['flash_message'] = "Kullanıcı bulunamadı.";
        header("Location: admin_kullanicilar.php");
        exit();
    }
    $companies = $db->query("SELECT id, name FROM Bus_Company ORDER BY name")->fetchAll();
    $message = '';
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }

    //--- BÖLÜM 2: GÖRSEL OLUŞTURMA (ARTIK HTML BAŞLAYABİLİR) ---
    require 'header.php';
?>

<div class="container">
    <nav class="page-nav">
        <a href="admin_kullanicilar.php">&larr; Kullanıcı Listesine Dön</a>
    </nav>
    <h1>Kullanıcı Düzenle</h1>

    <?php if ($message): ?>
        <div class="message error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-section">
        <form action="admin_kullanici_duzenle.php?id=<?php echo htmlspecialchars($user['id']); ?>" method="POST">
            <label for="full_name">Ad Soyad:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            
            <label for="email">E-posta:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="password">Yeni Şifre (değiştirmek istemiyorsanız boş bırakın):</label>
            <input type="password" id="password" name="password">

            <label for="company_id">Atandığı Firma:</label>
            <select id="company_id" name="company_id" required>
                <?php foreach ($companies as $company): ?>
                    <option value="<?php echo htmlspecialchars($company['id']); ?>" <?php echo ($user['company_id'] == $company['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($company['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Güncelle</button>
        </form>
    </div>
</div>

<?php
    require 'footer.php';
?>