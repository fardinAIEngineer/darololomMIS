<?php
$linkedUser = $linkedUser ?? null;
$oldOr = static fn(string $key, mixed $fallback = ''): mixed => old($key, $teacher[$key] ?? $fallback);

$selectedClassIdsInput = old('class_ids', $selectedClassIds ?? []);
if (!is_array($selectedClassIdsInput)) {
    $selectedClassIdsInput = [];
}
$selectedClassIds = array_map('intval', $selectedClassIdsInput);

$selectedSubjectIdsInput = old('subject_ids', $selectedSubjectIds ?? []);
if (!is_array($selectedSubjectIdsInput)) {
    $selectedSubjectIdsInput = [];
}
$selectedSubjectIds = array_map('intval', $selectedSubjectIdsInput);

$selectedLevelIdsInput = old('level_ids', $selectedLevelIds ?? []);
if (!is_array($selectedLevelIdsInput)) {
    $selectedLevelIdsInput = [];
}
$selectedLevelIds = array_map('intval', $selectedLevelIdsInput);

$selectedSemesterIdsInput = old('semester_ids', $selectedSemesterIds ?? []);
if (!is_array($selectedSemesterIdsInput)) {
    $selectedSemesterIdsInput = [];
}
$selectedSemesterIds = array_map('intval', $selectedSemesterIdsInput);

$selectedPeriodIdsInput = old('period_ids', $selectedPeriodIds ?? []);
if (!is_array($selectedPeriodIdsInput)) {
    $selectedPeriodIdsInput = [];
}
$selectedPeriodIds = array_map('intval', $selectedPeriodIdsInput);
$accountEmail = (string) old('account_email', (string) ($linkedUser['email'] ?? ''));
$mustSetPassword = empty($linkedUser['id']);
?>

<div class="section-title">
    <h2><?= e($teacher ? 'ویرایش استاد' : 'ثبت استاد جدید') ?></h2>
</div>

