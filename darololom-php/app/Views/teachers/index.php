<?php
$totalPages = max(1, (int) ceil($total / max(1, $pageSize)));
?>

<div class="section-title">
    <h2>لیست اساتید</h2>
</div>

<div class="toolbar-row">
    <form method="get" class="filter-form form-inline">
        <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="جستجو نام، پدر یا تذکره...">
        <select name="page_size" class="form-control">
            <?php foreach ($allowedSizes as $size): ?>
                <option value="<?= e((string) $size) ?>" <?= (int) $pageSize === (int) $size ? 'selected' : '' ?>><?= e((string) $size) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="section-btn btn btn-default" type="submit">فیلتر</button>
    </form>

    <?php if (can('register_teachers')): ?>
        <a class="section-btn btn btn-default" href="<?= e(url('/teachers/create')) ?>">+ ثبت استاد</a>
    <?php endif; ?>
</div>

<div class="news-thumb">
    <div class="news-info">
        <table class="table table-bordered table-hover teacher-table">
            <thead>
            <tr>
                <th>نام</th>
                <th>نام پدر</th>
                <th>تذکره</th>
                <th>سطوح/سمستر/دوره</th>
                <th>مضامین</th>
                <th>صنوف</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($teachers as $teacher): ?>
                <tr>
                    <td><?= e($teacher['name']) ?></td>
                    <td><?= e($teacher['father_name'] ?: '—') ?></td>
                    <td><?= e($teacher['id_number'] ?: '—') ?></td>
                    <td>
                        <div>سطح: <?= e($teacher['levels_display'] ?: '—') ?></div>
                        <div>سمستر: <?= e($teacher['semesters_display'] ?: '—') ?></div>
                        <div>دوره: <?= e($teacher['periods_display'] ?: '—') ?></div>
                    </td>
                    <td><?= e($teacher['subjects_display'] ?: '—') ?></td>
                    <td><?= e($teacher['classes_display'] ?: '—') ?></td>
                    <td class="actions-cell">
                        <?php if (can('manage_teachers')): ?>
                            <a class="btn btn-xs btn-info" href="<?= e(url('/teachers/' . $teacher['id'] . '/edit')) ?>">ویرایش</a>
                        <?php endif; ?>
                        <?php if (can('manage_contracts')): ?>
                            <a class="btn btn-xs btn-success" href="<?= e(url('/contracts/' . $teacher['id'])) ?>">قرارداد</a>
                        <?php endif; ?>
                        <a class="btn btn-xs btn-primary" href="<?= e(url('/teachers/' . $teacher['id'] . '/appreciation')) ?>" target="_blank">تقدیرنامه</a>
                        <?php if (can('manage_teachers')): ?>
                            <form method="post" action="<?= e(url('/teachers/' . $teacher['id'] . '/delete')) ?>" onsubmit="return confirm('آیا مطمئن هستید؟');">
                                <?= csrf_field() ?>
                                <button class="btn btn-xs btn-danger" type="submit">حذف</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr class="behavior-row">
                    <td colspan="7">
                        <div class="behavior-grid">
                            <?php if (can('manage_teachers')): ?>
                                <form class="behavior-form" method="post" action="<?= e(url('/teachers/' . $teacher['id'] . '/behavior')) ?>">
                                    <?= csrf_field() ?>
                                    <select name="entry_type" class="form-control" required>
                                        <option value="merit">امتیاز</option>
                                        <option value="violation">تخلف</option>
                                    </select>
                                    <input type="text" name="note" class="form-control" placeholder="یادداشت">
                                    <button class="btn btn-default btn-sm" type="submit">ثبت</button>
                                </form>
                            <?php endif; ?>

                            <div class="behavior-list">
                                <?php foreach (($behaviors[$teacher['id']] ?? []) as $entry): ?>
                                    <div class="behavior-item <?= e($entry['entry_type']) ?>">
                                        <span>
                                            <?= $entry['entry_type'] === 'merit' ? 'امتیاز' : 'تخلف' ?>:
                                            <?= e($entry['note'] ?: '—') ?>
                                        </span>
                                        <?php if (can('manage_teachers')): ?>
                                            <form method="post" action="<?= e(url('/teachers/behavior/' . $entry['id'] . '/delete')) ?>">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-xs btn-link" type="submit">حذف</button>
                                            </form>
                                        <?php endif; ?>
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
                    <a class="btn btn-default btn-sm" href="<?= e(url('/teachers?q=' . urlencode($q) . '&page_size=' . $pageSize . '&page=' . ($page - 1))) ?>">قبلی</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a class="btn btn-default btn-sm" href="<?= e(url('/teachers?q=' . urlencode($q) . '&page_size=' . $pageSize . '&page=' . ($page + 1))) ?>">بعدی</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
