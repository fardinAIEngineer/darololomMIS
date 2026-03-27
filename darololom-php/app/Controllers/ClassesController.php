<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class ClassesController extends Controller
{
    public function index(array $params = []): void
    {
        $this->authorize('manage_classes', 'شما اجازه دسترسی به مدیریت صنوف را ندارید.');
        clear_old();
        $db = Database::connection();

        $q = trim((string) ($_GET['q'] ?? ''));
        $level = trim((string) ($_GET['level'] ?? 'aali'));

        $sql = 'SELECT sc.*, l.name AS level_name, l.code AS level_code, se.number AS semester_number, cp.number AS period_number
            FROM school_classes sc
            LEFT JOIN study_levels l ON l.id = sc.level_id
            LEFT JOIN semesters se ON se.id = sc.semester_id
            LEFT JOIN course_periods cp ON cp.id = sc.period_id
            WHERE 1=1';

        $bind = [];
        if ($q !== '') {
            $sql .= ' AND sc.name LIKE :q';
            $bind['q'] = '%' . $q . '%';
        }

        if (in_array($level, ['aali', 'moteseta', 'ebtedai'], true)) {
            $sql .= ' AND l.code = :level';
            $bind['level'] = $level;
        }

        $sql .= ' ORDER BY sc.created_at DESC';

        $stmt = $db->prepare($sql);
        $stmt->execute($bind);

        $this->render('classes/index', [
            'title' => 'لیست صنوف',
            'classes' => $stmt->fetchAll(),
            'q' => $q,
            'level' => $level,
        ]);
    }

    public function create(array $params = []): void
    {
        $this->authorize('manage_classes', 'شما اجازه ثبت صنف جدید را ندارید.', '/');
        clear_old();
        $this->render('classes/form', [
            'title' => 'ثبت صنف',
            'classItem' => null,
            ...$this->references(),
            'formAction' => url('/classes/store'),
        ]);
    }

    public function store(array $params = []): void
    {
        $this->authorize('manage_classes', 'شما اجازه ثبت صنف جدید را ندارید.', '/');
        $this->csrfCheck();
        $validation = $this->validateClassInput();
        if (!$validation['valid']) {
            with_old($_POST);
            flash('error', $validation['error']);
            $this->redirect('/classes/create');
        }

        $db = Database::connection();
        $stmt = $db->prepare('INSERT INTO school_classes (name, level_id, semester_id, period_id, created_at)
            VALUES (:name, :level_id, :semester_id, :period_id, NOW())');
        $stmt->execute($validation['payload']);

        flash('success', 'صنف ثبت شد.');
        $this->redirect('/classes');
    }

    public function edit(array $params = []): void
    {
        $this->authorize('manage_classes', 'شما اجازه ویرایش صنف را ندارید.', '/');
        clear_old();
        $id = $this->intParam($params, 'id');
        $db = Database::connection();

        $stmt = $db->prepare('SELECT * FROM school_classes WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $classItem = $stmt->fetch();

        if (!$classItem) {
            flash('error', 'صنف پیدا نشد.');
            $this->redirect('/classes');
        }

        $this->render('classes/form', [
            'title' => 'ویرایش صنف',
            'classItem' => $classItem,
            ...$this->references(),
            'formAction' => url('/classes/' . $id . '/update'),
        ]);
    }

    public function update(array $params = []): void
    {
        $this->authorize('manage_classes', 'شما اجازه ویرایش صنف را ندارید.', '/');
        $this->csrfCheck();
        $id = $this->intParam($params, 'id');
        $validation = $this->validateClassInput();
        if (!$validation['valid']) {
            with_old($_POST);
            flash('error', $validation['error']);
            $this->redirect('/classes/' . $id . '/edit');
        }

        $db = Database::connection();
        $payload = $validation['payload'];
        $payload['id'] = $id;

        $stmt = $db->prepare('UPDATE school_classes
            SET name = :name, level_id = :level_id, semester_id = :semester_id, period_id = :period_id
            WHERE id = :id');
        $stmt->execute($payload);

        flash('success', 'صنف بروزرسانی شد.');
        $this->redirect('/classes');
    }

    public function destroy(array $params = []): void
    {
        $this->authorize('manage_classes', 'شما اجازه حذف صنف را ندارید.', '/');
        $this->csrfCheck();
        $id = $this->intParam($params, 'id');

        $db = Database::connection();
        $db->prepare('DELETE FROM school_classes WHERE id = :id')->execute(['id' => $id]);

        flash('success', 'صنف حذف شد.');
        $this->redirect('/classes');
    }

    public function apiSearch(array $params = []): void
    {
        $this->authorize('manage_classes', 'شما اجازه جستجوی صنوف را ندارید.', '/');
        $q = trim((string) ($_GET['q'] ?? ''));
        $db = Database::connection();

        if ($q === '') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([]);
            return;
        }

        $stmt = $db->prepare('SELECT id, name FROM school_classes WHERE name LIKE :q ORDER BY name ASC LIMIT 20');
        $stmt->execute(['q' => '%' . $q . '%']);
        $rows = $stmt->fetchAll();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    }

    private function references(): array
    {
        $db = Database::connection();
        return [
            'levels' => $db->query('SELECT * FROM study_levels ORDER BY id')->fetchAll(),
            'semesters' => $db->query('SELECT * FROM semesters ORDER BY number')->fetchAll(),
            'periods' => $db->query('SELECT * FROM course_periods ORDER BY number')->fetchAll(),
        ];
    }

    private function validateClassInput(): array
    {
        $db = Database::connection();
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            return ['valid' => false, 'error' => 'نام صنف الزامی است.', 'payload' => []];
        }

        $levelId = (int) ($_POST['level_id'] ?? 0);
        if ($levelId <= 0) {
            return ['valid' => false, 'error' => 'سطح آموزشی را انتخاب کنید.', 'payload' => []];
        }

        $levelStmt = $db->prepare('SELECT id, code FROM study_levels WHERE id = :id LIMIT 1');
        $levelStmt->execute(['id' => $levelId]);
        $level = $levelStmt->fetch();
        if (!$level) {
            return ['valid' => false, 'error' => 'سطح آموزشی معتبر نیست.', 'payload' => []];
        }

        $levelCode = (string) ($level['code'] ?? '');
        $payload = [
            'name' => $name,
            'level_id' => $levelId,
            'semester_id' => null,
            'period_id' => null,
        ];

        if ($levelCode === 'aali') {
            $aaliClass = (int) ($_POST['aali_class'] ?? 0);
            if (!in_array($aaliClass, [13, 14], true)) {
                return ['valid' => false, 'error' => 'برای سطح عالی، صنف ۱۳ یا صنف ۱۴ را انتخاب کنید.', 'payload' => []];
            }

            $semesterId = $this->ensureSemesterByNumber($db, $aaliClass);
            if ($semesterId <= 0) {
                return ['valid' => false, 'error' => 'ثبت صنف عالی ممکن نشد. دوباره تلاش کنید.', 'payload' => []];
            }

            $payload['semester_id'] = $semesterId;
            return ['valid' => true, 'error' => '', 'payload' => $payload];
        }

        if ($levelCode === 'moteseta' || $levelCode === 'ebtedai') {
            $periodId = (int) ($_POST['period_id'] ?? 0);
            if ($periodId <= 0) {
                return ['valid' => false, 'error' => 'برای سطح ابتداییه/متوسطه، دوره را انتخاب کنید.', 'payload' => []];
            }

            $periodStmt = $db->prepare('SELECT id, number FROM course_periods WHERE id = :id LIMIT 1');
            $periodStmt->execute(['id' => $periodId]);
            $period = $periodStmt->fetch();
            if (!$period) {
                return ['valid' => false, 'error' => 'دوره انتخاب‌شده معتبر نیست.', 'payload' => []];
            }

            $periodNumber = (int) ($period['number'] ?? 0);
            if ($periodNumber < 1 || $periodNumber > 6) {
                return ['valid' => false, 'error' => 'دوره باید بین ۱ تا ۶ باشد.', 'payload' => []];
            }

            $payload['period_id'] = $periodId;
            return ['valid' => true, 'error' => '', 'payload' => $payload];
        }

        return ['valid' => false, 'error' => 'کد سطح آموزشی پشتیبانی نمی‌شود.', 'payload' => []];
    }

    private function ensureSemesterByNumber(\PDO $db, int $number): int
    {
        $selectStmt = $db->prepare('SELECT id FROM semesters WHERE number = :number LIMIT 1');
        $selectStmt->execute(['number' => $number]);
        $foundId = (int) ($selectStmt->fetchColumn() ?: 0);
        if ($foundId > 0) {
            return $foundId;
        }

        $insertStmt = $db->prepare('INSERT INTO semesters (number) VALUES (:number)');
        $insertStmt->execute(['number' => $number]);

        $selectStmt->execute(['number' => $number]);
        return (int) ($selectStmt->fetchColumn() ?: 0);
    }
}
