<?php
//セッションのスタート
session_start();

if (!(isset($_SESSION['login']) && $_SESSION['login'] == 'OK')) {
    header('Location: login.html');
    exit();
}

//接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');

//DBへの接続
$dbh = connectDB();

// チーム情報の取得
$team_sql = 'SELECT `id`, `team_name`, `public`, `owner_id` FROM `team_tb` ORDER BY `id`';
$team_sth = $dbh->query($team_sql); //SQLの実行

//チーム一覧（自分がメンバーのもの）
$team_member_sql = 'SELECT `id`, `team_id`, `user_id`, `role` FROM `team_member_tb`';
$team_member_sth = $dbh->prepare($team_member_sql);
$team_member_sth->execute();

$user_sql = 'SELECT `id` FROM `user_tb`';
$user_sth = $dbh->prepare($user_sql);
$user_sth->execute();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style_X22054.css" rel="stylesheet" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <title>チーム削除</title>
    <style>
        body {
            padding: 20px;
        }

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

        .btn-wrap {
            margin-bottom: 15px;
        }

        .container {
            background-color: #ffffff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }


        .section {
            margin-bottom: 20px;
        }

        .section h2 {
            margin-bottom: 10px;
        }

        .table th,
        .table td {
            text-align: center;
        }

        .table th {
            background-color: #f8f9fa;
        }

        #fixedForm {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #f8f9fa;
            /* フォームの背景色を指定 */
            padding: 10px;
            border-top: 1px solid #dee2e6;
            /* フォームの上部にボーダーを追加 */
        }
    </style>
</head>

<body>
    <h2>
        <?php
        //ログイン確認
        if ((isset($_SESSION['login']) && $_SESSION['login'] == 'OK')) {
            //ログイン成功
            echo 'Login:' . $_SESSION['username'];
        } else {
            //ログイン失敗
            echo '■ログインしていません.';
        }
        ?>
    </h2>
    <hr>
    <div class="btn-wrap">
        <a href="logout.php" >
            <p>ログアウト</p>
        </a>
    </div>
    <hr>
    ■削除チーム一覧(自分の役割が所有者のチームのみ削除できます)
    <form action="delete_team.php" method="post">
        <table border=1 class="design01">
            <tr bgcolor="#CCCCCC">
                <td>選択</td>
                <td>名前</td>
                <td>公開</td>
            </tr>
            <?php
            $team_rows = [];
            while ($row = $team_sth->fetch()) {
                $team_rows[] = $row;
            }
            while ($member_row = $team_member_sth->fetch()) {
                if ($_SESSION['id'] == $member_row['user_id']) {
                    foreach ($team_rows as $team_row) {
                        if ($team_row['id'] == $member_row['team_id']) {
                            if ($member_row['role'] == 1) {
                                echo '<tr>';
                                echo '<td><input type="checkbox" name="check[]" value="' . $team_row['id'] . '"></td>';
                                echo '<td>' . $team_row['team_name'] . '</td>';
                                echo '<td>' .  $member_row['role']. '</td>';
                                echo '</tr>';
                            }
                        }
                    }
                }
            }
            ?>
        </table><br>
        選択した項目を削除しますか。<br>
        <input type="submit" value="消去" >
        <input type="reset" value="リセット" >
    </form>
    <a href="top_page.php">すべてのチーム</a><br>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>
