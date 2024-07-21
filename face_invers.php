<?php
error_log("face_invers.phpが呼び出されました");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    if (isset($_POST["imgdata"]) && isset($_POST["name"])) {
        $imgdata = $_POST["imgdata"];
        $name = $_POST["name"];

        // 取得した画像をデコードする
        $face_picture = file_get_contents($imgdata);

        if ($face_picture === false) {
            echo "画像デコードに失敗しました";
            exit;
        }

        // 画像形式を確認
        $img_info = getimagesizefromstring($face_picture);
        if ($img_info === false) {
            echo "無効な画像形式です";
            exit;
        }

        $image_type = $img_info[2]; // 画像タイプ
        $mime_type = $img_info['mime']; // MIMEタイプ
        error_log("画像タイプ: " . $image_type);
        error_log("MIMEタイプ: " . $mime_type);

        try {
            $pdo = new PDO(
                "mysql:host=localhost;dbname=faceID;charset=utf8",
                "root",
                "",
                [
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );

            $Check = $pdo->prepare("SELECT COUNT(*) from face WHERE name = :name;");
            $Check->bindParam('name', $name, PDO::PARAM_STR);
            $Check->execute();
            $count = $Check->fetchColumn();

            if ($count > 0) {
                echo "この名前は存在しています";
                exit;
            }

            // SQL文を修正して `name` フィールドにも値を挿入する
            $sql = "INSERT INTO face(name, face_picture) VALUES(:name, :face_picture)";
            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':face_picture', $face_picture, PDO::PARAM_LOB);

            $result = $stmt->execute();

            if ($result == true) {
                echo "登録に成功しました";
            } else {
                echo "登録に失敗しました";
            }
        } catch (Exception $e) {
            echo "データベースエラー: " . $e->getMessage() . "<br>";
        }

    } elseif (isset($_POST["loginImgdata"]) && isset($_POST["loginName"])) {
        $loginImgdata = $_POST["loginImgdata"];
        $name = $_POST["loginName"];


        // 取得した画像をデコードする
        $face_picture = file_get_contents($loginImgdata);


        if ($face_picture === false) {
            echo "画像デコードに失敗しました";
            exit;
        }

        try {
            $pdo = new PDO(
                "mysql:host=localhost;dbname=faceID;charset=utf8",
                "root",
                "",
                [
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );

            $sql = "INSERT INTO ninsho(name, face_picture) VALUES(:name, :face_picture)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':face_picture', $face_picture, PDO::PARAM_LOB);

            $result = $stmt->execute();

            if ($result == true) {
                echo "認証の準備ができたお\n";

                exec('/opt/anaconda3/envs/kaji-envi/bin/python /Applications/XAMPP/xamppfiles/htdocs/pro_make/faceID.py 2>&1', $output);
                echo "$output[0]";
            } else {
                echo "できないんだけど泣";
            }


        } catch (Exception $e) {
            echo "データベースエラー: " . $e->getMessage() . "<br>";
        }
        exit;
    } else {
        echo "画像または名前は認証できませんでした";
    }
} else {
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Webカメラ</title>

    </head>
    <link rel="stylesheet" href="face_catliena.css">

    <body>
        <div class="front">
            <h1>何か</h1>
            <button id="registerBtn">新規登録</button><br><br>
            <button id="loginBtn">ログイン</button>
        </div>
        <div id="REG" style="display: none;">

            <div class="position">
                <video id="video" width="1000" height="600" autoplay></video>
                <canvas id="canvas" width="1000" height="600" class="hidden"></canvas>
            </div>
            <div>
                <label for="name">名前を入力してください:</label>
                <input type="text" id="name" name="name"><br>
                <button id="snap" disabled class="position_love">登録</button>
            </div>
        </div>
        <div id="LOGIN" style="display: none;">
            <div class="position">
                <video id="loginVideo" width="1000" height="600" autoplay></video>
                <canvas id="loginCanvas" width="1000" height="600" class="hidden"></canvas>
            </div>
            <div>
                <label for="loginName">名前を入力してください:</label>
                <input type="text" id="loginName" name="loginName"><br>
                <button id="loginSnap" disabled class="position_love">認証</button>
            </div>

        </div>

    </body>
    <script src="face.JS"></script>

    </html>
    <?php
}
?>