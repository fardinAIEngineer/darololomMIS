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

        $totalStudents = $cards['students'];
        $genderStats = $this->buildGenderStats($db);
        $levelStats = $this->buildLevelStats($db, $totalStudents);

        $this->render('dashboard/index', [
            'title' => 'داشبورد',
            'cards' => $cards,
            'genderStats' => $genderStats,
            'levelStats' => $levelStats,
        ]);
    }

    private function buildGenderStats(\PDO $db): array
    {
        $rows = $db->query(
            'SELECT gender, COUNT(*) AS total
             FROM students
             WHERE gender IN (\'male\', \'female\')
             GROUP BY gender'
        )->fetchAll();

        $maleCount = 0;
        $femaleCount = 0;

        foreach ($rows as $row) {
            $key = (string) ($row['gender'] ?? '');
            $count = (int) ($row['total'] ?? 0);
            if ($key === 'male') {
                $maleCount += $count;
            } elseif ($key === 'female') {
                $femaleCount += $count;
            }
        }

        $knownTotal = $maleCount + $femaleCount;
        $malePercent = $knownTotal > 0 ? round(($maleCount * 100) / $knownTotal, 1) : 0.0;
        $femalePercent = $knownTotal > 0 ? round(($femaleCount * 100) / $knownTotal, 1) : 0.0;

        return [
            'male_count' => $maleCount,
            'female_count' => $femaleCount,
            'male_percent' => $malePercent,
            'female_percent' => $femalePercent,
            'total' => $knownTotal,
        ];
    }

    private function buildLevelStats(\PDO $db, int $totalStudents): array
    {
        if ($totalStudents === 0) {
            return [];
        }

        $rows = $db->query(
            'SELECT COALESCE(l.name, \'نامشخص\') AS level_name, COUNT(*) AS total
             FROM students s
             LEFT JOIN study_levels l ON l.id = s.level_id
             GROUP BY l.id, l.name, l.code
             ORDER BY CASE l.code
                 WHEN \'aali\' THEN 1
                 WHEN \'moteseta\' THEN 2
                 WHEN \'ebtedai\' THEN 3
                 ELSE 4
             END'
        )->fetchAll();

        $palette = ['chart-fill-level-1', 'chart-fill-level-2', 'chart-fill-level-3', 'chart-fill-level-4', 'chart-fill-level-5'];
        $items = [];

        foreach ($rows as $index => $row) {
            $count = (int) ($row['total'] ?? 0);
            $percent = $totalStudents > 0 ? round(($count * 100) / $totalStudents, 1) : 0.0;
            $items[] = [
                'label' => (string) ($row['level_name'] ?? 'نامشخص'),
                'count' => $count,
                'percent' => $percent,
                'color_class' => $palette[$index % count($palette)],
            ];
        }

        return $items;
    }
}
