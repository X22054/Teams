<?php
// セッションのスタート
session_start();

// 接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');


// DBへの接続
$dbh = connectDB();

// チャンネル一覧の取得
$channel_sql = 'SELECT `id`,`name`,`type`,`team_id` FROM `channel_tb` ORDER BY `id`';
$channel_sth = $dbh->query($channel_sql); // SQLの実行

$post_sth = null;


// 取得
$post_sql = 'SELECT `id`, `team_name` FROM `team_tb`';
$post_sth = $dbh->prepare($post_sql);
$post_sth->execute();

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!--参考URL URL クエリパラメータの正しい設定方法 https://help.webantenna.info/8821/ -->
    <!--参考URL　検索フォームを作り https://www.sejuku.net/blog/104455 -->
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
    <title>Document</title>
</head>

<body>
    <?php
    //ログイン確認
    if ((isset($_SESSION['login']) && $_SESSION['login'] == 'OK')) {
        //ログイン成功
        echo 'Login:' . $_SESSION['username'];
    } else {
        //ログイン失敗
        echo '■ログインしていません.';
    }

    //接続用関数の呼び出し
    require_once(__DIR__ . '/functions.php');
    ?>
    <hr>
    <div class="btn-wrap">
        <a href="logout.php">
            <p class="text-btn">ログアウト</p>
        </a>
    </div>
    <hr>
    <div class="container">
    <h1>チーム作成</h1>
    <div id="posts">
        <div id="post_list">

            <form action="insert_team.php" method="post">
                名前:<br>
                <input type="text" name="team_name" size="50"><br><br>
                <div>
                    <label><input type="radio" name="radio" class="radio" value="0">プライベート</label>
                    <label><input type="radio" name="radio" class="radio" value="1">パブリック</label>
                </div>
                <input type="submit" value="登録" class="btn btn-outline-primary">
            </form>
            <a href="top_page.php">戻る</a><br>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>