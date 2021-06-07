<?php

#region zacatek
//hlavni konfiguracni soubor celeho webu
require_once "inc/appConfig.php";

//nacteni pripojeni k databazi
require_once "inc/db.php";

//spusteni session a verifikace uzivatele + zjisteni informaci o nem
require_once "inc/userVerify.php";
#endregion zacatek


$mutedUserName = '';
if(isset($_SESSION['user_id']) && isset($_GET['uid']) && isset($_GET['action'])) {
    if($_GET['action'] === "0" || $_GET['action'] === "1") {
        if($userRole === $configRoleAdmin) {
            $checkQuery=$db->prepare('SELECT * FROM '.$configDatabaseTableUsers.' WHERE user_id=:user_id LIMIT 1;');
            $checkQuery->execute([
                ':user_id' => $_GET['uid']
            ]);

            if ($checkQuery->rowCount() > 0) {
                $muteQuery=$db->prepare('UPDATE '.$configDatabaseTableUsers.' SET muted=:muted WHERE user_id=:user_id LIMIT 1;');
                $muteQuery->execute([
                    ':user_id' => $_GET['uid'],
                    ':muted' => $_GET['action']
                ]);

                $profile = $checkQuery->fetch();
                $mutedUserName = $profile['name'];
            }
        }
    }
}


//presmerovani
if(mb_strlen($mutedUserName,'UTF-8') == 0) {
    header("Location: index.php");
}
else {
    header("Location: profile.php?user=".$mutedUserName);
}