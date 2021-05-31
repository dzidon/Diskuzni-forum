<?php

#region zacatek
//hlavni konfiguracni soubor celeho webu
require_once "inc/appConfig.php";

//nacteni pripojeni k databazi
require_once "inc/db.php";

//spusteni session a verifikace uzivatele + zjisteni informaci o nem
require_once "inc/userVerify.php";

//nastaveni title a nacteni headeru
$pageTitle = 'Domů';
include "inc/html/header.php";
#endregion zacatek


//vyhledávání
echo '<div class="main-wrap">
          <h1>Všechny sekce</h1>
          <div class="search-wrap">
            <i class="fas fa-search search-icon"></i>
            <form class="search-form" action="search.php" method="get">
                <input type="text" class="search-bar" id="word" name="word" placeholder="Hledat příspěvky, uživatele" autocomplete="off">
            </form>
          </div>';

//výpis sekcí a poslední aktivity v nich
$sectionsQuery=$db->prepare('SELECT * FROM '.$configDatabaseTableSections.';');
$sectionsQuery->execute();
if($sectionsQuery->rowCount() > 0) {

    //načteme si funkci pro vykreslení odkazu na post
    require_once "inc/functionRenderPost.php";

    $sections = $sectionsQuery->fetchAll(PDO::FETCH_ASSOC);
    foreach($sections as $section) {
        echo '<div class="section-header-wrap">
                <div class="section-header-name"><i class="far fa-comments section-header-icon"></i>'.htmlspecialchars($section['name']).'</div>
                <a href="link" class="section-header-button">Zobrazit vše</a>
              </div>';

        //ziskame posledni aktivitu v sekci
        $postsQuery=$db->prepare('SELECT 
        threads.post_id AS thread_post_id, threads.section_id AS thread_section_id, threads.user_id AS thread_user_id, threads.name AS thread_name, threads.created AS thread_created, threads.pinned AS thread_pinned, threads.locked AS thread_locked, threads.views AS thread_views, replies.post_id AS reply_post_id, replies.user_id AS reply_user_id, replies.created AS reply_created 
        FROM '.$configDatabaseTablePosts.' AS threads 
        LEFT JOIN (
            SELECT * FROM sp_posts 
            WHERE (post_parent_id,created) IN 
            ( SELECT post_parent_id, MAX(created) FROM sp_posts GROUP BY post_parent_id )
        ) AS replies ON threads.post_id = replies.post_parent_id 
        WHERE threads.post_parent_id=threads.post_id AND threads.section_id=:section_id
        ORDER BY reply_created DESC LIMIT '.$configHomepageMaxPosts.';');
        $postsQuery->execute([
            ':section_id' => $section['section_id']
        ]);

        //vypsani poslednich prispevku v sekci
        $count = $postsQuery->rowCount();
        if($count == 0) {
            echo '<div class="section-error-noposts">V této sekci nejsou žádná témata, <a href="odkazNaVytvoreniPrispevku" class="section-link-newpost">napište nové</a>.</div>';
        }
        else {
            $posts = $postsQuery->fetchAll(PDO::FETCH_ASSOC);
            $i = 0;
            foreach($posts as $post) {
                $i++;
                renderPost($post, $db, $configDatabaseTablePosts, $configDatabaseTableUsers);
                if($i != $count) {
                    echo'<div class="line"></div>';
                }
            }
        }
    }
}

echo '</div>';


#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec