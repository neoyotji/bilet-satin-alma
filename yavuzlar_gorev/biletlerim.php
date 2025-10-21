<?php
require 'config.php';
require 'check_role.php';

check_role(['user']);
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_ticket'])) {
    $ticket_id_to_cancel = $_POST['ticket_id'];
    
    $db->beginTransaction();
    try {
        $stmt = $db->prepare("SELECT Tickets.id, Tickets.total_price, Trips.departure_time FROM Tickets JOIN Trips ON Tickets.trip_id = Trips.id WHERE Tickets.id = :ticket_id AND Tickets.user_id = :user_id AND Tickets.status = 'active'");
        $stmt->execute([':ticket_id' => $ticket_id_to_cancel, ':user_id' => $user_id]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            throw new Exception("İptal edilecek aktif bilet bulunamadı veya bilet size ait değil.");
        }

        $departure_timestamp = strtotime($ticket['departure_time']);
        $current_timestamp = time();

        if (($departure_timestamp - $current_timestamp) / 3600 < 1) {
            throw new Exception("Seferin kalkışına 1 saatten az kaldığı için bilet iptal edilemez.");
        }
        
        $stmt = $db->prepare("UPDATE Tickets SET status = 'canceled' WHERE id = :ticket_id");
        $stmt->execute([':ticket_id' => $ticket_id_to_cancel]);

        $stmt = $db->prepare("UPDATE User SET balance = balance + :refund_amount WHERE id = :user_id");
        $stmt->execute([':refund_amount' => $ticket['total_price'], ':user_id' => $user_id]);

        $db->commit();
        $_SESSION['flash_message'] = "Biletiniz başarıyla iptal edildi ve ücret iadesi yapıldı.";

    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['flash_message'] = "HATA: " . $e->getMessage();
    }
    
    header("Location: biletlerim.php");
    exit();
}

$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

$stmt_tickets = $db->prepare("SELECT Tickets.id, Tickets.status, Tickets.total_price, Trips.departure_city, Trips.destination_city, Trips.departure_time, Bus_Company.name AS company_name, GROUP_CONCAT(Booked_Seats.seat_number) AS seat_numbers FROM Tickets JOIN Trips ON Tickets.trip_id = Trips.id JOIN Bus_Company ON Trips.company_id = Bus_Company.id LEFT JOIN Booked_Seats ON Booked_Seats.ticket_id = Tickets.id WHERE Tickets.user_id = :user_id GROUP BY Tickets.id ORDER BY Trips.departure_time DESC");
$stmt_tickets->execute([':user_id' => $user_id]);
$tickets = $stmt_tickets->fetchAll();

$stmt_balance = $db->prepare("SELECT balance FROM User WHERE id = :user_id");
$stmt_balance->execute([':user_id' => $user_id]);
$current_balance = $stmt_balance->fetchColumn();
require 'header.php';
?>

<div class="container">
    <nav class="page-nav">
        <a href="index.php">&larr; Ana Sayfa</a>
    </nav>
    <h1>Biletlerim ve Hesap Bilgileri</h1>

    <?php if ($message): ?>
        <div class="message <?php echo (strpos($message, 'HATA') === 0) ? 'error' : ''; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="balance-box form-section">
        <h2>Hesap Bakiyeniz</h2>
        <p class="balance-amount"><?php echo htmlspecialchars(number_format($current_balance, 2, ',', '.')); ?> TL</p>
        <a href="bakiye_yukle.php" class="btn-add-credit">+ Yükleme Yap</a>
    </div>
    <h2 style="margin-top: 2rem;">Geçmiş Biletleriniz</h2>
    <div class="tickets-list">
        <?php if (empty($tickets)): ?>
            <p>Henüz satın alınmış bir biletiniz bulunmuyor.</p>
        <?php else: ?>
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket-card status-<?php echo htmlspecialchars($ticket['status']); ?>">
                    <h3><?php echo htmlspecialchars($ticket['departure_city']); ?> &rarr; <?php echo htmlspecialchars($ticket['destination_city']); ?></h3>
                    <p><strong>Firma:</strong> <?php echo htmlspecialchars($ticket['company_name']); ?></p>
                    <p><strong>Kalkış Tarihi:</strong> <?php echo date('d.m.Y H:i', strtotime($ticket['departure_time'])); ?></p>
                    <p><strong>Koltuk No:</strong> <?php echo htmlspecialchars($ticket['seat_numbers']); ?></p>
                    <p><strong>Ödenen Tutar:</strong> <?php echo htmlspecialchars(number_format($ticket['total_price'], 2, ',', '.')); ?> TL</p>
                    <p><strong>Durum:</strong> <?php echo htmlspecialchars(ucfirst($ticket['status'])); ?></p>
                    
                    <div class="ticket-actions">
                        <?php if ($ticket['status'] == 'active'): ?>
                            <a href="download_ticket.php?ticket_id=<?php echo htmlspecialchars($ticket['id']); ?>" class="action-download">PDF İndir</a>
                        <?php endif; ?>
                        
                        <?php
                        $can_be_canceled = false;
                        if ($ticket['status'] == 'active' && strtotime($ticket['departure_time']) > (time() + 3600)) {
                            $can_be_canceled = true;
                        }
                        
                        if ($can_be_canceled):
                        ?>
                            <form action="biletlerim.php" method="POST" onsubmit="return confirm('Bu bileti iptal etmek istediğinizden emin misiniz? Ücret iadesi yapılacaktır.');">
                                <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket['id']); ?>">
                                <button type="submit" name="cancel_ticket">Bileti İptal Et</button>
                            </form>
                        <?php elseif ($ticket['status'] == 'active'): ?>
                            <span>İptal süresi doldu</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
require 'footer.php';
?>