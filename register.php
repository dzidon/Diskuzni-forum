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
    $formName = trim(@$_POST['name']);
    $formEmail = trim(@$_POST['email']);
    $formPassword = @$_POST['password'];
    $formPassword2 = @$_POST['password2'];

    $errors = array();

    //kontrola jmena
    if(mb_strlen($formName,'UTF-8') >= $configUserMinNameLen && mb_strlen($formName,'UTF-8') <= $configUserMaxNameLen) {
        if(strpos($formName, ' ') === false) {
            if(preg_match("/^\w+$/", $formName)) {
                $nameQuery=$db->prepare('SELECT * FROM '.$configDatabaseTableUsers.' WHERE name=:name LIMIT 1;');
                $nameQuery->execute([
                    ':name'=>$formName
                ]);

                if ($nameQuery->rowCount() > 0) {
                    $errors['name'] = 'Zadanou přezdívku už někdo má.';
                }
            }
            else $errors['name'] = 'Přezdívka může obsahovat jen následující znaky: a-z A-Z 0-9 _';
        }
        else $errors['name'] = 'Přezdívka nesmí obsahovat mezery.';
    }
    else $errors['name'] = 'Délka přezdívky musí být mezi '.$configUserMinNameLen.' a '.$configUserMaxNameLen.' znaky.';

    //kontrola e-mailu
    if(filter_var($formEmail, FILTER_VALIDATE_EMAIL)) {
        $emailQuery=$db->prepare('SELECT * FROM '.$configDatabaseTableUsers.' WHERE email=:email LIMIT 1;');
        $emailQuery->execute([
            ':email'=>$formEmail
        ]);

        if ($emailQuery->rowCount() > 0) {
            $errors['email'] = 'Zadaný e-mail už někdo má.';
        }
    }
    else $errors['email'] = 'Zadaný e-mail je neplatný.';

    //kontrola hesla
    if(mb_strlen($formPassword,'UTF-8') >= $configUserMinPassLen && mb_strlen($formPassword,'UTF-8') <= $configUserMaxPassLen) {
        if(strpos($formPassword, ' ') === false) {
            if($formPassword !== $formPassword2) {
                $errors['password2'] = 'Zadaná hesla se neshodují.';
            }
        }
        else $errors['password'] = 'Heslo nesmí obsahovat mezery.';
    }
    else $errors['password'] = 'Délka hesla musí být mezi '.$configUserMinPassLen.' a '.$configUserMaxPassLen.' znaky.';

    //zadane udaje jsou v poradku
    if(empty($errors)) {
        $hashedPassword = password_hash($formPassword, PASSWORD_DEFAULT);
        $activationCode = bin2hex(random_bytes(15));

        //vlozeni do databaze
        $query=$db->prepare('INSERT INTO '.$configDatabaseTableUsers.' (name, email, password, activation_code) VALUES (:name, :email, :password, :activation_code);');
        $query->execute([
            ':name' => $formName,
            ':email' => $formEmail,
            ':password' => $hashedPassword,
            ':activation_code' => $activationCode
        ]);
        $lastID = $db->lastInsertId();

        //prihlaseni
        $_SESSION['user_id'] = $lastID;
        $_SESSION['last_active'] = time();

        //email
        $link = $configAccountActivationURL.'?uid='.$lastID.'&code='.$activationCode;
        $to = $formEmail;
        $subject = $configAccountActivationMailSubject;
        $message = $configAccountActivationMail1.htmlspecialchars($formName).$configAccountActivationMail2.'<a href="'.$link.'">zde</a>'.$configAccountActivationMail3;
        $headers = "MIME-Version: 1.0"."\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8"."\r\n";
        $headers .= 'From: '.$configAccountActivationMailFrom."\r\n";
        mail($to,$subject,$message,$headers);

        //redirect
        header("Location: account_not_activated.php");
        exit();
    }
}

//nastaveni title a nacteni headeru
$pageTitle = 'Registrace';
include "inc/html/header.php";
#endregion zacatek


echo '<div class="main-wrap main-wrap-small">
            <h1>Registrace</h1>
            <form action="" method="post">
                <label for="name">Přezdívka:</label><br>
                <input type="text" id="name" name="name" class="user-input" size="40" value="'.htmlspecialchars(@$formName).'">
                '.(!empty($errors['name'])?'<div class="input-error">'.$errors['name'].'</div>':'<br>').'
                <br>
                <label for="email">E-mail:</label><br>
                <input type="email" id="email" name="email" class="user-input" size="40" value="'.htmlspecialchars(@$formEmail).'">
                '.(!empty($errors['email'])?'<div class="input-error">'.$errors['email'].'</div>':'<br>').'
                <br>
                <label for="password">Heslo:</label><br>
                <input type="password" id="password" name="password" class="user-input" size="40">
                '.(!empty($errors['password'])?'<div class="input-error">'.$errors['password'].'</div>':'<br>').'
                <br>
                <label for="password2">Heslo znovu:</label><br>
                <input type="password" id="password2" name="password2" class="user-input" size="40">
                '.(!empty($errors['password2'])?'<div class="input-error">'.$errors['password2'].'</div>':'<br>').'
                <br>
                <div class="login-links">
                    <div>Už máte účet? <a href="login.php" class="text-link">Přihlásit se</a></div>
                </div>
                <div class="buttons-wrap buttons-center">
                    <input type="submit" value="Registrovat" class="button-primary">
                    <a href="index.php" class="button-secondary">Domů</a>
                </div>
            </form>
        </div>';


#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec