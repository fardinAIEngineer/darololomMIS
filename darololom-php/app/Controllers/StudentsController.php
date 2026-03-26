<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

final class StudentsController extends Controller
{
    public function index(array $params = []): void
    {
        clear_old();
        $db = Database::connection();

        $q = trim((string) ($_GET['q'] ?? ''));
        $level = trim((string) ($_GET['level'] ?? 'aali'));
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $allowedSizes = paginated_sizes();
        $pageSize = (int) ($_GET['page_size'] ?? config('pagination.default_page_size', 20));
        if (!in_array($pageSize, $allowedSizes, true)) {
            $pageSize = 20;
        }

        $filters = [];
        $bind = [];

        if ($q !== '') {
            $filters[] = '(s.name LIKE :q OR s.father_name LIKE :q OR s.mobile_number LIKE :q OR s.id_number LIKE :q)';
            $bind['q'] = '%' . $q . '%';
        }

        if (in_array($level, ['aali', 'moteseta', 'ebtedai'], true)) {
            $filters[] = 'lvl.code = :level_code';
            $bind['level_code'] = $level;
        }

        $where = $filters ? 'WHERE ' . implode(' AND ', $filters) : '';

        $countSql = "SELECT COUNT(*)
            FROM students s
            LEFT JOIN study_levels lvl ON lvl.id = s.level_id
            LEFT JOIN school_classes sc ON sc.id = s.school_class_id
            $where";

        $countStmt = $db->prepare($countSql);
        foreach ($bind as $key => $value) {
            $countStmt->bindValue(':' . $key, $value);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $pageSize;

        $sql = "SELECT s.*, lvl.name AS level_name, sc.name AS class_name,
                (
                    SELECT GROUP_CONCAT(se.number ORDER BY se.number SEPARATOR ' ')
                    FROM student_semester ss
                    JOIN semesters se ON se.id = ss.semester_id
                    WHERE ss.student_id = s.id
                ) AS semesters_display,
                (
                    SELECT GROUP_CONCAT(cp.number ORDER BY cp.number SEPARATOR ' ')
                    FROM student_period sp
                    JOIN course_periods cp ON cp.id = sp.period_id
                    WHERE sp.student_id = s.id
                ) AS periods_display,
                (
                    SELECT COUNT(*)
                    FROM student_behaviors sb
                    WHERE sb.student_id = s.id AND sb.entry_type = 'merit'
                ) AS merit_count
            FROM students s
            LEFT JOIN study_levels lvl ON lvl.id = s.level_id
            LEFT JOIN school_classes sc ON sc.id = s.school_class_id
            $where
            ORDER BY s.created_at DESC
            LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($sql);
        foreach ($bind as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $students = $stmt->fetchAll();

        $studentIds = array_map(static fn (array $row): int => (int) $row['id'], $students);
        $behaviors = $this->loadBehaviorMap($studentIds);

        $this->render('students/index', [
            'title' => 'لیست دانش‌آموزان',
            'students' => $students,
            'behaviors' => $behaviors,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'q' => $q,
            'level' => $level,
            'allowedSizes' => $allowedSizes,
        ]);
    }

    public function create(array $params = []): void
    {
        clear_old();
        $this->render('students/form', [
            'title' => 'ثبت دانش‌آموز',
            'student' => null,
            ...$this->references(),
            'selectedSemesters' => [],
            'selectedPeriods' => [],
            'formAction' => url('/students/store'),
        ]);
    }

    public function store(array $params = []): void
    {
        $this->csrfCheck();
        $db = Database::connection();

        $name = trim((string) ($_POST['name'] ?? ''));
        $levelId = (int) ($_POST['level_id'] ?? 0);

        if ($name === '' || $levelId <= 0) {
            with_old($_POST);
            flash('error', 'نام و سطح آموزشی الزامی است.');
            $this->redirect('/students/create');
        }

        $image = upload_file('image', 'students', ['jpg', 'jpeg', 'png', 'webp']);
        $certificate = upload_file('certificate_file', 'student_certificates', ['pdf']);

        $stmt = $db->prepare('INSERT INTO students (
            name, father_name, grandfather_name, birth_date, id_number, exam_number,
            gender, current_address, village, district, area, time_start, time_end,
            permanent_address, school_class_id, mobile_number, image_path, certificate_file,
            level_id, is_grade12_graduate, is_graduated, certificate_number, created_at
        ) VALUES (
            :name, :father_name, :grandfather_name, :birth_date, :id_number, :exam_number,
            :gender, :current_address, :village, :district, :area, :time_start, :time_end,
            :permanent_address, :school_class_id, :mobile_number, :image_path, :certificate_file,
            :level_id, :is_grade12_graduate, :is_graduated, :certificate_number, NOW()
        )');

        $stmt->execute($this->payload($image, $certificate));
        $studentId = (int) $db->lastInsertId();

        $this->syncSemesters($studentId, $_POST['semester_ids'] ?? []);
        $this->syncPeriods($studentId, $_POST['period_ids'] ?? []);

        clear_old();
        flash('success', 'دانش‌آموز با موفقیت ثبت شد.');
        $this->redirect('/students');
    }

    public function edit(array $params = []): void
    {
        clear_old();
        $id = $this->intParam($params, 'id');
        $db = Database::connection();

        $stmt = $db->prepare('SELECT * FROM students WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $student = $stmt->fetch();

        if (!$student) {
            flash('error', 'دانش‌آموز پیدا نشد.');
            $this->redirect('/students');
        }

        $selectedSemesters = $db->prepare('SELECT semester_id FROM student_semester WHERE student_id = :id');
        $selectedSemesters->execute(['id' => $id]);
        $semesterIds = array_map(static fn (array $r): int => (int) $r['semester_id'], $selectedSemesters->fetchAll());

        $selectedPeriods = $db->prepare('SELECT period_id FROM student_period WHERE student_id = :id');
        $selectedPeriods->execute(['id' => $id]);
        $periodIds = array_map(static fn (array $r): int => (int) $r['period_id'], $selectedPeriods->fetchAll());

        $this->render('students/form', [
            'title' => 'ویرایش دانش‌آموز',
            'student' => $student,
            ...$this->references(),
            'selectedSemesters' => $semesterIds,
            'selectedPeriods' => $periodIds,
            'formAction' => url('/students/' . $id . '/update'),
        ]);
    }

    public function update(array $params = []): void
    {
        $this->csrfCheck();
        $id = $this->intParam($params, 'id');
        $db = Database::connection();

        $stmt = $db->prepare('SELECT image_path, certificate_file FROM students WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $student = $stmt->fetch();

        if (!$student) {
            flash('error', 'دانش‌آموز پیدا نشد.');
            $this->redirect('/students');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $levelId = (int) ($_POST['level_id'] ?? 0);

        if ($name === '' || $levelId <= 0) {
            with_old($_POST);
            flash('error', 'نام و سطح آموزشی الزامی است.');
            $this->redirect('/students/' . $id . '/edit');
        }

        $image = upload_file('image', 'students', ['jpg', 'jpeg', 'png', 'webp']) ?: $student['image_path'];
        $certificate = upload_file('certificate_file', 'student_certificates', ['pdf']) ?: $student['certificate_file'];

        $payload = $this->payload($image, $certificate);
        $payload['id'] = $id;

        $update = $db->prepare('UPDATE students SET
            name = :name,
            father_name = :father_name,
            grandfather_name = :grandfather_name,
            birth_date = :birth_date,
            id_number = :id_number,
            exam_number = :exam_number,
            gender = :gender,
            current_address = :current_address,
            village = :village,
            district = :district,
            area = :area,
            time_start = :time_start,
            time_end = :time_end,
            permanent_address = :permanent_address,
            school_class_id = :school_class_id,
            mobile_number = :mobile_number,
            image_path = :image_path,
            certificate_file = :certificate_file,
            level_id = :level_id,
            is_grade12_graduate = :is_grade12_graduate,
            is_graduated = :is_graduated,
            certificate_number = :certificate_number
            WHERE id = :id');

        $update->execute($payload);

        $this->syncSemesters($id, $_POST['semester_ids'] ?? []);
        $this->syncPeriods($id, $_POST['period_ids'] ?? []);

        flash('success', 'اطلاعات دانش‌آموز بروزرسانی شد.');
        $this->redirect('/students');
    }

    public function destroy(array $params = []): void
    {
        $this->csrfCheck();
        $id = $this->intParam($params, 'id');

        $db = Database::connection();
        $stmt = $db->prepare('DELETE FROM students WHERE id = :id');
        $stmt->execute(['id' => $id]);

        flash('success', 'دانش‌آموز حذف شد.');
        $this->redirect('/students');
    }

    public function addBehavior(array $params = []): void
    {
        $this->csrfCheck();
        $studentId = $this->intParam($params, 'id');
        $type = trim((string) ($_POST['entry_type'] ?? ''));
        $note = trim((string) ($_POST['note'] ?? ''));

        if (!in_array($type, ['merit', 'violation'], true)) {
            flash('error', 'نوع ثبت معتبر نیست.');
            $this->redirect('/students');
        }

        $db = Database::connection();
        $stmt = $db->prepare('INSERT INTO student_behaviors (student_id, entry_type, note, created_at) VALUES (:student_id, :entry_type, :note, NOW())');
        $stmt->execute([
            'student_id' => $studentId,
            'entry_type' => $type,
            'note' => $note,
        ]);

        flash('success', 'رکورد رفتاری دانش‌آموز ثبت شد.');
        $this->redirect('/students');
    }

    public function deleteBehavior(array $params = []): void
    {
        $this->csrfCheck();
        $id = $this->intParam($params, 'id');

        $db = Database::connection();
        $stmt = $db->prepare('DELETE FROM student_behaviors WHERE id = :id');
        $stmt->execute(['id' => $id]);

        flash('success', 'رکورد رفتاری حذف شد.');
        $this->redirect('/students');
    }

    public function results(array $params = []): void
    {
        $id = $this->intParam($params, 'id');
        $db = Database::connection();

        $studentStmt = $db->prepare('SELECT s.*, l.name AS level_name FROM students s LEFT JOIN study_levels l ON l.id = s.level_id WHERE s.id = :id LIMIT 1');
        $studentStmt->execute(['id' => $id]);
        $student = $studentStmt->fetch();

        if (!$student) {
            flash('error', 'دانش‌آموز پیدا نشد.');
            $this->redirect('/students');
        }

        $scores = $db->prepare('SELECT sub.name AS subject_name, ss.score
            FROM student_scores ss
            JOIN subjects sub ON sub.id = ss.subject_id
            WHERE ss.student_id = :id
            ORDER BY sub.name');
        $scores->execute(['id' => $id]);

        $this->render('students/results', [
            'title' => 'نتایج امتحان دانش‌آموز',
            'student' => $student,
            'scores' => $scores->fetchAll(),
        ]);
    }

    public function certificate(array $params = []): void
    {
        $id = $this->intParam($params, 'id');
        $db = Database::connection();

        $stmt = $db->prepare('SELECT s.*, l.name AS level_name, sc.name AS class_name
            FROM students s
            LEFT JOIN study_levels l ON l.id = s.level_id
            LEFT JOIN school_classes sc ON sc.id = s.school_class_id
            WHERE s.id = :id
            LIMIT 1');
        $stmt->execute(['id' => $id]);
        $student = $stmt->fetch();

        if (!$student) {
            flash('error', 'دانش‌آموز پیدا نشد.');
            $this->redirect('/students');
        }

        $this->render('students/certificate', [
            'title' => 'سرتفیکت دانش‌آموز',
            'student' => $student,
        ]);
    }

    public function appreciation(array $params = []): void
    {
        $id = $this->intParam($params, 'id');
        $db = Database::connection();

        $stmt = $db->prepare('SELECT s.*, l.name AS level_name
            FROM students s
            LEFT JOIN study_levels l ON l.id = s.level_id
            WHERE s.id = :id
            LIMIT 1');
        $stmt->execute(['id' => $id]);
        $student = $stmt->fetch();

        if (!$student) {
            flash('error', 'دانش‌آموز پیدا نشد.');
            $this->redirect('/students');
        }

        $countStmt = $db->prepare('SELECT COUNT(*) FROM student_behaviors WHERE student_id = :id AND entry_type = :type');
        $countStmt->execute(['id' => $id, 'type' => 'merit']);
        $meritCount = (int) $countStmt->fetchColumn();

        if ($meritCount < 3) {
            flash('error', 'برای چاپ تقدیرنامه، حداقل ۳ امتیاز لازم است.');
            $this->redirect('/students');
        }

        $this->render('students/appreciation', [
            'title' => 'تقدیرنامه دانش‌آموز',
            'student' => $student,
            'meritCount' => $meritCount,
        ]);
    }

    public function promoteToMoteseta(array $params = []): void
    {
        $this->csrfCheck();
        $id = $this->intParam($params, 'id');
        $db = Database::connection();

        $studentStmt = $db->prepare('SELECT * FROM students WHERE id = :id LIMIT 1');
        $studentStmt->execute(['id' => $id]);
        $student = $studentStmt->fetch();

        if (!$student) {
            flash('error', 'دانش‌آموز پیدا نشد.');
            $this->redirect('/students');
        }

        $levelStmt = $db->query("SELECT id, code FROM study_levels WHERE code IN ('aali', 'moteseta')");
        $levels = $levelStmt->fetchAll();
        $levelMap = [];
        foreach ($levels as $level) {
            $levelMap[$level['code']] = (int) $level['id'];
        }

        if (($levelMap['moteseta'] ?? 0) <= 0) {
            flash('error', 'سطح متوسطه در دیتابیس موجود نیست.');
            $this->redirect('/students');
        }

        $db->prepare('UPDATE students SET level_id = :level_id WHERE id = :id')
            ->execute([
                'level_id' => $levelMap['moteseta'],
                'id' => $id,
            ]);

        $db->prepare('DELETE FROM student_semester WHERE student_id = :id')->execute(['id' => $id]);

        flash('success', 'دانش‌آموز با موفقیت به سطح متوسطه ارتقا یافت.');
        $this->redirect('/students');
    }

    private function references(): array
    {
        $db = Database::connection();
        return [
            'levels' => $db->query('SELECT * FROM study_levels ORDER BY id')->fetchAll(),
            'classes' => $db->query('SELECT sc.*, l.name AS level_name FROM school_classes sc LEFT JOIN study_levels l ON l.id = sc.level_id ORDER BY sc.name')->fetchAll(),
            'semesters' => $db->query('SELECT * FROM semesters ORDER BY number')->fetchAll(),
            'periods' => $db->query('SELECT * FROM course_periods ORDER BY number')->fetchAll(),
        ];
    }

    private function payload(?string $imagePath, ?string $certificatePath): array
    {
        return [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'father_name' => trim((string) ($_POST['father_name'] ?? '')),
            'grandfather_name' => trim((string) ($_POST['grandfather_name'] ?? '')),
            'birth_date' => (($_POST['birth_date'] ?? '') !== '') ? $_POST['birth_date'] : null,
            'id_number' => trim((string) ($_POST['id_number'] ?? '')),
            'exam_number' => trim((string) ($_POST['exam_number'] ?? '')),
            'gender' => in_array(($_POST['gender'] ?? 'male'), ['male', 'female'], true) ? $_POST['gender'] : 'male',
            'current_address' => trim((string) ($_POST['current_address'] ?? '')),
            'village' => trim((string) ($_POST['village'] ?? '')),
            'district' => trim((string) ($_POST['district'] ?? '')),
            'area' => trim((string) ($_POST['area'] ?? '')),
            'time_start' => (($_POST['time_start'] ?? '') !== '') ? $_POST['time_start'] : null,
            'time_end' => (($_POST['time_end'] ?? '') !== '') ? $_POST['time_end'] : null,
            'permanent_address' => trim((string) ($_POST['permanent_address'] ?? '')),
            'school_class_id' => (int) ($_POST['school_class_id'] ?? 0) ?: null,
            'mobile_number' => trim((string) ($_POST['mobile_number'] ?? '')),
            'image_path' => $imagePath,
            'certificate_file' => $certificatePath,
            'level_id' => (int) ($_POST['level_id'] ?? 0) ?: null,
            'is_grade12_graduate' => isset($_POST['is_grade12_graduate']) ? 1 : 0,
            'is_graduated' => isset($_POST['is_graduated']) ? 1 : 0,
            'certificate_number' => trim((string) ($_POST['certificate_number'] ?? '')) ?: null,
        ];
    }

    private function syncSemesters(int $studentId, array $semesterIds): void
    {
        $db = Database::connection();
        $db->prepare('DELETE FROM student_semester WHERE student_id = :id')->execute(['id' => $studentId]);

        $insert = $db->prepare('INSERT INTO student_semester (student_id, semester_id) VALUES (:student_id, :semester_id)');
        foreach ($semesterIds as $semesterId) {
            $sid = (int) $semesterId;
            if ($sid > 0) {
                $insert->execute(['student_id' => $studentId, 'semester_id' => $sid]);
            }
        }
    }

    private function syncPeriods(int $studentId, array $periodIds): void
    {
        $db = Database::connection();
        $db->prepare('DELETE FROM student_period WHERE student_id = :id')->execute(['id' => $studentId]);

        $insert = $db->prepare('INSERT INTO student_period (student_id, period_id) VALUES (:student_id, :period_id)');
        foreach ($periodIds as $periodId) {
            $pid = (int) $periodId;
            if ($pid > 0) {
                $insert->execute(['student_id' => $studentId, 'period_id' => $pid]);
            }
        }
    }

    private function loadBehaviorMap(array $studentIds): array
    {
        if ($studentIds === []) {
            return [];
        }

        $db = Database::connection();
        $ids = implode(',', array_fill(0, count($studentIds), '?'));

        $stmt = $db->prepare("SELECT * FROM student_behaviors WHERE student_id IN ($ids) ORDER BY created_at DESC");
        $stmt->execute($studentIds);

        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(int) $row['student_id']][] = $row;
        }

        return $map;
    }
}
