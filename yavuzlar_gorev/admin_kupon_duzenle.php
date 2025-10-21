<?php
    require 'config.php';
    require 'check_role.php';
    require 'header.php';

    check_role(['admin']);

    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header("Location: admin_kuponlar.php");
        exit();
    }
    $couponId = $_GET['id'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $code = strtoupper(trim($_POST['code']));
        $discount = trim($_POST['discount']);
        $usage_limit = trim($_POST['usage_limit']);
        $expire_date = trim($_POST['expire_date']);

        if (!empty($code) && is_numeric($discount) && is_numeric($usage_limit) && !empty($expire_date)) {
            try {
                $stmt = $db->prepare(
                    "UPDATE Coupons SET code = :code, discount = :discount, usage_limit = :ul, expire_date = :ed 
                    WHERE id = :id AND company_id IS NULL"
                );
                $stmt->execute([
                    ':code' => $code,
                    ':discount' => $discount,
                    ':ul' => $usage_limit,
                    ':ed' => $expire_date,
                    ':id' => $couponId
                ]);
                $_SESSION['flash_message'] = "Kupon güncellendi.";
                header("Location: admin_kuponlar.php");
                exit();
            } catch (PDOException $e) {
                $_SESSION['flash_message'] = "HATA: " . $e->getMessage();
            }
        } else {
            $_SESSION['flash_message'] = "HATA: Lütfen tüm alanları doğru doldurun.";
        }
        header("Location: admin_kupon_duzenle.php?id=" . $couponId);
        exit();
    }

    $stmt = $db->prepare("SELECT * FROM Coupons WHERE id = :id AND company_id IS NULL");
    $stmt->execute([':id' => $couponId]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        $_SESSION['flash_message'] = "Kupon bulunamadı veya bu kupon genel bir kupon değil.";
        header("Location: admin_kuponlar.php");
        exit();
    }

    $message = '';
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }
?>

<div class="container">
    <meta charset="UTF-8">
    <title>Genel Kupon Düzenle</title>
    <link rel="stylesheet" href="style.css">
    <div class="container">
        <a href="admin_kuponlar.php">&larr; Kupon Listesine Dön</a>
        <h1>Genel Kupon Düzenle</h1>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <div class="form-section">
            <form action="admin_kupon_duzenle.php?id=<?php echo htmlspecialchars($coupon['id']); ?>" method="POST">
                <label for="code">Kupon Kodu:</label>
                <input type="text" id="code" name="code" value="<?php echo htmlspecialchars($coupon['code']); ?>" required>
                <label for="discount">İndirim Oranı (%):</label>
                <input type="number" step="0.01" id="discount" name="discount" value="<?php echo htmlspecialchars($coupon['discount']); ?>" required>
                <label for="usage_limit">Kullanım Limiti:</label>
                <input type="number" id="usage_limit" name="usage_limit" value="<?php echo htmlspecialchars($coupon['usage_limit']); ?>" required>
                <label for="expire_date">Son Kullanma Tarihi:</label>
                <input type="date" id="expire_date" name="expire_date" value="<?php echo htmlspecialchars($coupon['expire_date']); ?>" required>

                <button type="submit">Güncelle</button>
            </form>
        </div>
    </div>
</div>
<?php
    require 'footer.php';
?>