<?php
// セッションのスタート
session_start();

// 接続用関数の呼び出し
require_once(__DIR__ . '/functions.php');
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>リンクで追加</title>
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
// チームIDの取得
if (isset($_GET['team_id'])) {
    // URLからteam_idの値を抽出する
    $url = $_GET['team_id'];
    // 参考URL preg_match　https://www.php.net/manual/ja/function.preg-match.php
    //参考URL preg_match https://codelikes.com/php-preg-match/
    preg_match('/[?&]team_id=(\d+)/', $url, $matches);
    
    if (isset($matches[1])) {
        $team_id = (int)$matches[1]; // 数値以外を取り除く
        if ($team_id > 0 && isset($_SESSION['id'])) {

            // チームメンバーの追加処理
            $dbh = connectDB();
            $team_member_sql = 'INSERT INTO `team_member_tb` (`team_id`, `user_id`, `role`) VALUES (?, ?, ?)';
            $team_member_sth = $dbh->prepare($team_member_sql);
            $team_member_sth->execute([$team_id, $_SESSION['id'], 0]);

            // 成功した場合のリダイレクトなど
            echo 'ユーザがチームに招待されました。';
            // header('Location: top_page.php');
            exit;
        }
    }
} else {
    // チームIDが指定されていない場合はエラー処理など
    echo 'チームIDが指定されていません。';
    exit;
}
?>
<a href="top_page.php">チーム一覧に戻る</a><br>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>