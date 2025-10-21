<?php
    require 'config.php';
    require 'header.php';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $fullName = $_POST['full_name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $id = uniqid('', true);
        try {
            $stmt = $db->prepare("INSERT INTO User (id, full_name, email, password, role) VALUES (:id, :full_name, :email, :password, 'user')");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':full_name', $fullName);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute();
            echo "Kayıt başarılı! Lütfen giriş yapın.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo "Bu e-posta adresi zaten kullanılıyor.";
            } else {
                echo "Bir hata oluştu: " . $e->getMessage();
            }
        }
    }
?>
<?php
    require 'footer.php';
?>

<div class="container">
    <title>Kayıt Ol</title>
    <form action="register.php" method="POST">
        <label for="full_name">Ad Soyad:</label><br>
        <input type="text" id="full_name" name="full_name" required><br>
        <label for="email">E-posta:</label><br>
        <input type="email" id="email" name="email" required><br>
                <label for="password">Şifre:</label><br>
        <input type="password" id="password" name="password" required><br>
        <button type="submit">Kayıt Ol</button>
    </form>
</div>