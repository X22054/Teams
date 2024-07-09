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

// チーム情報の取得
$team_sql = 'SELECT `id`, `team_name`, `public` FROM `team_tb` WHERE `id` = :team_id';
$team_sth = $dbh->prepare($team_sql);
$team_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
$team_sth->execute();

// チームアイコン情報の取得
$icon_sql = 'SELECT `file_path` FROM `file_tb` WHERE `team_id` = :team_id';
$icon_sth = $dbh->prepare($icon_sql);
$icon_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
$icon_sth->execute();
$icon_row = $icon_sth->fetch();

// チャンネル情報取得
$channel_sql  = 'SELECT `id`,`name`,`type`,`team_id` FROM `channel_tb`';
$channel_sth = $dbh->prepare($channel_sql);
$channel_sth->execute();

// チームメンバー情報取得
$member_sql = 'SELECT `id`,`team_id`,`user_id`,`role` FROM `team_member_tb` WHERE `team_id` = :team_id';
$member_sth = $dbh->prepare($member_sql);
$member_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
$member_sth->execute();

if (isset($_GET['user-id'])) {
    $user_id = $_GET['user-id'];
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>チームページ</title>
    <!-- 参考URL URL クエリパラメータの正しい設定方法 https://help.webantenna.info/8821/ -->
    <!-- 参考URL 検索フォームを作り https://www.sejuku.net/blog/104455 -->

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
    <!-- アイコンの表示 -->

    <?php
    // チーム情報が取得できた場合
    if ($team_row = $team_sth->fetch()) {
        echo '<h1>チーム名: ' . $team_row['team_name'] . '</h1>';
        $_SESSION['team_name'] = $team_row['team_name'];
        $_SESSION['public'] = $team_row['public'];

        // チームアイコンがあれば表示
        if ($icon_row && file_exists($icon_row['file_path'])) {
            echo '<img src="' . $icon_row['file_path'] . '" alt="チームアイコン">';
        } else {
            echo '<p>チームアイコンがありません。</p>';
        }
    } else {
        echo '<p>チームが見つかりません。</p>';
    }

    // 出力
    echo "Team ID  $team_id";
    ?>
    <p>アイコン写真の変更</p><br>
    <?php
    echo '<form action="update_icon.php?team_id=' . $team_id . '" method="post" enctype="multipart/form-data">' ?>
    <input type="file" name="icon" accept="image/*">
    <input type="submit" value="アイコンを変更">
    </form>

    <?php
    echo '<p>チャンネル一覧</p><br>';
    echo '<ul>'; // チャンネル一覧の開始

    while ($channel_row = $channel_sth->fetch()) {
        if ($channel_row['team_id'] == $team_id) {
            echo '<li><a href="channel_page.php?team_id=' . $team_id . '&channel_id=' . $channel_row['id'] . '">';
            echo '<p>チャンネル名: ' . $channel_row['name'] . '</p>';
            if ($channel_row['type'] == 0) {
                echo '<p>非公開</p><br>';
            } else {
                echo '<p>公開</p><br>';
            }
            echo '</a></li>';
        }
    }

    echo '</ul>'; // チャンネル一覧の終了

    // ユーザー情報の取得
    $user_sql = 'SELECT `id`, `username`, `email` FROM `user_tb` WHERE `id` = :user_id';
    $user_sth = $dbh->prepare($user_sql);
    $user_sth->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $user_sth->execute();

    if ($user_row = $user_sth->fetch()) {
        echo '<p>選択したユーザーの情報:</p>';
        echo '<p>ユーザー名: ' . $user_row['username'] . '</p>';
        echo '<p>Email: ' . $user_row['email'] . '</p>';

        // チームメンバー情報取得
        $team_member_sql = 'SELECT `id`,`team_id`,`user_id`,`role` FROM `team_member_tb` WHERE `team_id` = :team_id AND `user_id` = :user_id';
        $team_member_sth = $dbh->prepare($team_member_sql);
        $team_member_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
        $team_member_sth->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $team_member_sth->execute();

        if ($team_member_row = $team_member_sth->fetch()) {
            echo '<p>すでにチームメンバーです。</p>';
        } else {
            ?>
            <form action="insert_member_page.php?team_id=<?php echo $team_id; ?>&user_id=<?php echo $user_id; ?>" method="post">
                <input type="submit" value="チームに追加する">
            </form>
    <?php
        }
    } else {
        echo '<p>ユーザーが見つかりません。</p>';
    }
}
?>
    <p>チャンネル追加</p><br>
    <form action="insert_channel.php?team_id=<?php echo $team_id; ?>" method="post">
        <input type="hidden" name="team_id" value="<?php echo $team_id; ?>">
        チャンネル名:<br>
        <input type="text" name="name" size="50"><br><br>
        <div>
            <label><input type="radio" name="radio_channel" class="radio" value="0">プライベート</label>
            <label><input type="radio" name="radio_channel" class="radio" value="1">パブリック</label>
        </div>
        <input type="submit" value="登録" class="btn btn-outline-primary">
    </form>

    <a href="top_page.php">チーム一覧に戻る</a><br>
    <?php
    echo '<a href="team_page.php?team_id=' . $team_id . '&public=' . $_SESSION['public'] . '" name="team_id">チームトップに戻る</a>';
    ?>
</body>

</html>
