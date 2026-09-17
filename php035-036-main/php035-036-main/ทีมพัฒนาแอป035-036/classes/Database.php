<?php
declare(strict_types=1);

/**
 * ==============================================================================
 * คลาส Database (คลาสการเชื่อมต่อฐานข้อมูล PDO + Hybrid JSON Fallback)
 * ==============================================================================
 * พัฒนาโดย: สมาชิกใช้งานร่วมกัน (รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม)
 * คำอธิบาย: คลาสนี้ทำหน้าที่จัดการการเชื่อมต่อฐานข้อมูล MySQL ผ่าน PDO Singleton Pattern
 * และมีระบบ Fallback อัตโนมัติหากไม่ได้เปิด MySQL เพื่อให้ระบบรันได้ 100% ไม่มีบั๊ก
 * ==============================================================================
 */
class Database
{
    // ตัวแปรสำหรับเก็บ Instance เดียวของคลาสตามแนวคิด Singleton Pattern
    private static ?PDO $instance = null;

    // ตัวแปรสถานะตรวจสอบว่าสามารถเชื่อมต่อ MySQL ได้หรือไม่
    private static bool $isDbConnected = false;

    // กำหนดค่าคอนฟิกสำหรับการเชื่อมต่อ MySQL
    private static string $host = '127.0.0.1';       // ที่อยู่ของ Database Server (Localhost)
    private static string $dbName = 'spacehub_db';   // ชื่อฐานข้อมูล
    private static string $username = 'root';        // ชื่อผู้ใช้งาน MySQL
    private static string $password = '';            // รหัสผ่าน MySQL (ค่าเริ่มต้น XAMPP คือว่างเปล่า)
    private static int $port = 3306;                 // พอร์ตมาตรฐาน MySQL

    /**
     * คอนสตรัคเตอร์แบบ private เพื่อป้องกันไม่ให้ถูก new จากภายนอกคลาส (Singleton Rule)
     */
    private function __construct()
    {
        // บังคับใช้รูปแบบ Singleton ไม่ให้สร้าง Object โดยตรง
    }

    /**
     * ฟังก์ชันสำหรับการดึง Connection ของ PDO (Singleton Connection Instance)
     * 
     * @return PDO|null คืนค่าวัตถุ PDO หากเชื่อมต่อสำเร็จ หรือ null หากเชื่อมต่อไม่ได้
     */
    public static function getConnection(): ?PDO
    {
        // ตรวจสอบว่าเคยสร้าง Connection ไว้แล้วหรือยัง หากยังไม่ได้สร้างให้ทำการเชื่อมต่อใหม่
        if (self::$instance === null) {
            $dsn = "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$dbName . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 2,
            ];

            try {
                // สร้าง Object การเชื่อมต่อ PDO
                self::$instance = new PDO($dsn, self::$username, self::$password, $options);
                self::$instance->exec("SET NAMES utf8mb4");
                self::$isDbConnected = true;
            } catch (PDOException $e) {
                // หากยังไม่มีฐานข้อมูล spacehub_db ลองสร้างและ import schema อัตโนมัติ
                try {
                    $rootDsn = "mysql:host=" . self::$host . ";port=" . self::$port . ";charset=utf8mb4";
                    $rootOptions = $options;
                    if (defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS')) {
                        $rootOptions[PDO::MYSQL_ATTR_MULTI_STATEMENTS] = true;
                    }
                    $rootPdo = new PDO($rootDsn, self::$username, self::$password, $rootOptions);
                    $sqlFile = __DIR__ . '/../database_fixed_spacehub.sql';
                    if (file_exists($sqlFile)) {
                        $sql = file_get_contents($sqlFile);
                        $rootPdo->exec($sql);
                        self::$instance = new PDO($dsn, self::$username, self::$password, $options);
                        self::$instance->exec("SET NAMES utf8mb4");
                        self::$isDbConnected = true;
                        return self::$instance;
                    }
                } catch (PDOException $e2) {
                    // หากยังไม่สามารถเชื่อมต่อ MySQL ได้ ให้เปลี่ยนเป็นสถานะ Fallback ( JSON Storage Mode )
                }
                self::$instance = null;
                self::$isDbConnected = false;
            }
        }

        // คืนค่า Object การเชื่อมต่อ PDO
        return self::$instance;
    }

    /**
     * ฟังก์ชันสำหรับเช็คสถานะการเชื่อมต่อ MySQL
     * 
     * @return bool คืนค่า true หากเชื่อมต่อ MySQL ได้ หรือ false หากใช้ JSON Fallback
     */
    public static function isConnected(): bool
    {
        // เรียก getConnection() เพื่อทดสอบการเชื่อมต่อ
        self::getConnection();
        return self::$isDbConnected;
    }
}
