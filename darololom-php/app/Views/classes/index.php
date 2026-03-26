<div class="section-title">
    <h2>لیست صنوف</h2>
</div>

<div class="toolbar-row">
    <form method="get" class="filter-form form-inline">
        <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="جستجو صنف...">
        <select name="level" class="form-control">
            <option value="aali" <?= $level === 'aali' ? 'selected' : '' ?>>عالی</option>
            <option value="moteseta" <?= $level === 'moteseta' ? 'selected' : '' ?>>متوسطه</option>
            <option value="ebtedai" <?= $level === 'ebtedai' ? 'selected' : '' ?>>ابتداییه</option>
        </select>
        <button class="section-btn btn btn-default" type="submit">جستجو</button>
    </form>

    <a class="section-btn btn btn-default" href="<?= e(url('/classes/create')) ?>">+ افزودن صنف</a>
</div>

<div class="news-thumb">
    <div class="news-info">
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>نام صنف</th>
                <th>سطح</th>
                <th>سمستر</th>
                <th>دوره</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($classes as $class): ?>
                <tr>
                    <td><?= e($class['name']) ?></td>
                    <td><?= e($class['level_name'] ?? '—') ?></td>
                    <td><?= e((string) ($class['semester_number'] ?? '—')) ?></td>
                    <td><?= e((string) ($class['period_number'] ?? '—')) ?></td>
                    <td class="actions-cell">
                        <a class="btn btn-xs btn-info" href="<?= e(url('/classes/' . $class['id'] . '/edit')) ?>">ویرایش</a>
                        <form method="post" action="<?= e(url('/classes/' . $class['id'] . '/delete')) ?>" onsubmit="return confirm('آیا مطمئن هستید؟');">
                            <?= csrf_field() ?>
                            <button class="btn btn-xs btn-danger" type="submit">حذف</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
