<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use App\Notifications\LineMessagingService;
use App\Repositories\CategoryRepository;
use App\Repositories\UserRepository;

/**
 * Admin Management Controller
 * Handles user management, categories, and notification settings
 */
class AdminController extends BaseController
{
    private UserRepository $userRepo;
    private CategoryRepository $categoryRepo;
    private LineMessagingService $lineService;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
        $this->categoryRepo = new CategoryRepository();
        $this->lineService = new LineMessagingService();
    }

    public function users(Request $request): void
    {
        $roleFilter = $request->get('role');
        $users = $this->userRepo->getUsersByRole($roleFilter);
        $roleCounts = $this->userRepo->countByRole();

        $this->view('admin/users', [
            'users'       => $users,
            'roleCounts'  => $roleCounts,
            'currentRole' => $roleFilter,
        ]);
    }

    public function createUser(Request $request): void
    {
        $name = trim($request->post('name', ''));
        $email = trim($request->post('email', ''));
        $role = $request->post('role', 'user');
        $password = (string) $request->post('password', '');
        $lineUserId = trim($request->post('line_user_id', ''));

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'password' => $password,
        ], [
            'name' => 'required|min:3|max:100',
            'email' => 'required|email|max:150',
            'role' => 'required|in:user,technician,admin',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            View::setFlash('error', $validator->firstError());
            $this->redirect('/admin/users');
            return;
        }

        if ($this->userRepo->findByEmail($email)) {
            View::setFlash('error', 'อีเมลนี้ถูกใช้งานแล้วในระบบ');
            $this->redirect('/admin/users');
            return;
        }

        $this->userRepo->createWithPassword($name, $email, $password, $role, $lineUserId ?: null);
        View::setFlash('success', "เพิ่มผู้ใช้งาน '{$name}' ในฐานะ {$role} สำเร็จ");
        $this->redirect('/admin/users');
    }

    public function categories(Request $request): void
    {
        $categories = $this->categoryRepo->allWithCount();
        $this->view('admin/categories', ['categories' => $categories]);
    }

    public function createCategory(Request $request): void
    {
        $name = trim($request->post('name', ''));
        $description = trim($request->post('description', ''));

        $validator = Validator::make([
            'name' => $name,
        ], [
            'name' => 'required|min:2|max:100',
        ]);

        if ($validator->fails()) {
            View::setFlash('error', $validator->firstError());
            $this->redirect('/admin/categories');
            return;
        }

        $this->categoryRepo->create([
            'name' => $name,
            'description' => $description,
        ]);

        View::setFlash('success', "เพิ่มหมวดหมู่อุปกรณ์ '{$name}' เรียบร้อยแล้ว");
        $this->redirect('/admin/categories');
    }

    public function deleteCategory(Request $request): void
    {
        $id = (int) $request->param('id');
        try {
            $this->categoryRepo->delete($id);
            View::setFlash('success', 'ลบหมวดหมู่อุปกรณ์เรียบร้อยแล้ว');
        } catch (\Throwable $e) {
            View::setFlash('error', 'ไม่สามารถลบหมวดหมู่นี้ได้เนื่องจากมีใบแจ้งซ่อมใช้งานอยู่');
        }
        $this->redirect('/admin/categories');
    }

    public function lineLogs(Request $request): void
    {
        $logs = LineMessagingService::getRecentLogs(25);
        $hasToken = !empty(trim($_ENV['LINE_CHANNEL_ACCESS_TOKEN'] ?? ''));

        $this->view('admin/line_logs', [
            'logs'     => $logs,
            'hasToken' => $hasToken,
        ]);
    }

    public function testLineNotification(Request $request): void
    {
        $targetUser = $request->post('recipient', '');
        $message = "🧪 [ทดสอบการแจ้งเตือน] Smart IT Helpdesk LINE Service เชื่อมต่อพร้อมทำงาน (" . date('H:i:s') . ")";

        $result = $this->lineService->send($targetUser, $message);
        if ($result) {
            View::setFlash('success', 'ทดสอบส่งข้อความแจ้งเตือนผ่าน LINE Service สำเร็จ ตรวจสอบประวัติในตารางด้านล่าง');
        } else {
            View::setFlash('warning', 'การส่งแจ้งเตือนถูกปิดไว้ หรือโทเค็นไม่ถูกต้อง');
        }

        $this->redirect('/admin/line-logs');
    }
}
