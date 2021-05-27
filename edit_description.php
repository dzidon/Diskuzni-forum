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
$textAreaContent = $userDescription;
if(isset($_POST['description'])) {
    $newDescription = trim($_POST['description']);
    if(mb_strlen($newDescription, 'UTF-8') <= $configUserMaxDescriptionLen) {
        $asSignature = 0;
        if(isset($_POST['signature'])) {
            if($_POST['signature'] === 'yes') {
                $asSignature = 1;
            }
        }

        $descriptionQuery=$db->prepare('UPDATE '.$configDatabaseTableUsers.' SET description=:description, desc_as_signature=:desc_as_signature WHERE user_id=:user_id LIMIT 1;');
        $descriptionQuery->execute([
            ':user_id' => $_SESSION['user_id'],
            ':description' => $newDescription,
            ':desc_as_signature' => $asSignature
        ]);

        header("Location: profile.php?user=".$userName);
        exit();
    }
    else {
        $errors['description'] = 'Popis nesmí mít více než '.$configUserMaxDescriptionLen.' znaků.';
        $textAreaContent = $newDescription;
    }
}

//nastaveni bbcode
$loadBBcode = true;
$BBcodeEditorID = 'description';
$BBcodeEditorHeight = '500';

//nastaveni title a nacteni headeru
$pageTitle = 'Úprava popisu';
include "inc/html/header.php";
#endregion zacatek


echo '<div class="main-wrap">
            <h1 id="test">Upravit popis profilu</h1>
            <form action="" method="post">
                <textarea id="description" name="description" rows="10" class="description-edit-textarea" placeholder="Zadejte popis..." autofocus>'.htmlspecialchars($textAreaContent).'</textarea>
                '.(!empty($errors['description'])?'<div class="input-error">'.$errors['description'].'</div>':'').'
                <br>
                <input type="checkbox" name="signature" value="yes" '.(($userDescriptionAsSignature)?'checked':'').'>
                <label for="signature">Zobrazovat popis pod každým mým příspěvkem</label><br><br>
                <div class="buttons-wrap">
                    <input type="submit" value="Uložit" class="button-primary">
                    <a href="profile.php?user='.htmlspecialchars($userName).'" class="button-secondary">Zpět</a>
                </div>
            </form>
        </div>';

#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec