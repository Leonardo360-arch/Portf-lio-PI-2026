<?php

session_start();

unset($_SESSION['cliente_id'], $_SESSION['cliente_nome'], $_SESSION['cliente_email']);

header('Location: login-cliente.php');
exit;
