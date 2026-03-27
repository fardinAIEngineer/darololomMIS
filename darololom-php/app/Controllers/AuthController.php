<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class AuthController extends Controller
{
    public function showLogin(array $params = []): void
    {
        if (auth_check()) {
            $this->redirect('/');
        }

        $this->render('auth/login', [
            'title' => 'ورود به سیستم',
        ]);
    }

    public function login(array $params = []): void
    {
        $this->csrfCheck();

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            with_old(['username' => $username]);
            flash('error', 'نام کاربری و رمز عبور الزامی است.');
            $this->redirect('/login');
        }

        $db = Database::connection();
        $stmt = $db->prepare('SELECT id, full_name, username, password_hash, role, permissions, can_register_students, can_register_teachers, is_active
            FROM users
            WHERE username = :username
            LIMIT 1');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if (
            !$user ||
            (int) ($user['is_active'] ?? 0) !== 1 ||
            !password_verify($password, (string) ($user['password_hash'] ?? ''))
        ) {
            with_old(['username' => $username]);
            flash('error', 'نام کاربری یا رمز عبور نادرست است.');
            $this->redirect('/login');
        }

        auth_login($user);
        clear_old();
        flash('success', 'خوش آمدید، ' . ((string) ($user['full_name'] ?? 'کاربر')));

        $target = (string) ($_SESSION['_intended'] ?? '/');
        unset($_SESSION['_intended']);

        if ($target === '' || !str_starts_with($target, '/') || $target === '/login') {
            $target = '/';
        }

        $this->redirect($target);
    }

    public function logout(array $params = []): void
    {
        $this->requireAuth();
        $this->csrfCheck();

        auth_logout();
        flash('success', 'با موفقیت از سیستم خارج شدید.');
        $this->redirect('/login');
    }
}
