<?php
//セッションのスタート
session_start();

//接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');

//チームの名前と公開非公開の値を変数に入れる
$team_name = htmlspecialchars($_POST['team_name'], ENT_QUOTES);
if (isset($_POST['radio']) && $_POST['radio'] == '0') {
    $pub = '0';
} elseif (isset($_POST['radio']) && $_POST['radio'] == '1') {
    $pub = '1';
}

// DBへの接続
$dbh = connectDB();

if (isset($_SESSION['team_id'])) {
    $team_id = $_SESSION['team_id'];
}

if (isset($_SESSION['id'])) {
    if ($dbh) {
        // team_member_tb
$member_sql = 'INSERT INTO `team_member_tb` (`team_id`, `user_id`,`role`) VALUES (?, ?, ?)';
$member_sth = $dbh->prepare($member_sql);
$member_sth->execute([$team_id,$_SESSION['id']],1);
header('Location: top_page.php');
    }
}

?>


