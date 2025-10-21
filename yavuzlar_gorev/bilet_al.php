<?php
require 'config.php';
require 'check_role.php';

check_role(['user']);

if (!isset($_GET['trip_id']) || empty($_GET['trip_id'])) {
    header("Location: index.php");
    exit();
}
$trip_id = $_GET['trip_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_seats = isset($_POST['seats']) ? $_POST['seats'] : [];
    $coupon_code = strtoupper(trim($_POST['coupon_code']));

    if (empty($selected_seats)) {
        $_SESSION['flash_message'] = "HATA: Lütfen en az bir koltuk seçin.";
    } else {
        $db->beginTransaction(); 
        try {
            $stmt_price = $db->prepare("SELECT price, company_id FROM Trips WHERE id = :trip_id");
            $stmt_price->execute([':trip_id' => $trip_id]);
            $trip_data = $stmt_price->fetch();
            
            $total_price = count($selected_seats) * $trip_data['price'];
            $final_price = $total_price;

            if (!empty($coupon_code)) {
                $stmt_coupon = $db->prepare("SELECT * FROM Coupons WHERE code = :code AND expire_date >= date('now')");
                $stmt_coupon->execute([':code' => $coupon_code]);
                $coupon = $stmt_coupon->fetch();

                if ($coupon && ($coupon['company_id'] == null || $coupon['company_id'] == $trip_data['company_id'])) {
                    $final_price = $total_price - (($total_price * $coupon['discount']) / 100);
                } else {
                    throw new Exception("Geçersiz veya süresi dolmuş kupon kodu.");
                }
            }

            $user_id = $_SESSION['user_id'];
            $stmt_balance = $db->prepare("SELECT balance FROM User WHERE id = :id");
            $stmt_balance->execute([':id' => $user_id]);
            $user_balance = $stmt_balance->fetchColumn();

            if ($user_balance < $final_price) {
                throw new Exception("Yetersiz bakiye. Mevcut bakiyeniz: " . number_format($user_balance, 2) . " TL");
            }

            $ticket_id = uniqid('TCK');
            $stmt_ticket = $db->prepare("INSERT INTO Tickets (id, trip_id, user_id, total_price) VALUES (:id, :tid, :uid, :price)");
            $stmt_ticket->execute([':id' => $ticket_id, ':tid' => $trip_id, ':uid' => $user_id, ':price' => $final_price]);

            $stmt_seat = $db->prepare("INSERT INTO Booked_Seats (id, ticket_id, seat_number) VALUES (:id, :tid, :s_num)");
            foreach ($selected_seats as $seat) {
                $stmt_seat->execute([':id' => uniqid('BS'), ':tid' => $ticket_id, ':s_num' => (int)$seat]);
            }

            $new_balance = $user_balance - $final_price;
            $stmt_update_balance = $db->prepare("UPDATE User SET balance = :balance WHERE id = :id");
            $stmt_update_balance->execute([':balance' => $new_balance, ':id' => $user_id]);

            $db->commit(); 
            $_SESSION['flash_message'] = "Biletiniz başarıyla satın alındı!";
            header("Location: biletlerim.php"); 
            exit();

        } catch (Exception $e) {
            $db->rollBack(); 
            $_SESSION['flash_message'] = "HATA: " . $e->getMessage();
        }
    }
    header("Location: bilet_al.php?trip_id=" . $trip_id);
    exit();
}

$stmt_trip_details = $db->prepare("SELECT Trips.*, Bus_Company.name AS company_name FROM Trips JOIN Bus_Company ON Trips.company_id = Bus_Company.id WHERE Trips.id = :trip_id");
$stmt_trip_details->execute([':trip_id' => $trip_id]);
$trip = $stmt_trip_details->fetch();

if (!$trip) {
    die("Sefer bulunamadı.");
}

$stmt_booked_seats = $db->prepare("SELECT seat_number FROM Booked_Seats JOIN Tickets ON Booked_Seats.ticket_id = Tickets.id WHERE Tickets.trip_id = :trip_id AND Tickets.status = 'active'");
$stmt_booked_seats->execute([':trip_id' => $trip_id]);
$booked_seats_raw = $stmt_booked_seats->fetchAll(PDO::FETCH_COLUMN);

$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

require 'header.php';
?>

<div class="container">
    <nav class="page-nav">
        <a href="index.php">&larr; Sefer Aramaya Dön</a>
    </nav>
    <h1>Bilet Satın Al</h1>

    <?php if ($message): ?>
        <div class="message error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="trip-details form-section">
        <h3><?php echo htmlspecialchars($trip['company_name']); ?></h3>
        <p>
            <strong>Güzergah:</strong> <?php echo htmlspecialchars($trip['departure_city']); ?> &rarr; <?php echo htmlspecialchars($trip['destination_city']); ?>
        </p>
        <p>
            <strong>Tarih & Saat:</strong> <?php echo date('d.m.Y H:i', strtotime($trip['departure_time'])); ?>
        </p>
        <p><strong>Fiyat (Tek Koltuk):</strong> <?php echo htmlspecialchars($trip['price']); ?> TL</p>
    </div>

    <form action="bilet_al.php?trip_id=<?php echo $trip_id; ?>" method="POST">
        <h2>Koltuk Seçimi</h2>
        <div class="seat-map">
            <?php for ($i = 1; $i <= $trip['capacity']; $i++): ?>
                <?php
                if ($i > 1 && $i % 2 != 0 && ($i - 1) % 4 == 0) {
                    echo '<div class="seat-label"></div>'; 
                }
                $is_booked = in_array($i, $booked_seats_raw);
                ?>
                <div class="seat">
                    <input type="checkbox" name="seats[]" value="<?php echo $i; ?>" id="seat-<?php echo $i; ?>" <?php if($is_booked) echo 'disabled'; ?>>
                    <label for="seat-<?php echo $i; ?>"><?php echo $i; ?></label>
                </div>
            <?php endfor; ?>
        </div>
            
        <div class="form-section">
            <h2>Ödeme</h2>
            <label for="coupon_code">İndirim Kuponu:</label>
            <input type="text" name="coupon_code" id="coupon_code" placeholder="Varsa kupon kodunu girin">
            <button type="submit">Satın Al</button>
        </div>
    </form>
</div>

<?php
require 'footer.php';

?>
