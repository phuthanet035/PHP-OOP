<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';

/**
 * ==============================================================================
 * คลาส User (คลาสจัดการผู้ใช้งานและระบบล็อกอิน - User Authentication Class)
 * ==============================================================================
 * พัฒนาโดย: สมาชิกใช้งานร่วมกัน (รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม)
 * คำอธิบาย: คลาสที่ทำหน้าที่ในการรับสิทธิ์การล็อกอิน (Login), สมัครสมาชิก (Register),
 * การตรวจสอบสิทธิ์ผ่าน Session และบันทึกรหัสผ่านด้วยเทคนิค bcrypt ความปลอดภัยสูง
 * ==============================================================================
 */
class User
{
    // คุณลักษณะของสมาชิกในระบบ (Encapsulation Properties)
    private int $id;              // รหัสประจำตัวผู้ใช้ (Primary Key)
    private string $username;     // ชื่อบัญชีผู้ใช้งาน
    private string $password;     // รหัสผ่าน (ผ่านการ Hash ปลอดภัย)
    private string $fullname;     // ชื่อ-นามสกุลจริง
    private string $email;        // อีเมลผู้ใช้
    private string $phone;        // เบอร์โทรศัพท์
    private string $role;         // สิทธิ์การใช้งาน ('ADMIN' หรือ 'MEMBER')
    private string $avatar;       // ลิงก์รูปภาพประจำตัว (Avatar Profile Image)
    private string $createdAt;    // วันเวลาที่สร้างบัญชี

    /**
     * คอนสตรัคเตอร์สำหรับกำหนดค่าเริ่มต้นให้กับวัตถุ User
     */
    public function __construct(
        int $id = 0,
        string $username = '',
        string $password = '',
        string $fullname = '',
        string $email = '',
        string $phone = '',
        string $role = 'MEMBER',
        string $avatar = '',
        string $createdAt = ''
    ) {
        $this->id = $id;                                                                 // กำหนด ID
        $this->username = trim($username);                                               // กำหนดชื่อบัญชี ตัดช่องว่าง
        $this->password = $password;                                                     // กำหนดรหัสผ่าน
        $this->fullname = trim($fullname);                                               // กำหนดชื่อจริง
        $this->email = trim($email);                                                     // กำหนดอีเมล
        $this->phone = trim($phone);                                                     // กำหนดเบอร์โทร
        $this->role = strtoupper($role) === 'ADMIN' ? 'ADMIN' : 'MEMBER';               // กำหนดสิทธิ์การใช้งาน
        $this->avatar = $avatar ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150'; // กำหนดรูปประจำตัวเริ่มต้น
        $this->createdAt = $createdAt ?: date('Y-m-d H:i:s');                           // กำหนดวันเวลา
    }

    // --- Encapsulation Getter Methods (ฟังก์ชันเข้าถึงข้อมูลในคลาส) ---
    public function getId(): int { return $this->id; }                                   // อ่านค่า ID
    public function getUsername(): string { return $this->username; }                   // อ่านค่า ชื่อบัญชี
    public function getFullname(): string { return $this->fullname; }                   // อ่านค่า ชื่อ-นามสกุล
    public function getEmail(): string { return $this->email; }                         // อ่านค่า อีเมล
    public function getPhone(): string { return $this->phone; }                         // อ่านค่า เบอร์โทร
    public function getRole(): string { return $this->role; }                           // อ่านค่า สิทธิ์
    public function getAvatar(): string { return $this->avatar; }                       // อ่านค่า ลิงก์รูปภาพ
    public function getCreatedAt(): string { return $this->createdAt; }                 // อ่านค่า วันเวลาสร้าง

    /**
     * ฟังก์ชันสำหรับการยืนยันรหัสผ่าน (Password Verification)
     * 
     * @param string $plainPassword รหัสผ่านข้อความธรรมดาที่ผู้ใช้พิมพ์เข้ามา
     * @return bool คืนค่า true หากรหัสผ่านถูกต้อง
     */
    public function verifyPassword(string $plainPassword): bool
    {
        // ใช้ฟังก์ชันมาตรฐาน password_verify ตรวจสอบ Hash bcrypt
        return password_verify($plainPassword, $this->password);
    }

