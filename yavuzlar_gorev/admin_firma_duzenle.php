<?php
    require 'config.php';
    require 'check_role.php';

    check_role(['admin']);

    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header("Location: admin_firmalar.php");
        exit();
    }
    $companyId = $_GET['id'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $companyName = trim($_POST['company_name']);

        if (!empty($companyName)) {
            try {
                $stmt = $db->prepare("UPDATE Bus_Company SET name = :name WHERE id = :id");
                $stmt->execute([':name' => $companyName, ':id' => $companyId]);
                $_SESSION['flash_message'] = "Firma başarıyla güncellendi.";
                header("Location: admin_firmalar.php");
                exit();
            } catch (PDOException $e) {
                $_SESSION['flash_message'] = "HATA: " . $e->getMessage();
                header("Location: admin_firma_duzenle.php?id=" . $companyId);
                exit();
            }
        }
    }

    try {
        $stmt = $db->prepare("SELECT * FROM Bus_Company WHERE id = :id");
        $stmt->bindParam(':id', $companyId);
        $stmt->execute();
        $company = $stmt->fetch();

        if (!$company) {
            $_SESSION['flash_message'] = "Firma bulunamadı.";
            header("Location: admin_firmalar.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Veritabanı hatası: " . $e->getMessage());
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
        <a href="admin_firmalar.php">&larr; Firma Listesine Dön</a>
    </nav>
    
    <h1>Firma Düzenle</h1>
        
    <?php if ($message): ?>
        <div class="message error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-section">
        <form action="admin_firma_duzenle.php?id=<?php echo htmlspecialchars($company['id']); ?>" method="POST">
            <label for="company_name">Firma Adı:</label>
            <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($company['name']); ?>" required>
            <button type="submit">Güncelle</button>
        </form>
    </div>
</div>

<?php
    require 'footer.php';

?>
