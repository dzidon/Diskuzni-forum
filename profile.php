<?php

#region zacatek
//hlavni konfiguracni soubor celeho webu
require_once "inc/appConfig.php";

//nacteni pripojeni k databazi
require_once "inc/db.php";

//spusteni session a verifikace uzivatele + zjisteni informaci o nem
require_once "inc/userVerify.php";

//nastaveni title
$pageTitle = 'Profil nenalezen';

//zpracovani getu
$userExists = false;
if(isset($_GET['user'])) {
    $profileQuery=$db->prepare('SELECT * FROM '.$configDatabaseTableUsers.' WHERE name=:name LIMIT 1;');
    $profileQuery->execute([
        ':name' => $_GET['user']
    ]);

    if ($profileQuery->rowCount() > 0) {
        $profile = $profileQuery->fetch();
        $userExists = true;
        $pageTitle = $profile['name'];
    }
}

//nacteni headeru
include "inc/html/header.php";
#endregion zacatek


if($userExists) {
    //na teto strance budeme chtit parsovat bbcode
    require_once "bbcode/JBBCode/Parser.php";
    $parser = new JBBCode\Parser();
    $parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
    require_once "bbcode/JBBCode/customTags.php";
    $parser->parse(nl2br(htmlspecialchars($profile['description'])));

    //bbcode smajlici
    require_once("bbcode/JBBCode/visitors/SmileyVisitor.php");
    $smileyVisitor = new \JBBCode\visitors\SmileyVisitor();
    $parser->accept($smileyVisitor);

    $error = '';

    //kontrola toho, jestli prihlaseny uzivatel vlastni zobrazovany profil
    $ownsThisProfile = false;
    if(isset($_SESSION['user_id'])) {
        if($_SESSION['user_id'] === $profile['user_id']) $ownsThisProfile = true;
    }

    //kontrola souboru, ktery uzivatel nahrava (profilovka/uvodka)
    if(isset($_POST['submit']) && isset($_POST['imageType']) && !empty($_FILES["fileToUpload"]["name"]) && ($_POST['imageType'] === 'profile_picture' || $_POST['imageType'] === 'banner')) {
        if($ownsThisProfile) {
            if($_POST['imageType'] === 'profile_picture') { $target_dir = "img/profile_pictures/"; $subFolderName = 'profile_pictures/'; $userFileName = $userProfilePictureFile; $pictureMaxSize = $configUserMaxProfilePictureSize; }
            else { $target_dir = "img/banners/"; $subFolderName = 'banners/'; $userFileName = $userBannerFile; $pictureMaxSize = $configUserMaxBannerSize; }
            $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
            $uploadOk = true;
            $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

            // jedna se o obrazek?
            $check = @getimagesize($_FILES["fileToUpload"]["tmp_name"]);
            if($check === false) {
                $error = 'Nahrávaný soubor není obrázek nebo je příliš velký. Obrázek nesmí mít více než '.($pictureMaxSize/1000).' KB.';
                $uploadOk = false;
            }
            else {
                // v pripade profilovky zkontrolujeme, ze se jedna o ctverec
                if($_POST['imageType'] === 'profile_picture') {
                    $width = $check[0];
                    $height = $check[1];
                    if($width != $height) {
                        $error = 'Nahrávaný profilový obrázek musí mít stejnou šířku a výšku.';
                        $uploadOk = false;
                    }
                }
            }

            // kontrola velikosti souboru
            if ($_FILES["fileToUpload"]["size"] > $pictureMaxSize) {
                $error = 'Obrázek nesmí mít více než '.($pictureMaxSize/1000).' KB.';
                $uploadOk = false;
            }

            // povolene budou jen urcite formaty
            if($imageFileType !== "jpg" && $imageFileType !== "png" && $imageFileType !== "jpeg" && $imageFileType !== "gif" ) {
                $error = 'Jsou povoleny jen následující formáty: JPG, JPEG, PNG, GIF';
                $uploadOk = false;
            }

            //vse je ok
            if($uploadOk) {
                $removePreviousImage = true;
                if($_POST['imageType'] === 'profile_picture') {
                    if($userProfilePictureFile === $configUserNoProfilePicture) $removePreviousImage = false;
                }
                else {
                    if($userBannerFile === $configUserNoBanner) $removePreviousImage = false;
                }

                //smazeme predeslou profilovku, pokud ma nejakou nastavenou, pokud existuje
                if ($removePreviousImage && file_exists('img/'.$userFileName)) {
                    unlink('img/'.$userFileName);
                }

                $newFilePath = $target_dir.$_SESSION['user_id'].'.'.$imageFileType;
                $upload = move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $newFilePath);
                if($upload) {
                    if($_POST['imageType'] === 'profile_picture') {
                        $updateQuery=$db->prepare('UPDATE '.$configDatabaseTableUsers.' SET picture_file=:picture_file WHERE user_id=:user_id LIMIT 1;');
                        $updateQuery->execute([
                            ':user_id' => $_SESSION['user_id'],
                            ':picture_file' => $subFolderName.$_SESSION['user_id'].'.'.$imageFileType
                        ]);
                    }
                    else {
                        $updateQuery=$db->prepare('UPDATE '.$configDatabaseTableUsers.' SET banner_file=:banner_file WHERE user_id=:user_id LIMIT 1;');
                        $updateQuery->execute([
                            ':user_id' => $_SESSION['user_id'],
                            ':banner_file' => $subFolderName.$_SESSION['user_id'].'.'.$imageFileType
                        ]);
                    }

                    header("Location: profile.php?user=".$userName);
                    exit();
                }
                else $error = 'Došlo k chybě při nahrávání obrázku.';
            }
        }
    }

    //kontrola, jestli existuje soubor profilovky a uvodky, pokud neexistuje, resetuje se v databazi
    if($profile['picture_file'] !== $configUserNoProfilePicture) {
        if (!file_exists('img/'.$profile['picture_file'])) {
            $updateQuery=$db->prepare('UPDATE '.$configDatabaseTableUsers.' SET picture_file=:picture_file WHERE user_id=:user_id LIMIT 1;');
            $updateQuery->execute([
                ':user_id' => $profile['user_id'],
                ':picture_file' => $configUserNoProfilePicture
            ]);
        }
    }
    if($profile['banner_file'] !== $configUserNoBanner) {
        if (!file_exists('img/'.$profile['banner_file'])) {
            $updateQuery=$db->prepare('UPDATE '.$configDatabaseTableUsers.' SET banner_file=:banner_file WHERE user_id=:user_id LIMIT 1;');
            $updateQuery->execute([
                ':user_id' => $profile['user_id'],
                ':banner_file' => $configUserNoBanner
            ]);
        }
    }

    //role
    $profileRoleName = 'Uživatel';
    if($profile['role'] === $configRoleAdmin) $profileRoleName = 'Administrátor';

    //formatovani data registrace
    $time = strtotime($profile['registered']);
    $dateRegistered = date("d/m/Y", $time);

    //mute element
    $linkMute = 'mute.php?uid='.$profile['user_id'];
    $muteElement = '<a href="'.htmlspecialchars($linkMute).'&action=1" class="user-header-name-edit"><i class="fas fa-ban"></i></a>';
    if($profile['muted']) $muteElement = '<a href="'.htmlspecialchars($linkMute).'&action=0" class="user-header-unmute"><i class="fas fa-ban"></i></a>';

    echo '<div class="main-wrap">
            <!-- Header s profilovkou, bannerem a jménem -->
            <div class="user-header-wrap">
                <div class="user-header-banner" id="user-header-banner">
                    '.( $ownsThisProfile ? '<div class="user-header-banner-edit" id="user-header-banner-edit"><i class="fas fa-camera user-header-picture-edit-icon"></i></div>':'').'
                </div>
                <div class="user-header">
                    <div class="user-header-picture">
                        <img src="img/'.htmlspecialchars($profile['picture_file']).'" alt="Profilový obrázek" width="120" height="120" class="user-header-picture-img">
                        '.( $ownsThisProfile ? '<div class="user-header-picture-edit" id="user-header-picture-edit"><i class="fas fa-camera user-header-picture-edit-icon"></i></div>':'').'
                    </div>
                    <div class="user-header-info">
                        <div class="user-header-name-wrap">
                            <h1 class="user-header-name">'.htmlspecialchars($profile['name']).'</h1>
                            '.( $ownsThisProfile ? '<a href="edit_name.php" class="user-header-name-edit"><i class="fas fa-pen"></i></a>':'').'
                            
                            '.( $userRole === $configRoleAdmin ? $muteElement:'').'
                        </div>
                        <div class="user-header-role">'.$profileRoleName.'</div>
                    </div>
                </div>
            </div>

            <!-- Upload profilovky/úvodky -->
            <div id="user-upload-wrap-pp" class="user-picture-upload">
                <form action="" method="post" enctype="multipart/form-data" class="file-upload-form">
                    <i class="fas fa-upload file-upload-icon section-header-icon"></i><div class="file-upload-title">Nahrajte profilový obrázek:</div>
                    <input type="hidden" id="imageType" name="imageType" value="profile_picture">
                    <input type="file" name="fileToUpload" id="fileToUpload" class="file-choose-button">
                    <input type="submit" value="Uložit" name="submit" class="file-upload-button">
                </form>
                <div class="file-upload-buttons">
                    <a href="picture_remove.php?type=profile_picture" class="section-header-button file-upload-button-delete">Smazat</a>
                    <i class="fas fa-times file-upload-button-close" id="file-upload-close-pp"></i>
                </div>
            </div>
            
            <div id="user-upload-wrap-banner" class="user-picture-upload">    
                <form action="" method="post" enctype="multipart/form-data" class="file-upload-form">
                    <i class="fas fa-upload file-upload-icon section-header-icon"></i><div class="file-upload-title">Nahrajte úvodní obrázek:</div>
                    <input type="hidden" id="imageType" name="imageType" value="banner">
                    <input type="file" name="fileToUpload" id="fileToUpload" class="file-choose-button">
                    <input type="submit" value="Uložit" name="submit" class="file-upload-button">
                </form>
                <div class="file-upload-buttons">
                    <a href="picture_remove.php?type=banner" class="section-header-button file-upload-button-delete">Smazat</a>
                    <i class="fas fa-times file-upload-button-close" id="file-upload-close-banner"></i>
                </div>
            </div>

            <!-- Error -->
            '.( mb_strlen($error,'UTF-8') > 0 ?'<div class="input-error">'.$error.'</div>':'').'

            <!-- Popis uživatele -->
            <div class="section-header-wrap">
                <div class="section-header-name"><i class="fas fa-bullhorn section-header-icon"></i>Popis uživatele</div>
                '.( $ownsThisProfile ? '<a href="edit_description.php" class="section-header-button">Upravit</a>':'').'
            </div>
            <div class="user-description" id="user-description">
                '.( mb_strlen($profile['description'],'UTF-8') > 0 ? ($parser->getAsHtml()):'<i>Uživatel o sobě nic nenapsal.</i>').'
            </div>

            <!-- Statistiky uživatele -->
            <div class="section-header-wrap">
                <div class="section-header-name"><i class="fas fa-chart-line section-header-icon"></i>Statistiky uživatele</div>
            </div>

            <!-- Statistika: registrovan -->
            <div class="stat-wrap">
                <div class="stat-left">
                    Registrován
                </div>
                <div class="stat-right">
                    '.htmlspecialchars($dateRegistered).'
                </div>
            </div>
            <div class="line"></div>

            <!-- Statistika: zalozenych temat -->
            <div class="stat-wrap">
                <div class="stat-left">
                    Založených témat
                </div>
                <div class="stat-right">
                    todo
                </div>
            </div>
            <div class="line"></div>

            <!-- Statistika: odpovědí -->
            <div class="stat-wrap">
                <div class="stat-left">
                    Napsáno odpovědí
                </div>
                <div class="stat-right">
                    todo
                </div>
            </div>
            <div class="line"></div>

            <!-- Statistika: celkem příspěvků -->
            <div class="stat-wrap">
                <div class="stat-left">
                    Celkem příspěvků
                </div>
                <div class="stat-right">
                    todo
                </div>
            </div>
            <div class="line"></div>

            <!-- Statistika: umlčen -->
            <div class="stat-wrap">
                <div class="stat-left">
                    Umlčen
                </div>
                <div class="stat-right">
                    '.( $profile['muted'] ? 'Ano':'Ne').'
                </div>
            </div>

            <!-- Nedávná aktivita -->
            <div class="section-header-wrap">
                <div class="section-header-name"><i class="far fa-comments section-header-icon"></i>Nedávná aktivita</div>
            </div>';

    //TODO: temata (posledni aktivita)
    echo '<div class="section-error-noposts">Nenalezeny žádné příspěvky.</div>';

    echo '</div>';
}
else {
    echo '<div class="main-wrap">
            <h1>Chyba</h1>
            Uživatel nenalezen.
          </div>';
}


#region konec
//nacteni footeru
$loadProfileJS = true;
include "inc/html/footer.php";
#endregion konec