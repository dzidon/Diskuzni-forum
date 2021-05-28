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
$userSearch=$db->prepare('SELECT * FROM '.$configDatabaseTableUsers.' WHERE name LIKE :word LIMIT '.$configSearchMaxUsers.';');
$userSearch->execute([
    ':word' => '%'.$searched.'%'
]);

//nacteni nekolika prispevku podle hledaneho vyrazu
//TODO: sql dotaz

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
              </div>
              <div class="section-error-noposts">Nebyla nalezena žádná témata odpovídající hledanému výrazu.</div>';

echo '</div>';


#region konec
//nacteni footeru
include "inc/html/footer.php";
#endregion konec