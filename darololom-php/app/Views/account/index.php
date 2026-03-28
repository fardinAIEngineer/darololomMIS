<?php
$current = auth_user();
$emailValue = (string) old('email', (string) ($current['email'] ?? ''));
$educationMap = ['p' => 'چهارده پاس', 'b' => 'لیسانس', 'm' => 'ماستر', 'd' => 'دوکتور'];
?>

<div class="section-title">
    <h2><?= $role === 'teacher' ? 'حساب کاربری استاد' : 'حساب کاربری شاگرد' ?></h2>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="news-thumb">
            <div class="news-info">
                <?php if ($role === 'student' && $student): ?>
                    <h3><?= e((string) $student['name']) ?></h3>
                    <p><strong>نام پدر:</strong> <?= e((string) ($student['father_name'] ?? '—')) ?></p>
                    <p><strong>سطح آموزشی:</strong> <?= e((string) ($student['level_name'] ?? '—')) ?></p>
                    <p><strong>صنف:</strong> <?= e((string) ($student['class_name'] ?? '—')) ?></p>
                    <p><strong>شماره تماس:</strong> <?= e((string) ($student['mobile_number'] ?? '—')) ?></p>
                    <p><strong>سکونت فعلی:</strong> <?= e('ولایت: ' . (string) ($student['current_address'] ?? '—') . ' | ناحیه: ' . (string) ($student['area'] ?? '—') . ' | کوچه: ' . (string) ($student['current_street'] ?? '—')) ?></p>
                    <p><strong>سکونت اصلی:</strong> <?= e('ولایت: ' . (string) ($student['permanent_address'] ?? '—') . ' | ولسوالی: ' . (string) ($student['district'] ?? '—') . ' | قریه: ' . (string) ($student['village'] ?? '—')) ?></p>

                    <hr>
                    <h4>نتایج نمرات</h4>
                    <?php if ($studentScores === []): ?>
                        <p class="field-help">هنوز نمره‌ای برای شما ثبت نشده است.</p>
                    <?php else: ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>مضمون</th>
                                    <th>نمره</th>
                                    <th>آخرین بروزرسانی</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($studentScores as $row): ?>
                                    <tr>
                                        <td><?= e((string) $row['subject_name']) ?></td>
                                        <td><?= e((string) $row['score']) ?></td>
                                        <td><?= e((string) $row['updated_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($role === 'teacher' && $teacher): ?>
                    <h3><?= e((string) $teacher['name']) ?></h3>
                    <p><strong>نام پدر:</strong> <?= e((string) ($teacher['father_name'] ?? '—')) ?></p>
                    <p><strong>سویه تحصیلی:</strong> <?= e($educationMap[(string) ($teacher['education_level'] ?? '')] ?? '—') ?></p>
                    <p><strong>آدرس فعلی:</strong> <?= e((string) ($teacher['current_address'] ?? '—')) ?></p>
                    <p><strong>آدرس اصلی:</strong> <?= e((string) ($teacher['permanent_address'] ?? '—')) ?></p>

                    <hr>
                    <h4>صنوف اختصاص‌داده‌شده</h4>
                    <?php if (($teacherAssignments['classes'] ?? []) === []): ?>
                        <p class="field-help">صنفی برای شما تخصیص نشده است.</p>
                    <?php else: ?>
                        <div class="inline-checks">
                            <?php foreach ($teacherAssignments['classes'] as $class): ?>
                                <label><?= e((string) $class['name']) ?></label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <h4 style="margin-top:16px;">مضامین اختصاص‌داده‌شده</h4>
                    <?php if (($teacherAssignments['subjects'] ?? []) === []): ?>
                        <p class="field-help">مضمونی برای شما تخصیص نشده است.</p>
                    <?php else: ?>
                        <div class="inline-checks">
                            <?php foreach ($teacherAssignments['subjects'] as $subject): ?>
                                <label><?= e((string) $subject['name']) ?></label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top: 18px;">
                        <a class="section-btn btn btn-default" href="<?= e(url('/grades')) ?>">ثبت/ویرایش نمرات صنوف من</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="news-thumb auth-card">
            <div class="news-info">
                <h4>تغییر ایمیل و رمز عبور</h4>
                <p class="auth-note">تنها همین بخش قابل ویرایش است.</p>

                <form method="post" action="<?= e(url('/account/security')) ?>" class="auth-form">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label>ایمیل</label>
                        <input type="email" name="email" class="form-control" required value="<?= e($emailValue) ?>" placeholder="example@domain.com">
                    </div>

                    <div class="form-group">
                        <label>رمز عبور جدید</label>
                        <input type="password" name="password" class="form-control" placeholder="اگر تغییر نمی‌دهید خالی بگذارید" minlength="8">
                    </div>

                    <div class="form-group">
                        <label>تکرار رمز عبور جدید</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="تکرار رمز عبور">
                    </div>

                    <div class="auth-actions">
                        <button type="submit" class="section-btn btn btn-default">ذخیره تغییرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
