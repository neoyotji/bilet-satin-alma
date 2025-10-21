<?php
    require 'config.php';
    require 'check_role.php';
    check_role(['admin']);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_company'])) {
        $companyName = trim($_POST['company_name']);
        
        if (!empty($companyName)) {
            try {
                $id = uniqid('C');
                $stmt = $db->prepare("INSERT INTO Bus_Company (id, name) VALUES (:id, :name)");
                $stmt->execute([':id' => $id, ':name' => $companyName]);
                $_SESSION['flash_message'] = "Firma başarıyla eklendi.";
            } catch (PDOException $e) {
                $_SESSION['flash_message'] = "HATA: Bu firma adı zaten mevcut olabilir.";
            }
        } else {
            $_SESSION['flash_message'] = "HATA: Firma adı boş olamaz.";
        }
        header("Location: admin_firmalar.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_company'])) {
        $companyId = $_POST['company_id'];
        try {
            $stmt = $db->prepare("DELETE FROM Bus_Company WHERE id = :id");
            $stmt->bindParam(':id', $companyId); 
            $stmt->execute();
            $_SESSION['flash_message'] = "Firma başarıyla silindi.";
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "HATA: Firma silinemedi. Bu firmaya ait seferler veya kullanıcılar olabilir.";
        }
        header("Location: admin_firmalar.php");
        exit();
    }

    $message = '';
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }
    $companies = $db->query("SELECT * FROM Bus_Company ORDER BY name")->fetchAll();

    require 'header.php';
?>

<div class="container">
    <nav class="page-nav">
        <a href="admin_panel.php">&larr; Admin Paneline Dön</a>
    </nav>

    <h1>Firma Yönetimi</h1>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-section">
        <h2>Yeni Firma Ekle</h2>
        <form action="admin_firmalar.php" method="POST">
            <label for="company_name">Firma Adı:</label>
            <input type="text" id="company_name" name="company_name" required>
            <button type="submit" name="add_company">Ekle</button>
        </form>
    </div>

    <h2>Mevcut Firmalar</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Firma Adı</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($companies)): ?>
                <tr>
                    <td colspan="3">Henüz eklenmiş bir firma bulunmuyor.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($companies as $company): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($company['id']); ?></td>
                        <td><?php echo htmlspecialchars($company['name']); ?></td>
                        <td class="action-links">
                            <a href="admin_firma_duzenle.php?id=<?php echo htmlspecialchars($company['id']); ?>">Düzenle</a>
                            <form action="admin_firmalar.php" method="POST" style="display:inline;" onsubmit="return confirm('Bu firmayı silmek istediğinizden emin misiniz?');">
                                <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($company['id']); ?>">
                                <button type="submit" name="delete_company">Sil</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
    require 'footer.php';
?>