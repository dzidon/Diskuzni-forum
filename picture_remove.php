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
#endregion zacatek


if(isset($_GET['type'])) {
    if($_GET['type'] === 'profile_picture' && $userProfilePictureFile !== $configUserNoProfilePicture) {
        if (file_exists('img/'.$userProfilePictureFile)) {
            unlink('img/'.$userProfilePictureFile);
            $updateQuery=$db->prepare('UPDATE '.$configDatabaseTableUsers.' SET picture_file=:picture_file WHERE user_id=:user_id LIMIT 1;');
            $updateQuery->execute([
                ':user_id' => $_SESSION['user_id'],
                ':picture_file' => $configUserNoProfilePicture
            ]);
        }
    }
    if($_GET['type'] === 'banner' && $userBannerFile !== $configUserNoBanner) {
        if (file_exists('img/'.$userBannerFile)) {
            unlink('img/'.$userBannerFile);
            $updateQuery=$db->prepare('UPDATE '.$configDatabaseTableUsers.' SET banner_file=:banner_file WHERE user_id=:user_id LIMIT 1;');
            $updateQuery->execute([
                ':user_id' => $_SESSION['user_id'],
                ':banner_file' => $configUserNoBanner
            ]);
        }
    }
}


#region konec
header("Location: profile.php?user=".$userName);
#endregion konec