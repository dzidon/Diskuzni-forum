<?php

#region zacatek
//hlavni konfiguracni soubor celeho webu
require_once "inc/appConfig.php";

//nacteni pripojeni k databazi
require_once "inc/db.php";

//spusteni session a verifikace uzivatele + zjisteni informaci o nem
require_once "inc/userVerify.php";

//pokud nic nezada, presmerujeme ho zpet na index
if(!isset($_GET['word']) || mb_strlen($_GET['word'], 'utf-8') == 0) {
    header("Location: index.php");
    exit();
}
$searched = trim($_GET['word']);

//nastaveni title a nacteni headeru
$pageTitle = $searched;
include "inc/html/header.php";
#endregion zacatek


//nacteni nekolika uzivatelu podle hledaneho vyrazu
$userSearch=$db->prepare('SELECT * FROM '.$configDatabaseTableUsers.' WHERE name LIKE :word ORDER BY name ASC LIMIT '.$configSearchMaxUsers.';');
$userSearch->execute([
    ':word' => '%'.$searched.'%'
]);

//nacteni nekolika prispevku podle hledaneho vyrazu
$postsSearch=$db->prepare('SELECT 
        threads.post_id AS thread_post_id, threads.section_id AS thread_section_id, threads.user_id AS thread_user_id, threads.name AS thread_name, threads.created AS thread_created, threads.pinned AS thread_pinned, threads.locked AS thread_locked, threads.views AS thread_views, replies.post_id AS reply_post_id, replies.user_id AS reply_user_id, replies.created AS reply_created 
        FROM '.$configDatabaseTablePosts.' AS threads 
        LEFT JOIN (
            SELECT * FROM sp_posts 
            WHERE (post_parent_id,created) IN 
            ( SELECT post_parent_id, MAX(created) FROM sp_posts GROUP BY post_parent_id )
        ) AS replies ON threads.post_id = replies.post_parent_id 
        WHERE threads.post_parent_id=threads.post_id AND threads.name LIKE :word
        ORDER BY reply_created DESC LIMIT '.$configSearchMaxPosts.';');
$postsSearch->execute([
    ':word' => '%'.$searched.'%'
]);

echo '<div class="main-wrap">
        <h1>Výsledky vyhledávání pro "'.htmlspecialchars($searched).'"</h1>
        <div class="search-wrap">
            <i class="fas fa-search search-icon"></i>
            <form class="search-form" action="search.php" method="get">
                <input type="text" class="search-bar" id="word" name="word" placeholder="Hledat příspěvky, uživatele" autocomplete="off" value="'.htmlspecialchars($searched).'">
            </form>
        </div>
        <div class="section-header-wrap">
            <div class="section-header-name"><i class="far fa-user section-header-icon"></i>Uživatelé</div>
        </div>';

        //výpis všech nalezených uživatelů
        $count = $userSearch->rowCount();
        if($count == 0) {
            echo '<div class="section-error-noposts">Nebyli nalezeni žádní uživatelé odpovídající hledanému výrazu.</div>';
        }
        else {
            $users = $userSearch->fetchAll(PDO::FETCH_ASSOC);
            $i = 0;
            foreach($users as $user) {
                $i++;
                echo '<div class="section-post">
                        <div class="section-post-left">
                            <div class="section-post-image-wrap-nohiding">
                                <a href="profile.php?user='.htmlspecialchars($user['name']).'" class="section-post-image-author">
                                    <img src="img/'.htmlspecialchars($user['picture_file']).'" alt="Profilový obrázek" width="40" height="40">
                                </a>
                            </div>
        
                            <div class="section-post-links-username">
                                <a href="profile.php?user='.htmlspecialchars($user['name']).'" class="section-post-name">'.htmlspecialchars($user['name']).'</a>
                            </div>
                        </div>
                      </div>';
                if($i != $count) {
                    echo'<div class="line"></div>';
                }
            }
        }

        echo '<div class="section-header-wrap">
                <div class="section-header-name"><i class="far fa-comments section-header-icon"></i>Témata</div>
              </div>';

        //výpis všech nalezených příspěvků
        $count = $postsSearch->rowCount();
        if($count == 0) {
            echo '<div class="section-error-noposts">Nebyla nalezena žádná témata odpovídající hledanému výrazu.</div>';
        }
        else {
            //načteme si funkci pro vykreslení odkazu na post
            require_once "inc/functionRenderPost.php";

            $posts = $postsSearch->fetchAll(PDO::FETCH_ASSOC);
            $i = 0;
            foreach($posts as $post) {
                $i++;
                renderPost($post, $db, $configDatabaseTablePosts, $configDatabaseTableUsers, 0);
                if($i != $count) {
                    echo'<div class="line"></div>';
                }
            }
        }

echo '</div>';


#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec