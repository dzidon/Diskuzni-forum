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


echo '<div class="main-wrap">
          <h1>Všechny sekce</h1>
          <!-- Vyhledávání -->
          <div class="search-wrap">
            <i class="fas fa-search search-icon"></i>
            <form class="search-form" action="search.php" method="get">
                <input type="text" class="search-bar" id="word" name="word" placeholder="Hledat příspěvky, uživatele" autocomplete="off">
            </form>
          </div>
      </div>';


#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec