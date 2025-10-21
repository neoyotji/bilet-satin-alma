<?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
?>
<!DOCTYPE html>
<html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bilet Satın Alma Platformu</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header class="main-header">
            <div class="container">
                <a href="index.php" class="logo">BiletAl.com</a>
                <nav class="main-nav">
                    <ul>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <li><a href="admin_panel.php">Admin Paneli</a></li>
                            <?php elseif ($_SESSION['user_role'] === 'firma_admin'): ?>
                                <li><a href="firma_admin_panel.php">Firma Paneli</a></li>
                            <?php else: ?>
                                <li><a href="biletlerim.php">Biletlerim</a></li>
                            <?php endif; ?>
                            
                            <li class="user-info"><span>Hoş geldiniz, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span></li>
                            <li><a href="logout.php" class="btn btn-secondary">Çıkış Yap</a></li>
                            
                        <?php else: ?>
                            <li><a href="login.php" class="btn btn-secondary">Giriş Yap</a></li>
                            <li><a href="register.php" class="btn btn-primary">Kayıt Ol</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </header>
    <main>