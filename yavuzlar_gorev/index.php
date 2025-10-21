<?php
require 'config.php';
require 'header.php';

$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

$my_active_tickets = [];
$searched_trips = [];
$departureCity = $_GET['departure_city'] ?? '';
$destinationCity = $_GET['destination_city'] ?? '';

if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'user') {

    $stmt_my_tickets = $db->prepare(
        "SELECT 
            Tickets.id, Tickets.status,
            Trips.departure_city, Trips.destination_city, Trips.departure_time,
            Bus_Company.name AS company_name,
            GROUP_CONCAT(Booked_Seats.seat_number) AS seat_numbers
         FROM Tickets
         JOIN Trips ON Tickets.trip_id = Trips.id
         JOIN Bus_Company ON Trips.company_id = Bus_Company.id
         LEFT JOIN Booked_Seats ON Booked_Seats.ticket_id = Tickets.id
         WHERE Tickets.user_id = :user_id AND Tickets.status = 'active'
         GROUP BY Tickets.id
         ORDER BY Trips.departure_time ASC"
    );
    $stmt_my_tickets->execute([':user_id' => $_SESSION['user_id']]);
    $my_active_tickets = $stmt_my_tickets->fetchAll();
}

if (!empty($departureCity) && !empty($destinationCity)) {
    $stmt_search = $db->prepare(
        "SELECT Trips.*, Bus_Company.name as company_name 
         FROM Trips 
         JOIN Bus_Company ON Trips.company_id = Bus_Company.id 
         WHERE departure_city LIKE :dep_city AND destination_city LIKE :dest_city
         ORDER BY departure_time ASC"
    );
    $stmt_search->bindValue(':dep_city', '%' . $departureCity . '%');
    $stmt_search->bindValue(':dest_city', '%' . $destinationCity . '%');
    $stmt_search->execute();
    $searched_trips = $stmt_search->fetchAll();
}
?>

<div class="container">
    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'user'): ?>
        <div class="welcome-section">
            <h2>Yaklaşan Aktif Biletleriniz</h2>
            <?php if (empty($my_active_tickets)): ?>
                <p>Yaklaşan aktif bir biletiniz bulunmuyor. Hemen yeni bir sefer arayarak biletinizi alın!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Firma</th>
                            <th>Güzergah</th>
                            <th>Kalkış</th>
                            <th>Koltuk No</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my_active_tickets as $ticket): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ticket['company_name']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['departure_city']); ?> &rarr; <?php echo htmlspecialchars($ticket['destination_city']); ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($ticket['departure_time'])); ?></td>
                                <td><strong><?php echo htmlspecialchars($ticket['seat_numbers']); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p style="text-align: right; margin-top: 1rem;"><a href="biletlerim.php">Tüm Biletlerimi Görüntüle &rarr;</a></p>
            <?php endif; ?>
        </div>
        <hr class="section-divider">
    <?php endif; ?>

    <div class="form-section">
        <h1>Yeni Sefer Ara</h1>
        <form action="index.php" method="GET">
            <label for="departure_city">Kalkış Şehri:</label>
            <input type="text" id="departure_city" name="departure_city" value="<?= htmlspecialchars($departureCity) ?>" required>
            
            <label for="destination_city">Varış Şehri:</label>
            <input type="text" id="destination_city" name="destination_city" value="<?= htmlspecialchars($destinationCity) ?>" required>
            
            <button type="submit">Seferleri Bul</button>
        </form>
    </div>

    <?php if (!empty($departureCity)):  ?>
        <h2 style="margin-top: 2rem;">Arama Sonuçları</h2>
        <?php if (empty($searched_trips)): ?>
            <p>Aradığınız kriterlere uygun sefer bulunamadı.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Firma</th>
                        <th>Güzergah</th>
                        <th>Kalkış Saati</th>
                        <th>Fiyat</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($searched_trips as $trip): ?>
                        <tr>
                            <td><?= htmlspecialchars($trip['company_name']) ?></td>
                            <td><?= htmlspecialchars($trip['departure_city']) ?> &rarr; <?= htmlspecialchars($trip['destination_city']) ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($trip['departure_time'])) ?></td>
                            <td><?= htmlspecialchars($trip['price']) ?> TL</td>
                            <td>
                                <a href="bilet_al.php?trip_id=<?= htmlspecialchars($trip['id']) ?>" class="btn btn-primary">Bilet Al</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
require 'footer.php'; 

?>
