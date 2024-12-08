<?php
$host = 'postgres';
$user = 'db_user';
$pass = 'db_password';
$db = 'test_database';

$pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);

if (!$pdo) {
    die("Connection failed");
}

// values in hand
$table_of_memes = file_get_contents('definition.sql', true);
$memes = file_get_contents('funny.sql',true);

// setting up the tables
$stmt  = $pdo->prepare($table_of_memes);
$stmt->execute();

// filling the tables
$stmt  = $pdo->prepare($memes);
$stmt->execute();

// telling a funny joke ....
$stmt = $pdo->prepare("SELECT message FROM jokes ORDER BY RANDOM() LIMIT 1");
$stmt->execute();
$joke = $stmt->fetch(PDO::FETCH_ASSOC);

echo $message = ($joke ? $joke['message'] : 'sadly there are no more jokes ....');

// So you can ask another joke, we delete the table and go again, it is super inefficient but this is just an example project
$pdo->exec("DROP TABLE jokes");

?>