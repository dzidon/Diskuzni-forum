<?php

#region zacatek
//hlavni konfiguracni soubor celeho webu
require_once "inc/appConfig.php";

//nacteni pripojeni k databazi
require_once "inc/db.php";

//spusteni session a verifikace uzivatele + zjisteni informaci o nem
require_once "inc/userVerify.php";
#endregion zacatek

if(isset($_SESSION['user_id'])) {
    if(isset($_GET['id'])) {
        $postID = trim($_GET['id']);

        $postQuery=$db->prepare('SELECT * FROM '.$configDatabaseTablePosts.' WHERE post_id=:post_id LIMIT 1;');
        $postQuery->execute([
            ':post_id' => $postID
        ]);

        if($postQuery->rowCount() > 0) { //prispevek existuje
            $likeQuery=$db->prepare('SELECT * FROM ' . $configDatabaseTableLikes . ' WHERE post_id=:post_id AND user_id=:user_id LIMIT 1;');
            $likeQuery->execute([
                ':post_id' => $postID,
                ':user_id' => $_SESSION['user_id']
            ]);

            if ($likeQuery->rowCount() > 0) { //uz je liked
                $deleteQuery=$db->prepare('DELETE FROM ' . $configDatabaseTableLikes . ' WHERE post_id=:post_id AND user_id=:user_id;');
                $deleteQuery->execute([
                    ':post_id' => $postID,
                    ':user_id' => $_SESSION['user_id']
                ]);
            }
            else { //jeste neni liked
                $insertQuery=$db->prepare('INSERT INTO '.$configDatabaseTableLikes.' (post_id, user_id) VALUES (:post_id, :user_id);');
                $insertQuery->execute([
                    ':post_id' => $postID,
                    ':user_id' => $_SESSION['user_id']
                ]);
            }
        }
    }
}
else {
    header("Location: login.php");
    exit();
}


//presmerovani
if(isset($_GET['thread_id'])) {
    $page = 1;
    if(isset($_GET['page']) && $_GET['page'] > 1) {
        $page = $_GET['page'];
    }

    header("Location: thread.php?id=".$_GET['thread_id']."&page=".$page);
}
else {
    header("Location: index.php");
}