<div class="news-thumb teacher-wizard-wrap">
    <div class="news-info">
        <div class="wizard-header">
            <button type="button" class="wizard-step is-active" data-step-target="1">۱) اطلاعات فردی</button>
            <button type="button" class="wizard-step" data-step-target="2">۲) آدرس</button>
            <button type="button" class="wizard-step" data-step-target="3">۳) اسناد</button>
            <button type="button" class="wizard-step" data-step-target="4">۴) تدریس و حساب</button>
        </div>

        <form id="teacherWizardForm" method="post" action="<?= e($formAction) ?>" enctype="multipart/form-data" class="module-form teacher-form-grid" novalidate>
            <?= csrf_field() ?>

            <section class="wizard-panel is-active" data-step-panel="1">
                <div class="form-group">
                    <label>نام و تخلص استاد</label>
                    <input class="form-control" name="name" value="<?= e((string) $oldOr('name')) ?>" placeholder="مثال: مولوی عبدالرحیم عابدی" minlength="3" maxlength="255" required>
                    <small class="field-help">نام کامل استاد را وارد کنید (حداقل ۳ حرف).</small>
                </div>

                <div class="form-group">
                    <label>نام پدر</label>
                    <input class="form-control" name="father_name" value="<?= e((string) $oldOr('father_name')) ?>" placeholder="مثال: عبدالکریم" minlength="3" maxlength="255" required>
                    <small class="field-help">نام پدر استاد الزامی است.</small>
                </div>

                <div class="form-group">
                    <label>تاریخ تولد</label>
                    <input class="form-control" type="date" name="birth_date" value="<?= e((string) $oldOr('birth_date')) ?>" required>
                    <small class="field-help">فرمت تاریخ: YYYY-MM-DD</small>
                </div>

                <div class="form-group">
                    <label>جنسیت</label>
                    <select class="form-control" name="gender" required>
                        <option value="male" <?= (string) $oldOr('gender', 'male') === 'male' ? 'selected' : '' ?>>مرد</option>
                        <option value="female" <?= (string) $oldOr('gender') === 'female' ? 'selected' : '' ?>>زن</option>
                    </select>
                    <small class="field-help">یکی از گزینه‌ها را انتخاب کنید.</small>
                </div>

                <div class="form-group">
                    <label>سویه تحصیلی</label>
                    <select class="form-control" name="education_level" required>
                        <option value="p" <?= (string) $oldOr('education_level', 'p') === 'p' ? 'selected' : '' ?>>چهارده پاس</option>
                        <option value="b" <?= (string) $oldOr('education_level') === 'b' ? 'selected' : '' ?>>لیسانس</option>
                        <option value="m" <?= (string) $oldOr('education_level') === 'm' ? 'selected' : '' ?>>ماستر</option>
                        <option value="d" <?= (string) $oldOr('education_level') === 'd' ? 'selected' : '' ?>>دوکتور</option>
                    </select>
                    <small class="field-help">آخرین درجه تحصیلی استاد.</small>
                </div>

                <div class="form-group">
                    <label>نمبر تذکره</label>
                    <input class="form-control" name="id_number" value="<?= e((string) $oldOr('id_number')) ?>" placeholder="مثال: 12345-98765" pattern="[0-9A-Za-z\-\/\s]+" maxlength="100" required>
                    <small class="field-help">اعداد/حروف انگلیسی، خط تیره و اسلش مجاز است.</small>
                </div>
            </section>

            <section class="wizard-panel" data-step-panel="2">
                <div class="form-group full">
                    <label>ولایت/سکونت فعلی</label>
                    <input class="form-control" name="current_address" value="<?= e((string) $oldOr('current_address')) ?>" placeholder="مثال: کابل" minlength="2" required>
                    <small class="field-help">محل سکونت فعلی استاد.</small>
                </div>

                <div class="form-group full">
                    <label>ولایت/سکونت اصلی</label>
                    <input class="form-control" name="permanent_address" value="<?= e((string) $oldOr('permanent_address')) ?>" placeholder="مثال: بغلان" minlength="2" required>
                    <small class="field-help">محل سکونت اصلی استاد.</small>
                </div>

                <div class="form-group">
                    <label>قریه</label>
                    <input class="form-control" name="village" value="<?= e((string) $oldOr('village')) ?>" placeholder="مثال: نوآباد" maxlength="150" required>
                    <small class="field-help">نام قریه را وارد کنید.</small>
                </div>

                <div class="form-group">
                    <label>ولسوالی</label>
                    <input class="form-control" name="district" value="<?= e((string) $oldOr('district')) ?>" placeholder="مثال: خنجان" maxlength="150" required>
                    <small class="field-help">نام ولسوالی را وارد کنید.</small>
                </div>

                <div class="form-group">
                    <label>ناحیه</label>
                    <input class="form-control" name="area" value="<?= e((string) $oldOr('area')) ?>" placeholder="مثال: ناحیه ۴" maxlength="150" required>
                    <small class="field-help">ناحیه فعلی را وارد کنید.</small>
                </div>
            </section>

            <section class="wizard-panel" data-step-panel="3">
                <div class="form-group">
                    <label>عکس استاد</label>
                    <input type="file" class="form-control" name="image" accept=".jpg,.jpeg,.png,.webp,image/*">
                    <small class="field-help">فرمت مجاز: JPG/PNG/WEBP | حداکثر 2MB</small>
                    <?php if (!empty($teacher['image_path'])): ?><a href="<?= e(url($teacher['image_path'])) ?>" target="_blank">نمایش فایل فعلی</a><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>پلان درسی (PDF)</label>
                    <input type="file" class="form-control" name="plan_file" accept=".pdf,application/pdf">
                    <small class="field-help">فقط PDF | حداکثر 5MB</small>
                    <?php if (!empty($teacher['plan_file'])): ?><a href="<?= e(url($teacher['plan_file'])) ?>" target="_blank">نمایش فایل فعلی</a><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>سند تحصیلی</label>
                    <input type="file" class="form-control" name="education_document" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/*">
                    <small class="field-help">PDF/JPG/PNG | حداکثر 5MB</small>
                    <?php if (!empty($teacher['education_document'])): ?><a href="<?= e(url($teacher['education_document'])) ?>" target="_blank">نمایش فایل فعلی</a><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>سند تجربه کاری</label>
                    <input type="file" class="form-control" name="experience_document" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/*">
                    <small class="field-help">PDF/JPG/PNG | حداکثر 5MB</small>
                    <?php if (!empty($teacher['experience_document'])): ?><a href="<?= e(url($teacher['experience_document'])) ?>" target="_blank">نمایش فایل فعلی</a><?php endif; ?>
                </div>
            </section>

            <section class="wizard-panel" data-step-panel="4">
                <div class="form-group full picker-group">
                    <label>صنوف تدریس</label>
                    <div class="inline-checks">
                        <?php foreach ($classes as $item): ?>
                            <label>
                                <input type="checkbox" name="class_ids[]" data-class-level-id="<?= e((string) ($item['level_id'] ?? '')) ?>" value="<?= e((string) $item['id']) ?>" <?= in_array((int) $item['id'], $selectedClassIds, true) ? 'checked' : '' ?>>
                                <?= e($item['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <small class="field-help">حداقل یک صنف انتخاب کنید.</small>
                </div>

                <div class="form-group full picker-group">
                    <label>مضامین تدریس</label>
                    <div class="inline-checks">
                        <?php foreach ($subjects as $item): ?>
                            <label><input type="checkbox" name="subject_ids[]" value="<?= e((string) $item['id']) ?>" <?= in_array((int) $item['id'], $selectedSubjectIds, true) ? 'checked' : '' ?>> <?= e($item['name']) ?></label>
                        <?php endforeach; ?>
                    </div>
                    <small class="field-help">حداقل یک مضمون انتخاب کنید.</small>
                </div>

                <div class="form-group full picker-group">
                    <label>سطوح تدریس</label>
                    <div class="inline-checks">
                        <?php foreach ($levels as $item): ?>
                            <label>
                                <input type="checkbox" name="level_ids[]" data-level-code="<?= e((string) ($item['code'] ?? '')) ?>" value="<?= e((string) $item['id']) ?>" <?= in_array((int) $item['id'], $selectedLevelIds, true) ? 'checked' : '' ?>>
                                <?= e($item['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <small class="field-help">حداقل یک سطح انتخاب کنید.</small>
                </div>

                <div class="form-group full picker-group" id="teacher_semester_block">
                    <label>سمسترها</label>
                    <div class="inline-checks">
                        <?php foreach ($semesters as $item): ?>
                            <label><input type="checkbox" name="semester_ids[]" value="<?= e((string) $item['id']) ?>" <?= in_array((int) $item['id'], $selectedSemesterIds, true) ? 'checked' : '' ?>> <?= e((string) $item['number']) ?></label>
                        <?php endforeach; ?>
                    </div>
                    <small class="field-help">برای سطح عالی حداقل یک سمستر ضروری است.</small>
                </div>

                <div class="form-group full picker-group" id="teacher_period_block">
                    <label>دوره‌ها</label>
                    <div class="inline-checks">
                        <?php foreach ($periods as $item): ?>
                            <label><input type="checkbox" name="period_ids[]" value="<?= e((string) $item['id']) ?>" <?= in_array((int) $item['id'], $selectedPeriodIds, true) ? 'checked' : '' ?>> <?= e((string) $item['number']) ?></label>
                        <?php endforeach; ?>
                    </div>
                    <small class="field-help">برای ابتداییه/متوسطه حداقل یک دوره ضروری است.</small>
                </div>

                <div class="form-group">
                    <label>ایمیل حساب استاد</label>
                    <input class="form-control" type="email" name="account_email" id="teacher_account_email" value="<?= e($accountEmail) ?>" placeholder="مثال: teacher@example.com" required>
                    <small class="field-help">استاد با همین ایمیل وارد حساب خود می‌شود.</small>
                </div>

                <div class="form-group">
                    <label>رمز عبور حساب استاد</label>
                    <input class="form-control" type="password" name="account_password" id="teacher_account_password" placeholder="<?= $mustSetPassword ? 'حداقل ۸ کاراکتر (الزامی)' : 'اگر تغییر نمی‌دهید خالی بگذارید' ?>" minlength="8" <?= $mustSetPassword ? 'required' : '' ?>>
                    <small class="field-help"><?= $mustSetPassword ? 'برای ایجاد حساب استاد، رمز عبور الزامی است.' : 'در ویرایش، فقط در صورت نیاز رمز جدید وارد کنید.' ?></small>
                </div>

                <div class="form-group">
                    <label>تکرار رمز عبور</label>
                    <input class="form-control" type="password" name="account_password_confirmation" id="teacher_account_password_confirmation" placeholder="تکرار رمز عبور" minlength="8" <?= $mustSetPassword ? 'required' : '' ?>>
                    <small class="field-help">باید دقیقاً با رمز عبور یکسان باشد.</small>
                </div>
            </section>

            <div class="full wizard-actions">
                <button type="button" class="btn btn-default" id="teacherPrevStepBtn" disabled>مرحله قبلی</button>
                <button type="button" class="section-btn btn btn-default" id="teacherNextStepBtn">مرحله بعدی</button>
                <button class="section-btn btn btn-default" type="submit" id="teacherSubmitBtn" style="display:none;">ذخیره نهایی</button>
                <a class="btn btn-default" href="<?= e(url('/teachers')) ?>">انصراف</a>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('teacherWizardForm');
    if (!form) return;

    const steps = Array.from(document.querySelectorAll('.wizard-step'));
    const panels = Array.from(document.querySelectorAll('.wizard-panel'));
    const prevBtn = document.getElementById('teacherPrevStepBtn');
    const nextBtn = document.getElementById('teacherNextStepBtn');
    const submitBtn = document.getElementById('teacherSubmitBtn');
    const accountEmail = document.getElementById('teacher_account_email');
    const accountPassword = document.getElementById('teacher_account_password');
    const accountPasswordConfirm = document.getElementById('teacher_account_password_confirmation');
    const mustSetPassword = <?= $mustSetPassword ? 'true' : 'false' ?>;

    let currentStep = 0;

    function showStep(index) {
        currentStep = Math.max(0, Math.min(index, panels.length - 1));

        panels.forEach((panel, i) => panel.classList.toggle('is-active', i === currentStep));
        steps.forEach((step, i) => {
            step.classList.toggle('is-active', i === currentStep);
            step.classList.toggle('is-done', i < currentStep);
        });

        prevBtn.disabled = currentStep === 0;
        const isLast = currentStep === panels.length - 1;
        nextBtn.style.display = isLast ? 'none' : '';
        submitBtn.style.display = isLast ? '' : 'none';
    }

    function validateNative(panel) {
        const fields = Array.from(panel.querySelectorAll('input, select, textarea'));
        for (const field of fields) {
            if (field.type === 'hidden' || field.disabled || field.type === 'checkbox') continue;
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

    function selectedLevelCodes() {
        const selected = Array.from(form.querySelectorAll('input[name="level_ids[]"]:checked'));
        return selected.map(el => el.dataset.levelCode || '');
    }

    function selectedLevelIds() {
        const selected = Array.from(form.querySelectorAll('input[name="level_ids[]"]:checked'));
        return selected.map(el => String(el.value));
    }

    function customValidateTeaching() {
        if (checkedCount('class_ids[]') < 1) {
            alert('حداقل یک صنف تدریس انتخاب کنید.');
            return false;
        }
        if (checkedCount('subject_ids[]') < 1) {
            alert('حداقل یک مضمون انتخاب کنید.');
            return false;
        }
        if (checkedCount('level_ids[]') < 1) {
            alert('حداقل یک سطح تدریس انتخاب کنید.');
            return false;
        }

        const codes = selectedLevelCodes();
        const levelIds = selectedLevelIds();
        const hasAali = codes.includes('aali');
        const hasBase = codes.includes('moteseta') || codes.includes('ebtedai');

        if (hasAali && checkedCount('semester_ids[]') < 1) {
            alert('برای سطح عالی حداقل یک سمستر انتخاب کنید.');
            return false;
        }
        if (!hasAali && checkedCount('semester_ids[]') > 0) {
            alert('بدون سطح عالی، سمستر نباید انتخاب شود.');
            return false;
        }

        if (hasBase && checkedCount('period_ids[]') < 1) {
            alert('برای ابتداییه/متوسطه حداقل یک دوره انتخاب کنید.');
            return false;
        }
        if (!hasBase && checkedCount('period_ids[]') > 0) {
            alert('وقتی ابتداییه/متوسطه انتخاب نشده، دوره نباید انتخاب شود.');
            return false;
        }

        const classChecks = Array.from(form.querySelectorAll('input[name="class_ids[]"]:checked'));
        for (const item of classChecks) {
            const classLevelId = item.dataset.classLevelId || '';
            if (classLevelId && !levelIds.includes(classLevelId)) {
                alert('سطح صنف‌های انتخابی باید داخل سطوح تدریس باشد.');
                return false;
            }
        }

        if (!accountEmail || !accountEmail.value.trim() || !accountEmail.checkValidity()) {
            alert('ایمیل حساب استاد معتبر نیست.');
            if (accountEmail) accountEmail.focus();
            return false;
        }

        if (mustSetPassword && (!accountPassword || accountPassword.value.trim() === '')) {
            alert('برای ایجاد حساب استاد، رمز عبور الزامی است.');
            if (accountPassword) accountPassword.focus();
            return false;
        }

        if (accountPassword && accountPassword.value !== '') {
            if (accountPassword.value.length < 8) {
                alert('رمز عبور حساب استاد باید حداقل ۸ کاراکتر باشد.');
                accountPassword.focus();
                return false;
            }
            if (!accountPasswordConfirm || accountPassword.value !== accountPasswordConfirm.value) {
                alert('تکرار رمز عبور حساب استاد یکسان نیست.');
                if (accountPasswordConfirm) accountPasswordConfirm.focus();
                return false;
            }
        } else if (accountPasswordConfirm && accountPasswordConfirm.value !== '') {
            alert('برای تکرار رمز، ابتدا رمز عبور جدید استاد را وارد کنید.');
            if (accountPassword) accountPassword.focus();
            return false;
        }

        return true;
    }

    function validateCurrentStep() {
        const panel = panels[currentStep];
        if (!panel) return false;
        if (!validateNative(panel)) return false;

        if (currentStep === 3) {
            return customValidateTeaching();
        }
        return true;
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
                showStep(i);
                return;
            }
        }
    });

    showStep(0);
})();
</script>
