<?php
return [
    "params" => [
        "runtimeDirectory" =>  @$_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "runtime",
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
    "dbSphinx" => [
        "adapter" => "mysql",
        "host" => "localhost",
        "name" => "db5",
        "user" => "dbUserdb5",
        "pass" => "124578dbUser",
        "port" => "3306",
        "charset" => "utf8"
    ]
];
