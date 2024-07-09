<?php
//セッションのスタート
session_start();

//接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');

// DBへの接続
$dbh = connectDB();

// チームIDの取得
if (isset($_GET['team_id'])) {
    $team_id = $_GET['team_id'];
}

//新しい役割の取得
if (isset($_POST['new_role'])) {
    $new_role = $_POST['new_role'];
}

foreach ($_POST['new_role'] as $user_id => $new_role) {
    $update_role_sql = 'UPDATE `team_member_tb` SET `role` = :new_role WHERE `team_id` = :team_id AND `user_id` = :user_id';
    $update_role_sth = $dbh->prepare($update_role_sql);
    $update_role_sth->bindParam(':new_role', $new_role, PDO::PARAM_INT);
    $update_role_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
    $update_role_sth->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $update_role_sth->execute();
}

// team_page.php?team_id=' . $team_idに戻る
header('Location: team_page.php?team_id=' . $team_id);
exit();
