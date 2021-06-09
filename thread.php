<?php

#region zacatek
//hlavni konfiguracni soubor celeho webu
require_once "inc/appConfig.php";

//nacteni pripojeni k databazi
require_once "inc/db.php";

//spusteni session a verifikace uzivatele + zjisteni informaci o nem
require_once "inc/userVerify.php";

$error = '';
if(isset($_GET['id'])) {
    if($_GET['id'] > 0) {
        $threadQuery=$db->prepare('SELECT * FROM '.$configDatabaseTablePosts.' WHERE post_id=:post_id AND post_parent_id=:post_id LIMIT 1;');
        $threadQuery->execute([
            ':post_id' => $_GET['id']
        ]);

        if($threadQuery->rowCount() > 0) {
            $thread = $threadQuery->fetch();
        }
        else $error = 'Téma nenalezeno.';
    }
    else $error = 'Téma nenalezeno.';
}
else $error = 'V odkazu chybí téma.';

//zpracovani postu
$errors = array();
if(isset($_POST['new-response']) && isset($thread)) {
    if(isset($_SESSION['user_id']) && !$thread['locked'] && $userActivated && !$userMuted) {
        $responseText = trim($_POST['new-response']);
        if(mb_strlen($responseText, 'utf-8') >= $configResponseMinLen && mb_strlen($responseText, 'utf-8') <= $configResponseMaxLen) {
            $newResponseQuery=$db->prepare('INSERT INTO '.$configDatabaseTablePosts.' (`post_id`, `post_parent_id`, `user_id`, `section_id`, `name`, `text`, `created`, `pinned`, `locked`, `views`, `edited`) VALUES (NULL, :post_parent_id, :user_id, :section_id, NULL, :text, current_timestamp(), 0, 0, 0, 0);');
            $newResponseQuery->execute([
                ':post_parent_id' => $thread['post_id'],
                ':user_id' => $_SESSION['user_id'],
                ':section_id' => $thread['section_id'],
                ':text' => $responseText
            ]);

            //zjisteni celkoveho poctu prispevku v tematu
            $allPostsQuery=$db->prepare('SELECT COUNT(post_id) AS total_posts FROM '.$configDatabaseTablePosts.' WHERE post_parent_id=:post_parent_id;');
            $allPostsQuery->execute([
                ":post_parent_id" => $thread['post_id']
            ]);
            $result = $allPostsQuery->fetch();

            //presmerovani na posledni stranku v tematu
            $lastPage = ceil($result['total_posts']/$configThreadPageMaxPosts);
            header('Location: thread.php?id='.$thread['post_id'].'&page='.$lastPage);
            exit();
        }
        else {
            $errors['response'] = 'Délka textu musí být mezi '.$configResponseMinLen.' a '.$configResponseMaxLen.' znaky.';
        }
    }
}

//nastaveni bbcode
$loadBBcode = true;
$BBcodeEditorID = 'new-response';
$BBcodeEditorHeight = '200';

//nastaveni title a nacteni headeru
if(isset($thread['name'])) {
    $pageTitle = $thread['name'];
}
else {
    $pageTitle = 'Téma nenalezeno';
}
include "inc/html/header.php";
#endregion zacatek



if(mb_strlen($error, 'utf-8') == 0) {
    $offset = 0;
    $page = 1;
    if(isset($_GET['page'])) {
        if(is_numeric($_GET['page']) && $_GET['page'] >= 1) {
            $page = (int) $_GET['page'];
            if($page >= 2) {
                $offset = ($page-1)*$configThreadPageMaxPosts;
            }
        }
    }

    //ziskame vsechny prispevky v tematu
    $postsQuery=$db->prepare('SELECT posts.post_id, posts.post_parent_id, posts.section_id, posts.name, posts.text, posts.created, posts.edited, posts.user_id, users.name AS user_name, users.role AS user_role, users.picture_file AS user_picture_file, users.registered AS user_registered, users.muted AS user_muted, users.desc_as_signature AS user_desc_as_signature, users.description AS user_description
                                    FROM '.$configDatabaseTablePosts.' AS posts JOIN '.$configDatabaseTableUsers.' AS users ON posts.user_id=users.user_id
                                    WHERE post_parent_id=:post_parent_id
                                    ORDER BY created ASC LIMIT '.$configThreadPageMaxPosts.' OFFSET :off;');
    $postsQuery->bindParam(":post_parent_id", $thread['post_parent_id']);
    $postsQuery->bindParam(":off", $offset, PDO::PARAM_INT);
    $postsQuery->execute();

    //vypsani poslednich prispevku v tematu
    $count = $postsQuery->rowCount();
    if($count > 0) {
        //tema a odpovedi
        echo '<div class="main-wrap">';
        echo '<div class="post-top-wrap">
                <h1 class="post-name">' . htmlspecialchars($thread['name']) . '</h1>';
                if($userRole === $configRoleAdmin) {
                    $pinAction = 0;
                    if(!$thread['pinned']) $pinAction = 1;

                    $lockAction = 0;
                    if(!$thread['locked']) $lockAction = 1;

                    echo '<div class="post-top-admin-buttons">
                            <a href="pin_post.php?id='.htmlspecialchars($thread['post_id']).'&page='.htmlspecialchars($page).'&action='.htmlspecialchars($pinAction).'">
                                <i class="fas fa-thumbtack post-admin-button-pin ' . ($thread['pinned'] ? '' : 'post-admin-button-active') . '"></i>
                            </a>
                            <a href="lock_post.php?id='.htmlspecialchars($thread['post_id']).'&page='.htmlspecialchars($page).'&action='.htmlspecialchars($lockAction).'">
                                <i class="fas fa-lock post-admin-button-lock ' . ($thread['locked'] ? '' : 'post-admin-button-active') . '"></i>
                            </a>
                          </div>';
                }
        echo '</div>';

        //zjisteni celkoveho poctu prispevku v tematu
        $allPostsQuery=$db->prepare('SELECT COUNT(post_id) AS total_posts FROM '.$configDatabaseTablePosts.' WHERE post_parent_id=:post_parent_id;');
        $allPostsQuery->execute([
            ":post_parent_id" => $thread['post_id']
        ]);
        $result = $allPostsQuery->fetch();

        //vytvoreni tlacitek na strankovani
        $totalPages = ceil($result['total_posts']/$configThreadPageMaxPosts);
        $buttons = array();

        if($page == 1) { $leftPages = 0; $rightPages = 4; }
        else if($page == 2) { $leftPages = 1; $rightPages = 3; }
        else if($page == $totalPages) { $leftPages = 4; $rightPages = 0; }
        else if($page == $totalPages-1) { $leftPages = 3; $rightPages = 1; }
        else { $leftPages = 2; $rightPages = 2; }

        for($i = $leftPages; $i >= 1; $i--) { //zkontrolujeme, jestli existuje i stranek pred aktualni strankou
            if($page-$i >= 1) array_push($buttons, array($page-$i, false));
        }
        array_push($buttons, array($page, true)); //aktualni stranka
        for($i = 1; $i <= $rightPages; $i++) { //zkontrolujeme, jestli existuje i stranek po aktualni strance
            if($page+$i <= $totalPages) array_push($buttons, array($page+$i, false));
        }

        $pageNavigation = '';

        //strankovani nahore
        $pageNavigation .= '<div class="post-pages">';
        if(!empty($buttons[0][0]) && $buttons[0][0] != 1) {
            $pageNavigation .= '<a href="thread.php?id='.htmlspecialchars($thread['post_id']).'&page=1" class="post-page-number">1</a>';
            if($buttons[0][0] > 2) {
                $pageNavigation .= '<div class="post-page-gap">...</div>';
            }
        }
        foreach($buttons as $button) {
            $pageNavigation .= '<a href="thread.php?id='.htmlspecialchars($thread['post_id']).'&page='.htmlspecialchars($button[0]).'" class="'.( $button[1] ? 'post-page-number-current':'post-page-number').'">'.htmlspecialchars($button[0]).'</a>';
        }
        if(!empty($buttons[4][0]) && $buttons[4][0] != $totalPages) {
            if($buttons[4][0] < $totalPages-1) {
                $pageNavigation .= '<div class="post-page-gap">...</div>';
            }
            $pageNavigation .= '<a href="thread.php?id='.htmlspecialchars($thread['post_id']).'&page='.htmlspecialchars($totalPages).'" class="post-page-number">'.htmlspecialchars($totalPages).'</a>';
        }
        $pageNavigation .= '</div>';

        echo $pageNavigation;

        $posts = $postsQuery->fetchAll(PDO::FETCH_ASSOC);

        //na teto strance budeme chtit parsovat bbcode
        require_once "bbcode/JBBCode/Parser.php";
        $parser = new JBBCode\Parser();
        $parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
        require_once "bbcode/JBBCode/customTags.php";
        require_once "bbcode/JBBCode/visitors/SmileyVisitor.php";
        $smileyVisitor = new \JBBCode\visitors\SmileyVisitor();

        $i = ($page-1)*$configThreadPageMaxPosts;
        foreach($posts as $post) {
            $i++;

            //celkovy pocet prispevku uzivatele
            $allPostsQuery=$db->prepare('SELECT COUNT(user_id) as total_posts FROM '.$configDatabaseTablePosts.' WHERE user_id=:user_id;');
            $allPostsQuery->execute([
                ':user_id' => $post['user_id']
            ]);

            if ($allPostsQuery->rowCount() > 0) {
                $result = $allPostsQuery->fetch();
                $userTotalPosts = $result['total_posts'];
            }

            //parsovani bbcode
            if($post['user_desc_as_signature']) {
                $parser->parse(nl2br(htmlspecialchars($post['user_description'])));
                $parser->accept($smileyVisitor);
                $parsedDescription = $parser->getAsHtml();
            }

            $parser->parse(nl2br(htmlspecialchars($post['text'])));
            $parser->accept($smileyVisitor);
            $parsedText = $parser->getAsHtml();

            echo '<div class="section-header-wrap">
                    <div class="section-header-name">#'.htmlspecialchars($i).( $post['edited'] ? ' (upraveno)':'').'</div>
                    <div class="section-header-name">'.htmlspecialchars(date('d/m/Y \v H:i:s', strtotime($post['created']))).'</div>
                  </div>
    
                  <div class="post-wrap">
                    <div class="post-wrap-left">
                        <a href="profile.php?user='.htmlspecialchars($post['user_name']).'" class="section-post-name post-author-name">'.htmlspecialchars($post['user_name']).'</a>
                        <div class="post-author-role">'.($post['user_role'] === "admin" ? 'Administrátor' : 'Uživatel').'</div>
                        <a href="profile.php?user='.htmlspecialchars($post['user_name']).'" class="post-picture">
                            <img src="img/'.htmlspecialchars($post['user_picture_file']).'" alt="Profilový obrázek" width="100" height="100">
                        </a>
                        <div class="post-author-stat">Registrován: '.htmlspecialchars(date('d/m/Y', strtotime($post['user_registered']))).'</div>
                        <div class="post-author-stat">Napsáno příspěvků: '.htmlspecialchars($userTotalPosts).'</div>
                        <div class="post-author-stat">Umlčen: '.( $post['user_muted'] ? 'Ano':'Ne').'</div>
                        
                        '. ( isset($_SESSION['user_id']) && $_SESSION['user_id'] === $post['user_id'] && !$userMuted ? '<a href="edit_post.php?id='.htmlspecialchars($post['post_id']).'" class="section-post-name post-edit">Upravit</a>':'').'
                    </div>
                    <div class="post-wrap-right">
                        <div>'.$parsedText.'</div><div class="post-like"><strong><a class="section-link-newpost" href="like_post.php?id='.htmlspecialchars($post['post_id']).'&thread_id='.htmlspecialchars($post['post_parent_id']).'&page='.htmlspecialchars($page).'">To se mi líbí (20)</a></strong></div>' . ( $post['user_desc_as_signature'] && mb_strlen($post['user_description'], 'utf-8') > 0 ? '<div class="line"></div><div class="post-user-signature">'.$parsedDescription.'</div>':'').'
                    </div>
                  </div>';
        }

        //strankovani dole
        echo '<div class="line"></div>';
        echo $pageNavigation;

        echo '</div>';

        //formular na odpoved dole
        echo '<div class="main-wrap">';
        if (!$thread['locked']) {
            if (isset($_SESSION['user_id'])) {
                if ($userActivated) {
                    if(!$userMuted) {
                        echo '<form action="" method="post">
                                <label for="new-response">Odpovědět:</label>
                                <textarea id="new-response" name="new-response" rows="10" class="description-edit-textarea">'.htmlspecialchars(@$responseText).'</textarea>
                                '.(!empty($errors['response'])?'<div class="input-error">'.$errors['response'].'</div><br>':'<br>').'
                                <div class="buttons-wrap">
                                    <input type="submit" value="Odpovědět" class="button-primary">
                                </div>
                              </form>';
                    }
                    else {
                        echo 'Jste umlčen, nemůžete odpovědět.';
                    }

                } else {
                    echo 'Pro napsání odpovědi <strong><a href="account_not_activated.php" class="section-link-newpost">aktivujte svůj účet</a></strong>.';
                }
            } else {
                echo 'Pro napsání odpovědi <strong><a href="login.php" class="section-link-newpost">se přihlašte</a></strong>.';
            }
        } else {
            echo 'Toto téma je zamčené.';
        }

        echo '</div>';
    }
    else {
        echo '<div class="main-wrap">';
        echo '<h1>Chyba</h1>Nenalezeny žádné příspěvky.';
        echo '</div>';
    }
}
else {
    echo '<div class="main-wrap">';
    echo '<h1>Chyba</h1>'.htmlspecialchars($error);
    echo '</div>';
}



#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec