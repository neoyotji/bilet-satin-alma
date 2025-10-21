<?php
require 'config.php';
require 'check_role.php';
check_role(['user']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_balance'])) {
    $amount_to_add = $_POST['amount'];

    if (is_numeric($amount_to_add) && $amount_to_add > 0) {
        try {
            $stmt = $db->prepare("UPDATE User SET balance = balance + :amount WHERE id = :user_id");
            $stmt->execute([
                ':amount' => $amount_to_add,
                ':user_id' => $_SESSION['user_id']
            ]);
            $_SESSION['flash_message'] = htmlspecialchars($amount_to_add) . " TL başarıyla hesabınıza eklendi.";
            header("Location: biletlerim.php"); 
            exit();
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "HATA: Bakiye güncellenirken bir sorun oluştu.";
            header("Location: bakiye_yukle.php");
            exit();
        }
    } else {
        $_SESSION['flash_message'] = "HATA: Lütfen geçerli bir tutar girin.";
        header("Location: bakiye_yukle.php");
        exit();
    }
}

$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

require 'header.php';
?>

<div class="container">
    <nav class="page-nav">
        <a href="biletlerim.php">&larr; Biletlerim Sayfasına Dön</a>
    </nav>
    <h1>Hesaba Bakiye Yükle</h1>

    <?php if ($message): ?>
        <div class="message error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-section">
        <h2>Yüklemek İstediğiniz Tutar</h2>
        <form action="bakiye_yukle.php" method="POST">
            <label for="amount">Tutar (TL):</label>
            <input type="number" id="amount" name="amount" step="10" min="10" placeholder="Örn: 100" required>
            <button type="submit" name="add_balance">Yüklemeyi Onayla</button>
        </form>
    </div>
</div>

<?php
require 'footer.php';

?>
