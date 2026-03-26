<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class ClassesController extends Controller
{
    public function index(array $params = []): void
    {
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
        $this->csrfCheck();
        $name = trim((string) ($_POST['name'] ?? ''));

        if ($name === '') {
            with_old($_POST);
            flash('error', 'نام صنف الزامی است.');
            $this->redirect('/classes/create');
        }

        $db = Database::connection();
        $stmt = $db->prepare('INSERT INTO school_classes (name, level_id, semester_id, period_id, created_at)
            VALUES (:name, :level_id, :semester_id, :period_id, NOW())');

        $stmt->execute($this->payload());

        flash('success', 'صنف ثبت شد.');
        $this->redirect('/classes');
    }

    public function edit(array $params = []): void
    {
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
        $this->csrfCheck();
        $id = $this->intParam($params, 'id');
        $name = trim((string) ($_POST['name'] ?? ''));

        if ($name === '') {
            with_old($_POST);
            flash('error', 'نام صنف الزامی است.');
            $this->redirect('/classes/' . $id . '/edit');
        }

        $db = Database::connection();
        $payload = $this->payload();
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
        $this->csrfCheck();
        $id = $this->intParam($params, 'id');

        $db = Database::connection();
        $db->prepare('DELETE FROM school_classes WHERE id = :id')->execute(['id' => $id]);

        flash('success', 'صنف حذف شد.');
        $this->redirect('/classes');
    }

    public function apiSearch(array $params = []): void
    {
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

    private function payload(): array
    {
        return [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'level_id' => (int) ($_POST['level_id'] ?? 0) ?: null,
            'semester_id' => (int) ($_POST['semester_id'] ?? 0) ?: null,
            'period_id' => (int) ($_POST['period_id'] ?? 0) ?: null,
        ];
    }
}
