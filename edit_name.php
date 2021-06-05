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

//validace vstupu
$errors = array();
$inputContent = $userName;
if(isset($_POST['name'])) {
    $newName = trim($_POST['name']);
    if(mb_strlen($newName,'UTF-8') >= $configUserMinNameLen && mb_strlen($newName,'UTF-8') <= $configUserMaxNameLen) {
        if(strpos($newName, ' ') === false) {
            if(preg_match("/^\w+$/", $newName)) {
                $nameQuery=$db->prepare('SELECT * FROM '.$configDatabaseTableUsers.' WHERE name=:name LIMIT 1;');
                $nameQuery->execute([
                    ':name'=>$newName
                ]);

                $user = $nameQuery->fetch();
                if ($nameQuery->rowCount() > 0 && $user['user_id'] != $_SESSION['user_id']) {
                    $errors['name'] = 'Zadanou přezdívku už má někdo jiný.';
                    $inputContent = $newName;
                }
                else {
                    $updateQuery=$db->prepare('UPDATE '.$configDatabaseTableUsers.' SET name=:name WHERE user_id=:user_id LIMIT 1;');
                    $updateQuery->execute([
                        ':user_id' => $_SESSION['user_id'],
                        ':name' => $newName
                    ]);

                    header("Location: profile.php?user=".$newName);
                    exit();
                }
            }
            else {
                $errors['name'] = 'Přezdívka může obsahovat jen následující znaky: a-z A-Z 0-9 _';
                $inputContent = $newName;
            }
        }
        else {
            $errors['name'] = 'Přezdívka nesmí obsahovat mezery.';
            $inputContent = $newName;
        }
    }
    else {
        $errors['name'] = 'Délka přezdívky musí být mezi '.$configUserMinNameLen.' a '.$configUserMaxNameLen.' znaky.';
        $inputContent = $newName;
    }
}

//nastaveni title a nacteni headeru
$pageTitle = 'Úprava přezdívky';
include "inc/html/header.php";
#endregion zacatek


echo '<div class="main-wrap main-wrap-small">
            <h1>Upravit přezdívku</h1>
            <form action="" method="post">
                <label for="name">Zadejte novou přezdívku:</label><br>
                <input type="text" id="name" name="name" class="user-input" size="40" value="'.htmlspecialchars($inputContent).'">
                '.(!empty($errors['name'])?'<div class="input-error">'.$errors['name'].'</div>':'<br>').'
                <br>
                <div class="buttons-wrap buttons-center">
                    <input type="submit" value="Uložit" class="button-primary">
                    <a href="profile.php?user='.htmlspecialchars($userName).'" class="button-secondary">Zpět</a>
                </div>
            </form>
        </div>';


#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec