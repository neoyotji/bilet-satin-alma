<?php
    require 'config.php';
    require 'check_role.php';

    check_role(['admin']);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_coupon'])) {
        $code = strtoupper(trim($_POST['code']));
        $discount = trim($_POST['discount']);
        $usage_limit = trim($_POST['usage_limit']);
        $expire_date = trim($_POST['expire_date']);

        if (!empty($code) && is_numeric($discount) && is_numeric($usage_limit) && !empty($expire_date)) {
            try {
                $id = uniqid('CP');
                $stmt = $db->prepare("INSERT INTO Coupons (id, code, discount, usage_limit, expire_date, company_id) VALUES (:id, :code, :discount, :ul, :ed, NULL)");
                $stmt->execute([':id' => $id, ':code' => $code, ':discount' => $discount, ':ul' => $usage_limit, ':ed' => $expire_date]);
                $_SESSION['flash_message'] = "Genel kupon başarıyla eklendi.";
            } catch (PDOException $e) {
                $_SESSION['flash_message'] = "HATA: Bu kupon kodu zaten mevcut olabilir.";
            }
        } else {
            $_SESSION['flash_message'] = "HATA: Lütfen tüm alanları doğru bir şekilde doldurun.";
        }
        header("Location: admin_kuponlar.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_coupon'])) {
        $couponId = $_POST['coupon_id'];
        try {
            $stmt = $db->prepare("DELETE FROM Coupons WHERE id = :id AND company_id IS NULL");
            $stmt->execute([':id' => $couponId]);
            $_SESSION['flash_message'] = "Kupon başarıyla silindi.";
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "HATA: Kupon silinemedi.";
        }
        header("Location: admin_kuponlar.php");
        exit();
    }

    $message = '';
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }
//sql inj var mı bak
    $coupons = $db->query("SELECT * FROM Coupons WHERE company_id IS NULL ORDER BY created_at DESC")->fetchAll();

    require 'header.php';
?>

<div class="container">
    <nav class="page-nav">
        <a href="admin_panel.php">&larr; Admin Paneline Dön</a>
    </nav>
    <h1>Genel Kupon Yönetimi</h1>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-section">
        <h2>Yeni Genel Kupon Ekle</h2>
        <form action="admin_kuponlar.php" method="POST">
            <label for="code">Kupon Kodu:</label>
            <input type="text" id="code" name="code" required>
            
            <label for="discount">İndirim Oranı (%):</label>
            <input type="number" step="0.01" id="discount" name="discount" required>

            <label for="usage_limit">Kullanım Limiti:</label>
            <input type="number" id="usage_limit" name="usage_limit" required>

            <label for="expire_date">Son Kullanma Tarihi:</label>
            <input type="date" id="expire_date" name="expire_date" required>

            <button type="submit" name="add_coupon">Ekle</button>
        </form>
    </div>

    <h2>Mevcut Genel Kuponlar</h2>
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
                    <td><?php echo htmlspecialchars($coupon['usage_limit'] ?? 'Limitsiz'); ?></td>
                    <td><?php echo htmlspecialchars(date('d.m.Y', strtotime($coupon['expire_date']))); ?></td>
                    <td class="action-links">
                        <a href="admin_kupon_duzenle.php?id=<?php echo htmlspecialchars($coupon['id']); ?>">Düzenle</a>
                        <form action="admin_kuponlar.php" method="POST" style="display:inline;" onsubmit="return confirm('Bu kuponu silmek istediğinizden emin misiniz?');">
                            <input type="hidden" name="coupon_id" value="<?php echo htmlspecialchars($coupon['id']); ?>">
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

