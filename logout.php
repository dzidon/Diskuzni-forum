<?php

#region zacatek
//hlavni konfiguracni soubor celeho webu
require_once "inc/appConfig.php";

//nacteni pripojeni k databazi
require_once "inc/db.php";

//spusteni session a verifikace uzivatele + zjisteni informaci o nem
require_once "inc/userVerify.php";

//pokud je prihlaseny, odhlasime ho
if(isset($_SESSION['user_id'])) {
    unset($_SESSION['user_id']);
}

if(isset($_SESSION['last_active'])) {
    unset($_SESSION['last_active']);
}
#endregion zacatek

//ve vsech pripadech presmerujeme uzivatele na index
header("Location: index.php");