<?php

//udaje k databazi
$configDatabaseHost = '127.0.0.1';
$configDatabaseName = 'zidd00';
$configDatabasePassword = 'toz4iekeedataf7Eet';
$configDatabaseCharset = 'utf8';

//nazvy tabulek v databazi
$configDatabaseTableUsers = 'sp_users';
$configDatabaseTableForgottenPass = 'sp_forgotten_passwords';
$configDatabaseTableSections = 'sp_sections';
$configDatabaseTablePosts = 'sp_posts';

//nazvy uzivatelskych roli
$configRoleUser = 'user';
$configRoleAdmin = 'admin';

//omezeni uzivatelu
$configUserMinNameLen = 3;
$configUserMaxNameLen = 32;
$configUserMinPassLen = 5;
$configUserMaxPassLen = 255;
$configUserMaxDescriptionLen = 1000;
$configUserMaxInactivity = 3600; //jak dlouho uživatel může zůstat přihlášený bez aktivity (v sekundách)
$configUserActivationInterval = 600; //jak dlouho bude trvat, nez si uzivatel bude moct poslat aktivacni email
$configUserPasswordInterval = 600; //jak dlouho bude trvat, nez si uzivatel bude moct poslat email na obnovu hesla
$configUserMaxProfilePictureSize = 500000; // 500000 = 500 KB
$configUserMaxBannerSize = 1000000; // 1000000 = 1000 KB

//omezeni zobrazovani prispevku
$configHomepageMaxPosts = 5; //kolik se ma maximalne zobrazovat prispevku s posledni aktivitou na hlavni strance v jedne sekci
$configSearchMaxUsers = 30; //kolik uzivatelskych uctu se muze maximalne zobrazit pri jednom hledani
$configSearchMaxPosts = 30; //kolik temat se muze maximalne zobrazit pri jednom hledani
$configProfileMaxPosts = 20; //kolik temat se muze maximalne zobrazit v posledni aktivite na uzivatelskem profilu
$configSectionPageMaxPosts = 1; //kolik temat se muze maximalne zobrazit na jedne strance pri rozkliknuti sekce

//odkazy
$configUserNoProfilePicture = 'noprofilepic.png';
$configUserNoBanner = 'default-banner.jpg';
$configAccountActivationURL = 'https://eso.vse.cz/~zidd00/php/sp/account_activate.php';
$configPasswordResetURL = 'https://eso.vse.cz/~zidd00/php/sp/password_reset_final.php';

//e-maily - aktivace uctu
$configAccountActivationMailSubject = 'Aktivace účtu';
$configAccountActivationMailFrom = 'noreply@superforum.cz';
$configAccountActivationMail1 =
'<html lang="cs">
    <head>
        <title>Aktivační e-mail</title>
    </head>
    <body>
        Vítejte <strong>';
$configAccountActivationMail2 =
'</strong>,<br><br>
děkujeme Vám za registraci na našem fóru! Pro jeho plné používání zbývá udělat poslední krok. Klikněte <strong>';
$configAccountActivationMail3 = '</strong> pro aktivování Vašeho účtu. Tento e-mail byl vygenerován automaticky, proto na něj nemá cenu odpovídat.</body></html>';

//e-maily - obnova hesla
$configPasswordResetMailSubject = 'Obnova hesla';
$configPasswordResetMailFrom = 'noreply@superforum.cz';
$configPasswordResetMail1 =
    '<html lang="cs">
    <head>
        <title>E-mail pro obnovu hesla</title>
    </head>
    <body>
        Dobrý den <strong>';
$configPasswordResetMail2 =
    '</strong>,<br><br>
na Vašem účtu byl vytvořen požadavek na změnu hesla. Klikněte <strong>';
$configPasswordResetMail3 = '</strong> pro zadání nového hesla. Pokud jste o změnu hesla nezažádali Vy, tuto zprávu ignorujte. Požadavek na změnu hesla automaticky vyprší za 60 minut od vytvoření. Tento e-mail byl vygenerován automaticky, proto na něj nemá cenu odpovídat.</body></html>';