<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class GradesController extends Controller
{
    public function index(array $params = []): void
    {
        clear_old();
        $db = Database::connection();

        $students = $db->query('SELECT id, name FROM students ORDER BY name')->fetchAll();
        $studentId = (int) ($_GET['student_id'] ?? ($students[0]['id'] ?? 0));

        $selectedStudent = null;
        $subjects = [];
        $scoreMap = [];

        if ($studentId > 0) {
            $selectedStudent = $this->studentProfile($studentId);
            if ($selectedStudent) {
                $subjects = $this->availableSubjectsForStudent($selectedStudent);

                $scoresStmt = $db->prepare('SELECT subject_id, score FROM student_scores WHERE student_id = :student_id');
                $scoresStmt->execute(['student_id' => $studentId]);
                foreach ($scoresStmt->fetchAll() as $row) {
                    $scoreMap[(int) $row['subject_id']] = $row['score'];
                }
            }
        }

        $this->render('grades/index', [
            'title' => 'ثبت نمرات',
            'students' => $students,
            'selectedStudent' => $selectedStudent,
            'subjects' => $subjects,
            'scoreMap' => $scoreMap,
        ]);
    }

    public function store(array $params = []): void
    {
        $this->csrfCheck();
        $db = Database::connection();

        $studentId = (int) ($_POST['student_id'] ?? 0);
        if ($studentId <= 0) {
            flash('error', 'دانش‌آموز انتخاب نشده است.');
            $this->redirect('/grades');
        }

        $scores = $_POST['scores'] ?? [];
        if (!is_array($scores)) {
            $scores = [];
        }

        $stmt = $db->prepare('INSERT INTO student_scores (student_id, subject_id, score, created_at, updated_at)
            VALUES (:student_id, :subject_id, :score, NOW(), NOW())
            ON DUPLICATE KEY UPDATE score = VALUES(score), updated_at = NOW()');

        foreach ($scores as $subjectId => $score) {
            $subjectId = (int) $subjectId;
            if ($subjectId <= 0 || $score === '') {
                continue;
            }

            $scoreValue = max(0, min(100, (int) $score));
            $stmt->execute([
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'score' => $scoreValue,
            ]);
        }

        flash('success', 'نمرات با موفقیت ذخیره شد.');
        $this->redirect('/grades?student_id=' . $studentId);
    }

    private function studentProfile(int $studentId): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT s.*, l.code AS level_code
            FROM students s
            LEFT JOIN study_levels l ON l.id = s.level_id
            WHERE s.id = :id
            LIMIT 1');
        $stmt->execute(['id' => $studentId]);
        $student = $stmt->fetch();

        return $student ?: null;
    }

    private function availableSubjectsForStudent(array $student): array
    {
        $db = Database::connection();

        if (($student['level_code'] ?? '') === 'aali') {
            $semesterStmt = $db->prepare('SELECT semester_id FROM student_semester WHERE student_id = :id ORDER BY semester_id LIMIT 1');
            $semesterStmt->execute(['id' => $student['id']]);
            $semesterId = (int) ($semesterStmt->fetchColumn() ?: 0);

            if ($semesterId <= 0) {
                return [];
            }

            $numberStmt = $db->prepare('SELECT number FROM semesters WHERE id = :id LIMIT 1');
            $numberStmt->execute(['id' => $semesterId]);
            $semesterNumber = (int) ($numberStmt->fetchColumn() ?: 0);

            $stmt = $db->prepare('SELECT id, name FROM subjects WHERE level_id = :level_id AND semester = :semester ORDER BY name');
            $stmt->execute([
                'level_id' => $student['level_id'],
                'semester' => $semesterNumber,
            ]);
            return $stmt->fetchAll();
        }

        $periodStmt = $db->prepare('SELECT period_id FROM student_period WHERE student_id = :id ORDER BY period_id LIMIT 1');
        $periodStmt->execute(['id' => $student['id']]);
        $periodId = (int) ($periodStmt->fetchColumn() ?: 0);

        if ($periodId <= 0) {
            return [];
        }

        $stmt = $db->prepare('SELECT id, name FROM subjects WHERE level_id = :level_id AND period_id = :period_id ORDER BY name');
        $stmt->execute([
            'level_id' => $student['level_id'],
            'period_id' => $periodId,
        ]);

        return $stmt->fetchAll();
    }
}
