<?php
    require 'config.php';
    require 'check_role.php';
    require 'fpdf/fpdf.php';

    check_role(['user']);
    $user_id = $_SESSION['user_id'];

    if (!isset($_GET['ticket_id'])) {
        die("Bilet ID'si belirtilmedi.");
    }
    $ticket_id = $_GET['ticket_id'];

    $stmt = $db->prepare(
        "SELECT 
            Tickets.id, Tickets.total_price,
            Trips.departure_city, Trips.destination_city, Trips.departure_time,
            Bus_Company.name AS company_name,
            User.full_name AS passenger_name,
            GROUP_CONCAT(Booked_Seats.seat_number ORDER BY Booked_Seats.seat_number ASC) AS seat_numbers
         FROM Tickets
         JOIN Trips ON Tickets.trip_id = Trips.id
         JOIN Bus_Company ON Trips.company_id = Bus_Company.id
         JOIN User ON Tickets.user_id = User.id
         LEFT JOIN Booked_Seats ON Booked_Seats.ticket_id = Tickets.id
         WHERE Tickets.id = :ticket_id AND Tickets.user_id = :user_id AND Tickets.status = 'active'
         GROUP BY Tickets.id"
    );
    $stmt->execute([':ticket_id' => $ticket_id, ':user_id' => $user_id]);
    $ticket = $stmt->fetch();

    if (!$ticket) {
        die("Bilet bulunamadı veya bu bileti görüntüleme yetkiniz yok.");
    }

    function replace_tr_chars($text) {
        $search = array('ç', 'Ç', 'ğ', 'Ğ', 'ı', 'İ', 'ö', 'Ö', 'ş', 'Ş', 'ü', 'Ü');
        $replace = array('c', 'C', 'g', 'G', 'i', 'I', 'o', 'O', 's', 'S', 'u', 'U');
        return str_replace($search, $replace, $text);
    }

    //--- PDF Oluşturma ---
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    // Başlık (Türkçe karakterler dönüştürülmüş)
    $pdf->Cell(0, 10, replace_tr_chars('Yolcu Bilet Bilgisi'), 0, 1, 'C');
    $pdf->Ln(10);

    // Firma Adı
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, replace_tr_chars($ticket['company_name']), 0, 1, 'C');
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); 
    $pdf->Ln(10);

    // Bilet Detayları
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, replace_tr_chars('Yolcu Adi:'));
    $pdf->Cell(0, 10, replace_tr_chars($ticket['passenger_name']));
    $pdf->Ln();

    $pdf->Cell(40, 10, replace_tr_chars('Guzergah:'));
    $pdf->Cell(0, 10, replace_tr_chars($ticket['departure_city'] . ' -> ' . $ticket['destination_city']));
    $pdf->Ln();

    $pdf->Cell(40, 10, replace_tr_chars('Kalkis Tarihi:'));
    $pdf->Cell(0, 10, date('d.m.Y H:i', strtotime($ticket['departure_time'])));
    $pdf->Ln();

    $pdf->Cell(40, 10, replace_tr_chars('Koltuk No:'));
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, $ticket['seat_numbers']);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, replace_tr_chars('Odenen Tutar:'));
    $pdf->Cell(0, 10, number_format($ticket['total_price'], 2, ',', '.') . ' TL');
    $pdf->Ln(20);

    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, replace_tr_chars('Iyi yolculuklar dileriz!'), 0, 1, 'C');

    // PDF'i tarayıcıya gönder
    $pdf->Output('D', 'bilet_'. $ticket_id .'.pdf');
    exit; // PDF gönderildikten sonra başka bir işlem yapılmaması için script'i sonlandır.
?>