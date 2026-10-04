<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use App\Repositories\UserRepository;

/**
 * Authentication Controller
 */
class AuthController extends BaseController
{
    private UserRepository $userRepo;

    public function __construct(?UserRepository $userRepo = null)
    {
        $this->userRepo = $userRepo ?? new UserRepository();
    }

    public function showLogin(Request $request): void
    {
        if (Auth::check()) {
            $this->redirect(Auth::isAdmin() ? '/admin/dashboard' : '/tickets');
        }

        $redirect = $request->get('redirect', '/tickets');
        $this->view('auth/login', ['redirect' => $redirect], 'layouts/auth');
    }

    public function login(Request $request): void
    {
        $email = trim($request->post('email', ''));
        $password = (string) $request->post('password', '');
        $redirect = $request->post('redirect', '/tickets');

        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
        ], [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            View::setFlash('error', $validator->firstError());
            $this->view('auth/login', ['email' => $email, 'redirect' => $redirect], 'layouts/auth');
            return;
        }

        if (!Auth::login($email, $password)) {
            View::setFlash('error', 'อีเมลหรือรหัสผ่านไม่ถูกต้อง');
            $this->view('auth/login', ['email' => $email, 'redirect' => $redirect], 'layouts/auth');
            return;
        }

        View::setFlash('success', 'เข้าสู่ระบบสำเร็จ ยินดีต้อนรับ ' . Auth::user()['name']);

        if (Auth::isAdmin() && ($redirect === '/tickets' || $redirect === '/')) {
            $this->redirect('/admin/dashboard');
        } else {
            $this->redirect($redirect ?: '/tickets');
        }
    }

    public function showRegister(Request $request): void
    {
        if (Auth::check()) {
            $this->redirect('/tickets');
        }
        $this->view('auth/register', [], 'layouts/auth');
    }

    public function register(Request $request): void
    {
        $name = trim($request->post('name', ''));
        $email = trim($request->post('email', ''));
        $password = (string) $request->post('password', '');
        $passwordConfirm = (string) $request->post('password_confirmation', '');
        $lineUserId = trim($request->post('line_user_id', ''));

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required|min:3|max:100',
            'email' => 'required|email|max:150',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            View::setFlash('error', $validator->firstError());
            $this->view('auth/register', ['name' => $name, 'email' => $email, 'line_user_id' => $lineUserId], 'layouts/auth');
            return;
        }

        if ($password !== $passwordConfirm) {
            View::setFlash('error', 'การยืนยันรหัสผ่านไม่ตรงกัน');
            $this->view('auth/register', ['name' => $name, 'email' => $email, 'line_user_id' => $lineUserId], 'layouts/auth');
            return;
        }

        // Check if email already taken
        if ($this->userRepo->findByEmail($email)) {
            View::setFlash('error', 'อีเมลนี้ถูกลงทะเบียนไว้ในระบบแล้ว');
            $this->view('auth/register', ['name' => $name, 'email' => $email, 'line_user_id' => $lineUserId], 'layouts/auth');
            return;
        }

        $userId = $this->userRepo->createWithPassword($name, $email, $password, 'user', $lineUserId ?: null);

        // Auto login
        Auth::loginUsingId($userId);
        View::setFlash('success', 'สมัครสมาชิกสำเร็จ ยินดีต้อนรับเข้าสู่ระบบแจ้งซ่อม');
        $this->redirect('/tickets');
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        View::setFlash('info', 'ออกจากระบบเรียบร้อยแล้ว');
        $this->redirect('/login');
    }

    public function demoLogin(Request $request): void
    {
        $role = $request->param('role', 'user');

        $demoEmails = [
            'admin'      => 'admin@helpdesk.com',
            'technician' => 'tech@helpdesk.com',
            'user'       => 'user@helpdesk.com',
        ];

        $targetEmail = $demoEmails[$role] ?? 'user@helpdesk.com';
        $user = $this->userRepo->findByEmail($targetEmail);

        if ($user) {
            Auth::loginUsingId((int) $user['id']);
            View::setFlash('success', "เข้าใช้งานโหมดสาธิตในฐานะ: {$user['name']} ({$role})");
            $this->redirect($role === 'admin' ? '/admin/dashboard' : '/tickets');
        } else {
            View::setFlash('error', 'ไม่พบบัญชีผู้ใช้ตัวอย่าง');
            $this->redirect('/login');
        }
    }
}
