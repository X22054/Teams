<?php
//セッションのスタート
session_start();

//接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');

//ログイン確認
if ((isset($_SESSION['login']) && $_SESSION['login'] == 'OK')) {
    //ログイン成功
    echo 'Login:' . $_SESSION['name'];
} else {
    //ログイン失敗
    echo '■ログインしていません.';
}

 // チームIDとユーザIDを取得
 $team_id = $_GET['team_id'];
 $user_ids = $_POST['user_ids']; // ここで、JavaScriptから受け取るユーザIDの配列を取得する

// DBへの接続
$dbh = connectDB();

if ($dbh) {
    // データベースへの問い合わせSQL文(文字列)
    $sql = 'INSERT INTO teamsub_tb (teamsub_id, user_id) VALUES (?, ?)';
    $sth = $dbh->prepare($sql);
    $sth->execute([$team_id, $user_id]);
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
<?php
    //ログイン確認
    if (isset($_SESSION['login']) && $_SESSION['login'] == 'OK') {
        //ログイン成功
        echo 'Login:' . $_SESSION['name'];
    } else {
        //ログイン失敗
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
    <a href="logout.php">[ログアウト]</a><br>
    <hr>
    ■メンバーを追加しました。<br>
    <br>
    <br>
    <?php
    echo '<a href="team_page.php?team_id=' . $_GET['team_id'] . '">戻る</a>';
    ?>
</body>

</html>
