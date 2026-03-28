<?php
$linkedUser = $linkedUser ?? null;
$oldOr = static fn(string $key, mixed $fallback = ''): mixed => old($key, $student[$key] ?? $fallback);

$selectedSemestersInput = old('semester_ids', $selectedSemesters ?? []);
if (!is_array($selectedSemestersInput)) {
    $selectedSemestersInput = [];
}
$selectedSemesters = array_map('intval', $selectedSemestersInput);

$selectedPeriodsInput = old('period_ids', $selectedPeriods ?? []);
if (!is_array($selectedPeriodsInput)) {
    $selectedPeriodsInput = [];
}
$selectedPeriods = array_map('intval', $selectedPeriodsInput);

$hasExistingCertificate = !empty($student['certificate_file']);
$accountEmail = (string) old('account_email', (string) ($linkedUser['email'] ?? ''));
$mustSetPassword = empty($linkedUser['id']);
?>

<div class="section-title">
    <h2><?= e($student ? 'ویرایش دانش‌آموز' : 'ثبت دانش‌آموز جدید') ?></h2>
</div>

<div class="news-thumb student-wizard-wrap">
    <div class="news-info">
        <div class="wizard-header">
            <button type="button" class="wizard-step is-active" data-step-target="1">۱) مشخصات فردی</button>
            <button type="button" class="wizard-step" data-step-target="2">۲) مشخصات آموزشی</button>
            <button type="button" class="wizard-step" data-step-target="3">۳) آدرس و زمان</button>
            <button type="button" class="wizard-step" data-step-target="4">۴) فایل‌ها، حساب و ثبت</button>
        </div>

        <form id="studentWizardForm" method="post" action="<?= e($formAction) ?>" enctype="multipart/form-data" class="module-form student-form-grid" novalidate>
            <?= csrf_field() ?>

            <section class="wizard-panel is-active" data-step-panel="1">
                <div class="form-group">
                    <label>نام دانش‌آموز</label>
                    <input class="form-control" type="text" name="name" value="<?= e((string) $oldOr('name')) ?>" placeholder="مثال: عبدالرحمن نادری" minlength="3" maxlength="255" required>
                    <small class="field-help">نام کامل را حداقل با ۳ حرف وارد کنید.</small>
                </div>

                <div class="form-group">
                    <label>نام پدر</label>
                    <input class="form-control" type="text" name="father_name" value="<?= e((string) $oldOr('father_name')) ?>" placeholder="مثال: محمدکریم" minlength="3" maxlength="255" required>
                    <small class="field-help">نام پدر الزامی است.</small>
                </div>

                <div class="form-group">
                    <label>نام پدرکلان</label>
                    <input class="form-control" type="text" name="grandfather_name" value="<?= e((string) $oldOr('grandfather_name')) ?>" placeholder="مثال: غلام‌نبی" maxlength="255">
                    <small class="field-help">اختیاری؛ در صورت نیاز وارد شود.</small>
                </div>

                <div class="form-group">
                    <label>تاریخ تولد</label>
                    <input class="form-control" type="date" name="birth_date" value="<?= e((string) $oldOr('birth_date')) ?>" required>
                    <small class="field-help">فرمت: سال-ماه-روز (میلادی).</small>
                </div>

                <div class="form-group">
                    <label>جنسیت</label>
                    <select class="form-control" name="gender" required>
                        <option value="male" <?= (string) $oldOr('gender', 'male') === 'male' ? 'selected' : '' ?>>مذکر</option>
                        <option value="female" <?= (string) $oldOr('gender') === 'female' ? 'selected' : '' ?>>مونث</option>
                    </select>
                    <small class="field-help">یکی از گزینه‌ها را انتخاب کنید.</small>
                </div>

                <div class="form-group">
                    <label>نمبر تذکره</label>
                    <input class="form-control" type="text" name="id_number" value="<?= e((string) $oldOr('id_number')) ?>" placeholder="مثال: 12345-67890" pattern="[0-9A-Za-z\-\/\s]+" maxlength="100" required>
                    <small class="field-help">اعداد/حروف انگلیسی، خط تیره یا اسلش مجاز است.</small>
                </div>

                <div class="form-group">
                    <label>شماره تماس</label>
                    <input class="form-control" type="text" name="mobile_number" value="<?= e((string) $oldOr('mobile_number')) ?>" placeholder="مثال: 0700123456" pattern="[0-9+\-\s]{7,20}" maxlength="20" required>
                    <small class="field-help">حداقل ۷ رقم، فقط عدد و + و - مجاز است.</small>
                </div>

                <div class="form-group">
                    <label>نمبر امتحان کانکور</label>
                    <input class="form-control" type="text" name="exam_number" value="<?= e((string) $oldOr('exam_number')) ?>" placeholder="مثال: KANKOR-2026-145" pattern="[0-9A-Za-z\-\/\s]*" maxlength="100">
                    <small class="field-help">برای سطح عالی الزامی می‌شود.</small>
                </div>
            </section>

            <section class="wizard-panel" data-step-panel="2">
                <div class="form-group">
                    <label>سطح آموزشی</label>
                    <select class="form-control" name="level_id" id="level_id" required>
                        <option value="">انتخاب کنید</option>
                        <?php foreach ($levels as $level): ?>
                            <option value="<?= e((string) $level['id']) ?>" data-level-code="<?= e((string) ($level['code'] ?? '')) ?>" <?= (string) $oldOr('level_id') === (string) $level['id'] ? 'selected' : '' ?>>
                                <?= e($level['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="field-help">سطح باید با صنف انتخابی هم‌خوانی داشته باشد.</small>
                </div>

                <div class="form-group">
                    <label>صنف</label>
                    <select class="form-control" name="school_class_id" id="school_class_id">
                        <option value="">—</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= e((string) $class['id']) ?>" data-class-level-id="<?= e((string) ($class['level_id'] ?? '')) ?>" <?= (string) $oldOr('school_class_id') === (string) $class['id'] ? 'selected' : '' ?>>
                                <?= e($class['name']) ?> (<?= e($class['level_name'] ?? '-') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="field-help">در صورت نیاز صنف را انتخاب کنید.</small>
                </div>

                <div class="form-group">
                    <label>شماره سرتفیکت</label>
                    <input class="form-control" type="text" name="certificate_number" value="<?= e((string) $oldOr('certificate_number')) ?>" placeholder="مثال: CERT-2026-001" pattern="[0-9A-Za-z\-\/]*" maxlength="50">
                    <small class="field-help">نباید تکراری باشد.</small>
                </div>

                <div class="form-group check-group">
                    <label><input type="checkbox" name="is_grade12_graduate" id="is_grade12_graduate" <?= (int) $oldOr('is_grade12_graduate', 0) === 1 ? 'checked' : '' ?>> فارغ صنف دوازدهم</label>
                    <label><input type="checkbox" name="is_graduated" <?= (int) $oldOr('is_graduated', 0) === 1 ? 'checked' : '' ?>> فارغ از دوره</label>
                    <small class="field-help">برای سطح عالی، گزینه فارغ صنف دوازدهم باید فعال باشد.</small>
                </div>

                <div class="form-group full picker-group" id="semester_block">
                    <label>سمسترها</label>
                    <div class="inline-checks">
                        <?php foreach ($semesters as $item): ?>
                            <label><input type="checkbox" name="semester_ids[]" value="<?= e((string) $item['id']) ?>" <?= in_array((int) $item['id'], $selectedSemesters, true) ? 'checked' : '' ?>> <?= e((string) $item['number']) ?></label>
                        <?php endforeach; ?>
                    </div>
                    <small class="field-help">برای سطح عالی دقیقاً یک سمستر انتخاب کنید.</small>
                </div>

                <div class="form-group full picker-group" id="period_block">
                    <label>دوره‌ها</label>
                    <div class="inline-checks">
                        <?php foreach ($periods as $item): ?>
                            <label><input type="checkbox" name="period_ids[]" value="<?= e((string) $item['id']) ?>" <?= in_array((int) $item['id'], $selectedPeriods, true) ? 'checked' : '' ?>> <?= e((string) $item['number']) ?></label>
                        <?php endforeach; ?>
                    </div>
                    <small class="field-help">برای ابتداییه/متوسطه دقیقاً یک دوره انتخاب کنید.</small>
                </div>
            </section>

            <section class="wizard-panel" data-step-panel="3">
                <div class="form-group full">
                    <label>نشانی فعلی</label>
                    <textarea class="form-control" name="current_address" rows="2" placeholder="مثال: کابل، ناحیه ۸، کوچه گلستان" required><?= e((string) $oldOr('current_address')) ?></textarea>
                    <small class="field-help">نشانی دقیق فعلی دانش‌آموز.</small>
                </div>

                <div class="form-group full">
                    <label>نشانی دایمی</label>
                    <textarea class="form-control" name="permanent_address" rows="2" placeholder="مثال: بغلان، ولسوالی خنجان، قریه نوآباد" required><?= e((string) $oldOr('permanent_address')) ?></textarea>
                    <small class="field-help">نشانی اصلی خانواده.</small>
                </div>

                <div class="form-group">
                    <label>قریه</label>
                    <input class="form-control" type="text" name="village" value="<?= e((string) $oldOr('village')) ?>" placeholder="مثال: نوآباد" maxlength="150" required>
                    <small class="field-help">نام قریه را وارد کنید.</small>
                </div>

                <div class="form-group">
                    <label>ولسوالی</label>
                    <input class="form-control" type="text" name="district" value="<?= e((string) $oldOr('district')) ?>" placeholder="مثال: خنجان" maxlength="150" required>
                    <small class="field-help">نام ولسوالی را وارد کنید.</small>
                </div>

                <div class="form-group">
                    <label>ناحیه</label>
                    <input class="form-control" type="text" name="area" value="<?= e((string) $oldOr('area')) ?>" placeholder="مثال: ناحیه ۳" maxlength="150" required>
                    <small class="field-help">ناحیه فعلی را وارد کنید.</small>
                </div>

                <div class="form-group">
                    <label>تایم آغاز</label>
                    <input class="form-control" type="time" name="time_start" id="time_start" value="<?= e((string) $oldOr('time_start')) ?>">
                    <small class="field-help">مثال: 08:00</small>
                </div>

                <div class="form-group">
                    <label>تایم ختم</label>
                    <input class="form-control" type="time" name="time_end" id="time_end" value="<?= e((string) $oldOr('time_end')) ?>">
                    <small class="field-help">باید بعد از تایم آغاز باشد.</small>
                </div>
            </section>

            <section class="wizard-panel" data-step-panel="4">
                <div class="form-group">
                    <label>عکس</label>
                    <input class="form-control" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/*">
                    <small class="field-help">فرمت مجاز: JPG/PNG/WEBP | حداکثر 2MB</small>
                    <?php if (!empty($student['image_path'])): ?><a href="<?= e(url($student['image_path'])) ?>" target="_blank">نمایش فایل فعلی</a><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>شهادت‌نامه (PDF)</label>
                    <input class="form-control" type="file" name="certificate_file" accept=".pdf,application/pdf">
                    <small class="field-help">برای سطح عالی الزامی است | حداکثر 5MB</small>
                    <?php if (!empty($student['certificate_file'])): ?><a href="<?= e(url($student['certificate_file'])) ?>" target="_blank">نمایش فایل فعلی</a><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>ایمیل حساب شاگرد</label>
                    <input class="form-control" type="email" name="account_email" id="account_email" value="<?= e($accountEmail) ?>" placeholder="مثال: student@example.com" required>
                    <small class="field-help">شاگرد با همین ایمیل وارد حساب خود می‌شود.</small>
                </div>

                <div class="form-group">
                    <label>رمز عبور حساب شاگرد</label>
                    <input class="form-control" type="password" name="account_password" id="account_password" placeholder="<?= $mustSetPassword ? 'حداقل ۸ کاراکتر (الزامی)' : 'اگر تغییر نمی‌دهید خالی بگذارید' ?>" minlength="8" <?= $mustSetPassword ? 'required' : '' ?>>
                    <small class="field-help"><?= $mustSetPassword ? 'برای ایجاد حساب شاگرد، رمز عبور الزامی است.' : 'در ویرایش، فقط در صورت نیاز رمز جدید وارد کنید.' ?></small>
                </div>

                <div class="form-group">
                    <label>تکرار رمز عبور</label>
                    <input class="form-control" type="password" name="account_password_confirmation" id="account_password_confirmation" placeholder="تکرار رمز عبور" minlength="8" <?= $mustSetPassword ? 'required' : '' ?>>
                    <small class="field-help">باید دقیقاً با رمز عبور یکسان باشد.</small>
                </div>

                <div class="form-group full">
                    <div class="summary-note">
                        قبل از ثبت نهایی، تمام اطلاعات را یک‌بار مرور کنید. در صورت خطا، سیستم پیام دقیق نمایش می‌دهد.
                    </div>
                </div>
            </section>

            <div class="full wizard-actions">
                <button type="button" class="btn btn-default" id="prevStepBtn" disabled>مرحله قبلی</button>
                <button type="button" class="section-btn btn btn-default" id="nextStepBtn">مرحله بعدی</button>
                <button class="section-btn btn btn-default" type="submit" id="submitBtn" style="display:none;">ذخیره نهایی</button>
                <a class="btn btn-default" href="<?= e(url('/students')) ?>">انصراف</a>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('studentWizardForm');
    if (!form) return;

    const steps = Array.from(document.querySelectorAll('.wizard-step'));
    const panels = Array.from(document.querySelectorAll('.wizard-panel'));
    const prevBtn = document.getElementById('prevStepBtn');
    const nextBtn = document.getElementById('nextStepBtn');
    const submitBtn = document.getElementById('submitBtn');
    const levelSelect = document.getElementById('level_id');
    const schoolClassSelect = document.getElementById('school_class_id');
    const timeStart = document.getElementById('time_start');
    const timeEnd = document.getElementById('time_end');
    const semesterBlock = document.getElementById('semester_block');
    const periodBlock = document.getElementById('period_block');
    const accountEmail = document.getElementById('account_email');
    const accountPassword = document.getElementById('account_password');
    const accountPasswordConfirm = document.getElementById('account_password_confirmation');
    const hasExistingCertificate = <?= $hasExistingCertificate ? 'true' : 'false' ?>;
    const mustSetPassword = <?= $mustSetPassword ? 'true' : 'false' ?>;

    let currentStep = 0;

    function selectedLevelCode() {
        const opt = levelSelect ? levelSelect.options[levelSelect.selectedIndex] : null;
        return opt ? (opt.dataset.levelCode || '') : '';
    }

    function showStep(index) {
        currentStep = Math.max(0, Math.min(index, panels.length - 1));

        panels.forEach((panel, i) => {
            panel.classList.toggle('is-active', i === currentStep);
        });

        steps.forEach((step, i) => {
            step.classList.toggle('is-active', i === currentStep);
            step.classList.toggle('is-done', i < currentStep);
        });

        prevBtn.disabled = currentStep === 0;
        const last = currentStep === panels.length - 1;
        nextBtn.style.display = last ? 'none' : '';
        submitBtn.style.display = last ? '' : 'none';
    }

    function validateNative(panel) {
        const fields = Array.from(panel.querySelectorAll('input, select, textarea'));
        for (const field of fields) {
            if (field.type === 'hidden' || field.disabled) continue;
            if (!field.checkValidity()) {
                field.reportValidity();
                return false;
            }
        }
        return true;
    }

    function checkedCount(name) {
        return form.querySelectorAll('input[name="' + name + '"]:checked').length;
    }

    function customValidate() {
        const levelCode = selectedLevelCode();

        if (levelSelect && !levelSelect.value) {
            alert('لطفاً سطح آموزشی را انتخاب کنید.');
            showStep(1);
            return false;
        }

        if (schoolClassSelect && schoolClassSelect.value && levelSelect && levelSelect.value) {
            const selectedClass = schoolClassSelect.options[schoolClassSelect.selectedIndex];
            const classLevelId = selectedClass ? (selectedClass.dataset.classLevelId || '') : '';
            if (classLevelId !== '' && classLevelId !== levelSelect.value) {
                alert('سطح صنف با سطح آموزشی انتخاب‌شده مطابقت ندارد.');
                showStep(1);
                return false;
            }
        }

        if (levelCode === 'aali') {
            const grade12 = document.getElementById('is_grade12_graduate');
            const examNumber = form.querySelector('input[name="exam_number"]');
            const certFile = form.querySelector('input[name="certificate_file"]');

            if (grade12 && !grade12.checked) {
                alert('برای سطح عالی، گزینه فارغ صنف دوازدهم باید فعال باشد.');
                showStep(1);
                return false;
            }
            if (examNumber && examNumber.value.trim() === '') {
                alert('برای سطح عالی، نمبر کانکور الزامی است.');
                showStep(0);
                examNumber.focus();
                return false;
            }
            if (checkedCount('semester_ids[]') !== 1) {
                alert('برای سطح عالی دقیقاً یک سمستر انتخاب کنید.');
                showStep(1);
                return false;
            }
            if (checkedCount('period_ids[]') > 0) {
                alert('برای سطح عالی نباید دوره انتخاب شود.');
                showStep(1);
                return false;
            }
            if (certFile && certFile.files.length === 0 && !hasExistingCertificate) {
                alert('برای سطح عالی، شهادت‌نامه الزامی است.');
                showStep(3);
                return false;
            }
        } else if (levelCode === 'moteseta' || levelCode === 'ebtedai') {
            if (checkedCount('period_ids[]') !== 1) {
                alert('برای ابتداییه/متوسطه دقیقاً یک دوره انتخاب کنید.');
                showStep(1);
                return false;
            }
            if (checkedCount('semester_ids[]') > 0) {
                alert('برای ابتداییه/متوسطه نباید سمستر انتخاب شود.');
                showStep(1);
                return false;
            }
        }

        if (timeStart && timeEnd && timeStart.value && timeEnd.value && timeStart.value >= timeEnd.value) {
            alert('تایم ختم باید بعد از تایم آغاز باشد.');
            showStep(2);
            return false;
        }

        if (!accountEmail || !accountEmail.value.trim() || !accountEmail.checkValidity()) {
            alert('ایمیل حساب شاگرد معتبر نیست.');
            showStep(3);
            if (accountEmail) accountEmail.focus();
            return false;
        }

        if (mustSetPassword && (!accountPassword || accountPassword.value.trim() === '')) {
            alert('برای ایجاد حساب شاگرد، رمز عبور الزامی است.');
            showStep(3);
            if (accountPassword) accountPassword.focus();
            return false;
        }

        if (accountPassword && accountPassword.value !== '') {
            if (accountPassword.value.length < 8) {
                alert('رمز عبور حساب شاگرد باید حداقل ۸ کاراکتر باشد.');
                showStep(3);
                accountPassword.focus();
                return false;
            }
            if (!accountPasswordConfirm || accountPassword.value !== accountPasswordConfirm.value) {
                alert('تکرار رمز عبور حساب شاگرد یکسان نیست.');
                showStep(3);
                if (accountPasswordConfirm) accountPasswordConfirm.focus();
                return false;
            }
        } else if (accountPasswordConfirm && accountPasswordConfirm.value !== '') {
            alert('برای تکرار رمز، ابتدا رمز عبور جدید را وارد کنید.');
            showStep(3);
            if (accountPassword) accountPassword.focus();
            return false;
        }

        return true;
    }

    function validateCurrentStep() {
        const panel = panels[currentStep];
        if (!panel) return false;
        if (!validateNative(panel)) return false;

        if (currentStep === 1 || currentStep === 3) {
            return customValidate();
        }
        return true;
    }

    function updateConditionalBlocks() {
        const levelCode = selectedLevelCode();
        const isAali = levelCode === 'aali';

        if (semesterBlock) semesterBlock.style.display = isAali ? '' : 'none';
        if (periodBlock) periodBlock.style.display = isAali ? 'none' : '';
    }

    nextBtn.addEventListener('click', () => {
        if (!validateCurrentStep()) return;
        showStep(currentStep + 1);
    });

    prevBtn.addEventListener('click', () => showStep(currentStep - 1));

    steps.forEach((step, idx) => {
        step.addEventListener('click', () => {
            if (idx <= currentStep) {
                showStep(idx);
            }
        });
    });

    form.addEventListener('submit', (e) => {
        for (let i = 0; i < panels.length; i += 1) {
            currentStep = i;
            if (!validateCurrentStep()) {
                e.preventDefault();
                return;
            }
        }
    });

    if (levelSelect) {
        levelSelect.addEventListener('change', updateConditionalBlocks);
    }

    showStep(0);
    updateConditionalBlocks();
})();
</script>
