<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class SubjectsController extends Controller
{
    public function index(array $params = []): void
    {
        clear_old();
        $db = Database::connection();

        $q = trim((string) ($_GET['q'] ?? ''));
        $level = trim((string) ($_GET['level'] ?? 'aali'));

        $sql = 'SELECT s.*, l.name AS level_name, l.code AS level_code, cp.number AS period_number
            FROM subjects s
            LEFT JOIN study_levels l ON l.id = s.level_id
            LEFT JOIN course_periods cp ON cp.id = s.period_id
            WHERE 1=1';

        $bind = [];
        if ($q !== '') {
            $sql .= ' AND s.name LIKE :q';
            $bind['q'] = '%' . $q . '%';
        }
        if (in_array($level, ['aali', 'moteseta', 'ebtedai'], true)) {
            $sql .= ' AND l.code = :level';
            $bind['level'] = $level;
        }

        $sql .= ' ORDER BY s.created_at DESC';

        $stmt = $db->prepare($sql);
        $stmt->execute($bind);

        $this->render('subjects/index', [
            'title' => 'لیست مضامین',
            'subjects' => $stmt->fetchAll(),
            'q' => $q,
            'level' => $level,
        ]);
    }

    public function create(array $params = []): void
    {
        clear_old();
        $this->render('subjects/form', [
            'title' => 'ثبت مضمون',
            'subject' => null,
            ...$this->references(),
            'formAction' => url('/subjects/store'),
        ]);
    }

    public function store(array $params = []): void
    {
        $this->csrfCheck();
        $name = trim((string) ($_POST['name'] ?? ''));

        if ($name === '') {
            with_old($_POST);
            flash('error', 'نام مضمون الزامی است.');
            $this->redirect('/subjects/create');
        }

        $db = Database::connection();
        $stmt = $db->prepare('INSERT INTO subjects (name, level_id, semester, period_id, created_at)
            VALUES (:name, :level_id, :semester, :period_id, NOW())');
        $stmt->execute($this->payload());

        flash('success', 'مضمون ثبت شد.');
        $this->redirect('/subjects');
    }

    public function edit(array $params = []): void
    {
        clear_old();
        $id = $this->intParam($params, 'id');
        $db = Database::connection();

        $stmt = $db->prepare('SELECT * FROM subjects WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $subject = $stmt->fetch();

        if (!$subject) {
            flash('error', 'مضمون پیدا نشد.');
            $this->redirect('/subjects');
        }

        $this->render('subjects/form', [
            'title' => 'ویرایش مضمون',
            'subject' => $subject,
            ...$this->references(),
            'formAction' => url('/subjects/' . $id . '/update'),
        ]);
    }

    public function update(array $params = []): void
    {
        $this->csrfCheck();
        $id = $this->intParam($params, 'id');
        $name = trim((string) ($_POST['name'] ?? ''));

        if ($name === '') {
            with_old($_POST);
            flash('error', 'نام مضمون الزامی است.');
            $this->redirect('/subjects/' . $id . '/edit');
        }

        $db = Database::connection();
        $payload = $this->payload();
        $payload['id'] = $id;

        $stmt = $db->prepare('UPDATE subjects
            SET name = :name, level_id = :level_id, semester = :semester, period_id = :period_id
            WHERE id = :id');
        $stmt->execute($payload);

        flash('success', 'مضمون بروزرسانی شد.');
        $this->redirect('/subjects');
    }

    public function destroy(array $params = []): void
    {
        $this->csrfCheck();
        $id = $this->intParam($params, 'id');

        $db = Database::connection();
        $db->prepare('DELETE FROM subjects WHERE id = :id')->execute(['id' => $id]);

        flash('success', 'مضمون حذف شد.');
        $this->redirect('/subjects');
    }

    private function references(): array
    {
        $db = Database::connection();
        return [
            'levels' => $db->query('SELECT * FROM study_levels ORDER BY id')->fetchAll(),
            'periods' => $db->query('SELECT * FROM course_periods ORDER BY number')->fetchAll(),
        ];
    }

    private function payload(): array
    {
        return [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'level_id' => (int) ($_POST['level_id'] ?? 0) ?: null,
            'semester' => (int) ($_POST['semester'] ?? 1),
            'period_id' => (int) ($_POST['period_id'] ?? 0) ?: null,
        ];
    }
}
