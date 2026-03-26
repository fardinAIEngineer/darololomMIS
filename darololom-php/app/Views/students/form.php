<?php
$oldOr = static fn(string $key, mixed $fallback = ''): mixed => old($key, $student[$key] ?? $fallback);
$selectedSemesters = array_map('intval', $selectedSemesters ?? []);
$selectedPeriods = array_map('intval', $selectedPeriods ?? []);
?>

<div class="section-title">
    <h2><?= e($student ? 'ویرایش دانش‌آموز' : 'ثبت دانش‌آموز جدید') ?></h2>
</div>

<div class="news-thumb">
    <div class="news-info">
        <form method="post" action="<?= e($formAction) ?>" enctype="multipart/form-data" class="module-form student-form-grid">
            <?= csrf_field() ?>

            <div class="form-group"><label>نام</label><input class="form-control" type="text" name="name" value="<?= e((string) $oldOr('name')) ?>" required></div>
            <div class="form-group"><label>نام پدر</label><input class="form-control" type="text" name="father_name" value="<?= e((string) $oldOr('father_name')) ?>"></div>
            <div class="form-group"><label>نام پدر کلان</label><input class="form-control" type="text" name="grandfather_name" value="<?= e((string) $oldOr('grandfather_name')) ?>"></div>
            <div class="form-group"><label>تاریخ تولد</label><input class="form-control" type="date" name="birth_date" value="<?= e((string) $oldOr('birth_date')) ?>"></div>

            <div class="form-group"><label>جنسیت</label>
                <select class="form-control" name="gender">
                    <option value="male" <?= (string) $oldOr('gender', 'male') === 'male' ? 'selected' : '' ?>>مذکر</option>
                    <option value="female" <?= (string) $oldOr('gender') === 'female' ? 'selected' : '' ?>>مونث</option>
                </select>
            </div>

            <div class="form-group"><label>نمبر تذکره</label><input class="form-control" type="text" name="id_number" value="<?= e((string) $oldOr('id_number')) ?>"></div>
            <div class="form-group"><label>نمبر کانکور</label><input class="form-control" type="text" name="exam_number" value="<?= e((string) $oldOr('exam_number')) ?>"></div>
            <div class="form-group"><label>شماره تماس</label><input class="form-control" type="text" name="mobile_number" value="<?= e((string) $oldOr('mobile_number')) ?>"></div>

            <div class="form-group"><label>سطح آموزشی</label>
                <select class="form-control" name="level_id" required>
                    <option value="">انتخاب کنید</option>
                    <?php foreach ($levels as $level): ?>
                        <option value="<?= e((string) $level['id']) ?>" <?= (string) $oldOr('level_id') === (string) $level['id'] ? 'selected' : '' ?>><?= e($level['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group"><label>صنف</label>
                <select class="form-control" name="school_class_id">
                    <option value="">—</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= e((string) $class['id']) ?>" <?= (string) $oldOr('school_class_id') === (string) $class['id'] ? 'selected' : '' ?>>
                            <?= e($class['name']) ?> (<?= e($class['level_name'] ?? '-') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group"><label>تایم آغاز</label><input class="form-control" type="time" name="time_start" value="<?= e((string) $oldOr('time_start')) ?>"></div>
            <div class="form-group"><label>تایم ختم</label><input class="form-control" type="time" name="time_end" value="<?= e((string) $oldOr('time_end')) ?>"></div>

            <div class="form-group full"><label>نشانی فعلی</label><textarea class="form-control" name="current_address" rows="2"><?= e((string) $oldOr('current_address')) ?></textarea></div>
            <div class="form-group full"><label>نشانی دایمی</label><textarea class="form-control" name="permanent_address" rows="2"><?= e((string) $oldOr('permanent_address')) ?></textarea></div>

            <div class="form-group"><label>قریه</label><input class="form-control" type="text" name="village" value="<?= e((string) $oldOr('village')) ?>"></div>
            <div class="form-group"><label>ولسوالی</label><input class="form-control" type="text" name="district" value="<?= e((string) $oldOr('district')) ?>"></div>
            <div class="form-group"><label>ناحیه</label><input class="form-control" type="text" name="area" value="<?= e((string) $oldOr('area')) ?>"></div>

            <div class="form-group">
                <label>عکس</label>
                <input class="form-control" type="file" name="image" accept="image/*">
                <?php if (!empty($student['image_path'])): ?><a href="<?= e(url($student['image_path'])) ?>" target="_blank">نمایش فایل فعلی</a><?php endif; ?>
            </div>

            <div class="form-group">
                <label>شهادت‌نامه (PDF)</label>
                <input class="form-control" type="file" name="certificate_file" accept="application/pdf">
                <?php if (!empty($student['certificate_file'])): ?><a href="<?= e(url($student['certificate_file'])) ?>" target="_blank">نمایش فایل فعلی</a><?php endif; ?>
            </div>

            <div class="form-group">
                <label>شماره سرتفیکت</label>
                <input class="form-control" type="text" name="certificate_number" value="<?= e((string) $oldOr('certificate_number')) ?>">
            </div>

            <div class="form-group check-group">
                <label><input type="checkbox" name="is_grade12_graduate" <?= (int) $oldOr('is_grade12_graduate', 0) === 1 ? 'checked' : '' ?>> فارغ صنف دوازدهم</label>
                <label><input type="checkbox" name="is_graduated" <?= (int) $oldOr('is_graduated', 0) === 1 ? 'checked' : '' ?>> فارغ از دوره</label>
            </div>

            <div class="form-group full picker-group">
                <label>سمسترها</label>
                <div class="inline-checks">
                    <?php foreach ($semesters as $item): ?>
                        <label><input type="checkbox" name="semester_ids[]" value="<?= e((string) $item['id']) ?>" <?= in_array((int) $item['id'], $selectedSemesters, true) ? 'checked' : '' ?>> <?= e((string) $item['number']) ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group full picker-group">
                <label>دوره‌ها</label>
                <div class="inline-checks">
                    <?php foreach ($periods as $item): ?>
                        <label><input type="checkbox" name="period_ids[]" value="<?= e((string) $item['id']) ?>" <?= in_array((int) $item['id'], $selectedPeriods, true) ? 'checked' : '' ?>> <?= e((string) $item['number']) ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="full form-actions">
                <button class="section-btn btn btn-default" type="submit">ذخیره</button>
                <a class="btn btn-default" href="<?= e(url('/students')) ?>">انصراف</a>
            </div>
        </form>
    </div>
</div>
