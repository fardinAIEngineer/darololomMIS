<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

final class TeachersController extends Controller
{
    public function index(array $params = []): void
    {
        $this->authorize('access_teachers', 'شما اجازه دسترسی به بخش اساتید را ندارید.');
        clear_old();
        $db = Database::connection();

        $q = trim((string) ($_GET['q'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $allowedSizes = paginated_sizes();
        $pageSize = (int) ($_GET['page_size'] ?? config('pagination.default_page_size', 20));

        if (!in_array($pageSize, $allowedSizes, true)) {
            $pageSize = 20;
        }

        $filters = [];
        $bind = [];

        if ($q !== '') {
            $filters[] = '(t.name LIKE :q OR t.father_name LIKE :q OR t.id_number LIKE :q)';
            $bind['q'] = '%' . $q . '%';
        }

        $where = $filters ? 'WHERE ' . implode(' AND ', $filters) : '';

        $countStmt = $db->prepare("SELECT COUNT(*) FROM teachers t $where");
        foreach ($bind as $k => $v) {
            $countStmt->bindValue(':' . $k, $v);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $pageSize;

        $sql = "SELECT t.*,
            (
                SELECT GROUP_CONCAT(sc.name ORDER BY sc.name SEPARATOR '، ')
                FROM teacher_class tc
                JOIN school_classes sc ON sc.id = tc.class_id
                WHERE tc.teacher_id = t.id
            ) AS classes_display,
            (
                SELECT GROUP_CONCAT(s.name ORDER BY s.name SEPARATOR '، ')
                FROM teacher_subject ts
                JOIN subjects s ON s.id = ts.subject_id
                WHERE ts.teacher_id = t.id
            ) AS subjects_display,
            (
                SELECT GROUP_CONCAT(l.name ORDER BY l.id SEPARATOR '، ')
                FROM teacher_level tl
                JOIN study_levels l ON l.id = tl.level_id
                WHERE tl.teacher_id = t.id
            ) AS levels_display,
            (
                SELECT GROUP_CONCAT(se.number ORDER BY se.number SEPARATOR ' ')
                FROM teacher_semester ts
                JOIN semesters se ON se.id = ts.semester_id
                WHERE ts.teacher_id = t.id
            ) AS semesters_display,
            (
                SELECT GROUP_CONCAT(cp.number ORDER BY cp.number SEPARATOR ' ')
                FROM teacher_period tp
                JOIN course_periods cp ON cp.id = tp.period_id
                WHERE tp.teacher_id = t.id
            ) AS periods_display,
            (
                SELECT COUNT(*) FROM teacher_behaviors tb WHERE tb.teacher_id = t.id AND tb.entry_type = 'merit'
            ) AS merit_count
            FROM teachers t
            $where
            ORDER BY t.created_at DESC
            LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($sql);
        foreach ($bind as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $teachers = $stmt->fetchAll();

        $teacherIds = array_map(static fn (array $row): int => (int) $row['id'], $teachers);
        $behaviors = $this->loadBehaviorMap($teacherIds);

        $this->render('teachers/index', [
            'title' => 'لیست اساتید',
            'teachers' => $teachers,
            'behaviors' => $behaviors,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'q' => $q,
            'allowedSizes' => $allowedSizes,
        ]);
    }

    public function create(array $params = []): void
    {
        $this->authorize('register_teachers', 'شما اجازه ثبت‌نام اساتید را ندارید.', '/teachers');

        $this->render('teachers/form', [
            'title' => 'ثبت استاد',
            'teacher' => null,
            ...$this->references(),
            'selectedClassIds' => [],
            'selectedSubjectIds' => [],
            'selectedLevelIds' => [],
            'selectedSemesterIds' => [],
            'selectedPeriodIds' => [],
            'formAction' => url('/teachers/store'),
        ]);
    }

    public function store(array $params = []): void
    {
        $this->authorize('register_teachers', 'شما اجازه ثبت‌نام اساتید را ندارید.', '/teachers');
        $this->csrfCheck();
        $db = Database::connection();

        $validation = $this->validateTeacherInput(null, null);
        if (!$validation['valid']) {
            with_old($_POST);
            flash('error', $validation['error']);
            $this->redirect('/teachers/create');
        }

        $image = upload_file('image', 'teachers', ['jpg', 'jpeg', 'png', 'webp']);
        $plan = upload_file('plan_file', 'teacher_plans', ['pdf']);
        $eduDoc = upload_file('education_document', 'teacher_documents/education', ['pdf', 'jpg', 'jpeg', 'png']);
        $expDoc = upload_file('experience_document', 'teacher_documents/experience', ['pdf', 'jpg', 'jpeg', 'png']);

        if ($this->isFileUploaded('image') && $image === null) {
            with_old($_POST);
            flash('error', 'آپلود عکس ناموفق بود.');
            $this->redirect('/teachers/create');
        }
        if ($this->isFileUploaded('plan_file') && $plan === null) {
            with_old($_POST);
            flash('error', 'آپلود پلان درسی ناموفق بود.');
            $this->redirect('/teachers/create');
        }
        if ($this->isFileUploaded('education_document') && $eduDoc === null) {
            with_old($_POST);
            flash('error', 'آپلود سند تحصیلی ناموفق بود.');
            $this->redirect('/teachers/create');
        }
        if ($this->isFileUploaded('experience_document') && $expDoc === null) {
            with_old($_POST);
            flash('error', 'آپلود سند تجربه ناموفق بود.');
            $this->redirect('/teachers/create');
        }

        $stmt = $db->prepare('INSERT INTO teachers (
            name, father_name, birth_date, permanent_address, current_address,
            village, district, area, gender, education_level, id_number,
            image_path, plan_file, education_document, experience_document, created_at
        ) VALUES (
            :name, :father_name, :birth_date, :permanent_address, :current_address,
            :village, :district, :area, :gender, :education_level, :id_number,
            :image_path, :plan_file, :education_document, :experience_document, NOW()
        )');

        $stmt->execute($this->payload($image, $plan, $eduDoc, $expDoc));
        $teacherId = (int) $db->lastInsertId();

        $this->syncMany($teacherId, 'teacher_class', 'class_id', $validation['class_ids']);
        $this->syncMany($teacherId, 'teacher_subject', 'subject_id', $validation['subject_ids']);
        $this->syncMany($teacherId, 'teacher_level', 'level_id', $validation['level_ids']);
        $this->syncMany($teacherId, 'teacher_semester', 'semester_id', $validation['semester_ids']);
        $this->syncMany($teacherId, 'teacher_period', 'period_id', $validation['period_ids']);

        clear_old();
        flash('success', 'استاد با موفقیت ثبت شد.');
        $this->redirect('/teachers');
    }

    public function edit(array $params = []): void
    {
        $this->authorize('manage_teachers', 'شما اجازه ویرایش اساتید را ندارید.', '/teachers');
        $id = $this->intParam($params, 'id');
        $db = Database::connection();

        $stmt = $db->prepare('SELECT * FROM teachers WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $teacher = $stmt->fetch();

        if (!$teacher) {
            flash('error', 'استاد پیدا نشد.');
            $this->redirect('/teachers');
        }

        $this->render('teachers/form', [
            'title' => 'ویرایش استاد',
            'teacher' => $teacher,
            ...$this->references(),
            'selectedClassIds' => $this->selectedIds('teacher_class', 'class_id', $id),
            'selectedSubjectIds' => $this->selectedIds('teacher_subject', 'subject_id', $id),
            'selectedLevelIds' => $this->selectedIds('teacher_level', 'level_id', $id),
            'selectedSemesterIds' => $this->selectedIds('teacher_semester', 'semester_id', $id),
            'selectedPeriodIds' => $this->selectedIds('teacher_period', 'period_id', $id),
            'formAction' => url('/teachers/' . $id . '/update'),
        ]);
    }

    public function update(array $params = []): void
    {
        $this->authorize('manage_teachers', 'شما اجازه ویرایش اساتید را ندارید.', '/teachers');
        $this->csrfCheck();
        $id = $this->intParam($params, 'id');
        $db = Database::connection();

        $exists = $db->prepare('SELECT image_path, plan_file, education_document, experience_document FROM teachers WHERE id = :id');
        $exists->execute(['id' => $id]);
        $teacher = $exists->fetch();

        if (!$teacher) {
            flash('error', 'استاد پیدا نشد.');
            $this->redirect('/teachers');
        }

        $validation = $this->validateTeacherInput($teacher, $id);
        if (!$validation['valid']) {
            with_old($_POST);
            flash('error', $validation['error']);
            $this->redirect('/teachers/' . $id . '/edit');
        }

        $image = upload_file('image', 'teachers', ['jpg', 'jpeg', 'png', 'webp']) ?: $teacher['image_path'];
        $plan = upload_file('plan_file', 'teacher_plans', ['pdf']) ?: $teacher['plan_file'];
        $eduDoc = upload_file('education_document', 'teacher_documents/education', ['pdf', 'jpg', 'jpeg', 'png']) ?: $teacher['education_document'];
        $expDoc = upload_file('experience_document', 'teacher_documents/experience', ['pdf', 'jpg', 'jpeg', 'png']) ?: $teacher['experience_document'];

        if ($this->isFileUploaded('image') && $image === null) {
            with_old($_POST);
            flash('error', 'آپلود عکس ناموفق بود.');
            $this->redirect('/teachers/' . $id . '/edit');
        }
        if ($this->isFileUploaded('plan_file') && $plan === null) {
            with_old($_POST);
            flash('error', 'آپلود پلان درسی ناموفق بود.');
            $this->redirect('/teachers/' . $id . '/edit');
        }
        if ($this->isFileUploaded('education_document') && $eduDoc === null) {
            with_old($_POST);
            flash('error', 'آپلود سند تحصیلی ناموفق بود.');
            $this->redirect('/teachers/' . $id . '/edit');
        }
        if ($this->isFileUploaded('experience_document') && $expDoc === null) {
            with_old($_POST);
            flash('error', 'آپلود سند تجربه ناموفق بود.');
            $this->redirect('/teachers/' . $id . '/edit');
        }

        $payload = $this->payload($image, $plan, $eduDoc, $expDoc);
        $payload['id'] = $id;

        $update = $db->prepare('UPDATE teachers SET
            name = :name,
            father_name = :father_name,
            birth_date = :birth_date,
            permanent_address = :permanent_address,
            current_address = :current_address,
            village = :village,
            district = :district,
            area = :area,
            gender = :gender,
            education_level = :education_level,
            id_number = :id_number,
            image_path = :image_path,
            plan_file = :plan_file,
            education_document = :education_document,
            experience_document = :experience_document
            WHERE id = :id');

        $update->execute($payload);

        $this->syncMany($id, 'teacher_class', 'class_id', $validation['class_ids']);
        $this->syncMany($id, 'teacher_subject', 'subject_id', $validation['subject_ids']);
        $this->syncMany($id, 'teacher_level', 'level_id', $validation['level_ids']);
        $this->syncMany($id, 'teacher_semester', 'semester_id', $validation['semester_ids']);
        $this->syncMany($id, 'teacher_period', 'period_id', $validation['period_ids']);

        clear_old();
        flash('success', 'اطلاعات استاد بروزرسانی شد.');
        $this->redirect('/teachers');
    }

    public function destroy(array $params = []): void
    {
        $this->authorize('manage_teachers', 'شما اجازه حذف اساتید را ندارید.', '/teachers');
        $this->csrfCheck();
        $id = $this->intParam($params, 'id');

        $db = Database::connection();
        $db->prepare('DELETE FROM teachers WHERE id = :id')->execute(['id' => $id]);

        flash('success', 'استاد حذف شد.');
        $this->redirect('/teachers');
    }

    public function addBehavior(array $params = []): void
    {
        $this->authorize('manage_teachers', 'شما اجازه ثبت رفتار برای اساتید را ندارید.', '/teachers');
        $this->csrfCheck();
        $teacherId = $this->intParam($params, 'id');
        $type = trim((string) ($_POST['entry_type'] ?? ''));
        $note = trim((string) ($_POST['note'] ?? ''));

        if (!in_array($type, ['merit', 'violation'], true)) {
            flash('error', 'نوع ثبت معتبر نیست.');
            $this->redirect('/teachers');
        }

        $db = Database::connection();
        $stmt = $db->prepare('INSERT INTO teacher_behaviors (teacher_id, entry_type, note, created_at) VALUES (:teacher_id, :entry_type, :note, NOW())');
        $stmt->execute([
            'teacher_id' => $teacherId,
            'entry_type' => $type,
            'note' => $note,
        ]);

        flash('success', 'رکورد رفتاری استاد ثبت شد.');
        $this->redirect('/teachers');
    }

    public function deleteBehavior(array $params = []): void
    {
        $this->authorize('manage_teachers', 'شما اجازه حذف رفتار اساتید را ندارید.', '/teachers');
        $this->csrfCheck();
        $id = $this->intParam($params, 'id');

        $db = Database::connection();
        $db->prepare('DELETE FROM teacher_behaviors WHERE id = :id')->execute(['id' => $id]);

        flash('success', 'رکورد رفتاری حذف شد.');
        $this->redirect('/teachers');
    }

    public function appreciation(array $params = []): void
    {
        $this->authorize('access_teachers', 'شما اجازه مشاهده تقدیرنامه اساتید را ندارید.', '/');
        $id = $this->intParam($params, 'id');
        $db = Database::connection();

        $stmt = $db->prepare('SELECT * FROM teachers WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $teacher = $stmt->fetch();

        if (!$teacher) {
            flash('error', 'استاد پیدا نشد.');
            $this->redirect('/teachers');
        }

        $countStmt = $db->prepare('SELECT COUNT(*) FROM teacher_behaviors WHERE teacher_id = :id AND entry_type = :type');
        $countStmt->execute(['id' => $id, 'type' => 'merit']);
        $meritCount = (int) $countStmt->fetchColumn();

        if ($meritCount < 3) {
            flash('error', 'برای چاپ تقدیرنامه، حداقل ۳ امتیاز لازم است.');
            $this->redirect('/teachers');
        }

        $this->render('teachers/appreciation', [
            'title' => 'تقدیرنامه استاد',
            'teacher' => $teacher,
            'meritCount' => $meritCount,
        ]);
    }

    private function references(): array
    {
        $db = Database::connection();
        return [
            'classes' => $db->query('SELECT * FROM school_classes ORDER BY name')->fetchAll(),
            'subjects' => $db->query('SELECT * FROM subjects ORDER BY name')->fetchAll(),
            'levels' => $db->query('SELECT * FROM study_levels ORDER BY id')->fetchAll(),
            'semesters' => $db->query('SELECT * FROM semesters WHERE number BETWEEN 1 AND 4 ORDER BY number')->fetchAll(),
            'periods' => $db->query('SELECT * FROM course_periods ORDER BY number')->fetchAll(),
        ];
    }

    private function payload(?string $imagePath, ?string $planFile, ?string $eduDoc, ?string $expDoc): array
    {
        $education = (string) ($_POST['education_level'] ?? 'p');
        if (!in_array($education, ['p', 'b', 'm', 'd'], true)) {
            $education = 'p';
        }

        $gender = (string) ($_POST['gender'] ?? 'male');
        if (!in_array($gender, ['male', 'female'], true)) {
            $gender = 'male';
        }

        return [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'father_name' => trim((string) ($_POST['father_name'] ?? '')),
            'birth_date' => (($_POST['birth_date'] ?? '') !== '') ? $_POST['birth_date'] : null,
            'permanent_address' => trim((string) ($_POST['permanent_address'] ?? '')),
            'current_address' => trim((string) ($_POST['current_address'] ?? '')),
            'village' => trim((string) ($_POST['village'] ?? '')),
            'district' => trim((string) ($_POST['district'] ?? '')),
            'area' => trim((string) ($_POST['area'] ?? '')),
            'gender' => $gender,
            'education_level' => $education,
            'id_number' => trim((string) ($_POST['id_number'] ?? '')),
            'image_path' => $imagePath,
            'plan_file' => $planFile,
            'education_document' => $eduDoc,
            'experience_document' => $expDoc,
        ];
    }

    private function syncMany(int $teacherId, string $table, string $column, array $ids): void
    {
        $db = Database::connection();

        $delete = $db->prepare("DELETE FROM {$table} WHERE teacher_id = :teacher_id");
        $delete->execute(['teacher_id' => $teacherId]);

        $insert = $db->prepare("INSERT INTO {$table} (teacher_id, {$column}) VALUES (:teacher_id, :value)");
        foreach ($ids as $id) {
            $value = (int) $id;
            if ($value > 0) {
                $insert->execute(['teacher_id' => $teacherId, 'value' => $value]);
            }
        }
    }

    private function selectedIds(string $table, string $column, int $teacherId): array
    {
        $db = Database::connection();
        $stmt = $db->prepare("SELECT {$column} FROM {$table} WHERE teacher_id = :teacher_id");
        $stmt->execute(['teacher_id' => $teacherId]);
        return array_map(static fn (array $row): int => (int) $row[$column], $stmt->fetchAll());
    }

    private function loadBehaviorMap(array $teacherIds): array
    {
        if ($teacherIds === []) {
            return [];
        }

        $db = Database::connection();
        $ids = implode(',', array_fill(0, count($teacherIds), '?'));

        $stmt = $db->prepare("SELECT * FROM teacher_behaviors WHERE teacher_id IN ($ids) ORDER BY created_at DESC");
        $stmt->execute($teacherIds);

        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(int) $row['teacher_id']][] = $row;
        }

        return $map;
    }

    /**
     * @return array{valid:bool,error:string,class_ids:array<int>,subject_ids:array<int>,level_ids:array<int>,semester_ids:array<int>,period_ids:array<int>}
     */
    private function validateTeacherInput(?array $existingTeacher, ?int $teacherId): array
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $fatherName = trim((string) ($_POST['father_name'] ?? ''));
        $birthDate = trim((string) ($_POST['birth_date'] ?? ''));
        $permanentAddress = trim((string) ($_POST['permanent_address'] ?? ''));
        $currentAddress = trim((string) ($_POST['current_address'] ?? ''));
        $village = trim((string) ($_POST['village'] ?? ''));
        $district = trim((string) ($_POST['district'] ?? ''));
        $area = trim((string) ($_POST['area'] ?? ''));
        $gender = (string) ($_POST['gender'] ?? '');
        $educationLevel = (string) ($_POST['education_level'] ?? '');
        $idNumber = trim((string) ($_POST['id_number'] ?? ''));

        if ($name === '' || mb_strlen($name) < 3 || mb_strlen($name) > 255) {
            return $this->validationError('نام و تخلص استاد الزامی است (حداقل ۳ حرف).');
        }
        if ($fatherName === '' || mb_strlen($fatherName) < 3 || mb_strlen($fatherName) > 255) {
            return $this->validationError('نام پدر الزامی است (حداقل ۳ حرف).');
        }
        if (!$this->isValidDate($birthDate)) {
            return $this->validationError('تاریخ تولد معتبر نیست. نمونه: 1994-02-20');
        }
        if (!in_array($gender, ['male', 'female'], true)) {
            return $this->validationError('جنسیت انتخاب نشده است.');
        }
        if (!in_array($educationLevel, ['p', 'b', 'm', 'd'], true)) {
            return $this->validationError('سویه تحصیلی معتبر نیست.');
        }
        if ($idNumber === '' || mb_strlen($idNumber) > 100 || !preg_match('/^[0-9A-Za-z\-\/\s]+$/u', $idNumber)) {
            return $this->validationError('نمبر تذکره معتبر نیست.');
        }
        if ($permanentAddress === '' || mb_strlen($permanentAddress) < 2) {
            return $this->validationError('ولایت اصلی/سکونت اصلی را وارد کنید.');
        }
        if ($currentAddress === '' || mb_strlen($currentAddress) < 2) {
            return $this->validationError('ولایت فعلی/سکونت فعلی را وارد کنید.');
        }
        if ($village === '' || mb_strlen($village) > 150) {
            return $this->validationError('قریه را به شکل درست وارد کنید.');
        }
        if ($district === '' || mb_strlen($district) > 150) {
            return $this->validationError('ولسوالی را به شکل درست وارد کنید.');
        }
        if ($area === '' || mb_strlen($area) > 150) {
            return $this->validationError('ناحیه را به شکل درست وارد کنید.');
        }

        $imageValidation = $this->validateUploadedFile('image', ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024, false, 'عکس');
        if ($imageValidation !== null) {
            return $this->validationError($imageValidation);
        }
        $planValidation = $this->validateUploadedFile('plan_file', ['pdf'], 5 * 1024 * 1024, false, 'پلان درسی');
        if ($planValidation !== null) {
            return $this->validationError($planValidation);
        }
        $eduValidation = $this->validateUploadedFile('education_document', ['pdf', 'jpg', 'jpeg', 'png'], 5 * 1024 * 1024, false, 'سند تحصیلی');
        if ($eduValidation !== null) {
            return $this->validationError($eduValidation);
        }
        $expValidation = $this->validateUploadedFile('experience_document', ['pdf', 'jpg', 'jpeg', 'png'], 5 * 1024 * 1024, false, 'سند تجربه');
        if ($expValidation !== null) {
            return $this->validationError($expValidation);
        }

        $classIds = $this->normalizeIdArray($_POST['class_ids'] ?? []);
        $subjectIds = $this->normalizeIdArray($_POST['subject_ids'] ?? []);
        $levelIds = $this->normalizeIdArray($_POST['level_ids'] ?? []);
        $semesterIds = $this->normalizeIdArray($_POST['semester_ids'] ?? []);
        $periodIds = $this->normalizeIdArray($_POST['period_ids'] ?? []);

        if (count($classIds) < 1) {
            return $this->validationError('حداقل یک صنف تدریس را انتخاب کنید.');
        }
        if (count($subjectIds) < 1) {
            return $this->validationError('حداقل یک مضمون را انتخاب کنید.');
        }
        if (count($levelIds) < 1) {
            return $this->validationError('حداقل یک سطح تدریس را انتخاب کنید.');
        }

        if (!$this->allIdsExist('school_classes', $classIds)) {
            return $this->validationError('صنف‌های انتخاب‌شده معتبر نیستند.');
        }
        if (!$this->allIdsExist('subjects', $subjectIds)) {
            return $this->validationError('مضامین انتخاب‌شده معتبر نیستند.');
        }
        if (!$this->allIdsExist('study_levels', $levelIds)) {
            return $this->validationError('سطوح انتخاب‌شده معتبر نیستند.');
        }
        if ($semesterIds !== [] && !$this->allIdsExist('semesters', $semesterIds)) {
            return $this->validationError('سمسترهای انتخاب‌شده معتبر نیستند.');
        }
        if ($periodIds !== [] && !$this->allIdsExist('course_periods', $periodIds)) {
            return $this->validationError('دوره‌های انتخاب‌شده معتبر نیستند.');
        }

        $db = Database::connection();
        $levelCodes = $this->levelCodesByIds($levelIds);

        if (in_array('aali', $levelCodes, true) && count($semesterIds) < 1) {
            return $this->validationError('برای سطح عالی، حداقل یک سمستر انتخاب کنید.');
        }

        $requiresPeriod = in_array('moteseta', $levelCodes, true) || in_array('ebtedai', $levelCodes, true);
        if ($requiresPeriod && count($periodIds) < 1) {
            return $this->validationError('برای متوسطه/ابتداییه، حداقل یک دوره انتخاب کنید.');
        }

        if (!in_array('aali', $levelCodes, true) && count($semesterIds) > 0) {
            return $this->validationError('وقتی سطح عالی انتخاب نیست، سمستر نباید انتخاب شود.');
        }

        if (!$requiresPeriod && count($periodIds) > 0) {
            return $this->validationError('وقتی متوسطه/ابتداییه انتخاب نیست، دوره نباید انتخاب شود.');
        }

        $classLevelStmt = $db->prepare('SELECT id, level_id FROM school_classes WHERE id IN (' . implode(',', array_fill(0, count($classIds), '?')) . ')');
        $classLevelStmt->execute($classIds);
        foreach ($classLevelStmt->fetchAll() as $row) {
            $classLevelId = (int) ($row['level_id'] ?? 0);
            if ($classLevelId > 0 && !in_array($classLevelId, $levelIds, true)) {
                return $this->validationError('سطح صنف‌های انتخابی باید داخل سطوح تدریس انتخاب‌شده باشد.');
            }
        }

        return [
            'valid' => true,
            'error' => '',
            'class_ids' => $classIds,
            'subject_ids' => $subjectIds,
            'level_ids' => $levelIds,
            'semester_ids' => in_array('aali', $levelCodes, true) ? $semesterIds : [],
            'period_ids' => $requiresPeriod ? $periodIds : [],
        ];
    }

    /**
     * @return array<int>
     */
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

    /**
     * @return array<int, string>
     */
    private function levelCodesByIds(array $levelIds): array
    {
        if ($levelIds === []) {
            return [];
        }

        $db = Database::connection();
        $placeholders = implode(',', array_fill(0, count($levelIds), '?'));
        $stmt = $db->prepare("SELECT code FROM study_levels WHERE id IN ($placeholders)");
        $stmt->execute($levelIds);

        return array_map(static fn (array $row): string => (string) $row['code'], $stmt->fetchAll());
    }

    private function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $date));
        return checkdate($month, $day, $year);
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

    /**
     * @return array{valid:bool,error:string,class_ids:array<int>,subject_ids:array<int>,level_ids:array<int>,semester_ids:array<int>,period_ids:array<int>}
     */
    private function validationError(string $message): array
    {
        return [
            'valid' => false,
            'error' => $message,
            'class_ids' => [],
            'subject_ids' => [],
            'level_ids' => [],
            'semester_ids' => [],
            'period_ids' => [],
        ];
    }
}
