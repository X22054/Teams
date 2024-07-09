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


?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>チーム招待</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
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
    <?php
    //ログイン確認
    if (isset($_SESSION['login']) && $_SESSION['login'] == 'OK') {
        //ログイン成功
        echo 'Login:' . $_SESSION['username'];
    } else {
        //ログイン失敗
        echo '■ログインしていません.';
    }
    ?>

    <hr>
    <div class="btn-wrap">
        <a href="logout.php">
            <p>ログアウト</p>
        </a>
    </div>
    <hr>
    <div class="container">
    <h1>チームへの招待フォーム</h1>

    <form action="invite.php" method="get">
    <label for="invite_link">招待リンク:</label>
    <input type="text" id="invite_link" name="team_id" required>

    <input type="submit" value="招待する">
</form>


    <a href="top_page.php">チーム一覧に戻る</a><br>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>