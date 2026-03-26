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
        $this->authorize('register_students', 'شما اجازه ثبت‌نام شاگردان را ندارید.', '/students');

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
        $this->authorize('register_students', 'شما اجازه ثبت‌نام شاگردان را ندارید.', '/students');
        $this->csrfCheck();
        $db = Database::connection();

        $validation = $this->validateStudentInput(null, null);
        if (!$validation['valid']) {
            with_old($_POST);
            flash('error', $validation['error']);
            $this->redirect('/students/create');
        }

        $image = upload_file('image', 'students', ['jpg', 'jpeg', 'png', 'webp']);
        $certificate = upload_file('certificate_file', 'student_certificates', ['pdf']);

        if ($this->isFileUploaded('image') && $image === null) {
            with_old($_POST);
            flash('error', 'آپلود عکس ناموفق بود. لطفاً دوباره تلاش کنید.');
            $this->redirect('/students/create');
        }
        if ($this->isFileUploaded('certificate_file') && $certificate === null) {
            with_old($_POST);
            flash('error', 'آپلود شهادت‌نامه ناموفق بود. لطفاً دوباره تلاش کنید.');
            $this->redirect('/students/create');
        }

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

        $this->syncSemesters($studentId, $validation['semester_ids']);
        $this->syncPeriods($studentId, $validation['period_ids']);

        clear_old();
        flash('success', 'دانش‌آموز با موفقیت ثبت شد.');
        $this->redirect('/students');
    }

    public function edit(array $params = []): void
    {
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

        $validation = $this->validateStudentInput($student, $id);
        if (!$validation['valid']) {
            with_old($_POST);
            flash('error', $validation['error']);
            $this->redirect('/students/' . $id . '/edit');
        }

        $image = upload_file('image', 'students', ['jpg', 'jpeg', 'png', 'webp']) ?: $student['image_path'];
        $certificate = upload_file('certificate_file', 'student_certificates', ['pdf']) ?: $student['certificate_file'];

        if ($this->isFileUploaded('image') && $image === null) {
            with_old($_POST);
            flash('error', 'آپلود عکس ناموفق بود. لطفاً دوباره تلاش کنید.');
            $this->redirect('/students/' . $id . '/edit');
        }
        if ($this->isFileUploaded('certificate_file') && $certificate === null) {
            with_old($_POST);
            flash('error', 'آپلود شهادت‌نامه ناموفق بود. لطفاً دوباره تلاش کنید.');
            $this->redirect('/students/' . $id . '/edit');
        }

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

        $this->syncSemesters($id, $validation['semester_ids']);
        $this->syncPeriods($id, $validation['period_ids']);

        clear_old();
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
            'semesters' => $db->query('SELECT * FROM semesters WHERE number BETWEEN 1 AND 4 ORDER BY number')->fetchAll(),
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

    /**
     * @return array{valid:bool,error:string,semester_ids:array<int>,period_ids:array<int>}
     */
    private function validateStudentInput(?array $existingStudent, ?int $studentId): array
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $fatherName = trim((string) ($_POST['father_name'] ?? ''));
        $grandfatherName = trim((string) ($_POST['grandfather_name'] ?? ''));
        $birthDate = trim((string) ($_POST['birth_date'] ?? ''));
        $idNumber = trim((string) ($_POST['id_number'] ?? ''));
        $examNumber = trim((string) ($_POST['exam_number'] ?? ''));
        $mobileNumber = trim((string) ($_POST['mobile_number'] ?? ''));
        $currentAddress = trim((string) ($_POST['current_address'] ?? ''));
        $permanentAddress = trim((string) ($_POST['permanent_address'] ?? ''));
        $village = trim((string) ($_POST['village'] ?? ''));
        $district = trim((string) ($_POST['district'] ?? ''));
        $area = trim((string) ($_POST['area'] ?? ''));
        $timeStart = trim((string) ($_POST['time_start'] ?? ''));
        $timeEnd = trim((string) ($_POST['time_end'] ?? ''));
        $certificateNumber = trim((string) ($_POST['certificate_number'] ?? ''));
        $gender = (string) ($_POST['gender'] ?? '');
        $levelId = (int) ($_POST['level_id'] ?? 0);
        $schoolClassId = (int) ($_POST['school_class_id'] ?? 0);

        if ($name === '' || mb_strlen($name) < 3 || mb_strlen($name) > 255) {
            return ['valid' => false, 'error' => 'نام دانش‌آموز الزامی است (حداقل ۳ حرف).', 'semester_ids' => [], 'period_ids' => []];
        }
        if ($fatherName === '' || mb_strlen($fatherName) < 3 || mb_strlen($fatherName) > 255) {
            return ['valid' => false, 'error' => 'نام پدر الزامی است (حداقل ۳ حرف).', 'semester_ids' => [], 'period_ids' => []];
        }
        if ($grandfatherName !== '' && mb_strlen($grandfatherName) > 255) {
            return ['valid' => false, 'error' => 'نام پدر کلان بیشتر از حد مجاز است.', 'semester_ids' => [], 'period_ids' => []];
        }
        if (!$this->isValidDate($birthDate)) {
            return ['valid' => false, 'error' => 'تاریخ تولد معتبر نیست. نمونه: 2006-08-15', 'semester_ids' => [], 'period_ids' => []];
        }
        if (!in_array($gender, ['male', 'female'], true)) {
            return ['valid' => false, 'error' => 'جنسیت را انتخاب کنید.', 'semester_ids' => [], 'period_ids' => []];
        }
        if ($idNumber === '' || mb_strlen($idNumber) > 100 || !preg_match('/^[0-9A-Za-z\-\/\s]+$/u', $idNumber)) {
            return ['valid' => false, 'error' => 'نمبر تذکره معتبر نیست.', 'semester_ids' => [], 'period_ids' => []];
        }
        if ($mobileNumber === '' || !preg_match('/^[0-9+\-\s]{7,20}$/', $mobileNumber)) {
            return ['valid' => false, 'error' => 'شماره تماس معتبر نیست. نمونه: 0700123456', 'semester_ids' => [], 'period_ids' => []];
        }
        if ($currentAddress === '' || mb_strlen($currentAddress) < 2) {
            return ['valid' => false, 'error' => 'نشانی فعلی را وارد کنید.', 'semester_ids' => [], 'period_ids' => []];
        }
        if ($permanentAddress === '' || mb_strlen($permanentAddress) < 2) {
            return ['valid' => false, 'error' => 'نشانی دایمی را وارد کنید.', 'semester_ids' => [], 'period_ids' => []];
        }
        if ($village === '' || mb_strlen($village) > 150) {
            return ['valid' => false, 'error' => 'قریه را درست وارد کنید.', 'semester_ids' => [], 'period_ids' => []];
        }
        if ($district === '' || mb_strlen($district) > 150) {
            return ['valid' => false, 'error' => 'ولسوالی را درست وارد کنید.', 'semester_ids' => [], 'period_ids' => []];
        }
        if ($area === '' || mb_strlen($area) > 150) {
            return ['valid' => false, 'error' => 'ناحیه را درست وارد کنید.', 'semester_ids' => [], 'period_ids' => []];
        }

        if ($timeStart !== '' && !$this->isValidTime($timeStart)) {
            return ['valid' => false, 'error' => 'تایم آغاز معتبر نیست.', 'semester_ids' => [], 'period_ids' => []];
        }
        if ($timeEnd !== '' && !$this->isValidTime($timeEnd)) {
            return ['valid' => false, 'error' => 'تایم ختم معتبر نیست.', 'semester_ids' => [], 'period_ids' => []];
        }
        if ($timeStart !== '' && $timeEnd !== '' && strcmp($timeStart, $timeEnd) >= 0) {
            return ['valid' => false, 'error' => 'تایم ختم باید بعد از تایم آغاز باشد.', 'semester_ids' => [], 'period_ids' => []];
        }

        $db = Database::connection();

        if ($levelId <= 0) {
            return ['valid' => false, 'error' => 'سطح آموزشی را انتخاب کنید.', 'semester_ids' => [], 'period_ids' => []];
        }

        $levelStmt = $db->prepare('SELECT id, code FROM study_levels WHERE id = :id LIMIT 1');
        $levelStmt->execute(['id' => $levelId]);
        $level = $levelStmt->fetch();
        if (!$level) {
            return ['valid' => false, 'error' => 'سطح آموزشی معتبر نیست.', 'semester_ids' => [], 'period_ids' => []];
        }
        $levelCode = (string) $level['code'];

        if ($schoolClassId > 0) {
            $classStmt = $db->prepare('SELECT id, level_id FROM school_classes WHERE id = :id LIMIT 1');
            $classStmt->execute(['id' => $schoolClassId]);
            $classRow = $classStmt->fetch();
            if (!$classRow) {
                return ['valid' => false, 'error' => 'صنف انتخاب‌شده معتبر نیست.', 'semester_ids' => [], 'period_ids' => []];
            }
            if ((int) ($classRow['level_id'] ?? 0) > 0 && (int) $classRow['level_id'] !== $levelId) {
                return ['valid' => false, 'error' => 'سطح صنف با سطح آموزشی انتخاب‌شده مطابقت ندارد.', 'semester_ids' => [], 'period_ids' => []];
            }
        }

        $semesterIds = $this->normalizeIdArray($_POST['semester_ids'] ?? []);
        $periodIds = $this->normalizeIdArray($_POST['period_ids'] ?? []);

        if ($semesterIds !== [] && !$this->allIdsExist('semesters', $semesterIds)) {
            return ['valid' => false, 'error' => 'سمسترهای انتخاب‌شده معتبر نیست.', 'semester_ids' => [], 'period_ids' => []];
        }
        if ($periodIds !== [] && !$this->allIdsExist('course_periods', $periodIds)) {
            return ['valid' => false, 'error' => 'دوره‌های انتخاب‌شده معتبر نیست.', 'semester_ids' => [], 'period_ids' => []];
        }

        $imageValidation = $this->validateUploadedFile('image', ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024, false, 'عکس');
        if ($imageValidation !== null) {
            return ['valid' => false, 'error' => $imageValidation, 'semester_ids' => [], 'period_ids' => []];
        }

        $certificateRequired = $levelCode === 'aali' && empty($existingStudent['certificate_file']);
        $certificateValidation = $this->validateUploadedFile('certificate_file', ['pdf'], 5 * 1024 * 1024, $certificateRequired, 'شهادت‌نامه');
        if ($certificateValidation !== null) {
            return ['valid' => false, 'error' => $certificateValidation, 'semester_ids' => [], 'period_ids' => []];
        }

        if ($levelCode === 'aali') {
            if (!isset($_POST['is_grade12_graduate'])) {
                return ['valid' => false, 'error' => 'برای سطح عالی، گزینه فارغ صنف دوازدهم باید فعال باشد.', 'semester_ids' => [], 'period_ids' => []];
            }
            if ($examNumber === '' || mb_strlen($examNumber) > 100 || !preg_match('/^[0-9A-Za-z\-\/\s]+$/u', $examNumber)) {
                return ['valid' => false, 'error' => 'برای سطح عالی، نمبر کانکور الزامی و معتبر است.', 'semester_ids' => [], 'period_ids' => []];
            }
            if (count($semesterIds) !== 1) {
                return ['valid' => false, 'error' => 'برای سطح عالی دقیقاً یک سمستر انتخاب کنید.', 'semester_ids' => [], 'period_ids' => []];
            }
        } else {
            if (count($periodIds) !== 1) {
                return ['valid' => false, 'error' => 'برای ابتداییه/متوسطه دقیقاً یک دوره انتخاب کنید.', 'semester_ids' => [], 'period_ids' => []];
            }
        }

        if ($certificateNumber !== '') {
            if (mb_strlen($certificateNumber) > 50 || !preg_match('/^[0-9A-Za-z\-\/]+$/', $certificateNumber)) {
                return ['valid' => false, 'error' => 'شماره سرتفیکت معتبر نیست.', 'semester_ids' => [], 'period_ids' => []];
            }

            if ($this->certificateNumberExists($certificateNumber, $studentId)) {
                return ['valid' => false, 'error' => 'شماره سرتفیکت تکراری است.', 'semester_ids' => [], 'period_ids' => []];
            }
        }

        return [
            'valid' => true,
            'error' => '',
            'semester_ids' => $levelCode === 'aali' ? $semesterIds : [],
            'period_ids' => $levelCode === 'aali' ? [] : $periodIds,
        ];
    }

    private function normalizeIdArray(mixed $input): array
    {
        if (!is_array($input)) {
            return [];
        }

        $normalized = [];
        foreach ($input as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $normalized[$id] = $id;
            }
        }
        return array_values($normalized);
    }

    private function allIdsExist(string $table, array $ids): bool
    {
        if ($ids === []) {
            return true;
        }

        $db = Database::connection();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("SELECT COUNT(*) FROM {$table} WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        return (int) $stmt->fetchColumn() === count($ids);
    }

    private function certificateNumberExists(string $certificateNumber, ?int $studentId): bool
    {
        $db = Database::connection();

        if ($studentId === null) {
            $stmt = $db->prepare('SELECT id FROM students WHERE certificate_number = :certificate_number LIMIT 1');
            $stmt->execute(['certificate_number' => $certificateNumber]);
            return (bool) $stmt->fetch();
        }

        $stmt = $db->prepare('SELECT id FROM students WHERE certificate_number = :certificate_number AND id <> :student_id LIMIT 1');
        $stmt->execute([
            'certificate_number' => $certificateNumber,
            'student_id' => $studentId,
        ]);
        return (bool) $stmt->fetch();
    }

    private function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        [$year, $month, $day] = array_map('intval', explode('-', $date));
        return checkdate($month, $day, $year);
    }

    private function isValidTime(string $time): bool
    {
        return (bool) preg_match('/^\d{2}:\d{2}$/', $time);
    }

    private function isFileUploaded(string $field): bool
    {
        return isset($_FILES[$field]) && (int) ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    private function validateUploadedFile(string $field, array $allowedExtensions, int $maxBytes, bool $required, string $label): ?string
    {
        if (!isset($_FILES[$field]) || (int) ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $required ? "{$label} الزامی است." : null;
        }

        $error = (int) ($_FILES[$field]['error'] ?? UPLOAD_ERR_OK);
        if ($error !== UPLOAD_ERR_OK) {
            return "آپلود {$label} ناموفق بود.";
        }

        $name = strtolower((string) ($_FILES[$field]['name'] ?? ''));
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if ($ext === '' || !in_array($ext, $allowedExtensions, true)) {
            return "فرمت {$label} مجاز نیست.";
        }

        $size = (int) ($_FILES[$field]['size'] ?? 0);
        if ($size > $maxBytes) {
            return "حجم {$label} بیش از حد مجاز است.";
        }

        return null;
    }
}
