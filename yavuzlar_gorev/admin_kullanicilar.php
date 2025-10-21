<?php
    require 'config.php';
    require 'check_role.php';
    check_role(['admin']);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
        $fullName = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $companyId = $_POST['company_id'];
        $role = 'firma_admin';

        if (!empty($fullName) && !empty($email) && !empty($password) && !empty($companyId)) {
            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $id = uniqid('U');
                $stmt = $db->prepare("INSERT INTO User (id, full_name, email, password, role, company_id) VALUES (:id, :fn, :em, :pw, :role, :cid)");
                $stmt->execute([':id' => $id, ':fn' => $fullName, ':em' => $email, ':pw' => $hashedPassword, ':role' => $role, ':cid' => $companyId]);
                $_SESSION['flash_message'] = "Firma admini başarıyla eklendi.";
            } catch (PDOException $e) {
                $_SESSION['flash_message'] = "HATA: Bu e-posta adresi zaten kullanılıyor olabilir.";
            }
        } else {
            $_SESSION['flash_message'] = "HATA: Lütfen tüm alanları doldurun.";
        }
        header("Location: admin_kullanicilar.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
        $userId = $_POST['user_id'];
        try {
            $stmt = $db->prepare("DELETE FROM User WHERE id = :id AND role = 'firma_admin'");
            $stmt->execute([':id' => $userId]);
            $_SESSION['flash_message'] = "Kullanıcı başarıyla silindi.";
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "HATA: Kullanıcı silinemedi.";
        }
        header("Location: admin_kullanicilar.php");
        exit();
    }

    $message = '';
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }
    $companies = $db->query("SELECT id, name FROM Bus_Company ORDER BY name")->fetchAll();
    $users = $db->query("SELECT User.id, User.full_name, User.email, Bus_Company.name AS company_name FROM User JOIN Bus_Company ON User.company_id = Bus_Company.id WHERE User.role = 'firma_admin' ORDER BY User.full_name")->fetchAll();

    require 'header.php';
?>

<div class="container">
    <nav class="page-nav">
        <a href="admin_panel.php">&larr; Admin Paneline Dön</a>
    </nav>
    <h1>Firma Admin Yönetimi</h1>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-section">
        <h2>Yeni Firma Admini Ekle</h2>
        <form action="admin_kullanicilar.php" method="POST">
            <label for="full_name">Ad Soyad:</label>
            <input type="text" id="full_name" name="full_name" required>
            
            <label for="email">E-posta:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Şifre:</label>
            <input type="password" id="password" name="password" required>

            <label for="company_id">Atanacak Firma:</label>
            <select id="company_id" name="company_id" required>
                <option value="">-- Firma Seçin --</option>
                <?php foreach ($companies as $company): ?>
                    <option value="<?php echo htmlspecialchars($company['id']); ?>">
                        <?php echo htmlspecialchars($company['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="add_user">Ekle</button>
        </form>
    </div>

    <h2>Mevcut Firma Adminleri</h2>
    <table>
        <thead>
            <tr>
                <th>Ad Soyad</th>
                <th>E-posta</th>
                <th>Atandığı Firma</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['company_name']); ?></td>
                    <td class="action-links">
                        <a href="admin_kullanici_duzenle.php?id=<?php echo htmlspecialchars($user['id']); ?>">Düzenle</a>
                        <form action="admin_kullanicilar.php" method="POST" style="display:inline;" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?');">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                            <button type="submit" name="delete_user">Sil</button>
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