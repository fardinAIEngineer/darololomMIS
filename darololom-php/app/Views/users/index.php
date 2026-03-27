<?php $permissionMap = permission_definitions(); ?>

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
                <th>صلاحیت‌ها</th>
                <th>ایجادکننده</th>
                <th>وضعیت</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <?php
                $keys = user_permission_keys($user);
                $labels = [];
                foreach ($keys as $key) {
                    $labels[] = $permissionMap[$key] ?? $key;
                }
                $permissionsText = $labels !== [] ? implode('، ', $labels) : '—';
                ?>
                <tr>
                    <td><?= e((string) ($user['full_name'] ?? '')) ?></td>
                    <td><?= e((string) ($user['username'] ?? '')) ?></td>
                    <td><?= (string) ($user['role'] ?? '') === 'super_admin' ? 'سوپرادمین' : 'ادمین' ?></td>
                    <td><?= e($permissionsText) ?></td>
                    <td><?= e((string) ($user['creator_name'] ?? 'سیستم')) ?></td>
                    <td><?= (int) ($user['is_active'] ?? 0) === 1 ? 'فعال' : 'غیرفعال' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
