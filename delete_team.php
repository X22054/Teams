<?php
// セッションのスタート
session_start();

// ログインしていない場合はログインページにリダイレクト
if (!(isset($_SESSION['login']) && $_SESSION['login'] == 'OK')) {
    header('Location: login.html');
    exit(); // リダイレクト後にスクリプトを終了
}

// チームIDの取得
if (!(isset($_POST['check'][0]))) {
    header('Location: show_team.php');
    exit();
}

// 接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');

// DBへの接続
$dbh = connectDB();

// 選択されたチームIDの取得
$selectedTeamIds = $_POST['check'];

// 削除対象のチーム情報取得用SQL
$team_sql = 'SELECT `id`, `team_name`, `public`, `owner_id` FROM `team_tb` WHERE';
$team_where_clause = '';
$team_flag = false;

// チームメンバー情報削除用SQL
$team_member_sql = 'DELETE FROM `team_member_tb` WHERE';
$team_member_where_clause = '';
$team_member_flag = false;

// ファイル情報削除用SQL
$file_sql = 'DELETE FROM `file_tb` WHERE `team_id` IN (';
$file_flag = false;

// チャンネル情報削除用SQL
$channel_sql = 'DELETE FROM `channel_tb` WHERE `team_id` IN (';
$channel_flag = false;

// チーム招待情報削除用SQL
$invitation_sql = 'DELETE FROM `team_invitation_tb` WHERE `team_id` IN (';
$invitation_flag = false;

// チームチャンネル情報削除用SQL
$team_channel_sql = 'DELETE FROM `team_channel_tb` WHERE `team_id` IN (';
$team_channel_flag = false;

foreach ($selectedTeamIds as $id) {
    if ($team_flag === false) {
        $team_flag = true;
    } else {
        $team_where_clause .= ' OR ';
        $team_member_where_clause .= ' OR ';
        $file_sql .= ', ';
        $channel_sql .= ', ';
        $invitation_sql .= ', ';
        $team_channel_sql .= ', ';
    }

    $team_where_clause .= '`id`=' . (int)$id;
    $team_member_where_clause .= '`team_id`=' . (int)$id;
    $file_sql .= (int)$id;
    $channel_sql .= (int)$id;
    $invitation_sql .= (int)$id;
    $team_channel_sql .= (int)$id;
}

$team_sql .= $team_where_clause;
$team_member_sql .= $team_member_where_clause;
$file_sql .= ')';
$channel_sql .= ')';
$invitation_sql .= ')';
$team_channel_sql .= ')';

// データベース削除を実行するSQL文（例）
$_SESSION['delete'] = $team_sql;
$team_sth = $dbh->query($team_sql); // チーム情報削除

$_SESSION['delete'] = $team_member_sql;
$team_member_sth = $dbh->query($team_member_sql); // チームメンバー情報削除

$_SESSION['delete'] = $file_sql;
$file_sth = $dbh->query($file_sql); // ファイル情報削除

$_SESSION['delete'] = $channel_sql;
$channel_sth = $dbh->query($channel_sql); // チャンネル情報削除

$_SESSION['delete'] = $invitation_sql;
$invitation_sth = $dbh->query($invitation_sql); // チーム招待情報削除

$_SESSION['delete'] = $team_channel_sql;
$team_channel_sth = $dbh->query($team_channel_sql); // チームチャンネル情報削除
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script language="JavaScript">
        function delRecordAlert() {
            res = confirm("このレコードを消去しますか. \n(この操作は取り消しできません)");
            if (res == true) {
                document.delform.submit(); // ここで送信
            } else {
                return false;
            }
        }
    </script>
</head>

<body>
    <?php
    // ログイン確認
    if ((isset($_SESSION['login']) && $_SESSION['login'] == 'OK')) {
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
    <table border=1 class="design01">
        <tr bgcolor="#CCCCCC">
            <td>名前</td>
            <td>公開</td>
            <p class="text-table">
                <?php
                while ($row = $team_sth->fetch()) {
                    echo '<tr>';
                    echo '<td>' . $row['team_name'] . '</td>';
                    echo '<td>' . $row['public'] . '</td>';
                    echo '</tr>';
                }
                ?>
            </p>
    </table><br>
    <form action="delete2_team.php" method="POST" name="delform">
        <input type="submit" value="削除" class="btn btn--blue" onclick="return delRecordAlert()">
        <?php header('Location:top_page.php');?>
    </form>
</body>

</html>
