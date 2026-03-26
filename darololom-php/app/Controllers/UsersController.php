<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class UsersController extends Controller
{
    public function index(array $params = []): void
    {
        $this->onlySuperAdmin('فقط سوپرادمین می‌تواند کاربران سیستم را مدیریت کند.');

        clear_old();
        $db = Database::connection();

        $users = $db->query(
            'SELECT u.*, creator.full_name AS creator_name
             FROM users u
             LEFT JOIN users creator ON creator.id = u.created_by
             ORDER BY u.created_at DESC'
        )->fetchAll();

        $this->render('users/index', [
            'title' => 'مدیریت کاربران',
            'users' => $users,
        ]);
    }

    public function create(array $params = []): void
    {
        $this->onlySuperAdmin('فقط سوپرادمین می‌تواند کاربر جدید ایجاد کند.');

        $this->render('users/form', [
            'title' => 'ثبت کاربر جدید',
            'formAction' => url('/users/store'),
        ]);
    }

    public function store(array $params = []): void
    {
        $this->onlySuperAdmin('فقط سوپرادمین می‌تواند کاربر جدید ایجاد کند.');
        $this->csrfCheck();

        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $canRegisterStudents = isset($_POST['can_register_students']) ? 1 : 0;
        $canRegisterTeachers = isset($_POST['can_register_teachers']) ? 1 : 0;

        if (mb_strlen($fullName) < 3) {
            with_old($_POST);
            flash('error', 'نام کامل کاربر حداقل ۳ حرف باشد.');
            $this->redirect('/users/create');
        }

        if (!preg_match('/^[A-Za-z0-9_.-]{4,50}$/', $username)) {
            with_old($_POST);
            flash('error', 'نام کاربری باید ۴ تا ۵۰ حرف بوده و فقط شامل حروف انگلیسی، عدد، نقطه، خط تیره یا زیرخط باشد.');
            $this->redirect('/users/create');
        }

        if (strlen($password) < 8) {
            with_old($_POST);
            flash('error', 'رمز عبور باید حداقل ۸ کاراکتر باشد.');
            $this->redirect('/users/create');
        }

        $db = Database::connection();
        $exists = $db->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
        $exists->execute(['username' => $username]);
        if ($exists->fetch()) {
            with_old($_POST);
            flash('error', 'این نام کاربری قبلاً ثبت شده است.');
            $this->redirect('/users/create');
        }

        $insert = $db->prepare(
            'INSERT INTO users
            (full_name, username, password_hash, role, can_register_students, can_register_teachers, created_by, is_active, created_at)
            VALUES
            (:full_name, :username, :password_hash, :role, :can_register_students, :can_register_teachers, :created_by, 1, NOW())'
        );

        $insert->execute([
            'full_name' => $fullName,
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'admin',
            'can_register_students' => $canRegisterStudents,
            'can_register_teachers' => $canRegisterTeachers,
            'created_by' => auth_id() ?: null,
        ]);

        clear_old();
        flash('success', 'کاربر جدید با موفقیت ایجاد شد.');
        $this->redirect('/users');
    }
}
