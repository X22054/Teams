<?php
session_start();
require_once(__DIR__ . '/functions.php');

if (isset($_SESSION['team_id'])) {
    $team_id = $_SESSION['team_id'];
}

$dbh = connectDB();

$channel_sql = 'SELECT `id`,`name`,`type` FROM `channel_tb` ORDER BY `id`';
$channel_sth = $dbh->query($channel_sql);

$post_sth = null;

//チーム一覧（自分が所有者のもの）
$team_sql = 'SELECT `id`, `team_name`, `public`, `owner_id` FROM `team_tb`';
$team_sth = $dbh->prepare($team_sql);
$team_sth->execute();

//チーム一覧（自分がメンバーのもの）
$team_member_sql = 'SELECT `id`, `team_id`, `user_id`, `role` FROM `team_member_tb`';
$team_member_sth = $dbh->prepare($team_member_sql);
$team_member_sth->execute();

$user_sql = 'SELECT `id` FROM `user_tb`';
$user_sth = $dbh->prepare($user_sql);
$user_sth->execute();


// ファイル一覧（指定条件でfile_tbのfailpathを表示）
$file_sql = 'SELECT `id`, `team_id`, `file_path` FROM `file_tb`';
$file_sth = $dbh->prepare($file_sql);
$file_sth->execute();
$file_rows = $file_sth->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>チーム一覧</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2Xof8/JjCDA+9aF4Q5gBTKBxI1z7oFpxLlT" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>

<style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }

      .container {
            background-color: #ffffff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
    </style>
<body>

        <?php
        if (isset($_SESSION['login']) && $_SESSION['login'] == 'OK') {
            echo '<div class="alert alert-success">Login: ' . $_SESSION['username'] . '</div>';
        } else {
            echo '<div class="alert alert-danger">■ログインしていません.</div>';
        }
        ?>

        <hr>

        <div class="btn-wrap">
            <a href="logout.php" class="btn btn-warning">
                <p class="text-btn">ログアウト</p>
            </a>
        </div>

        <hr>
        <div class="container">
        <strong>チーム</strong>
        <hr>
        <div id="posts">
            <ul class="list-group">
                <?php
                $team_rows = [];//$team_rowsの初期化
                while ($row = $team_sth->fetch()) {
                    $team_rows[] = $row;
                }
                while ($member_row = $team_member_sth->fetch()) {
                    if ($_SESSION['id'] == $member_row['user_id']) {
                        foreach ($team_rows as $team_row) { //$team_rowsの配列の各要素に対してループ
                            if ($team_row['id'] == $member_row['team_id']) {
                                foreach ($file_rows as $file_row) {
                                    if ($team_row['id'] == $file_row['team_id']) {
                                        echo '<img src="' . $file_row['file_path'] . '" alt="チームアイコン" width="80px">';
                                    }
                                }
                                echo '<li class="list-group-item"><a href="team_page.php?team_id=' . $team_row['id'] . '" name="team_id">';
                                echo $team_row['team_name'] . '</a></li>';
                               
                            }
                        }
                    }
                }
                ?>
            </ul>

            <button type="button" class="btn btn-primary"><a href="add_link.php" class="text-white text-decoration-none">チームをリンク追加</a></button><br>
<button type="button" class="btn btn-success"><a href="add_team.php" class="text-white text-decoration-none">チーム作成</a></button><br>
<button type="button" class="btn btn-danger"><a href="show_team.php" class="text-white text-decoration-none">チーム削除</a></button><br>

        </div>
    </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>