    /**
     * ฟังก์ชันลงทะเบียนผู้ใช้ใหม่ (User Registration)
     * 
     * @param string $username ชื่อบัญชี
     * @param string $password รหัสผ่าน
     * @param string $fullname ชื่อ-นามสกุล
     * @param string $email อีเมล
     * @param string $phone เบอร์โทรศัพท์
     * @return array คืนค่าสถานะ [success => bool, message => string, user => User|null]
     */
    public static function register(
        string $username,
        string $password,
        string $fullname,
        string $email,
        string $phone = ''
    ): array {
        // 1. ตรวจสอบความถูกต้องของข้อมูลนำเข้า (Validation)
        if (empty($username) || strlen($username) < 3) {
            return ['success' => false, 'message' => 'ชื่อบัญชีต้องมีความยาวอย่างน้อย 3 ตัวอักษร'];
        }
        if (empty($password) || strlen($password) < 4) {
            return ['success' => false, 'message' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 4 ตัวอักษร'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'รูปแบบอีเมลไม่ถูกต้อง'];
        }

        // 2. แฮชรหัสผ่านเพื่อความปลอดภัยด้วย bcrypt
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $avatarUrl = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150';

        // 3. ตรวจสอบการเชื่อมต่อฐานข้อมูล MySQL
        $pdo = Database::getConnection();
        if ($pdo !== null) {
            try {
                // ตรวจสอบชื่อบัญชีหรืออีเมลซ้ำในระบบ
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
                $checkStmt->execute([$username, $email]);
                if ((int)$checkStmt->fetchColumn() > 0) {
                    return ['success' => false, 'message' => 'ชื่อบัญชีหรืออีเมลนี้มีอยู่ในระบบแล้ว'];
                }

                // บันทึกผู้ใช้ใหม่ลงในฐานข้อมูล MySQL
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, password, fullname, email, phone, role, avatar, created_at)
                    VALUES (?, ?, ?, ?, ?, 'MEMBER', ?, NOW())
                ");
                $stmt->execute([$username, $hashedPassword, $fullname, $email, $phone, $avatarUrl]);
                $newId = (int)$pdo->lastInsertId();

                $newUser = new self($newId, $username, $hashedPassword, $fullname, $email, $phone, 'MEMBER', $avatarUrl);
                return ['success' => true, 'message' => 'ลงทะเบียนสำเร็จเข้าใช้งานได้ทันที', 'user' => $newUser];
            } catch (PDOException $e) {
                return ['success' => false, 'message' => 'เกิดข้อผิดพลาดฐานข้อมูล: ' . $e->getMessage()];
            }
        } else {
            // Fallback JSON Mode กรณีไม่มี MySQL
            $usersFile = __DIR__ . '/../data/users.json';
            $usersData = [];
            if (file_exists($usersFile)) {
                $usersData = json_decode(file_get_contents($usersFile), true) ?? [];
            }

            foreach ($usersData as $u) {
                if ($u['username'] === $username || $u['email'] === $email) {
                    return ['success' => false, 'message' => 'ชื่อบัญชีหรืออีเมลนี้มีอยู่ในระบบแล้ว'];
                }
            }

            $newId = count($usersData) + 1;
            $userArray = [
                'id' => $newId,
                'username' => $username,
                'password' => $hashedPassword,
                'fullname' => $fullname,
                'email' => $email,
                'phone' => $phone,
                'role' => 'MEMBER',
                'avatar' => $avatarUrl,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $usersData[] = $userArray;
            @file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $newUser = new self($newId, $username, $hashedPassword, $fullname, $email, $phone, 'MEMBER', $avatarUrl);
            return ['success' => true, 'message' => 'ลงทะเบียนสำเร็จ (JSON Mode)', 'user' => $newUser];
        }
    }

    /**
     * ฟังก์ชันตรวจสอบการเข้าสู่ระบบ (User Login Authentication)
     * 
     * @param string $usernameKey ชื่อบัญชี หรือ อีเมล
     * @param string $plainPassword รหัสผ่าน
     * @return array คืนค่าสถานะการล็อกอิน [success => bool, message => string, user => User|null]
     */
    public static function login(string $usernameKey, string $plainPassword): array
    {
        if (empty($usernameKey) || empty($plainPassword)) {
            return ['success' => false, 'message' => 'กรุณากรอกชื่อบัญชีและรหัสผ่านให้ครบถ้วน'];
        }

        $pdo = Database::getConnection();
        if ($pdo !== null) {
            try {
                // ค้นหาผู้ใช้จากชื่อบัญชี หรือ อีเมล
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
                $stmt->execute([$usernameKey, $usernameKey]);
                $userData = $stmt->fetch();

                if (!$userData) {
                    return ['success' => false, 'message' => 'ไม่พบชื่อบัญชีนี้ในระบบ'];
                }

                // ตรวจสอบความถูกต้องของรหัสผ่าน
                if (!password_verify($plainPassword, $userData['password'])) {
                    return ['success' => false, 'message' => 'รหัสผ่านไม่ถูกต้อง'];
                }

                $user = new self(
                    (int)$userData['id'],
                    $userData['username'],
                    $userData['password'],
                    $userData['fullname'],
                    $userData['email'],
                    $userData['phone'] ?? '',
                    $userData['role'] ?? 'MEMBER',
                    $userData['avatar'] ?? '',
                    $userData['created_at'] ?? ''
                );

                return ['success' => true, 'message' => 'เข้าสู่ระบบสำเร็จ', 'user' => $user];
            } catch (PDOException $e) {
                return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการตรวจสอบข้อมูล: ' . $e->getMessage()];
            }
        } else {
            // Fallback JSON Mode
            $usersFile = __DIR__ . '/../data/users.json';
            if (!file_exists($usersFile)) {
                // สร้างผู้ใช้เริ่มต้นกรณีไม่มีไฟล์
                self::initDefaultUsersJson($usersFile);
            }

            $usersData = json_decode(file_get_contents($usersFile), true) ?? [];
            foreach ($usersData as $u) {
                if ($u['username'] === $usernameKey || $u['email'] === $usernameKey) {
                    if (password_verify($plainPassword, $u['password'])) {
                        $user = new self(
                            (int)$u['id'],
                            $u['username'],
                            $u['password'],
                            $u['fullname'],
                            $u['email'],
                            $u['phone'] ?? '',
                            $u['role'] ?? 'MEMBER',
                            $u['avatar'] ?? '',
                            $u['created_at'] ?? ''
                        );
                        return ['success' => true, 'message' => 'เข้าสู่ระบบสำเร็จ (JSON Mode)', 'user' => $user];
                    } else {
                        return ['success' => false, 'message' => 'รหัสผ่านไม่ถูกต้อง'];
                    }
                }
            }

            return ['success' => false, 'message' => 'ไม่พบชื่อบัญชีนี้ในระบบ'];
        }
    }

    /**
     * ฟังก์ชันสร้างผู้ใช้เริ่มต้นสำหรับ JSON Mode
     */
    private static function initDefaultUsersJson(string $filePath): void
    {
        $defaultPassword = password_hash('admin123', PASSWORD_BCRYPT);
        $userPassword = password_hash('1234', PASSWORD_BCRYPT);

        $defaultUsers = [
            [
                'id' => 1,
                'username' => 'admin',
                'password' => $defaultPassword,
                'fullname' => 'ผู้ดูแลระบบ SpaceHub',
                'email' => 'admin@spacehub.com',
                'phone' => '081-234-5678',
                'role' => 'ADMIN',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'username' => 'suppanat036',
                'password' => $userPassword,
                'fullname' => 'ศุภนัฐ จันทร์เปรม (036)',
                'email' => 'suppanat036@spacehub.com',
                'phone' => '089-876-5432',
                'role' => 'MEMBER',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'username' => 'student035',
                'password' => $userPassword,
                'fullname' => 'สมาชิกทีม 035',
                'email' => 'student035@spacehub.com',
                'phone' => '082-111-2233',
                'role' => 'MEMBER',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];

        @file_put_contents($filePath, json_encode($defaultUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * แปลงวัตถุ User เป็น Array สำหรับส่งผลลัพธ์ผ่าน JSON API
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'fullname' => $this->fullname,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'avatar' => $this->avatar,
            'createdAt' => $this->createdAt
        ];
    }
}
