<?php
$oldOr = static fn(string $key, mixed $fallback = ''): mixed => old($key, $teacher[$key] ?? $fallback);
$selectedClassIds = array_map('intval', $selectedClassIds ?? []);
$selectedSubjectIds = array_map('intval', $selectedSubjectIds ?? []);
$selectedLevelIds = array_map('intval', $selectedLevelIds ?? []);
$selectedSemesterIds = array_map('intval', $selectedSemesterIds ?? []);
$selectedPeriodIds = array_map('intval', $selectedPeriodIds ?? []);
?>

<div class="section-title">
    <h2><?= e($teacher ? 'ویرایش استاد' : 'ثبت استاد جدید') ?></h2>
</div>

<div class="news-thumb">
    <div class="news-info">
        <form method="post" action="<?= e($formAction) ?>" enctype="multipart/form-data" class="module-form teacher-form-grid">
            <?= csrf_field() ?>

            <div class="form-group"><label>نام و تخلص</label><input class="form-control" name="name" value="<?= e((string) $oldOr('name')) ?>" required></div>
            <div class="form-group"><label>نام پدر</label><input class="form-control" name="father_name" value="<?= e((string) $oldOr('father_name')) ?>"></div>
            <div class="form-group"><label>تاریخ تولد</label><input class="form-control" type="date" name="birth_date" value="<?= e((string) $oldOr('birth_date')) ?>"></div>

            <div class="form-group">
                <label>جنسیت</label>
                <select class="form-control" name="gender">
                    <option value="male" <?= (string) $oldOr('gender', 'male') === 'male' ? 'selected' : '' ?>>مرد</option>
                    <option value="female" <?= (string) $oldOr('gender') === 'female' ? 'selected' : '' ?>>زن</option>
                </select>
            </div>

            <div class="form-group">
                <label>سویه تحصیلی</label>
                <select class="form-control" name="education_level">
                    <option value="p" <?= (string) $oldOr('education_level', 'p') === 'p' ? 'selected' : '' ?>>چهارده پاس</option>
                    <option value="b" <?= (string) $oldOr('education_level') === 'b' ? 'selected' : '' ?>>لیسانس</option>
                    <option value="m" <?= (string) $oldOr('education_level') === 'm' ? 'selected' : '' ?>>ماستر</option>
                    <option value="d" <?= (string) $oldOr('education_level') === 'd' ? 'selected' : '' ?>>دوکتور</option>
                </select>
            </div>

            <div class="form-group"><label>نمبر تذکره</label><input class="form-control" name="id_number" value="<?= e((string) $oldOr('id_number')) ?>"></div>
            <div class="form-group"><label>ولایت فعلی</label><input class="form-control" name="current_address" value="<?= e((string) $oldOr('current_address')) ?>"></div>
            <div class="form-group"><label>ولایت اصلی</label><input class="form-control" name="permanent_address" value="<?= e((string) $oldOr('permanent_address')) ?>"></div>
            <div class="form-group"><label>قریه</label><input class="form-control" name="village" value="<?= e((string) $oldOr('village')) ?>"></div>
            <div class="form-group"><label>ولسوالی</label><input class="form-control" name="district" value="<?= e((string) $oldOr('district')) ?>"></div>
            <div class="form-group"><label>ناحیه</label><input class="form-control" name="area" value="<?= e((string) $oldOr('area')) ?>"></div>

            <div class="form-group"><label>عکس</label><input type="file" class="form-control" name="image" accept="image/*"></div>
            <div class="form-group"><label>پلان درسی</label><input type="file" class="form-control" name="plan_file" accept="application/pdf"></div>
            <div class="form-group"><label>سند تحصیلی</label><input type="file" class="form-control" name="education_document"></div>
            <div class="form-group"><label>سند تجربه</label><input type="file" class="form-control" name="experience_document"></div>

            <div class="form-group full picker-group">
                <label>صنوف تدریس</label>
                <div class="inline-checks">
                    <?php foreach ($classes as $item): ?>
                        <label><input type="checkbox" name="class_ids[]" value="<?= e((string) $item['id']) ?>" <?= in_array((int) $item['id'], $selectedClassIds, true) ? 'checked' : '' ?>> <?= e($item['name']) ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group full picker-group">
                <label>مضامین</label>
                <div class="inline-checks">
                    <?php foreach ($subjects as $item): ?>
                        <label><input type="checkbox" name="subject_ids[]" value="<?= e((string) $item['id']) ?>" <?= in_array((int) $item['id'], $selectedSubjectIds, true) ? 'checked' : '' ?>> <?= e($item['name']) ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group full picker-group">
                <label>سطوح تدریس</label>
                <div class="inline-checks">
                    <?php foreach ($levels as $item): ?>
                        <label><input type="checkbox" name="level_ids[]" value="<?= e((string) $item['id']) ?>" <?= in_array((int) $item['id'], $selectedLevelIds, true) ? 'checked' : '' ?>> <?= e($item['name']) ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group full picker-group">
                <label>سمسترها</label>
                <div class="inline-checks">
                    <?php foreach ($semesters as $item): ?>
                        <label><input type="checkbox" name="semester_ids[]" value="<?= e((string) $item['id']) ?>" <?= in_array((int) $item['id'], $selectedSemesterIds, true) ? 'checked' : '' ?>> <?= e((string) $item['number']) ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group full picker-group">
                <label>دوره‌ها</label>
                <div class="inline-checks">
                    <?php foreach ($periods as $item): ?>
                        <label><input type="checkbox" name="period_ids[]" value="<?= e((string) $item['id']) ?>" <?= in_array((int) $item['id'], $selectedPeriodIds, true) ? 'checked' : '' ?>> <?= e((string) $item['number']) ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="full form-actions">
                <button class="section-btn btn btn-default" type="submit">ذخیره</button>
                <a class="btn btn-default" href="<?= e(url('/teachers')) ?>">انصراف</a>
            </div>
        </form>
    </div>
</div>
