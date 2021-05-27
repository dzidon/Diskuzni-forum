<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=600">
    <meta name="description" content="Nejlepší fórum na světě" />
    <meta name="author" content="David Židoň" />
    <meta name="keywords" content="fórum, forum, forums, diskuze, diskuse" />
    <title><?php echo (!empty($pageTitle) ? htmlspecialchars($pageTitle).' - ':'')?>Fórum</title>

    <!-- Fonty -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css">

    <!-- Styly -->
    <link rel="stylesheet" href="inc/html/styles.css">

    <!-- BBcode -->
    <?php
        if(isset($loadBBcode) && $loadBBcode === true) {
            echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sceditor@3/minified/themes/default.min.css"/>';
        }
    ?>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-wrap">
            <a href="index.php" class="header-logo">
                <i class="fas fa-user-graduate header-logo-icon"></i><span class="header-text">FÓRUM</span>
            </a>
            <nav>
                <?php
                    if(isset($_SESSION['user_id'])) { //prihlasen
                        echo '<a href="profile.php?user='.htmlspecialchars($userName).'" class="nav-button">'.htmlspecialchars($userName).'</a>';
                        echo '<a href="logout.php" class="nav-button">Odhlásit se</a>';
                    }
                    else { //neprihlasen
                        echo '<a href="login.php" class="nav-button">Přihlásit se</a>';
                    }
                ?>
            </nav>
        </div>
    </header>
    <!-- Main wrap -->
    <main>