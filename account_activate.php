<?php

#region zacatek
//hlavni konfiguracni soubor celeho webu
require_once "inc/appConfig.php";

//nacteni pripojeni k databazi
require_once "inc/db.php";

//spusteni session a verifikace uzivatele + zjisteni informaci o nem
require_once "inc/userVerify.php";

//nastaveni title a nacteni headeru
$pageTitle = 'Aktivace účtu';
include "inc/html/header.php";
#endregion zacatek


//zpracujeme get, pokud je uzivatel prihlaseny, pokud neni prihlaseny, zobrazime chybu
if(isset($_SESSION['user_id'])) { //je prihlaseny
    if(isset($_GET['uid']) && isset($_GET['code'])) {
        if(!$userActivated) {
            if($_GET['uid'] === $_SESSION['user_id'] && $_GET['code'] === $userActivationCode) {
                //aktualizujeme uzivatelovu promennou "activated"
                $activationQuery=$db->prepare('UPDATE '.$configDatabaseTableUsers.' SET activated=1 WHERE user_id=:user_id LIMIT 1;');
                $activationQuery->execute([
                    ':user_id' => $_SESSION['user_id']
                ]);

                //zobrazime zpravu
                echo '<div class="main-wrap">
                        <h1>Účet úspěšně aktivován!</h1>
                        Váš účet byl uspěšně aktivován, nyní můžete začít používat fórum naplno! <a href="index.php" class="text-link">Přejít na hlavní stránku</a>.
                      </div>';
            }
            else {
                echo '<div class="main-wrap">
                        <h1>Chyba</h1>
                        Váš účet se nepodařilo aktivovat.
                      </div>';
            }
        }
        else {
            echo '<div class="main-wrap">
                    <h1>Upozornění</h1>
                    Váš účet už je aktivován.
                  </div>';
        }
    }
    else {
        echo '<div class="main-wrap">
                <h1>Chyba</h1>
                V odkazu něco chybí.
              </div>';
    }
}
else { //neni prihlaseny
    echo '<div class="main-wrap">
            <h1>Nejste přihlášen</h1>
            Nejdříve se <a href="login.php" class="text-link">přihlašte</a> a poté klikněte na aktivační odkaz.
          </div>';
}


#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec