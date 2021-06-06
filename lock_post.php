<?php

#region zacatek
//hlavni konfiguracni soubor celeho webu
require_once "inc/appConfig.php";

//nacteni pripojeni k databazi
require_once "inc/db.php";

//spusteni session a verifikace uzivatele + zjisteni informaci o nem
require_once "inc/userVerify.php";
#endregion zacatek

$lockedPost = '';
if(isset($_SESSION['user_id']) && isset($_GET['id']) && isset($_GET['action'])) {
    if($_GET['action'] === "0" || $_GET['action'] === "1") {
        if($userRole === $configRoleAdmin) {
            $checkQuery=$db->prepare('SELECT * FROM '.$configDatabaseTablePosts.' WHERE post_id=:post_id AND post_parent_id=:post_id LIMIT 1;');
            $checkQuery->execute([
                ':post_id' => $_GET['id']
            ]);

            if($checkQuery->rowCount() > 0) {
                $lockQuery=$db->prepare('UPDATE '.$configDatabaseTablePosts.' SET locked=:locked WHERE post_id=:post_id LIMIT 1;');
                $lockQuery->execute([
                    ':post_id' => $_GET['id'],
                    ':locked' => $_GET['action']
                ]);

                $lockedPost = $_GET['id'];
            }
        }
    }
}

//presmerovani
if(mb_strlen($lockedPost,'UTF-8') == 0) {
    header("Location: index.php");
}
else {
    $page = 1;
    if(isset($_GET['page']) && $_GET['page'] > 1) {
        $page = $_GET['page'];
    }

    header("Location: thread.php?id=".$lockedPost."&page=".$page);
}