<?php
    function check_role($allowed_roles) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }
        
        if (!in_array($_SESSION['user_role'], $allowed_roles)) {
            header("Location: unauthorized.php");
            exit();
        }
    }
?>