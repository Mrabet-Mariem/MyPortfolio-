
<?php
session_start();
session_destroy();

// Remove remember-me cookie
setcookie('admin_token', '', time() - 3600, '/', '', false, true);

header('Location: login.php');
exit;
?>
