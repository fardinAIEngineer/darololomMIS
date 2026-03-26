<div class="section-title">
    <h2>مدیریت کاربران سیستم</h2>
</div>

<div class="toolbar-row">
    <div></div>
    <a class="section-btn btn btn-default" href="<?= e(url('/users/create')) ?>">+ ثبت کاربر جدید</a>
</div>

<div class="news-thumb">
    <div class="news-info">
        <table class="table table-bordered table-hover users-table">
            <thead>
            <tr>
                <th>نام کامل</th>
                <th>نام کاربری</th>
                <th>نقش</th>
                <th>صلاحیت ثبت شاگرد</th>
                <th>صلاحیت ثبت استاد</th>
                <th>ایجادکننده</th>
                <th>وضعیت</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= e((string) ($user['full_name'] ?? '')) ?></td>
                    <td><?= e((string) ($user['username'] ?? '')) ?></td>
                    <td><?= (string) ($user['role'] ?? '') === 'super_admin' ? 'سوپرادمین' : 'ادمین' ?></td>
                    <td><?= (int) ($user['can_register_students'] ?? 0) === 1 ? 'دارد' : 'ندارد' ?></td>
                    <td><?= (int) ($user['can_register_teachers'] ?? 0) === 1 ? 'دارد' : 'ندارد' ?></td>
                    <td><?= e((string) ($user['creator_name'] ?? 'سیستم')) ?></td>
                    <td><?= (int) ($user['is_active'] ?? 0) === 1 ? 'فعال' : 'غیرفعال' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
