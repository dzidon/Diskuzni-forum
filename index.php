<?php

#region zacatek
//hlavni konfiguracni soubor celeho webu
require_once "inc/appConfig.php";

//nacteni pripojeni k databazi
require_once "inc/db.php";

//spusteni session a verifikace uzivatele + zjisteni informaci o nem
require_once "inc/userVerify.php";

//nastaveni title a nacteni headeru
$pageTitle = 'Domů';
include "inc/html/header.php";
#endregion zacatek


//TODO: stred stranky
echo '<div class="main-wrap">
        <h1>TODO: Všechny sekce</h1>
      </div>';


#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec