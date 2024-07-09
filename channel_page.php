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

// チャンネルIDの取得
if (isset($_GET['channel_id'])) {
    $channel_id = $_GET['channel_id'];
}

$channel_type = null;
// チャンネルtypeの取得
if (isset($_GET['channel_type'])) {
    $channel_type = $_GET['channel_type'];
}


// チーム情報の取得
$team_sql = 'SELECT `id`, `team_name`, `public` FROM `team_tb` WHERE `id` = :team_id';
$team_sth = $dbh->prepare($team_sql);
$team_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
$team_sth->execute();

// チャンネル情報取得
$channel_sql  = 'SELECT `id`,`name`,`type`,`team_id` FROM `channel_tb` WHERE `team_id` = :team_id';
$channel_sth = $dbh->prepare($channel_sql);
$channel_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
$channel_sth->execute();

// チームのアイコン情報取得
$icon_sql = 'SELECT `file_path` FROM `file_tb` WHERE `team_id` = :team_id';
$icon_sth = $dbh->prepare($icon_sql);
$icon_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
$icon_sth->execute();
$icon_row = $icon_sth->fetch();

// ユーザの役割を確認
$user_id = $_SESSION['id']; // $_SESSION['id'] を使用
$role_sql = 'SELECT `role` FROM `team_member_tb` WHERE `team_id` = :team_id AND `user_id` = :user_id';
$role_sth = $dbh->prepare($role_sql);
$role_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
$role_sth->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$role_sth->execute();
$user_role_row = $role_sth->fetch();

// プライベートチャンネルのメンバー情報取得
$private_channel_member_sql = 'SELECT `channel_id`, `user_id` ,`team_id` FROM `private_channel_member_tb`';
$private_channel_member_sth = $dbh->prepare($private_channel_member_sql);
$private_channel_member_sth->execute();
$private_channel_member_rows = $private_channel_member_sth->fetchAll(PDO::FETCH_ASSOC);

// $user_role の値が取得できたら、$user_role に格納
if ($user_role_row) {
    $user_role = $user_role_row['role'];
} else {
    // エラー処理などが必要な場合はここに記述
}

// コメントの保存処理
if (isset($_POST['comment'])) {
    $comment = $_POST['comment'];

    // ファイルのアップロード処理（仮の例）
    $file_name = $_FILES['file']['name'];
    $file_path = 'uploads/' . $file_name; // 保存先のディレクトリを指定

    move_uploaded_file($_FILES['file']['tmp_name'], $file_path);

    // データベースにコメントを追加
    $comment_sql = 'INSERT INTO `comment_tb` (`channel_id`, `user_id`, `comment`, `file_name`, `file_path`, `created_at`) 
                    VALUES (:channel_id, :user_id, :comment, :file_name, :file_path, NOW())';

    $comment_sth = $dbh->prepare($comment_sql);
    $comment_sth->bindParam(':channel_id', $channel_id, PDO::PARAM_INT);
    $comment_sth->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);
    $comment_sth->bindParam(':comment', $comment, PDO::PARAM_STR);
    $comment_sth->bindParam(':file_name', $file_name, PDO::PARAM_STR);
    $comment_sth->bindParam(':file_path', $file_path, PDO::PARAM_STR);

    $comment_sth->execute();

    // Get the ID of the last inserted comment
    $last_comment_id = $dbh->lastInsertId();
}


// 返信コメントの保存処理
if (isset($_POST['reply_comment'])) {
    $reply_comment = $_POST['reply_comment'];
    $parent_comment_id = $_POST['parent_comment_id'];

    // ファイルのアップロード処理（仮の例）
    $file_name = $_FILES['file']['name'];
    $file_path = 'uploads/' . $file_name; // 保存先のディレクトリを指定

    move_uploaded_file($_FILES['file']['tmp_name'], $file_path);

    // データベースに返信コメントを追加
    $reply_sql = 'INSERT INTO `reply_tb` (`comment_id`, `user_id`, `file_name`, `file_path`, `content`, `created_at`) 
                  VALUES (:comment_id, :user_id, :file_name, :file_path, :content, NOW())';

    $reply_sth = $dbh->prepare($reply_sql);
    $reply_sth->bindParam(':comment_id', $parent_comment_id, PDO::PARAM_INT);
    $reply_sth->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);
    $reply_sth->bindParam(':file_name', $file_name, PDO::PARAM_STR);
    $reply_sth->bindParam(':file_path', $file_path, PDO::PARAM_STR);
    $reply_sth->bindParam(':content', $reply_comment, PDO::PARAM_STR);

    $reply_sth->execute();
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>チームページ</title>
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
    <!-- jQueryの読み込み -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        $(function() {
            // nl2br関数の定義
            function nl2br(str) {
                return str.replace(/\n/g, "<br>");
            }

            // 検索用のボックスが更新される度に呼ばれる
            $('#search_name').on('input', function() {
                console.log("search_name呼ばれた");
                $.ajax({
                        type: "POST",
                        url: "ajax_namesearch.php",
                        datatype: "json",
                        data: {
                            "search_name": $('#search_name').val()
                        }
                    })
                    .then(
                        function(data) {
                            $("#all_show_result tr:not(:first)").remove();
                            $.each(data, function(key, value) {
                                $("#all_show_result").append(
                                    "<tr><td>" +
                                    "<a href='insert_team_member.php?team_id=<?php echo $team_id; ?>&public=<?php echo $_SESSION['public']; ?>&user-id=" +
                                    value.id +
                                    "'>" +
                                    value.username + "</td><td>" +
                                    value.email + "</td></tr>"
                                );
                            });
                            console.log("通信成功(search_name)");
                        },
                        function(XMLHttpRequest, textStatus, errorThrown) {
                            console.log("通信失敗(search_message)!!!");
                            console.log("XMLHttpRequest : " + XMLHttpRequest.status);
                            console.log("textStatus     : " + textStatus);
                            console.log("errorThrown     : " + errorThrown.name);
                        }
                    );
            });
        });

        //参考URL　ドラッグ&ドロップエリア　https://kuwk.jp/blog/dd/

        // ドラッグ&ドロップエリアの取得
        var fileArea = document.getElementById('dropArea');

        // input[type=file]の取得
        var fileInput = document.getElementById('uploadFile');

        // ドラッグオーバー時の処理
        fileArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            fileArea.classList.add('dragover');
        });

        // ドラッグアウト時の処理
        fileArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            fileArea.classList.remove('dragover');
        });

        // ドロップ時の処理
        fileArea.addEventListener('drop', function(e) {
            e.preventDefault();
            fileArea.classList.remove('dragover');

            // ドロップしたファイルの取得
            var files = e.dataTransfer.files;

            // 取得したファイルをinput[type=file]へ
            fileInput.files = files;

            if (typeof files[0] !== 'undefined') {
                //ファイルが正常に受け取れた際の処理
            } else {
                //ファイルが受け取れなかった際の処理
            }
        });

        // input[type=file]に変更があれば実行
        fileInput.addEventListener('change', function(e) {
            var file = e.target.files[0];

            if (typeof e.target.files[0] !== 'undefined') {
                // ファイルが正常に受け取れた際の処理
            } else {
                // ファイルが受け取れなかった際の処理
            }
        }, false);

        // リアクションの処理
        // ボタンがクリックされたときの処理
        $('.reaction-button').click(function() {
            // リアクションの種類を取得
            // 参考URL https://www.sejuku.net/blog/38263
            var reactionType = $(this).data('reaction-type');

            // コメントIDを取得
            var commentId = $(this).siblings('span[id^="reaction-count"]').attr('id').split('-')[2];

            // Ajaxを使用してリアクションを追加する
            $.ajax({
                type: 'POST',
                url: 'add_reaction.php', // リアクション追加処理を行うPHPファイルのパス
                data: {
                    commentId: commentId,
                    reactionType: reactionType
                },
                success: function(response) {
                    // 成功時の処理
                    if (response.success) {
                        // カウントを更新
                        updateReactionCount(commentId);
                    } else {
                        alert('リアクションの追加に失敗しました。');
                    }
                },
                error: function() {
                    // エラー時の処理
                    alert('通信エラーが発生しました。');
                }
            });
        });
    </script>
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
            <p class="text-btn">ログアウト</p>
        </a>
    </div>
    <hr>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <!-- アイコンの表示 -->
                <a href="top_page.php">
                    <p><すべてのチーム</p>
                        <?php
                        // チーム情報が取得できた場合
                        if ($team_row = $team_sth->fetch()) {
                            // チームアイコン表示
                            if ($icon_row && file_exists($icon_row['file_path'])) {
                                echo '<img src="' . $icon_row['file_path'] . '" alt="チームアイコン" width="80px"><br>';
                            }
                            echo '<strong>' . $team_row['team_name'] . '</strong>';
                            $_SESSION['team_name'] = $team_row['team_name'];
                            $_SESSION['public'] = $team_row['public'];
                        } else {
                            echo '<p>チームが見つかりません。</p>';
                        }
                        ?><br>
                        <br><br>
                        <strong>アイコン写真の変更</strong>
                        <?php
                        echo '<form action="update_icon.php?team_id=' . $team_id . '" method="post" enctype="multipart/form-data">' ?>
                        <input type="file" name="icon" accept="image/*">
                        <input type="submit" value="アイコンを変更">
                        </form><br>
                        <br>

                        <?php
                        echo '<strong>チャンネル一覧</strong>';
                        echo '<ul>'; // チャンネル一覧の開始

                        while ($channel_row = $channel_sth->fetch()) {
                            $private_channel = false;

                            foreach ($private_channel_member_rows as $private_channel_member_row) {
                                if (
                                    $channel_row['team_id'] == $team_id &&
                                    $channel_row['type'] == 1 &&
                                    $channel_row['id'] == $private_channel_member_row['channel_id'] &&
                                    $_SESSION['id'] == $private_channel_member_row['user_id'] &&
                                    $team_id == $private_channel_member_row['team_id']
                                ) {
                                    $private_channel = true;
                                    break;
                                }
                            }

                            if ($private_channel) {
                                // チャンネルがプライベートかつユーザーがメンバーの場合
                                echo '<li><a href="channel_page.php?team_id=' . $team_id . '&channel_id=' . $channel_row['id'] . '&channel_type=' . $channel_row['type'] . '">';
                                echo '<p>チャンネル名: ' . $channel_row['name'] . '</p>';
                                echo '<p>非公開</p>';
                                echo '</a></li>';
                            } elseif ($channel_row['type'] != 1 && $channel_row['team_id'] == $team_id) {
                                // チャンネルがプライベートでない場合
                                echo '<li><a href="channel_page.php?team_id=' . $team_id . '&channel_id=' . $channel_row['id'] . '&channel_type=' . $channel_row['type'] . '">';
                                echo '<p>チャンネル名: ' . $channel_row['name'] . '</p>';
                                //  echo '<p>公開</p><br>';
                                echo '</a></li>';
                            }
                        }

                        echo '</ul>'; // チャンネル一覧の終了
                        ?><br>
                        <!-- チャンネル追加 -->
                        <?php
                        echo '<a href="add_channel.php?team_id=' . $team_id . '">チャンネル追加</a>';
                        ?><br>
                        <br>
                        <!-- 所属メンバー一覧 -->
                        <strong>所属メンバー一覧</strong>
                        <table border="1">
                            <tr>
                                <td>名前</td>
                                <td>メールアドレス</td>
                                <td>役割</td>
                            </tr>
                            <?php
                            // チームメンバー情報取得
                            $team_member_sql = 'SELECT `id` ,`team_id` ,`user_id`,`role` FROM `team_member_tb` WHERE `team_id` = :team_id';
                            $team_member_sth = $dbh->prepare($team_member_sql);
                            $team_member_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
                            $team_member_sth->execute();

                            $user_sql = 'SELECT `id`,`email`,`username` FROM `user_tb`';
                            $user_sth = $dbh->prepare($user_sql);
                            $user_sth->execute();
                            $user_rows = $user_sth->fetchAll(PDO::FETCH_ASSOC);


                            $team_member_sql = 'SELECT `id`, `team_id`, `user_id`, `role` FROM `team_member_tb` WHERE `team_id` = :team_id';
                            $team_member_sth = $dbh->prepare($team_member_sql);
                            $team_member_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
                            $team_member_sth->execute();
                            $team_member_rows = $team_member_sth->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($team_member_rows as $team_member_row) {
                                foreach ($user_rows as $user_row) {
                                    if ($user_row['id'] == $team_member_row['user_id']) {
                                        if ($team_id == $team_member_row['team_id']) {
                                            echo '<tr>';
                                            echo '<td>' . $user_row['username'] . '</td>';
                                            echo '<td>' . $user_row['email'] . '</td>';
                                            echo '<td>' . ($team_member_row['role'] == 1 ? '所有者' : 'メンバー') . '</td>'; //1なら所有者、それ以外はメンバー
                                            echo '</tr>';
                                        }
                                    }
                                }
                            }

                            echo '</table>';
                            ?>
                        </table><br>
                        <br>

                        <!-- 役割変更 ここで、所属しているメンバーteam_member_tbのroleを変えることができる-->
                        <!-- 参考URL select https://developer.mozilla.org/ja/docs/Web/HTML/Element/select -->
                        <strong>役割変更</strong>
                        <form action="update_role.php?team_id=<?php echo $team_id; ?>" method="post">
                            <table border="1">
                                <tr>
                                    <td>名前</td>
                                    <td>メールアドレス</td>
                                    <td>現在の役割</td>
                                    <td>新しい役割</td>
                                </tr>

                                <?php
                                foreach ($team_member_rows as $team_member_row) {
                                    foreach ($user_rows as $user_row) {
                                        if ($user_row['id'] == $team_member_row['user_id']) {
                                            if ($team_id == $team_member_row['team_id']) {
                                                echo '<tr>';
                                                echo '<td>' . $user_row['username'] . '</td>';
                                                echo '<td>' . $user_row['email'] . '</td>';
                                                echo '<td>' . ($team_member_row['role'] == 1 ? '所有者' : 'メンバー') . '</td>';
                                                echo '<td>';
                                                echo '<select name="new_role[' . $team_member_row['user_id'] . ']">';
                                                echo '<option value="1">所有者</option>';
                                                echo '<option value="0">メンバー</option>';
                                                echo '</select>';
                                                echo '</td>';
                                                echo '</tr>';
                                            }
                                        }
                                    }
                                }
                                ?>
                            </table>
                            <input type="submit" value="役割を変更">
                        </form><br>
                        <br>

                        <strong>メンバー追加</strong>
                        <div>
                            メンバー検索:<input id="search_name">
                        </div><br>
                        <br>

                        ■メンバー一覧
                        <table border=1 id="all_show_result">
                            <tr>
                                <td>名前</td>
                                <td>メールアドレス</td>
                            </tr>
                        </table>

                        <!-- チームへの招待(リンク) チーム招待のためのリンクをこの下に貼る-->
                        <?php
                        echo '<strong>チームへの招待リンク</strong>';
                        if ($user_role == 1) { // ユーザが所有者の場合のみ招待リンクを表示
                            $invite_link = 'http://' . $_SERVER['HTTP_HOST'] . '/WP2023/14/invite.php?team_id=' . $team_id;
                            echo '<a href="' . $invite_link . '">' . $invite_link . '</a>';

                            // 追加：リンクをQRコードで表示する
                            echo '<br><img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($invite_link) . '" alt="QR Code">';
                        } else {
                            echo '所有者ではないため招待リンクを表示できません。';
                        }
                        ?><br><br>


                        <!-- メンバーの強制退会 -->
                        <br>
                        <strong>メンバーの強制退会(所有者がメンバーを退会させることができます。)</strong>
                        <!-- 役割が所有者の人のみ強制退会させることができる -->
                        <?php if ($user_role == 1) { ?>
                            <form action="delete_member_page.php?team_id=<?php echo $team_id; ?>" method="post">
                                <table border="1">
                                    <tr>
                                        <td>選択</td>
                                        <td>名前</td>
                                        <td>メールアドレス</td>
                                        <td>現在の役割</td>
                                    </tr>
                                    <?php
                                    // チームメンバー情報取得
                                    $team_member_sql = 'SELECT `id`, `team_id`, `user_id`, `role` FROM `team_member_tb` WHERE `team_id` = :team_id';
                                    $team_member_sth = $dbh->prepare($team_member_sql);
                                    $team_member_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
                                    $team_member_sth->execute();

                                    foreach ($team_member_sth as $team_member_row) {
                                        foreach ($user_rows as $user_row) {
                                            if ($user_row['id'] == $team_member_row['user_id']) {
                                                if ($team_member_row['role'] == 0) {
                                                    echo '<tr>';
                                                    echo '<td><input type="checkbox" name="selected_members[]" value="' . $team_member_row['user_id'] . '"></td>';
                                                    echo '<td>' . $user_row['username'] . '</td>';
                                                    echo '<td>' . $user_row['email'] . '</td>';
                                                    echo '<td>' . ($team_member_row['role'] == 1 ? '所有者' : 'メンバー') . '</td>';
                                                    echo '</tr>';
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                </table>
                                <input type="submit" value="メンバーの強制退会">
                            </form>
                        <?php } else { ?>
                        <?php echo '所有者ではないため強制退会できません。';
                        } ?>


                        <!-- プライベートチャンネル追加 -->
                        <?php
                        // プライベートチャンネル情報取得
                        $channel_sql  = 'SELECT `id`, `type` ,name
            FROM `channel_tb` WHERE `team_id` = :team_id';
                        $channel_sth = $dbh->prepare($channel_sql);
                        $channel_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
                        $channel_sth->execute();
                        $channel_rows = $channel_sth->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($channel_rows as $channel_row) {
                            foreach ($private_channel_member_rows as $private_channel_member_row) {
                                if (
                                    $channel_type == 1 &&
                                    $channel_row['id'] == $private_channel_member_row['channel_id'] &&
                                    $_SESSION['id'] == $private_channel_member_row['user_id'] &&
                                    $team_id == $private_channel_member_row['team_id']
                                ) {
                        ?>
                                    <!-- プライベートチャンネルメンバー一覧 -->
                                    <p>メンバー追加（プライベートチャンネル）</p>
                                    <?php if (isset($channel_type) && $channel_type == 1) { ?>
                                        <form action="channel_page.php?team_id=<?php echo $team_id; ?>&channel_id=<?php echo $channel_id; ?>&channel_type=<?php echo $channel_type; ?>" method="post">
                                            <label for="user_id">ユーザネーム:</label>
                                            <select name="user_id" id="user_id">
                                            <?php
                                            foreach ($team_member_rows as $team_member_row) {
                                                foreach ($user_rows as $user_row) {
                                                    if ($user_row['id'] == $team_member_row['user_id'] && $team_id == $team_member_row['team_id']) {
                                                        if (!($user_row['id'] == $_SESSION['id'])) {
                                                            echo '<option value="' . $user_row['id'] . '">' . $user_row['username'] . '</option>';
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                            ?>
                                            </select>
                                            <input type="submit" name="add_member" value="メンバー追加">
                                        </form>
                            <?php
                                }
                            }
                        }

                        if (isset($_POST['add_member'])) {
                            $selected_user_id = $_POST['user_id'];
                            $insert_member_sql = 'INSERT INTO `private_channel_member_tb` (`channel_id`, `user_id`, `team_id`) VALUES (:channel_id, :user_id, :team_id)';
                            $insert_member_sth = $dbh->prepare($insert_member_sql);
                            $insert_member_sth->bindParam(':channel_id', $channel_id, PDO::PARAM_INT);
                            $insert_member_sth->bindParam(':user_id', $selected_user_id, PDO::PARAM_INT);
                            $insert_member_sth->bindParam(':team_id', $team_id, PDO::PARAM_INT);
                            $insert_member_sth->execute();
                        }
                            ?>

            </div>



            <div class="col-md-4">
                <!-- 選択してるチャンネルごとに内容が変わる -->
                <?php
                //選択しているチャンネル名の表示
                foreach ($channel_rows as $channel_row) {
                    if ($channel_row['id'] == $channel_id) {
                        echo '<strong>チャンネル名:' . $channel_row['name'] . '</strong>';
                    }
                }
                ?>

                <!-- コメントの表示 -->
                <?php
                // リアクションを追加する関数
                function addReaction($userId, $commentId, $reactionType)
                {
                    $reactionSql = 'INSERT INTO `reaction_tb` (`user_id`, `comment_id`, `reaction_type`) VALUES (:user_id, :comment_id, :reaction_type)';

                    // 実際のデータベースの接続とプリペアドステートメントの実行
                    $dbh = connectDB(); // 適切なデータベース接続関数を呼び出す
                    $reactionSth = $dbh->prepare($reactionSql);

                    // パラメータのバインド
                    $reactionSth->bindParam(':user_id', $userId, PDO::PARAM_INT);
                    $reactionSth->bindParam(':comment_id', $commentId, PDO::PARAM_INT);
                    $reactionSth->bindParam(':reaction_type', $reactionType, PDO::PARAM_STR);

                    // データベースへの挿入実行
                    $reactionSth->execute();
                }


                // コメント情報取得
                $comment_info_sql = 'SELECT * FROM `comment_tb` WHERE `channel_id` = :channel_id ORDER BY `created_at` ASC';
                $comment_info_sth = $dbh->prepare($comment_info_sql);
                $comment_info_sth->bindParam(':channel_id', $channel_id, PDO::PARAM_INT);
                $comment_info_sth->execute();
                $comment_info_rows = $comment_info_sth->fetchAll(PDO::FETCH_ASSOC);

                foreach ($comment_info_rows as $comment_info_row) {
                    echo '<p>投稿日時: ' . $comment_info_row['created_at'] . '</p>';
                    echo '<p>コメント: ' . $comment_info_row['comment'] . '</p>';



                    if (!empty($comment_info_row['file_name'])) {
                        echo '<p>ファイル名: ' . $comment_info_row['file_name'] . '</p>';
                        echo '<img src="' . $comment_info_row['file_path'] . '" alt="Comment File" width = 300px>';
                    }

                ?>
                    <?php

                    $reply_sql = 'SELECT * FROM `reply_tb` WHERE `comment_id` = :comment_id ORDER BY `created_at` DESC';
                    $reply_sth = $dbh->prepare($reply_sql);
                    $reply_sth->bindParam(':comment_id', $comment_info_row['id'], PDO::PARAM_INT);
                    $reply_sth->execute();
                    $reply_rows = $reply_sth->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($reply_rows as $reply_row) {
                        echo '<p>返信投稿日時: ' . $reply_row['created_at'] . '</p>';
                        echo '<p>' . $reply_row['content'] . '</p>';


                        if (!empty($reply_row['file_name'])) {
                            echo '<p>ファイル名: ' . $reply_row['file_name'] . '</p>';
                            echo '<img src="' . $reply_row['file_path'] . '" alt="Reply File" width = 300px>';
                        }
                    }

                    echo '<a href="channel_page.php?team_id=' . $team_id . '&channel_id=' . $channel_id . '&comment_id=' . $comment_info_row['id'] . '">返信する</a>';

                    if (isset($_GET['comment_id']) && $_GET['comment_id'] == $comment_info_row['id']) {
                        $parent_comment_id = $_GET['comment_id'];
                    ?>
                        <form action="channel_page.php?team_id=<?php echo $team_id; ?>&channel_id=<?php echo $channel_id; ?>" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="parent_comment_id" value="<?php echo $parent_comment_id; ?>">
                            <label for="reply_comment">コメントへの返信:</label>
                            <textarea name="reply_comment" id="reply_comment" rows="3" required></textarea>
                            <!-- ドラッグ＆ドロップエリア -->
                            <div id="upFileWrap">
                                <div id="inputFile">
                                    <!-- ドラッグ&ドロップエリア -->
                                    <p id="dropArea">ここにファイルをドロップしてください<br>または</p>

                                    <div id="inputFileWrap">
                                        <input type="file" name="file" accept=".jpg, .jpeg, .png, .gif, .bmp, .pdf, .php, .js, .css, .html, .txt, .doc, .docx" id="uploadFile">
                                        <div id="btnInputFile"><span>ファイルを選択する</span></div>
                                        <div id="btnChangeFile"><span>ファイルを変更する</span></div>
                                    </div>
                                </div>
                            </div>
                            <input type="submit" value="返信する">
                        </form>
                    <?php
                    }

                    // リアクション取得
                    $reactions_sql = 'SELECT * FROM `reaction_tb` WHERE `comment_id` = :comment_id';
                    $reactions_sth = $dbh->prepare($reactions_sql);
                    $reactions_sth->bindParam(':comment_id', $comment_info_row['id'], PDO::PARAM_INT);
                    $reactions_sth->execute();
                    $reactions = $reactions_sth->fetchAll(PDO::FETCH_ASSOC);

                    $count_reaction = 0;

                    // リアクション表示
                    // echo '<p>Reactions: ' . count($reactions) . '</p>';
                    // echo '<ul>';
                    foreach ($reactions as $reaction) {
                        $count_reaction++;
                        // echo '<li>' . $reaction['user_id'] . '</li>';
                    }
                    echo '</ul>';

                    // コメントへのリアクション追加フォーム
                    echo '<form action="channel_page.php?team_id=' . $team_id . '&channel_id=' . $channel_id . '" method="post">';
                    echo '<input type="hidden" name="comment_id" value="' . $comment_info_row['id'] . '">';
                    echo '<button type="submit" name="add_reaction">❤️</button>';
                    ?>
                    <!-- アカウント表示 -->
                    <span id="reaction-count-<?php echo $comment_info_row['id']; ?>"><?php echo $count_reaction ?></span>
                    <?php
                    echo '</form>';

                    ?>

                <?php
                }


                // リアクションの追加処理
                if (isset($_POST['add_reaction'])) {
                    // フォームから送信されたコメントIDを取得
                    $commentId = $_POST['comment_id'];

                    // リアクション追加関数の呼び出し
                    addReaction($_SESSION['id'], $commentId, 'like'); // ここで 'like' はリアクションの種類を表します
                }
                ?>


                <!-- コメントの投稿フォーム -->
                <div id="fixedForm">
                    <form action="channel_page.php?team_id=<?php echo $team_id; ?>&channel_id=<?php echo $channel_id; ?>" method="post" enctype="multipart/form-data">
                        <label for="comment">コメント:</label>
                        <textarea name="comment" id="comment" rows="3" required></textarea>
                        <div id="upFileWrap">
                            <div id="inputFile">
                                <!-- ドラッグ&ドロップエリア -->
                                <p id="dropArea">ここにファイルをドロップしてください<br>または</p>

                                <div id="inputFileWrap">
                                    <input type="file" name="file" accept=".jpg, .jpeg, .png, .gif, .bmp, .pdf, .php, .js, .css, .html, .txt, .doc, .docx" id="uploadFile">
                                    <div id="btnInputFile"><span>ファイルを選択する</span></div>
                                    <div id="btnChangeFile"><span>ファイルを変更する</span></div>
                                </div>
                            </div>
                        </div>
                        <input type="submit" value="コメント投稿">
                    </form>
                </div>
                <!-- channel_tbのtypeが「1」だった場合プライベートチャンネルになる。プライベートチャンネルはprivate_channel_member_tbのchannel_idと$channel_idが一致する。かつ、private_channel_member_tbのuser_idと
$_SESSION[id]が一致した場合メンバー追加画面が出るようにする-->
                <!-- 追加画面は$team_idとteam_member_tbのteam_idが一致した時、team_member_tbのuser_idを取得し、取得した user_idとuser_tbのidと一致した時、user_tbのusernameとemailをselectで表示する。usernameを選択したら追加ボタンを押す-->
                <!-- 追加ボタンを押された時に選択したusernameと同じ行にあるuser_tbのidを同時に取得し、team_member_tbのuser_idにINSERTする。$team_idと$channel_idも同時に取得する-->


                </br>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>