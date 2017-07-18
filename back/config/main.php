<?php
return [
    "params" => [
        "runtimeDirectory" =>  rtrim(@$_SERVER['DOCUMENT_ROOT'], '/') . "/runtime",
    ],
    "db" => include('db.php'),
    "dbDoctrine" => include('db.php'),
    "dbRemote" => [
        "host" => "192.168.1.134",
        "user" => "remoteUser",
        "pass" => "124578",
        "type" => "mysqli",
        "dbName" => "db5"
    ],
    "dbSphinx" => include('db.php'),
];
