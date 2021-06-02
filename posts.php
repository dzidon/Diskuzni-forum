<?php

#region zacatek
//hlavni konfiguracni soubor celeho webu
require_once "inc/appConfig.php";

//nacteni pripojeni k databazi
require_once "inc/db.php";

//spusteni session a verifikace uzivatele + zjisteni informaci o nem
require_once "inc/userVerify.php";

$error = '';
if(isset($_GET['section'])) {
    if($_GET['section'] > 0) {
        $sectionQuery=$db->prepare('SELECT * FROM '.$configDatabaseTableSections.' WHERE section_id=:section_id LIMIT 1;');
        $sectionQuery->execute([
            ':section_id' => $_GET['section']
        ]);

        if($sectionQuery->rowCount() > 0) {
            $section = $sectionQuery->fetch();
        }
        else $error = 'Sekce nenalezena.';
    }
    else $error = 'Sekce nenalezena.';
}
else $error = 'V odkazu chybí sekce.';

//nastaveni title a nacteni headeru
if(isset($section['name'])) {
    $pageTitle = $section['name'];
}
else {
    $pageTitle = 'Sekce nenalezena';
}
include "inc/html/header.php";
#endregion zacatek


echo '<div class="main-wrap">';

if(mb_strlen($error, 'utf-8') == 0) {
    $offset = 0;
    $page = 1;
    if(isset($_GET['page'])) {
        if(is_numeric($_GET['page'])) {
            $page = (int) $_GET['page'];
            if($page >= 2) {
                $offset = ($page-1)*$configSectionPageMaxPosts;
            }
        }
    }

    //ziskame posledni aktivitu v sekci
    $postsQuery=$db->prepare('SELECT 
        threads.post_id AS thread_post_id, threads.section_id AS thread_section_id, threads.user_id AS thread_user_id, threads.name AS thread_name, threads.created AS thread_created, threads.pinned AS thread_pinned, threads.locked AS thread_locked, threads.views AS thread_views, replies.post_id AS reply_post_id, replies.user_id AS reply_user_id, replies.created AS reply_created 
        FROM '.$configDatabaseTablePosts.' AS threads 
        LEFT JOIN (
            SELECT * FROM '.$configDatabaseTablePosts.' 
            WHERE (post_parent_id,created) IN 
            ( SELECT post_parent_id, MAX(created) FROM '.$configDatabaseTablePosts.' GROUP BY post_parent_id )
        ) AS replies ON threads.post_id = replies.post_parent_id 
        WHERE threads.post_parent_id=threads.post_id AND threads.section_id=:section_id
        ORDER BY thread_pinned DESC, reply_created DESC LIMIT '.$configSectionPageMaxPosts.' OFFSET :off;');
    $postsQuery->bindParam(":section_id", $section['section_id']);
    $postsQuery->bindParam(":off", $offset, PDO::PARAM_INT);
    $postsQuery->execute();

    //vypsani poslednich prispevku v sekci
    $count = $postsQuery->rowCount(); //pocet vykreslovanych prispevku na dane strance
    if($count > 0) {
        echo '<h1>Příspěvky</h1>
                  <div class="section-header-wrap">
                    <div class="section-header-name"><i class="far fa-comments section-header-icon"></i>'.htmlspecialchars($section['name']).'</div>';
        if(isset($_SESSION['user_id']) && $userActivated) {
            echo '<a href="new" class="section-header-button">Nové téma</a>';
        }
        echo '</div>';

        //zjisteni celkoveho poctu temat v sekci
        $threadsQuery=$db->prepare('SELECT COUNT(post_id) AS total_threads FROM '.$configDatabaseTablePosts.' WHERE post_parent_id=post_id AND section_id=:section_id;');
        $threadsQuery->execute([
            ":section_id" => $section['section_id']
        ]);
        $result = $threadsQuery->fetch();

        //vytvoreni tlacitek na strankovani
        $totalPages = $result['total_threads']/$configSectionPageMaxPosts;
        $buttons = array();
        for($i = 2; $i >= 1; $i--) { //zkontrolujeme, jestli existuje i stranek pred aktualni strankou
            if($page-$i >= 1) array_push($buttons, array($page-$i, false));
        }
        array_push($buttons, array($page, true)); //aktualni stranka
        for($i = 1; $i <= 2; $i++) { //zkontrolujeme, jestli existuje i stranek po aktualni strance
            if($page+$i <= $totalPages) array_push($buttons, array($page+$i, false));
        }

        //strankovani nahore
        echo '<div class="post-pages">';
        foreach($buttons as $button) {
            echo '<a href="posts.php?section='.htmlspecialchars($section['section_id']).'&page='.htmlspecialchars($button[0]).'" class="'.( $button[1] ? 'post-page-number-current':'post-page-number').'">'.htmlspecialchars($button[0]).'</a>';
        }
        echo '</div>';
        echo '<div class="line"></div>';

        //načteme si funkci pro vykreslení odkazu na post
        require_once "inc/functionRenderPost.php";

        //zobrazeni temat
        $posts = $postsQuery->fetchAll(PDO::FETCH_ASSOC);
        $i = 0;
        foreach($posts as $post) {
            $i++;
            renderPost($post, $db, $configDatabaseTablePosts, $configDatabaseTableUsers, 1);
            if($i != $count) {
                echo'<div class="line"></div>';
            }
        }

        //strankovani dole
        echo '<div class="line"></div>
              <div class="post-pages">
                <a href="odkazNaStranku" class="post-page-number">1</a>
                <a href="odkazNaStranku" class="post-page-number">2</a>
                <a href="odkazNaStranku" class="post-page-number-current">3</a>
                <a href="odkazNaStranku" class="post-page-number">128</a>
              </div>';
    }
    else {
        echo '<h1>Chyba</h1>Nenalezeny žádné příspěvky.';
    }
}
else {
    echo '<h1>Chyba</h1>'.htmlspecialchars($error);
}

echo '</div>';


#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec