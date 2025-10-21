<?php
    require 'config.php';
    require 'check_role.php';

    check_role(['firma_admin']);
    $company_id = $_SESSION['user_company_id'];

    if (empty($company_id)) {
        die("HATA: Bir firmaya atanmamışsınız.");
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_coupon'])) {
        $code = strtoupper(trim($_POST['code']));
        $discount = trim($_POST['discount']);
        $usage_limit = trim($_POST['usage_limit']);
        $expire_date = trim($_POST['expire_date']);

        if (!empty($code) && is_numeric($discount) && is_numeric($usage_limit) && !empty($expire_date)) {
            try {
                $id = uniqid('CP');
                $stmt = $db->prepare("INSERT INTO Coupons (id, code, discount, usage_limit, expire_date, company_id) VALUES (:id, :code, :discount, :ul, :ed, :cid)");
                $stmt->execute([':id' => $id, ':code' => $code, ':discount' => $discount, ':ul' => $usage_limit, ':ed' => $expire_date, ':cid' => $company_id]);
                $_SESSION['flash_message'] = "Firmaya özel kupon eklendi.";
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 19) {
                    $_SESSION['flash_message'] = "HATA: Bu kupon kodu zaten mevcut. Lütfen farklı bir kod girin.";
                } else {
                    $_SESSION['flash_message'] = "HATA: " . $e->getMessage();
                }
            }
        } else {
            $_SESSION['flash_message'] = "HATA: Lütfen tüm alanları doğru doldurun.";
        }
        header("Location: firma_admin_kuponlar.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_coupon'])) {
        $coupon_id = $_POST['coupon_id'];
        try {
            $stmt = $db->prepare("DELETE FROM Coupons WHERE id = :id AND company_id = :cid");
            $stmt->execute([':id' => $coupon_id, ':cid' => $company_id]);
            $_SESSION['flash_message'] = "Kupon silindi.";
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "HATA: Kupon silinemedi.";
        }
        header("Location: firma_admin_kuponlar.php");
        exit();
    }

    $message = '';
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }
    $stmt = $db->prepare("SELECT * FROM Coupons WHERE company_id = :cid ORDER BY created_at DESC");
    $stmt->execute([':cid' => $company_id]);
    $coupons = $stmt->fetchAll();

    require 'header.php';
?>

<div class="container">
    <nav class="page-nav">
        <a href="firma_admin_panel.php">&larr; Firma Paneline Dön</a>
    </nav>
    <h1>Firma Kupon Yönetimi</h1>

    <?php if ($message): ?>
        <div class="message <?php echo (strpos($message, 'HATA') === 0) ? 'error' : ''; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="form-section">
        <h2>Yeni Firma Kuponu Ekle</h2>
        <form action="firma_admin_kuponlar.php" method="POST">
            <label>Kupon Kodu:</label>
            <input type="text" name="code" placeholder="Kupon Kodu" required>
            <label>İndirim Oranı (%):</label>
            <input type="number" step="0.01" name="discount" placeholder="İndirim Oranı (%)" required>
            <label>Kullanım Limiti:</label>
            <input type="number" name="usage_limit" placeholder="Kullanım Limiti" required>
            <label>Son Kullanma Tarihi:</label>
            <input type="date" name="expire_date" required>
            <button type="submit" name="add_coupon">Ekle</button>
        </form>
    </div>

    <h2>Mevcut Firma Kuponları</h2>
    <table>
        <thead>
            <tr>
                <th>Kod</th>
                <th>İndirim (%)</th>
                <th>Limit</th>
                <th>Son Tarih</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($coupons as $coupon): ?>
                <tr>
                    <td><?php echo htmlspecialchars($coupon['code']); ?></td>
                    <td><?php echo htmlspecialchars($coupon['discount']); ?></td>
                    <td><?php echo htmlspecialchars($coupon['usage_limit'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars(date('d.m.Y', strtotime($coupon['expire_date']))); ?></td>
                    <td class="action-links">
                        <a href="firma_admin_kupon_duzenle.php?id=<?php echo htmlspecialchars($coupon['id']); ?>">Düzenle</a>
                        <form action="firma_admin_kuponlar.php" method="POST" style="display:inline;" onsubmit="return confirm('Bu kuponu silmek istediğinizden emin misiniz?');">
                            <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>">
                            <button type="submit" name="delete_coupon">Sil</button>
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
