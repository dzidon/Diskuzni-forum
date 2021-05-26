<?php
#region zacatek
//hlavni konfiguracni soubor celeho webu
require_once "inc/appConfig.php";

//nacteni pripojeni k databazi
require_once "inc/db.php";

//spusteni session a verifikace uzivatele + zjisteni informaci o nem
require_once "inc/userVerify.php";

//jestlize uz je uzivatel prihlaseny, nema cenu, aby na tuto stranku chodil, bude presmerovan na index
require_once "inc/userLoginForbidden.php";

//validace uzivatelskeho vstupu
$errors = array();
$warningClass = 'input-error';
if(!empty($_POST)) {
    $formEmail = trim(@$_POST['email']);
    $emailQuery=$db->prepare('SELECT * FROM '.$configDatabaseTableUsers.' WHERE email=:email LIMIT 1;');
    $emailQuery->execute([
        ':email' => $formEmail
    ]);

    if ($emailQuery->rowCount() > 0) { //zadaný e-mail někdo má
        $user = $emailQuery->fetch();
        $userID = $user['user_id'];
        $name = $user['name'];

        $passwordQuery=$db->prepare('SELECT * FROM '.$configDatabaseTableForgottenPass.' WHERE user_id=:user_id ORDER BY created DESC LIMIT 1;');
        $passwordQuery->execute([
            ':user_id' => $userID
        ]);

        if ($passwordQuery->rowCount() > 0) {
            $request = $passwordQuery->fetch();
            $futureTime = strtotime($request['created'])+$configUserPasswordInterval;
            if(time() < $futureTime) { //v posledních 10 minutách někdo na daný účet už poslal požadavek na obnovu hesla
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

                $errors['email'] = 'Další e-mail na obnovu hesla půjde odeslat za '.$timeString;
            }
        }
    }
    else $errors['email'] = 'Nepodařilo se odeslat e-mail.';

    if(empty($errors)) {
        $code = bin2hex(random_bytes(15));
        $newRequestQuery=$db->prepare('INSERT INTO '.$configDatabaseTableForgottenPass.' (user_id, code) VALUES (:user_id, :code);');
        $newRequestQuery->execute([
            ':user_id' => $userID,
            ':code' => $code
        ]);

        //email
        $link = $configPasswordResetURL.'?uid='.$userID.'&code='.$code;
        $to = $formEmail;
        $subject = $configPasswordResetMailSubject;
        $message = $configPasswordResetMail1.htmlspecialchars($name).$configPasswordResetMail2.'<a href="'.$link.'">zde</a>'.$configPasswordResetMail3;
        $headers = "MIME-Version: 1.0"."\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8"."\r\n";
        $headers .= 'From: '.$configPasswordResetMailFrom."\r\n";
        mail($to,$subject,$message,$headers);

        $errors['email'] = 'E-mail na obnovu hesla odeslán do schránky <strong>'.htmlspecialchars($formEmail).'</strong>';
        $warningClass = 'input-success';
        unset($formEmail);
    }
}

//nastaveni title a nacteni headeru
$pageTitle = 'Obnova hesla';
include "inc/html/header.php";
#endregion zacatek


echo '<div class="main-wrap main-wrap-small">
            <h1>Obnova hesla</h1>
            <form action="" method="post">
                <label for="email">E-mail:</label><br>
                <input type="email" id="email" name="email" class="user-input" size="40" value="'.htmlspecialchars(@$formEmail).'">
                '.(!empty($errors['email'])?'<div class="'.$warningClass.'">'.$errors['email'].'</div>':'<br>').'
                <br>
                <div class="buttons-wrap buttons-center">
                    <input type="submit" value="Odeslat" class="button-primary">
                    <a href="login.php" class="button-secondary">Přihlášení</a>
                </div>
            </form>
        </div>';


#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec