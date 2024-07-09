<?php
// セッションのスタート
session_start();

// 接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');

// DBへの接続
$dbh = connectDB();

// チームIDの取得
if (isset($_GET['team_id'])) {
    $team_id = $_GET['team_id'];
}

// ユーザーIDの取得
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
}

// チームメンバー情報を挿入
$insert_sql = 'INSERT INTO `team_member_tb` (`team_id`, `user_id`, `role`) VALUES (:team_id, :user_id, 0)';
$insert_sth = $dbh->prepare($insert_sql);
$insert_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
$insert_sth->bindParam(':user_id', $user_id, PDO::PARAM_INT);

if ($insert_sth->execute()) {
    echo '<p>ユーザーがチームに追加されました。</p>';
} else {
    echo '<p>エラーが発生しました。</p>';
}
echo '<a href="team_page.php?team_id=' . $team_id . '&public=' . $_SESSION['public'] . '" name="team_id">チームトップに戻る</a>';
?>
