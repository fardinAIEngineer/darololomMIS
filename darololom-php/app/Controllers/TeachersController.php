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
        clear_old();
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
        $this->csrfCheck();
        $db = Database::connection();

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            with_old($_POST);
            flash('error', 'نام استاد الزامی است.');
            $this->redirect('/teachers/create');
        }

        $image = upload_file('image', 'teachers', ['jpg', 'jpeg', 'png', 'webp']);
        $plan = upload_file('plan_file', 'teacher_plans', ['pdf']);
        $eduDoc = upload_file('education_document', 'teacher_documents/education', ['pdf', 'jpg', 'jpeg', 'png']);
        $expDoc = upload_file('experience_document', 'teacher_documents/experience', ['pdf', 'jpg', 'jpeg', 'png']);

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

        $this->syncMany($teacherId, 'teacher_class', 'class_id', $_POST['class_ids'] ?? []);
        $this->syncMany($teacherId, 'teacher_subject', 'subject_id', $_POST['subject_ids'] ?? []);
        $this->syncMany($teacherId, 'teacher_level', 'level_id', $_POST['level_ids'] ?? []);
        $this->syncMany($teacherId, 'teacher_semester', 'semester_id', $_POST['semester_ids'] ?? []);
        $this->syncMany($teacherId, 'teacher_period', 'period_id', $_POST['period_ids'] ?? []);

        flash('success', 'استاد با موفقیت ثبت شد.');
        $this->redirect('/teachers');
    }

    public function edit(array $params = []): void
    {
        clear_old();
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

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            with_old($_POST);
            flash('error', 'نام استاد الزامی است.');
            $this->redirect('/teachers/' . $id . '/edit');
        }

        $image = upload_file('image', 'teachers', ['jpg', 'jpeg', 'png', 'webp']) ?: $teacher['image_path'];
        $plan = upload_file('plan_file', 'teacher_plans', ['pdf']) ?: $teacher['plan_file'];
        $eduDoc = upload_file('education_document', 'teacher_documents/education', ['pdf', 'jpg', 'jpeg', 'png']) ?: $teacher['education_document'];
        $expDoc = upload_file('experience_document', 'teacher_documents/experience', ['pdf', 'jpg', 'jpeg', 'png']) ?: $teacher['experience_document'];

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

        $this->syncMany($id, 'teacher_class', 'class_id', $_POST['class_ids'] ?? []);
        $this->syncMany($id, 'teacher_subject', 'subject_id', $_POST['subject_ids'] ?? []);
        $this->syncMany($id, 'teacher_level', 'level_id', $_POST['level_ids'] ?? []);
        $this->syncMany($id, 'teacher_semester', 'semester_id', $_POST['semester_ids'] ?? []);
        $this->syncMany($id, 'teacher_period', 'period_id', $_POST['period_ids'] ?? []);

        flash('success', 'اطلاعات استاد بروزرسانی شد.');
        $this->redirect('/teachers');
    }

    public function destroy(array $params = []): void
    {
        $this->csrfCheck();
        $id = $this->intParam($params, 'id');

        $db = Database::connection();
        $db->prepare('DELETE FROM teachers WHERE id = :id')->execute(['id' => $id]);

        flash('success', 'استاد حذف شد.');
        $this->redirect('/teachers');
    }

    public function addBehavior(array $params = []): void
    {
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
        $this->csrfCheck();
        $id = $this->intParam($params, 'id');

        $db = Database::connection();
        $db->prepare('DELETE FROM teacher_behaviors WHERE id = :id')->execute(['id' => $id]);

        flash('success', 'رکورد رفتاری حذف شد.');
        $this->redirect('/teachers');
    }

    public function appreciation(array $params = []): void
    {
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
            'semesters' => $db->query('SELECT * FROM semesters ORDER BY number')->fetchAll(),
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
}
