<?php

$db = new PDO('mysql:host='.$configDatabaseHost.';dbname='.$configDatabaseName.';charset='.$configDatabaseCharset, $configDatabaseName, $configDatabasePassword);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);