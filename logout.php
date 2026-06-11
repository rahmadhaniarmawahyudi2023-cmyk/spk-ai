<?php
session_start();
setcookie('notif_toast', 'logout', time()+10, '/');
session_destroy();
header("Location: index.php");
exit;
?>