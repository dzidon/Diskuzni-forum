<?php

//hlavni konfiguracni soubor celeho webu
require_once "inc/appConfig.php";

//nacteni pripojeni k databazi
require_once "inc/db.php";

//header vhodny pro rest api
header("Content-type: application/json; charset=utf-8");

$json = file_get_contents("php://input");
$data = json_decode($json);
$name = trim($data->name);
$result = 'notfound';

$userQuery=$db->prepare('SELECT banner_file FROM '.$configDatabaseTableUsers.' WHERE name=:name LIMIT 1;');
$userQuery->execute([
    ':name' => $name
]);

if($userQuery->rowCount() > 0) { //uzivatel nalezen
    $user = $userQuery->fetch();
    if($user['banner_file'] !== $configUserNoBanner) $result = 'img/'.$user['banner_file'];
}

echo json_encode(array('res' => $result)); //vrati data klientovi