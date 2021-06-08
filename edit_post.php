<?php

#region zacatek
//hlavni konfiguracni soubor celeho webu
require_once "inc/appConfig.php";

//nacteni pripojeni k databazi
require_once "inc/db.php";

//spusteni session a verifikace uzivatele + zjisteni informaci o nem
require_once "inc/userVerify.php";

//jestlize neni uzivatel prihlaseny, nema cenu, aby na tuto stranku chodil, bude presmerovan na index
require_once "inc/userLoginRequired.php";

//ucet uzivatele musi byt aktivovan a nesmi byt umlcen
if (!$userActivated || $userMuted) {
    header('Location: index.php');
    exit();
}

//zpracovani postu
$errors = array();
if(isset($_POST['post-id']) && isset($_POST['edit-post-text'])) {
    $postName = trim(@$_POST['edit-post-name']);
    $postText = trim($_POST['edit-post-text']);
    $postID = trim($_POST['post-id']);

    $inputName = $postName;
    $inputText = $postText;

    if(isset($_POST['edit-post-name']) && (mb_strlen($postName, 'utf-8') < $configThreadNameMinLen || mb_strlen($postName, 'utf-8') > $configThreadNameMaxLen)) {
        $errors['post-name'] = 'Délka názvu musí být mezi '.$configThreadNameMinLen.' a '.$configThreadNameMaxLen.' znaky.';
    }

    if(mb_strlen($postText, 'utf-8') < $configResponseMinLen || mb_strlen($postText, 'utf-8') > $configResponseMaxLen) {
        $errors['post-text'] = 'Délka textu musí být mezi '.$configResponseMinLen.' a '.$configResponseMaxLen.' znaky.';
    }

    if(empty($errors)) {
        $postQuery=$db->prepare('SELECT * FROM '.$configDatabaseTablePosts.' WHERE post_id=:post_id LIMIT 1;');
        $postQuery->execute([
            ':post_id' => $postID
        ]);

        if($postQuery->rowCount() > 0) {
            $post = $postQuery->fetch();
            if ($post['user_id'] === $_SESSION['user_id']) {
                if($post['post_id'] === $post['post_parent_id'] && isset($_POST['edit-post-name'])) { //tema
                    $updateQuery=$db->prepare('UPDATE '.$configDatabaseTablePosts.' SET name=:name, text=:text, edited=1 WHERE post_id=:post_id LIMIT 1;');
                    $updateQuery->execute([
                        ':name' => $postName,
                        ':text' => $postText,
                        ':post_id' => $postID
                    ]);
                }
                else { //odpoved
                    $updateQuery=$db->prepare('UPDATE '.$configDatabaseTablePosts.' SET text=:text, edited=1 WHERE post_id=:post_id LIMIT 1;');
                    $updateQuery->execute([
                        ':text' => $postText,
                        ':post_id' => $postID
                    ]);
                }

                header('Location: thread.php?id='.$post['post_parent_id'].'&page=1');
                exit();
            }
            else {
                header('Location: index.php');
                exit();
            }
        }
        else {
            header('Location: index.php');
            exit();
        }
    }
}

//zpracovani getu
$error = '';
if(isset($_GET['id'])) {
    if($_GET['id'] > 0) {
        $postQuery=$db->prepare('SELECT * FROM '.$configDatabaseTablePosts.' WHERE post_id=:post_id LIMIT 1;');
        $postQuery->execute([
            ':post_id' => $_GET['id']
        ]);

        if($postQuery->rowCount() > 0) {
            $post = $postQuery->fetch();

            if(!isset($inputName) && !isset($inputText)) {
                $inputName = $post['name'];
                $inputText = $post['text'];
            }
        }
        else $error = 'Téma nenalezeno.';
    }
    else $error = 'Téma nenalezeno.';
}
else $error = 'V odkazu chybí téma.';

//nastaveni bbcode
$loadBBcode = true;
$BBcodeEditorID = 'edit-post-text';
$BBcodeEditorHeight = '500';

//nastaveni title a nacteni headeru
$pageTitle = 'Upravit příspěvěk';
include "inc/html/header.php";
#endregion zacatek

if (mb_strlen($error, 'utf-8') == 0) {
    if ($post['user_id'] === $_SESSION['user_id']) {
        echo '<div class="main-wrap">
                <h1>Upravit příspěvěk</h1>
                <form action="" method="post">
                    <input type="hidden" id="post-id" name="post-id" value="' . htmlspecialchars($post['post_id']) . '">';

        //pokud je to tema, dat moznost zmenit nazev
        if ($post['post_id'] === $post['post_parent_id']) {
            echo '<label for="edit-post-name">Název tématu:</label><br>
                    <input type="text" id="edit-post-name" name="edit-post-name" class="user-input new-topic-name" value="' . htmlspecialchars(@$inputName) . '">
                    ' . (!empty($errors['post-name']) ? '<div class="input-error">' . $errors['post-name'] . '</div>' : '<br>') . '
                    <br>';
        }

        echo '<label for="edit-post-text">Text:</label>
                    <textarea id="edit-post-text" name="edit-post-text" rows="10" class="description-edit-textarea">' . htmlspecialchars(@$inputText) . '</textarea>
                    ' . (!empty($errors['post-text']) ? '<div class="input-error">' . $errors['post-text'] . '</div><br>' : '<br>') . '
                    <div class="buttons-wrap">
                        <input type="submit" value="Upravit" class="button-primary">
                    </div>
                </form>
              </div>';
    } else {
        echo '<div class="main-wrap">';
        echo '<h1>Chyba</h1>Nejste autorem tohoto příspěvku.';
        echo '</div>';
    }
} else {
    echo '<div class="main-wrap">';
    echo '<h1>Chyba</h1>' . htmlspecialchars($error);
    echo '</div>';
}


#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec