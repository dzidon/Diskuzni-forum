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

                //zjisteni poctu odpovedi prispevku
                $countRepliesQuery=$db->prepare('SELECT post_parent_id AS thread_post_id, COUNT(post_parent_id)-1 AS replies
                                                       FROM '.$configDatabaseTablePosts.'
                                                       GROUP BY post_parent_id HAVING thread_post_id=:thread_post_id
                                                       LIMIT 1;');
                $countRepliesQuery->execute([
                    ':thread_post_id' => $post['thread_post_id']
                ]);
                if ($countRepliesQuery->rowCount() > 0) {
                    $countRepliesResult = $countRepliesQuery->fetch();
                    $replies = $countRepliesResult['replies'];
                }

                //zjisteni informaci o zakladateli
                $postCreatorQuery=$db->prepare('SELECT name, picture_file FROM '.$configDatabaseTableUsers.' WHERE user_id=:user_id LIMIT 1');
                $postCreatorQuery->execute([
                    ':user_id' => $post['thread_user_id']
                ]);
                if ($postCreatorQuery->rowCount() > 0) {
                    $creator = $postCreatorQuery->fetch();
                    $creatorName = $creator['name'];
                    $creatorPictureFile = $creator['picture_file'];
                }

                //post nemá žádné odpovědi, uživatel ho jen založil
                if($post['thread_post_id'] === $post['reply_post_id']) {
                    $threadCreatedTimestamp = strtotime($post['thread_created']);
                    $activityDate = date('d/m/Y \v H:i', $threadCreatedTimestamp);

                    echo '<div class="section-post">
                            <div class="section-post-left">
                                <div class="section-post-image-wrap">
                                    <a href="profile.php?user='.htmlspecialchars($creatorName).'" class="section-post-image-author">
                                        <img src="img/'.htmlspecialchars($creatorPictureFile).'" alt="Profilový obrázek" width="40" height="40">
                                    </a>
                                </div>
            
                                <div class="section-post-links">
                                    <a href="odkazNaPost" class="section-post-name">'.( $post['thread_pinned'] ? '<i class="fas fa-thumbtack section-post-pin"></i>':'').( $post['thread_locked'] ? '<i class="fas fa-lock section-post-lock"></i>':'').htmlspecialchars($post['thread_name']).'</a>
                                    <span class="section-post-info">
                                        Založil <a class="section-post-user" href="profile.php?user='.htmlspecialchars($creatorName).'">'.htmlspecialchars($creatorName).'</a> '.htmlspecialchars($activityDate).'
                                    </span>
                                </div>
                            </div>
            
                            <div class="section-post-right">
                                <div class="section-post-replies"><i class="fas fa-reply section-post-icon"></i>'.htmlspecialchars($replies).'</div>
                                <div class="section-post-views"><i class="fas fa-eye section-post-icon"></i>'.htmlspecialchars($post['thread_views']).'</div>
                            </div>
                          </div>';
                }
                else { //post má odpověď
                    $threadRepliedTimestamp = strtotime($post['reply_created']);
                    $activityDate = date('d/m/Y \v H:i', $threadRepliedTimestamp);

                    //zjisteni informaci o zakladateli
                    $postLastReplyQuery=$db->prepare('SELECT name, picture_file FROM '.$configDatabaseTableUsers.' WHERE user_id=:user_id LIMIT 1');
                    $postLastReplyQuery->execute([
                        ':user_id' => $post['reply_user_id']
                    ]);
                    if ($postLastReplyQuery->rowCount() > 0) {
                        $replyUser = $postLastReplyQuery->fetch();
                        $replyUserName = $replyUser['name'];
                        $replyUserPictureFile = $replyUser['picture_file'];
                    }

                    echo '<div class="section-post">
                            <div class="section-post-left">
                                <div class="section-post-image-wrap">
                                    <a href="profile.php?user='.htmlspecialchars($creatorName).'" class="section-post-image-author">
                                        <img src="img/'.htmlspecialchars($creatorPictureFile).'" alt="Profilový obrázek" width="40" height="40">
                                    </a>
                                    <a href="profile.php?user='.htmlspecialchars($replyUserName).'" class="section-post-image-author">
                                        <img class="section-post-image-reply" src="img/'.htmlspecialchars($replyUserPictureFile).'" alt="Profilový obrázek" width="20" height="20">
                                    </a>
                                </div>
            
                                <div class="section-post-links">
                                    <a href="odkazNaPost" class="section-post-name">'.( $post['thread_pinned'] ? '<i class="fas fa-thumbtack section-post-pin"></i>':'').( $post['thread_locked'] ? '<i class="fas fa-lock section-post-lock"></i>':'').htmlspecialchars($post['thread_name']).'</a>
                                    <span class="section-post-info">
                                        Odpověděl <a class="section-post-user" href="profile.php?user='.htmlspecialchars($replyUserName).'">'.htmlspecialchars($replyUserName).'</a> '.htmlspecialchars($activityDate).'
                                    </span>
                                </div>
                            </div>
            
                            <div class="section-post-right">
                                <div class="section-post-replies"><i class="fas fa-reply section-post-icon"></i>'.htmlspecialchars($replies).'</div>
                                <div class="section-post-views"><i class="fas fa-eye section-post-icon"></i>'.htmlspecialchars($post['thread_views']).'</div>
                            </div>
                          </div>';
                }

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