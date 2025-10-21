<?php
    require 'config.php';
    require 'check_role.php';
    require 'header.php';

    check_role(['firma_admin']);

?>
<?php
    require 'footer.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Firma Admin Paneli</title>
    </head>
    <body>
        <h1>Firma Admin Paneli</h1>
        <p>Seferlerinizi burada yönetebilirsiniz.</p>
    </body>
</html>