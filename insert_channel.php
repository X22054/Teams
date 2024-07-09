<?php
//セッションのスタート
session_start();

//接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');

//ログイン確認
if ((isset($_SESSION['login']) && $_SESSION['login'] == 'OK')) {
    //ログイン成功
    echo 'Login:' . $_SESSION['username'];
} else {
    //ログイン失敗
    echo '■ログインしていません.';
}

// チャンネルの名前とタイプを変数に入れる
$channel_name = htmlspecialchars($_POST['name'], ENT_QUOTES);
$channel_type = htmlspecialchars($_POST['type'], ENT_QUOTES);
// チームIDの取得
if (isset($_GET['team_id'])) {
    $team_id = $_GET['team_id'];
}

// DBへの接続
$dbh = connectDB();

if ($dbh) {
    // データベースへの問い合わせSQL文(文字列)
    $sql = 'INSERT INTO `channel_tb` (`name`, `type`, `team_id`) VALUES (:channel_name, :channel_type, :team_id)';
    $sth = $dbh->prepare($sql);
    $sth->bindParam(':channel_name', $channel_name, PDO::PARAM_STR); // チャンネル名をバインド
    $sth->bindParam(':channel_type', $channel_type, PDO::PARAM_INT); // チャンネルタイプをバインド
    $sth->bindParam(':team_id', $team_id, PDO::PARAM_INT); // チームIDをバインド
    if ($sth->execute()) {
        if($channel_type == 1){
        $last_id = $dbh->lastInsertId();//参考URL lastInsertId　https://gray-code.com/php/getting-id-of-last-inserted-data-by-pdo/
        $private_channel_sql = 'INSERT INTO `private_channel_tb` (`channel_id`, `owner_user_id`) 
        VALUES (:channel_id, :owner_user_id)';
        $private_channel_sth = $dbh->prepare($private_channel_sql);
        $private_channel_sth->bindParam(':channel_id', $last_id, PDO::PARAM_INT);
        $private_channel_sth->bindParam(':owner_user_id', $_SESSION['id'], PDO::PARAM_INT);
        $private_channel_sth->execute();

        $private_channel_member_sql = 'INSERT INTO `private_channel_member_tb` (`channel_id`, `user_id`,`team_id`) 
        VALUES (:channel_id, :user_id,:team_id)';
        $private_channel_member_sth = $dbh->prepare($private_channel_member_sql);
        $private_channel_member_sth->bindParam(':channel_id', $last_id, PDO::PARAM_INT);
        $private_channel_member_sth->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);
        $private_channel_member_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
        $private_channel_member_sth->execute();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- 参考URL　https://www.php.net/manual/ja/pdo.prepared-statements.php -->
</head>

<body>
    <hr>
    <a href="menu_message.php">[メニュー]</a>
    <a href="logout.php">[ログアウト]</a><br>
    <hr>
    ■<?php echo $channel_name; ?>を追加しました。<br>
    <br>
    <br>
    <?php
    echo '<a href="team_page.php?team_id=' . $team_id . '">戻る</a>';
    ?>
</body>

</html>
