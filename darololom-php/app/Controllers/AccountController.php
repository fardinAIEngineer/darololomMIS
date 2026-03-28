<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class AccountController extends Controller
{
    public function index(array $params = []): void
    {
        $user = $this->requireAuth();
        $role = (string) ($user['role'] ?? '');

        if ($role === 'student') {
            $student = $this->studentProfile((int) $user['id']);
            if (!$student) {
                flash('error', 'پروفایل شاگرد پیدا نشد.');
                $this->redirect('/login');
            }

            $scoresStmt = Database::connection()->prepare(
                'SELECT sub.name AS subject_name, ss.score, ss.updated_at
                 FROM student_scores ss
                 JOIN subjects sub ON sub.id = ss.subject_id
                 WHERE ss.student_id = :student_id
                 ORDER BY sub.name'
            );
            $scoresStmt->execute(['student_id' => $student['id']]);

            $this->render('account/index', [
                'title' => 'حساب شاگرد',
                'role' => 'student',
                'student' => $student,
                'studentScores' => $scoresStmt->fetchAll(),
                'teacher' => null,
                'teacherAssignments' => [],
            ]);
            return;
        }

        if ($role === 'teacher') {
            $teacher = $this->teacherProfile((int) $user['id']);
            if (!$teacher) {
                flash('error', 'پروفایل استاد پیدا نشد.');
                $this->redirect('/login');
            }

            $db = Database::connection();
            $classStmt = $db->prepare(
                'SELECT sc.id, sc.name
                 FROM teacher_class tc
                 JOIN school_classes sc ON sc.id = tc.class_id
                 WHERE tc.teacher_id = :teacher_id
                 ORDER BY sc.name'
            );
            $classStmt->execute(['teacher_id' => $teacher['id']]);

            $subjectStmt = $db->prepare(
                'SELECT s.id, s.name
                 FROM teacher_subject ts
                 JOIN subjects s ON s.id = ts.subject_id
                 WHERE ts.teacher_id = :teacher_id
                 ORDER BY s.name'
            );
            $subjectStmt->execute(['teacher_id' => $teacher['id']]);

            $this->render('account/index', [
                'title' => 'حساب استاد',
                'role' => 'teacher',
                'student' => null,
                'studentScores' => [],
                'teacher' => $teacher,
                'teacherAssignments' => [
                    'classes' => $classStmt->fetchAll(),
                    'subjects' => $subjectStmt->fetchAll(),
                ],
            ]);
            return;
        }

        $this->redirect('/');
    }

    public function updateSecurity(array $params = []): void
    {
        $user = $this->requireAuth();
        $role = (string) ($user['role'] ?? '');

        if (!in_array($role, ['student', 'teacher'], true)) {
            flash('error', 'اجازه تغییر حساب برای این نقش موجود نیست.');
            $this->redirect('/');
        }

        $this->csrfCheck();

        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

        with_old(['email' => $email]);

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
            flash('error', 'ایمیل واردشده معتبر نیست.');
            $this->redirect('/account');
        }

        $db = Database::connection();
        $existsStmt = $db->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
        $existsStmt->execute([
            'email' => $email,
            'id' => (int) $user['id'],
        ]);
        if ($existsStmt->fetch()) {
            flash('error', 'این ایمیل قبلاً ثبت شده است.');
            $this->redirect('/account');
        }

        $paramsToUpdate = [
            'id' => (int) $user['id'],
            'email' => $email,
        ];
        $setClauses = ['email = :email'];

        if ($password !== '') {
            if (mb_strlen($password) < 8) {
                flash('error', 'رمز عبور باید حداقل ۸ کاراکتر باشد.');
                $this->redirect('/account');
            }
            if ($password !== $passwordConfirmation) {
                flash('error', 'تکرار رمز عبور با رمز عبور یکسان نیست.');
                $this->redirect('/account');
            }

            $setClauses[] = 'password_hash = :password_hash';
            $paramsToUpdate['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $update = $db->prepare('UPDATE users SET ' . implode(', ', $setClauses) . ' WHERE id = :id');
        $update->execute($paramsToUpdate);

        $this->refreshAuthUser((int) $user['id']);
        clear_old();
        flash('success', 'اطلاعات امنیتی حساب با موفقیت بروزرسانی شد.');
        $this->redirect('/account');
    }

    private function studentProfile(int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT s.*, sc.name AS class_name, l.name AS level_name, u.email AS account_email
             FROM users u
             JOIN students s ON s.id = u.student_id
             LEFT JOIN school_classes sc ON sc.id = s.school_class_id
             LEFT JOIN study_levels l ON l.id = s.level_id
             WHERE u.id = :user_id
             LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function teacherProfile(int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT t.*, u.email AS account_email
             FROM users u
             JOIN teachers t ON t.id = u.teacher_id
             WHERE u.id = :user_id
             LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function refreshAuthUser(int $userId): void
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, full_name, username, email, password_hash, role, permissions, can_register_students, can_register_teachers, student_id, teacher_id, is_active
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();
        if ($user) {
            auth_login($user);
        }
    }
}
