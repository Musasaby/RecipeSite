<?php
/**
 * アプリケーション全体の設定を管理するクラス
 */
class Config {
    // データベース設定
    public static $dbHost = 'localhost';
    public static $dbName = 'test';
    public static $dbUser = 'root';
    public static $dbPassword = '';
    
    // ファイルアップロード設定
    public static $uploadDir = 'uploads/';
    public static $maxFileSize = 5242880; // 5MB (5 * 1024 * 1024)
    public static $allowedExtensions = ['jpg', 'jpeg'];
}