<?php
    require 'config.php';
    require 'check_role.php';
    check_role(['admin']);
    require 'header.php';
?>

<div class="container">
    <h1>Admin Paneli</h1>
    <p>Hoş geldiniz, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
    
    <nav class="page-nav">
        <a href="admin_firmalar.php">Firma Yönetimi</a>
        <a href="admin_kullanicilar.php">Firma Admin Yönetimi</a>
        <a href="admin_kuponlar.php">Genel Kupon Yönetimi</a>
    </nav>

    <div class="form-section">
        <h2>Genel Bakış</h2>
        <p>Sistemi yönetmek için yukarıdaki menüyü kullanabilirsiniz.</p>
    </div>
</div>

<?php
    require 'footer.php';
?>