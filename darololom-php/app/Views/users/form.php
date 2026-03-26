<?php
$oldFullName = (string) old('full_name', '');
$oldUsername = (string) old('username', '');
?>

<div class="section-title">
    <h2>ثبت کاربر جدید</h2>
</div>

<div class="news-thumb">
    <div class="news-info">
        <form method="post" action="<?= e($formAction) ?>" class="module-form users-form">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>نام کامل</label>
                <input type="text" name="full_name" class="form-control" value="<?= e($oldFullName) ?>" placeholder="مثال: عبدالرحمن نادری" required>
            </div>

            <div class="form-group">
                <label>نام کاربری</label>
                <input type="text" name="username" class="form-control" value="<?= e($oldUsername) ?>" placeholder="مثال: admin.students" required>
                <small class="field-help">فقط حروف انگلیسی، عدد، نقطه، خط تیره و زیرخط مجاز است.</small>
            </div>

            <div class="form-group">
                <label>رمز عبور</label>
                <input type="password" name="password" class="form-control" placeholder="حداقل ۸ کاراکتر" required>
            </div>

            <div class="form-group full">
                <label>صلاحیت‌ها</label>
                <div class="inline-checks">
                    <label>
                        <input type="checkbox" name="can_register_students" value="1" <?= old('can_register_students') ? 'checked' : '' ?>>
                        ثبت شاگردان
                    </label>
                    <label>
                        <input type="checkbox" name="can_register_teachers" value="1" <?= old('can_register_teachers') ? 'checked' : '' ?>>
                        ثبت اساتید
                    </label>
                </div>
                <small class="field-help">کاربر با نقش ادمین ساخته می‌شود و فقط همین صلاحیت‌ها را می‌گیرد.</small>
            </div>

            <div class="full form-actions users-actions">
                <button class="section-btn btn btn-default" type="submit">ذخیره کاربر</button>
                <a class="btn btn-default" href="<?= e(url('/users')) ?>">انصراف</a>
            </div>
        </form>
    </div>
</div>
