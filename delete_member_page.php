<?php
// セッションのスタート
session_start();

if (!(isset($_SESSION['login']) && $_SESSION['login'] == 'OK')) {
    header('Location: login.html');
}

// 接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');

// DBへの接続
$dbh = connectDB();

    // 選択されたメンバーを取得
    $selectedMembers = $_POST['selected_members'];

    if (!empty($selectedMembers)) {
        // 選択されたメンバーを強制退会する処理を実装
        $placeholders = rtrim(str_repeat('?,', count($selectedMembers)), ',');
        $sql = "DELETE FROM `team_member_tb` WHERE `user_id` IN ($placeholders)";
        
        $stmt = $dbh->prepare($sql);
        $stmt->execute($selectedMembers);

        // ここで成功時の処理を追加
        echo 'メンバーの強制退会が成功しました。';
    } else {
        echo '選択されたメンバーがありません。';
    }

?>
