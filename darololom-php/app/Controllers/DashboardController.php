<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class DashboardController extends Controller
{
    public function index(array $params = []): void
    {
        $db = Database::connection();

        $cards = [
            'students' => (int) $db->query('SELECT COUNT(*) FROM students')->fetchColumn(),
            'teachers' => (int) $db->query('SELECT COUNT(*) FROM teachers')->fetchColumn(),
            'classes' => (int) $db->query('SELECT COUNT(*) FROM school_classes')->fetchColumn(),
            'subjects' => (int) $db->query('SELECT COUNT(*) FROM subjects')->fetchColumn(),
        ];

        $recentStudents = $db->query(
            'SELECT s.id, s.name, l.name AS level_name, s.created_at
             FROM students s
             LEFT JOIN study_levels l ON l.id = s.level_id
             ORDER BY s.created_at DESC
             LIMIT 5'
        )->fetchAll();

        $recentTeachers = $db->query(
            'SELECT id, name, education_level, created_at
             FROM teachers
             ORDER BY created_at DESC
             LIMIT 5'
        )->fetchAll();

        $this->render('dashboard/index', [
            'title' => 'داشبورد',
            'cards' => $cards,
            'recentStudents' => $recentStudents,
            'recentTeachers' => $recentTeachers,
        ]);
    }
}
