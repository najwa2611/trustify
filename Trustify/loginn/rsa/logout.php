<?php
session_start();
session_destroy();
header('Location: rsa.php');
exit();