<?php
$totalPages = max(1, (int) ceil($total / max(1, $pageSize)));
?>

<div class="section-title">
    <h2>لیست دانش‌آموزان</h2>
</div>

<div class="toolbar-row">
    <form method="get" class="filter-form form-inline">
        <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="جستجو نام، پدر، موبایل یا تذکره...">
        <select name="level" class="form-control">
            <option value="aali" <?= $level === 'aali' ? 'selected' : '' ?>>عالی</option>
            <option value="moteseta" <?= $level === 'moteseta' ? 'selected' : '' ?>>متوسطه</option>
            <option value="ebtedai" <?= $level === 'ebtedai' ? 'selected' : '' ?>>ابتداییه</option>
        </select>
        <select name="page_size" class="form-control">
            <?php foreach ($allowedSizes as $size): ?>
                <option value="<?= e((string) $size) ?>" <?= (int) $pageSize === (int) $size ? 'selected' : '' ?>><?= e((string) $size) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="section-btn btn btn-default" type="submit">فیلتر</button>
    </form>

    <?php if (can('register_students')): ?>
        <a class="section-btn btn btn-default" href="<?= e(url('/students/create')) ?>">+ ثبت دانش‌آموز</a>
    <?php endif; ?>
</div>

<div class="news-thumb">
    <div class="news-info">
        <table class="table table-bordered table-hover student-table">
            <thead>
            <tr>
                <th>نام</th>
                <th>نام پدر</th>
                <th>سطح</th>
                <th>صنف</th>
                <th>سمستر/دوره</th>
                <th>امتیاز</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= e($student['name']) ?></td>
                    <td><?= e($student['father_name'] ?: '—') ?></td>
                    <td><?= e($student['level_name'] ?: '—') ?></td>
                    <td><?= e($student['class_name'] ?: '—') ?></td>
                    <td>
                        <?php if (!empty($student['semesters_display'])): ?>
                            سمستر: <?= e($student['semesters_display']) ?>
                        <?php elseif (!empty($student['periods_display'])): ?>
                            دوره: <?= e($student['periods_display']) ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td><?= e((string) ($student['merit_count'] ?? 0)) ?></td>
                    <td class="actions-cell">
                        <a class="btn btn-xs btn-info" href="<?= e(url('/students/' . $student['id'] . '/edit')) ?>">ویرایش</a>
                        <a class="btn btn-xs btn-success" href="<?= e(url('/students/' . $student['id'] . '/results')) ?>">نتایج</a>
                        <a class="btn btn-xs btn-primary" href="<?= e(url('/students/' . $student['id'] . '/appreciation')) ?>" target="_blank">تقدیرنامه</a>
                        <a class="btn btn-xs btn-warning" href="<?= e(url('/students/' . $student['id'] . '/certificate')) ?>">سرتفیکت</a>
                        <form method="post" action="<?= e(url('/students/' . $student['id'] . '/promote/moteseta')) ?>" onsubmit="return confirm('دانش‌آموز به متوسطه ارتقا یابد؟');">
                            <?= csrf_field() ?>
                            <button class="btn btn-xs btn-default" type="submit">ارتقا به متوسطه</button>
                        </form>
                        <form method="post" action="<?= e(url('/students/' . $student['id'] . '/delete')) ?>" onsubmit="return confirm('آیا مطمئن هستید؟');">
                            <?= csrf_field() ?>
                            <button class="btn btn-xs btn-danger" type="submit">حذف</button>
                        </form>
                    </td>
                </tr>
                <tr class="behavior-row">
                    <td colspan="7">
                        <div class="behavior-grid">
                            <form class="behavior-form" method="post" action="<?= e(url('/students/' . $student['id'] . '/behavior')) ?>">
                                <?= csrf_field() ?>
                                <select name="entry_type" class="form-control" required>
                                    <option value="merit">امتیاز</option>
                                    <option value="violation">تخلف</option>
                                </select>
                                <input type="text" name="note" class="form-control" placeholder="یادداشت">
                                <button class="btn btn-default btn-sm" type="submit">ثبت</button>
                            </form>

                            <div class="behavior-list">
                                <?php foreach (($behaviors[$student['id']] ?? []) as $entry): ?>
                                    <div class="behavior-item <?= e($entry['entry_type']) ?>">
                                        <span>
                                            <?= $entry['entry_type'] === 'merit' ? 'امتیاز' : 'تخلف' ?>:
                                            <?= e($entry['note'] ?: '—') ?>
                                        </span>
                                        <form method="post" action="<?= e(url('/students/behavior/' . $entry['id'] . '/delete')) ?>">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-xs btn-link" type="submit">حذف</button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination-wrap">
            <span>صفحه <?= e((string) $page) ?> از <?= e((string) $totalPages) ?></span>
            <div>
                <?php if ($page > 1): ?>
                    <a class="btn btn-default btn-sm" href="<?= e(url('/students?level=' . urlencode($level) . '&q=' . urlencode($q) . '&page_size=' . $pageSize . '&page=' . ($page - 1))) ?>">قبلی</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a class="btn btn-default btn-sm" href="<?= e(url('/students?level=' . urlencode($level) . '&q=' . urlencode($q) . '&page_size=' . $pageSize . '&page=' . ($page + 1))) ?>">بعدی</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
