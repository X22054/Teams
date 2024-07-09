<?php
// セッションのスタート
session_start();

// 接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');

// チームの名前と公開非公開の値を変数に入れる
$team_name = htmlspecialchars($_POST['team_name'], ENT_QUOTES);
if (isset($_POST['radio']) && $_POST['radio'] == '0') {
    $pub = '0';
} elseif (isset($_POST['radio']) && $_POST['radio'] == '1') {
    $pub = '1';
}

// DBへの接続
$dbh = connectDB();

if (isset($_SESSION['id'])) {
    if ($dbh) {
        // team_tb
        $sql = 'INSERT INTO `team_tb` (`team_name`, `public`, `owner_id`) VALUES (?, ?, ?)';
        $sth = $dbh->prepare($sql);
        $sth->execute([$team_name, $pub, $_SESSION['id']]);

        // 取得したteam_tbのID　　　参考URL lastInsertId　https://kinocolog.com/pdo_lastinsertid/
        $team_id = $dbh->lastInsertId();

        // team_member_tb
        $sql_member = 'INSERT INTO `team_member_tb` (`team_id`, `user_id`, `role`) VALUES (?, ?, ?)';
        $sth_member = $dbh->prepare($sql_member);
        $sth_member->execute([$team_id, $_SESSION['id'], 1]);

          // file_tb
          $sql_icon = 'INSERT INTO `file_tb` (`team_id`,`uploaded_at`) VALUES (?,NOW())';
          $sth_icon = $dbh->prepare($sql_icon);
          $sth_icon->execute([$team_id]);
    }
}


?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    // ログイン確認
    if (isset($_SESSION['login']) && $_SESSION['login'] == 'OK') {
        // ログイン成功
        echo 'Login:' . $_SESSION['username'];
    } else {
        // ログイン失敗
        echo '■ログインしていません.';
    }
    ?>

    <hr>
    <div class="btn-wrap">
        <a href="logout.php" class="btn btn--orange">
            <p class="text-btn">ログアウト</p>
        </a><br>
    </div>
    <hr>
    <hr>
    <a href="menu_message.php">[メニュー]</a>
    <a href="logout.php">[ログアウト]</a><br>
    <hr>
    ■チームを登録しました。<br>
    <br>
    <br>
    <a href="top_page.php">チーム一覧</a>
</body>

</html>
