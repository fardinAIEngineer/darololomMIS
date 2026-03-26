<div class="section-title">
    <h2>لیست مضامین</h2>
</div>

<div class="toolbar-row">
    <form method="get" class="filter-form form-inline">
        <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="جستجو مضمون...">
        <select name="level" class="form-control">
            <option value="aali" <?= $level === 'aali' ? 'selected' : '' ?>>عالی</option>
            <option value="moteseta" <?= $level === 'moteseta' ? 'selected' : '' ?>>متوسطه</option>
            <option value="ebtedai" <?= $level === 'ebtedai' ? 'selected' : '' ?>>ابتداییه</option>
        </select>
        <button class="section-btn btn btn-default" type="submit">جستجو</button>
    </form>

    <a class="section-btn btn btn-default" href="<?= e(url('/subjects/create')) ?>">+ افزودن مضمون</a>
</div>

<div class="news-thumb">
    <div class="news-info">
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th>نام مضمون</th>
                <th>سطح</th>
                <th>سمستر</th>
                <th>دوره</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($subjects as $item): ?>
                <tr>
                    <td><?= e($item['name']) ?></td>
                    <td><?= e($item['level_name'] ?? '—') ?></td>
                    <td><?= e((string) $item['semester']) ?></td>
                    <td><?= e((string) ($item['period_number'] ?? '—')) ?></td>
                    <td class="actions-cell">
                        <a class="btn btn-xs btn-info" href="<?= e(url('/subjects/' . $item['id'] . '/edit')) ?>">ویرایش</a>
                        <form method="post" action="<?= e(url('/subjects/' . $item['id'] . '/delete')) ?>" onsubmit="return confirm('آیا مطمئن هستید؟');">
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
