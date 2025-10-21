<?php
    require 'config.php';
    require 'check_role.php';
    require 'header.php';

    check_role(['firma_admin']);

    $company_name = '';
    if (!empty($_SESSION['user_company_id'])) {
        $stmt = $db->prepare("SELECT name FROM Bus_Company WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['user_company_id']]);
        $company = $stmt->fetch();
        if ($company) {
            $company_name = $company['name'];
        }
    }
?>
<?php
    require 'footer.php';
?>
<div class="container">
    <meta charset="UTF-8">
    <title>Firma Admin Paneli</title>
    <link rel="stylesheet" href="style.css">
    <div class="container">
        <h1>Firma Admin Paneli</h1>
        <h2><?php echo htmlspecialchars($company_name); ?></h2>
        <p>Hoş geldiniz, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
        <nav>
            <a href="firma_admin_seferler.php">Sefer Yönetimi</a>
            <a href="firma_admin_kuponlar.php">Firma Kupon Yönetimi</a>
            </nav>

        <p>Firmanıza ait seferleri ve kuponları yönetmek için yukarıdaki menüyü kullanabilirsiniz.</p>

        <br>
        <a href="logout.php">Çıkış Yap</a>
    </div>
</div>
