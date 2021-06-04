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

//musi byt admin
if($userRole !== $configRoleAdmin) {
    header("Location: index.php");
    exit();
}

//validace vstupu
$errors = array();
if(isset($_POST['name'])) {
    $sectionName = trim($_POST['name']);
    if(mb_strlen($sectionName,'UTF-8') >= $configSectionMinNameLen && mb_strlen($sectionName,'UTF-8') <= $configSectionMaxNameLen) {
        $nameQuery=$db->prepare('SELECT * FROM '.$configDatabaseTableSections.' WHERE name=:name LIMIT 1;');
        $nameQuery->execute([
            ':name'=>$sectionName
        ]);

        if ($nameQuery->rowCount() == 0) {
            $newSectionQuery=$db->prepare('INSERT INTO '.$configDatabaseTableSections.' (name, priority) VALUES (:name, 0);');
            $newSectionQuery->execute([
                ':name' => $sectionName
            ]);

            header("Location: index.php");
            exit();
        }
        else {
            $errors['name'] = 'Sekce s tímto názvem už existuje.';
        }
    }
    else {
        $errors['name'] = 'Délka názvu sekce musí být mezi '.$configSectionMinNameLen.' a '.$configSectionMaxNameLen.' znaky.';
    }
}

//nastaveni title a nacteni headeru
$pageTitle = 'Nová sekce';
include "inc/html/header.php";
#endregion zacatek


echo '<div class="main-wrap main-wrap-small">
            <h1>Vytvořit sekci</h1>
            <form action="" method="post">
                <label for="name">Zadejte název sekce:</label><br>
                <input type="text" id="name" name="name" class="user-input" size="40">
                '.(!empty($errors['name'])?'<div class="input-error">'.$errors['name'].'</div>':'<br>').'
                <br>
                <div class="buttons-wrap buttons-center">
                    <input type="submit" value="Vytvořit" class="button-primary">
                    <a href="index.php" class="button-secondary">Zpět</a>
                </div>
            </form>
      </div>';


#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec