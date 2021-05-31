<?php

/*
 * Funkce, která vykreslí odkaz na post
 */
function renderPost($post, $dbLocal, $tablePosts, $tableUsers)
{
    //zjisteni poctu odpovedi prispevku
    $countRepliesQuery = $dbLocal->prepare('SELECT post_parent_id AS thread_post_id, COUNT(post_parent_id)-1 AS replies
                                                       FROM ' . $tablePosts . '
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
    $postCreatorQuery = $dbLocal->prepare('SELECT name, picture_file FROM ' . $tableUsers . ' WHERE user_id=:user_id LIMIT 1');
    $postCreatorQuery->execute([
        ':user_id' => $post['thread_user_id']
    ]);
    if ($postCreatorQuery->rowCount() > 0) {
        $creator = $postCreatorQuery->fetch();
        $creatorName = $creator['name'];
        $creatorPictureFile = $creator['picture_file'];
    }

    //post nemá žádné odpovědi, uživatel ho jen založil
    if ($post['thread_post_id'] === $post['reply_post_id']) {
        $threadCreatedTimestamp = strtotime($post['thread_created']);
        $activityDate = date('d/m/Y \v H:i', $threadCreatedTimestamp);

        echo '<div class="section-post">
                    <div class="section-post-left">
                        <div class="section-post-image-wrap">
                            <a href="profile.php?user=' . htmlspecialchars($creatorName) . '" class="section-post-image-author">
                                <img src="img/' . htmlspecialchars($creatorPictureFile) . '" alt="Profilový obrázek" width="40" height="40">
                            </a>
                        </div>
    
                        <div class="section-post-links">
                            <a href="odkazNaPost" class="section-post-name">' . ($post['thread_pinned'] ? '<i class="fas fa-thumbtack section-post-pin"></i>' : '') . ($post['thread_locked'] ? '<i class="fas fa-lock section-post-lock"></i>' : '') . htmlspecialchars($post['thread_name']) . '</a>
                            <span class="section-post-info">
                                Založil <a class="section-post-user" href="profile.php?user=' . htmlspecialchars($creatorName) . '">' . htmlspecialchars($creatorName) . '</a> ' . htmlspecialchars($activityDate) . '
                            </span>
                        </div>
                    </div>
    
                    <div class="section-post-right">
                        <div class="section-post-replies"><i class="fas fa-reply section-post-icon"></i>' . htmlspecialchars($replies) . '</div>
                        <div class="section-post-views"><i class="fas fa-eye section-post-icon"></i>' . htmlspecialchars($post['thread_views']) . '</div>
                    </div>
              </div>';
    } else { //post má odpověď
        $threadRepliedTimestamp = strtotime($post['reply_created']);
        $activityDate = date('d/m/Y \v H:i', $threadRepliedTimestamp);

        //zjisteni informaci o zakladateli
        $postLastReplyQuery = $dbLocal->prepare('SELECT name, picture_file FROM ' . $tableUsers . ' WHERE user_id=:user_id LIMIT 1');
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
                        <a href="profile.php?user=' . htmlspecialchars($creatorName) . '" class="section-post-image-author">
                            <img src="img/' . htmlspecialchars($creatorPictureFile) . '" alt="Profilový obrázek" width="40" height="40">
                        </a>
                        <a href="profile.php?user=' . htmlspecialchars($replyUserName) . '" class="section-post-image-author">
                            <img class="section-post-image-reply" src="img/' . htmlspecialchars($replyUserPictureFile) . '" alt="Profilový obrázek" width="20" height="20">
                        </a>
                    </div>

                    <div class="section-post-links">
                        <a href="odkazNaPost" class="section-post-name">' . ($post['thread_pinned'] ? '<i class="fas fa-thumbtack section-post-pin"></i>' : '') . ($post['thread_locked'] ? '<i class="fas fa-lock section-post-lock"></i>' : '') . htmlspecialchars($post['thread_name']) . '</a>
                        <span class="section-post-info">
                            Odpověděl <a class="section-post-user" href="profile.php?user=' . htmlspecialchars($replyUserName) . '">' . htmlspecialchars($replyUserName) . '</a> ' . htmlspecialchars($activityDate) . '
                        </span>
                    </div>
                </div>

                <div class="section-post-right">
                    <div class="section-post-replies"><i class="fas fa-reply section-post-icon"></i>' . htmlspecialchars($replies) . '</div>
                    <div class="section-post-views"><i class="fas fa-eye section-post-icon"></i>' . htmlspecialchars($post['thread_views']) . '</div>
                </div>
              </div>';
    }
}