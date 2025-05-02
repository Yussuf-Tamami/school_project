<?php
    session_unset();
    session_destroy();
    header('Location: ../intro_Page/intro_Page.php');
    exit;
?>