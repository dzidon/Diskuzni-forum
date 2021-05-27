<?php

//spusteni session
session_start();

//inicializace promenynch o uzivateli, ktere jsou potreba v urcitych castech webu
$userActivated = 0; //urcuje, jestli je uzivateluv ucet aktivovan pres email
$userActivationCode = ''; //kod, kterym uzivatel aktivuje svuj ucet
$userActivationLastSent = '0000-00-00 00:00:00';
$userRole = $configRoleUser; //urcuje uzivatelovu roli (uzivatel/admin)
$userName = ''; //uzivatelova prezdivka
$userEmail = ''; //uzivateluv email
$userDescription = ''; //uzivateluv popis
$userProfilePictureFile = ''; //nazev souboru profilovky
$userBannerFile = ''; //nazev souboru banneru
$userDescriptionAsSignature = 0; //jestli zobrazovat popis pod prispevky

#region kontrola existence uzivatele v databazi a zjisteni nekterych informaci pro budouci pouziti
if(isset($_SESSION['user_id'])) {
    $inactiveForTooLong = false;
    if(isset($_SESSION['last_active'])) {
        if(time() >= $_SESSION['last_active']+$configUserMaxInactivity) {
            $inactiveForTooLong = true;
            unset($_SESSION['user_id']);
            unset($_SESSION['last_active']);
        }
    }

    if(!$inactiveForTooLong) {
        $userQuery=$db->prepare('SELECT * FROM '.$configDatabaseTableUsers.' WHERE user_id=:id LIMIT 1;');
        $userQuery->execute([
            ':id' => $_SESSION['user_id']
        ]);

        if($userQuery->rowCount() != 1) { //uzivatel nenalezen
            unset($_SESSION['user_id']);
            if(isset($_SESSION['last_active'])) {
                unset($_SESSION['last_active']);
            }
        }
        else { //uzivatel nalezen
            $_SESSION['last_active'] = time();
            $user = $userQuery->fetch();
            $userActivated = $user['activated'];
            $userActivationCode = $user['activation_code'];
            $userActivationLastSent = $user['activation_last_sent'];
            $userRole = $user['role'];
            $userName = $user['name'];
            $userEmail = $user['email'];
            $userDescription = $user['description'];
            $userProfilePictureFile = $user['picture_file'];
            $userBannerFile = $user['banner_file'];
            $userDescriptionAsSignature = $user['desc_as_signature'];
        }
    }
}
#endregion kontrola existence uzivatele v databazi a zjisteni nekterych informaci pro budouci pouziti