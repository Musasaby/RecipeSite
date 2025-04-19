<?php
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/'; // 保存先ディレクトリ（Web公開されている場所）
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $originalName = $_FILES['image']['name'];
    $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);

    // ファイルサイズ制限（5MB 以下）
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    if ($_FILES['image']['size'] > $maxFileSize) {
        echo "ファイルサイズが大きすぎます。5MB 以下のファイルをアップロードしてください。";
        exit;
    }

    // 許可する拡張子
    $allowedExtensions = ['jpg', 'jpeg'];
    if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
        echo "許可されていないファイル形式です。";
        exit;
    }

    // 安全なファイル名に変換
    $safeName = uniqid('img_', true) . '.' . $fileExtension;
    $destination = $uploadDir . $safeName;

    // ディレクトリが存在しない場合は作成
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // ファイルを移動
    if (move_uploaded_file($fileTmpPath, $destination)) {
        echo "アップロード成功！ファイル名: " . htmlspecialchars($safeName);
        // 必要ならこのファイル名をMySQLに保存する
    } else {
        echo "アップロード失敗しました。";
    }
} else {
    echo "ファイルが正しくアップロードされていません。";
}
?>
