<?php
    require 'config.php';
    require 'check_role.php';
    require 'header.php';

    check_role(['firma_admin']);
    $company_id = $_SESSION['user_company_id'];

    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header("Location: firma_admin_kuponlar.php");
        exit();
    }
    $coupon_id = $_GET['id'];

    $stmt = $db->prepare("SELECT * FROM Coupons WHERE id = :id AND company_id = :cid");
    $stmt->execute([':id' => $coupon_id, ':cid' => $company_id]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        $_SESSION['flash_message'] = "HATA: Kupon bulunamadı veya bu kupona erişim yetkiniz yok.";
        header("Location: firma_admin_kuponlar.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            $stmt = $db->prepare(
                "UPDATE Coupons SET code = ?, discount = ?, usage_limit = ?, expire_date = ? 
                WHERE id = ? AND company_id = ?"
            );
            $stmt->execute([
                strtoupper(trim($_POST['code'])), trim($_POST['discount']),
                trim($_POST['usage_limit']), trim($_POST['expire_date']),
                $coupon_id, $company_id
            ]);
            $_SESSION['flash_message'] = "Kupon güncellendi.";
            header("Location: firma_admin_kuponlar.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "HATA: " . $e->getMessage();
        }
    }

    $message = '';
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }
?>
<?php
    require 'footer.php';
?>

<div class="container">
    <meta charset="UTF-8">
    <title>Firma Kuponu Düzenle</title>
    <link rel="stylesheet" href="style.css">
    <div class="container">
        <a href="firma_admin_kuponlar.php">&larr; Kupon Listesine Dön</a>
        <h1>Firma Kuponu Düzenle</h1>           
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <div class="form-section">
            <form action="firma_admin_kupon_duzenle.php?id=<?php echo $coupon['id']; ?>" method="POST">
                <input type="text" name="code" value="<?php echo htmlspecialchars($coupon['code']); ?>" required>
                <input type="number" step="0.01" name="discount" value="<?php echo htmlspecialchars($coupon['discount']); ?>" required>
                <input type="number" name="usage_limit" value="<?php echo htmlspecialchars($coupon['usage_limit']); ?>" required>
                <label>Son Kullanma Tarihi:</label>
                <input type="date" name="expire_date" value="<?php echo htmlspecialchars($coupon['expire_date']); ?>" required>
                <button type="submit">Güncelle</button>
            </form>
        </div>
    </div>
</div>
