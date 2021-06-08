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
if(isset($_POST['new-topic-name']) && isset($_POST['new-topic-text']) && isset($_POST['section-id'])) {
    $threadName = trim($_POST['new-topic-name']);
    $threadText = trim($_POST['new-topic-text']);
    $threadSection = trim($_POST['section-id']);

    if(mb_strlen($threadName, 'utf-8') < $configThreadNameMinLen || mb_strlen($threadName, 'utf-8') > $configThreadNameMaxLen) {
        $errors['topic-name'] = 'Délka názvu musí být mezi '.$configThreadNameMinLen.' a '.$configThreadNameMaxLen.' znaky.';
    }

    if(mb_strlen($threadText, 'utf-8') < $configResponseMinLen || mb_strlen($threadText, 'utf-8') > $configResponseMaxLen) {
        $errors['topic-text'] = 'Délka textu musí být mezi '.$configResponseMinLen.' a '.$configResponseMaxLen.' znaky.';
    }

    if(empty($errors)) {
        $sectionQuery=$db->prepare('SELECT * FROM '.$configDatabaseTableSections.' WHERE section_id=:section_id LIMIT 1;');
        $sectionQuery->execute([
            ':section_id' => $threadSection
        ]);

        if($sectionQuery->rowCount() > 0) {
            $newResponseQuery=$db->prepare('INSERT INTO '.$configDatabaseTablePosts.' (`post_id`, `post_parent_id`, `user_id`, `section_id`, `name`, `text`, `created`, `pinned`, `locked`, `views`, `edited`) VALUES (NULL, 1, :user_id, :section_id, :name, :text, current_timestamp(), 0, 0, 0, 0);');
            $newResponseQuery->execute([
                ':user_id' => $_SESSION['user_id'],
                ':section_id' => $threadSection,
                ':name' => $threadName,
                ':text' => $threadText
            ]);

            $lastID = $db->lastInsertId();

            $updateQuery=$db->prepare('UPDATE '.$configDatabaseTablePosts.' SET post_parent_id=:post_id WHERE post_id=:post_id LIMIT 1;');
            $updateQuery->execute([
                ':post_id' => $lastID
            ]);

            header('Location: thread.php?id='.$lastID.'&page=1');
            exit();
        }
        else {
            header('Location: index.php');
            exit();
        }
    }
}

//nastaveni bbcode
$loadBBcode = true;
$BBcodeEditorID = 'new-topic-text';
$BBcodeEditorHeight = '500';

//nastaveni title a nacteni headeru
$pageTitle = 'Nové téma';
include "inc/html/header.php";
#endregion zacatek


if(isset($_GET['section'])) {
    $sectionQuery=$db->prepare('SELECT * FROM '.$configDatabaseTableSections.' WHERE section_id=:section_id LIMIT 1;');
    $sectionQuery->execute([
        ':section_id' => $_GET['section']
    ]);

    if($sectionQuery->rowCount() > 0) {
        $section = $sectionQuery->fetch();
        echo '<div class="main-wrap">
                <h1>Nové téma v sekci '.htmlspecialchars($section['name']).'</h1>
                <form action="" method="post">
                    <input type="hidden" id="section-id" name="section-id" value="'.htmlspecialchars($section['section_id']).'">
                    <label for="new-topic-name">Název tématu:</label><br>
                    <input type="text" id="new-topic-name" name="new-topic-name" class="user-input new-topic-name" value="'.htmlspecialchars(@$threadName).'">
                    '.(!empty($errors['topic-name'])?'<div class="input-error">'.$errors['topic-name'].'</div>':'<br>').'
                    <br>
                    <label for="new-topic-text">Obsah tématu:</label>
                    <textarea id="new-topic-text" name="new-topic-text" rows="10" class="description-edit-textarea">'.htmlspecialchars(@$threadText).'</textarea>
                    '.(!empty($errors['topic-text'])?'<div class="input-error">'.$errors['topic-text'].'</div><br>':'<br>').'
                    <div class="buttons-wrap">
                        <input type="submit" value="Zveřejnit" class="button-primary">
                        <a href="index.php" class="button-secondary">Domů</a>
                    </div>
                </form>
              </div>';
    }
    else {
        echo '<div class="main-wrap"><h1>Chyba</h1>Sekce nenalezena.</div>';
    }
}
else {
    echo '<div class="main-wrap"><h1>Chyba</h1>V odkazu chybí sekce.</div>';
}


#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec