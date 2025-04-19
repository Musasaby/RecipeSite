<?php
/**
 * ファイルアップロードを処理するクラス
 */
class FileUploader {
    private $uploadDir;
    private $maxFileSize;
    private $allowedExtensions;
    
    /**
     * コンストラクタ
     * 
     * @param string $uploadDir アップロードディレクトリ
     * @param int $maxFileSize 最大ファイルサイズ
     * @param array $allowedExtensions 許可された拡張子
     */
    public function __construct($uploadDir = null, $maxFileSize = null, $allowedExtensions = null) {
        // ?? : null合体演算子を使用して、引数がnullの場合はConfigクラスの設定を使用
        $this->uploadDir = $uploadDir ?? Config::$uploadDir;
        $this->maxFileSize = $maxFileSize ?? Config::$maxFileSize;
        $this->allowedExtensions = $allowedExtensions ?? Config::$allowedExtensions;
        
        // アップロードディレクトリが存在しない場合は作成
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * ファイルをアップロード
     * 
     * @param array $file $_FILESの要素
     * @return array 成功時: ['success' => true, 'filename' => 'ファイル名']
     *               失敗時: ['success' => false, 'message' => 'エラーメッセージ']
     */
    public function upload($file) {
        // ファイルがアップロードされているか確認
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'ファイルが正しくアップロードされていません。'];
        }
        
        // ファイルサイズをチェック
        if ($file['size'] > $this->maxFileSize) {
            return ['success' => false, 'message' => "ファイルサイズが大きすぎます。最大サイズ: " . ($this->maxFileSize / 1024 / 1024) . "MB"];
        }
        
        // 拡張子をチェック
        $originalName = $file['name'];
        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $this->allowedExtensions)) {
            return ['success' => false, 'message' => "許可されていないファイル形式です。許可される形式: " . implode(', ', $this->allowedExtensions)];
        }
        
        // 安全なファイル名を生成
        $safeName = uniqid('img_', true) . '.' . $fileExtension;
        $destination = $this->uploadDir . $safeName;
        
        // ファイルを移動
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => true, 'filename' => $safeName];
        } else {
            return ['success' => false, 'message' => 'ファイルの保存に失敗しました。'];
        }
    }
}