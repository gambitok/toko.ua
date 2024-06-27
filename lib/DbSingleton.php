<?php

require_once __DIR__ . '/mysql_class.php';
require_once __DIR__ . '/mysql.php';

class DbSingleton
{
    private static $instanceDb;
    private static $instanceTokoDb;
    private static $instanceTokoCacheDb;
    private static $instanceTokoEmojiDb;
    private static $instanceMyPartsDb;

    public static function getMyPartsDb()
    {
        if (self::$instanceMyPartsDb === null) {
            self::$instanceMyPartsDb = new myPartsDb();
        }

        return self::$instanceMyPartsDb;
    }

    public static function getDbm()
    {
        if (self::$instanceDb === null) {
            self::$instanceDb = new dbm();
            self::$instanceDb->connect();
        }

        return self::$instanceDb;
    }

    public static function getTokoDb()
    {
        if (self::$instanceTokoDb === null) {
            self::$instanceTokoDb = new db();
            self::$instanceTokoDb->connect();
        }

        return self::$instanceTokoDb;
    }

    public static function getTokoCacheDb()
    {
        if (self::$instanceTokoCacheDb === null) {
            self::$instanceTokoCacheDb = new dbc();
            self::$instanceTokoCacheDb->connect();
        }

        return self::$instanceTokoCacheDb;
    }

    public static function getTokoEmojiDb()
    {
        if (self::$instanceTokoEmojiDb === null) {
            self::$instanceTokoEmojiDb = new dbe();
            self::$instanceTokoEmojiDb->connect();
        }

        return self::$instanceTokoEmojiDb;
    }
}