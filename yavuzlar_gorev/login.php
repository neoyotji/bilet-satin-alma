<?php
require 'config.php'; 
if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 5) {
    die("Çok fazla hatalı giriş denemesi. Lütfen 5 dakika sonra tekrar deneyin.");
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $db->prepare("SELECT * FROM User WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            unset($_SESSION['login_attempts']);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_company_id'] = $user['company_id'];
            session_regenerate_id(true);
            $_SESSION['flash_message'] = "Giriş başarılı! Hoş geldiniz, " . htmlspecialchars($user['full_name']) . "!";
            header("Location: index.php");
            exit();
        } else {
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 0;
            }
            $_SESSION['login_attempts']++;
            $_SESSION['flash_message'] = "E-posta veya şifre hatalı.";
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Bir veritabanı hatası oluştu: " . $e->getMessage());
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
    <?php if ($message): ?>
        <div class="message error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form action="login.php" method="POST" class="form-section">
        <h2>Giriş Yap</h2>
        <label for="email">E-posta:</label>
        <input type="email" id="email" name="email" required>
        <label for="password">Şifre:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Giriş Yap</button>
    </form>
</div>

<?php
require 'footer.php';
?>