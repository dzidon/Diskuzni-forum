<?php

//uzivatel neni prihlaseny, presmerujeme ho na homepage
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}