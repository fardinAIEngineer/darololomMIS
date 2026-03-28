<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class UsersController extends Controller
{
    public function index(array $params = []): void
    {
        $this->authorize('manage_users', 'شما اجازه مدیریت کاربران سیستم را ندارید.');

        clear_old();
        $db = Database::connection();

        $users = $db->query(
            'SELECT u.*, creator.full_name AS creator_name
             FROM users u
             LEFT JOIN users creator ON creator.id = u.created_by
             WHERE u.role IN (\'super_admin\', \'admin\')
             ORDER BY u.created_at DESC'
        )->fetchAll();

        $this->render('users/index', [
            'title' => 'مدیریت کاربران',
            'users' => $users,
        ]);
    }

    public function create(array $params = []): void
    {
        $this->authorize('manage_users', 'شما اجازه ایجاد کاربر جدید را ندارید.');

        $this->render('users/form', [
            'title' => 'ثبت کاربر جدید',
            'formAction' => url('/users/store'),
            'permissions' => permission_definitions(),
        ]);
    }

    public function store(array $params = []): void
    {
        $this->authorize('manage_users', 'شما اجازه ایجاد کاربر جدید را ندارید.');
        $this->csrfCheck();

        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $requestedPermissions = $_POST['permissions'] ?? [];
        if (!is_array($requestedPermissions)) {
            $requestedPermissions = [];
        }
        $requestedPermissions = array_values(array_unique(array_map(static fn ($item): string => trim((string) $item), $requestedPermissions)));

        $definitions = permission_definitions();
        foreach ($requestedPermissions as $permission) {
            if (!array_key_exists($permission, $definitions)) {
                with_old($_POST);
                flash('error', 'یکی از صلاحیت‌های انتخاب‌شده معتبر نیست.');
                $this->redirect('/users/create');
            }
        }

        $canRegisterStudents = in_array('register_students', $requestedPermissions, true) ? 1 : 0;
        $canRegisterTeachers = in_array('register_teachers', $requestedPermissions, true) ? 1 : 0;

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
            (full_name, username, password_hash, role, permissions, can_register_students, can_register_teachers, created_by, is_active, created_at)
            VALUES
            (:full_name, :username, :password_hash, :role, :permissions, :can_register_students, :can_register_teachers, :created_by, 1, NOW())'
        );

        $insert->execute([
            'full_name' => $fullName,
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'admin',
            'permissions' => json_encode($requestedPermissions, JSON_UNESCAPED_UNICODE),
            'can_register_students' => $canRegisterStudents,
            'can_register_teachers' => $canRegisterTeachers,
            'created_by' => auth_id() ?: null,
        ]);

        clear_old();
        flash('success', 'کاربر جدید با موفقیت ایجاد شد.');
        $this->redirect('/users');
    }
}
