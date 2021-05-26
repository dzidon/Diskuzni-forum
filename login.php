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
if(!empty($_POST)) {
    $formEmail = trim(@$_POST['email']);
    $formPassword = @$_POST['password'];

    $errors = array();

    //kontrola kombinace
    $userQuery=$db->prepare('SELECT * FROM '.$configDatabaseTableUsers.' WHERE email=:email LIMIT 1;');
    $userQuery->execute([
        ':email'=>$formEmail
    ]);

    if ($userQuery->rowCount() > 0) {
        $user = $userQuery->fetch();
        if(password_verify($formPassword, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['last_active'] = time();
            header('Location: account_not_activated.php');
            exit();
        }
        else $errors['combination'] = 'Zadaná kombinace e-mailu a hesla neexistuje.';
    }
    else $errors['combination'] = 'Zadaná kombinace e-mailu a hesla neexistuje.';
}

//nastaveni title a nacteni headeru
$pageTitle = 'Přihlášení';
include "inc/html/header.php";
#endregion zacatek


echo '<div class="main-wrap main-wrap-small">
            <h1>Přihlášení</h1>
            <form action="" method="post">
                <label for="email">E-mail:</label><br>
                <input type="email" id="email" name="email" class="user-input" size="40" value="'.htmlspecialchars(@$formEmail).'">
                <br><br>
                <label for="password">Heslo:</label><br>
                <input type="password" id="password" name="password" class="user-input" size="40">
                '.(!empty($errors['combination'])?'<div class="input-error">'.$errors['combination'].'</div>':'<br>').'
                <br>
                <div class="login-links">
                    <div>Ještě nemáte účet? <a href="register.php" class="text-link">Zaregistrujte se</a></div>
                    <div>Zapomněli jste heslo? <a href="password_reset.php" class="text-link">Obnovit heslo</a></div>
                </div>
                <div class="buttons-wrap buttons-center">
                    <input type="submit" value="Přihlásit" class="button-primary">
                    <a href="index.php" class="button-secondary">Domů</a>
                </div>
            </form>
        </div>';


#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec