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
    //tema a odpovedi
    echo '<div class="main-wrap">';
    echo '<div class="post-top-wrap">
                <h1 class="post-name">'.htmlspecialchars($thread['name']).'</h1>
                <div class="post-top-admin-buttons">
                    <a href="odkazNaPin">
                        <i class="fas fa-thumbtack post-admin-button-pin '.( $thread['pinned'] ? '':'post-admin-button-active').'"></i>
                    </a>
                    <a href="odkazNaLock">
                        <i class="fas fa-lock post-admin-button-lock '.( $thread['locked'] ? '':'post-admin-button-active').'"></i>
                    </a>
                </div>
          </div>';

    //strankovani nahore
    echo '<div class="post-pages">
                <a href="odkazNaStranku" class="post-page-number">1</a>
                <a href="odkazNaStranku" class="post-page-number">2</a>
                <a href="odkazNaStranku" class="post-page-number-current">3</a>
                <a href="odkazNaStranku" class="post-page-number">128</a>
          </div>';

    //ziskame vsechny prispevky v tematu
    $postsQuery=$db->prepare('SET @row_number = 0;
                                    SELECT (@row_number:=@row_number+1) AS num, posts.post_id, posts.post_parent_id, posts.section_id, posts.name, posts.text, posts.created, posts.edited, posts.user_id, users.name AS user_name, users.role AS user_role, users.picture_file AS user_picture_file, users.registered AS user_registered, users.muted AS user_muted
                                    FROM '.$configDatabaseTablePosts.' AS posts JOIN '.$configDatabaseTableUsers.' AS users ON posts.user_id=users.user_id
                                    WHERE post_parent_id=:post_parent_id
                                    ORDER BY created ASC;');
    $postsQuery->execute([
        ':post_parent_id' => $thread['post_parent_id']
    ]);

    //vypsani poslednich prispevku v tematu
    $count = $postsQuery->rowCount();

    //TODO

    //strankovani dole
    echo '<div class="line"></div>
          <div class="post-pages">
            <a href="odkazNaStranku" class="post-page-number">1</a>
            <a href="odkazNaStranku" class="post-page-number">2</a>
            <a href="odkazNaStranku" class="post-page-number-current">3</a>
            <a href="odkazNaStranku" class="post-page-number">128</a>
          </div>';

    echo '</div>';

    //formular na odpoved dole
    echo '<div class="main-wrap">';
    if(!$thread['locked']) {
        if(isset($_SESSION['user_id'])) {
            if($userActivated) {
                echo '<form action="" method="post">
                        <label for="new-response">Odpovědět:</label>
                        <textarea id="new-response" name="new-response" rows="10" class="description-edit-textarea"></textarea>
                        <div class="input-error">Toto je error!</div><br>
                        <div class="buttons-wrap">
                            <input type="submit" value="Odpovědět" class="button-primary">
                        </div>
                      </form>';
            }
            else {
                echo 'Pro napsání odpovědi <strong><a href="account_not_activated.php" class="section-link-newpost">aktivujte svůj účet</a></strong>.';
            }
        }
        else {
            echo 'Pro napsání odpovědi <strong><a href="login.php" class="section-link-newpost">se přihlašte</a></strong>.';
        }
    }
    else {
        echo 'Toto téma je zamčené.';
    }

    echo '</div>';
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