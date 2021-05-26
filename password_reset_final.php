<?php

#region zacatek
//hlavni konfiguracni soubor celeho webu
require_once "inc/appConfig.php";

//nacteni pripojeni k databazi
require_once "inc/db.php";

//spusteni session a verifikace uzivatele + zjisteni informaci o nem
require_once "inc/userVerify.php";

//nastaveni title a nacteni headeru
$pageTitle = 'Obnova hesla';
include "inc/html/header.php";
#endregion zacatek


if (!isset($_SESSION['user_id'])) { //neni prihlaseny
    $receivedData = 'NONE';
    if(isset($_GET['uid']) && isset($_GET['code'])) {
        $receivedData = 'GET';
        $userID = $_GET['uid'];
        $code = $_GET['code'];
    }
    if(isset($_POST['uid']) && isset($_POST['code'])) {
        $receivedData = 'POST';
        $userID = $_POST['uid'];
        $code = $_POST['code'];
    }

    if ($receivedData !== 'NONE') {
        $checkRequestQuery=$db->prepare('SELECT * FROM '.$configDatabaseTableForgottenPass.' WHERE user_id=:user_id AND code=:code LIMIT 1;');
        $checkRequestQuery->execute([
            ':user_id' => $userID,
            ':code' => $code
        ]);

        if ($checkRequestQuery->rowCount() > 0) {
            $request = $checkRequestQuery->fetch();
            $secondsDifference = time()-strtotime($request['created']);
            if($secondsDifference <= 3600) {
                $errors = array();
                $passwordChanged = false;
                if($receivedData === 'POST') {
                    $formPassword = @$_POST['password'];
                    $formPassword2 = @$_POST['password2'];

                    //kontrola hesel
                    if(mb_strlen($formPassword,'UTF-8') >= $configUserMinPassLen && mb_strlen($formPassword,'UTF-8') <= $configUserMaxPassLen) {
                        if(strpos($formPassword, ' ') === false) {
                            if($formPassword !== $formPassword2) {
                                $errors['password2'] = 'Zadaná hesla se neshodují.';
                            }
                        }
                        else $errors['password'] = 'Heslo nesmí obsahovat mezery.';
                    }
                    else $errors['password'] = 'Délka hesla musí být mezi '.$configUserMinPassLen.' a '.$configUserMaxPassLen.' znaky.';

                    //vse v poradku, zmena hesla
                    if(empty($errors)) {
                        $hashedPassword = password_hash($formPassword, PASSWORD_DEFAULT);

                        //update hesla
                        $resetQuery=$db->prepare('UPDATE '.$configDatabaseTableUsers.' SET password=:password WHERE user_id=:user_id LIMIT 1;');
                        $resetQuery->execute([
                            ':user_id' => $userID,
                            ':password' => $hashedPassword
                        ]);

                        //smazeme vsechny pozadavky na zmenu hesla daneho uzivatele
                        $removeRequestsQuery=$db->prepare('DELETE FROM '.$configDatabaseTableForgottenPass.' WHERE user_id=:user_id;');
                        $removeRequestsQuery->execute([
                            ':user_id' => $userID
                        ]);

                        $passwordChanged = true;
                    }
                }

                if($passwordChanged) {
                    echo '<div class="main-wrap">
                            <h1>Heslo úspěšně změněno!</h1>
                            Heslo u Vašeho účtu bylo změněno, nyní se můžete <a href="login.php" class="text-link">přihlásit</a>.
                          </div>';
                }
                else {
                    echo '<div class="main-wrap main-wrap-small">
                        <h1>Obnova hesla</h1>
                        <form action="" method="post">
                            <input type="hidden" id="uid" name="uid" value="'.htmlspecialchars(@$userID).'">
                            <input type="hidden" id="code" name="code" value="'.htmlspecialchars(@$code).'">
                            <label for="password">Nové heslo:</label><br>
                            <input type="password" id="password" name="password" class="user-input" size="40">
                            '.(!empty($errors['password'])?'<div class="input-error">'.$errors['password'].'</div>':'<br>').'
                            <br>
                            <label for="password2">Nové heslo znovu:</label><br>
                            <input type="password" id="password2" name="password2" class="user-input" size="40">
                            '.(!empty($errors['password2'])?'<div class="input-error">'.$errors['password2'].'</div>':'<br>').'
                            <br>
                            <div class="buttons-wrap buttons-center">
                                <input type="submit" value="Nastavit" class="button-primary">
                            </div>
                        </form>
                    </div>';
                }
            }
            else {
                echo '<div class="main-wrap">
                        <h1>Chyba</h1>
                        Tento požadavek na obnovu hesla už vypršel.
                      </div>';
            }
        }
        else {
            echo '<div class="main-wrap">
                <h1>Chyba</h1>
                Požadavek na obnovu hesla nenalezen.
              </div>';
        }
    }
    else {
        echo '<div class="main-wrap">
                <h1>Chyba</h1>
                Nepodařilo se načíst formulář pro obnovu hesla.
              </div>';
    }
}
else { //je prihlaseny
    echo '<div class="main-wrap">
            <h1>Jste přihlášen</h1>
            Pro obnovení hesla nesmíte být přihlášen.
          </div>';
}


#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec