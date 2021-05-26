<?php

#region zacatek
//hlavni konfiguracni soubor celeho webu
require_once "inc/appConfig.php";

//nacteni pripojeni k databazi
require_once "inc/db.php";

//spusteni session a verifikace uzivatele + zjisteni informaci o nem
require_once "inc/userVerify.php";

//jestlize neni uzivatel prihlaseny, nema cenu, aby na tuto stranku chodil, bude presmerovan na index
require_once "inc/userLoginRequired.php";

//jestlize je uzivateluv ucet aktivovany, presmerujeme ho na index
if($userActivated) {
    header("Location: index.php");
    exit();
}

//zpracovani postu
if(isset($_POST['send_email'])) {
    if($_POST['send_email'] === "yes") {
        $futureTime = strtotime($userActivationLastSent)+$configUserActivationInterval;
        if(time() >= $futureTime) {
            //aktualizujeme uzivatelovu promennou "activation_last_sent"
            $timestampQuery=$db->prepare('UPDATE '.$configDatabaseTableUsers.' SET activation_last_sent=:activation_last_sent WHERE user_id=:user_id;');
            $timestampQuery->execute([
                ':activation_last_sent' => date('Y-m-d H:i:s'),
                ':user_id' => $_SESSION['user_id']
            ]);

            //email
            $link = $configAccountActivationURL.'?uid='.$_SESSION['user_id'].'&code='.$userActivationCode;
            $to = $userEmail;
            $subject = $configAccountActivationMailSubject;
            $message = $configAccountActivationMail1.htmlspecialchars($userName).$configAccountActivationMail2.'<a href="'.$link.'">zde</a>'.$configAccountActivationMail3;
            $headers = "MIME-Version: 1.0"."\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8"."\r\n";
            $headers .= 'From: '.$configAccountActivationMailFrom."\r\n";
            mail($to,$subject,$message,$headers);

            header("Location: account_not_activated.php");
            exit();
        }
    }
}

//nastaveni title a nacteni headeru
$pageTitle = 'Aktivujte si účet';
include "inc/html/header.php";
#endregion zacatek


echo '<div class="main-wrap">
            <h1>Aktivujte svůj účet</h1>
            Pro plné používání fóra budete muset aktivovat svůj účet. Přihlaste se na svůj e-mail <strong>'.htmlspecialchars($userEmail).'</strong> a klikněte na odkaz, který jsme Vám poslali. Pokud e-mail nevidíte, podívejte se do spamu. Jednou za '.($configUserActivationInterval/60).' minut můžete aktivační e-mail poslat znovu kliknutím na tlačítko dole.';
#region zobrazeni tlacitka/zpravy
$futureTime = strtotime($userActivationLastSent)+$configUserActivationInterval;
if(time() >= $futureTime) {
    echo '<form action="" method="post" class="form-new-activation-email">
                <input type="hidden" id="send_email" name="send_email" value="yes">
                <div class="buttons-wrap">
                    <input type="submit" value="Poslat nový e-mail" class="button-primary">
                </div>
          </form>';
}
else {
    $timeString = '';
    $secondsDifference = $futureTime-time();
    if($secondsDifference >= 60) {
        $minutesDisplayed = floor($secondsDifference/60);
        $secondsDisplayed = $secondsDifference%60;
        $timeString = sprintf('%02d', $minutesDisplayed).'m:'.sprintf('%02d', $secondsDisplayed).'s';
    }
    else {
        $timeString = '00m:'.sprintf('%02d', $secondsDifference).'s';
    }

    echo '<div class="input-warning">Email odeslán, další půjde poslat za '.$timeString.'</div>';
}
#endregion zobrazeni tlacitka/zpravy
echo '</div>';


#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec