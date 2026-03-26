<div class="section-title">
    <h2>ثبت نمرات دانش‌آموز</h2>
</div>

<div class="news-thumb">
    <div class="news-info">
        <form method="get" class="form-inline">
            <label>انتخاب دانش‌آموز:</label>
            <select class="form-control" name="student_id" onchange="this.form.submit()">
                <?php foreach ($students as $item): ?>
                    <option value="<?= e((string) $item['id']) ?>" <?= (int) ($selectedStudent['id'] ?? 0) === (int) $item['id'] ? 'selected' : '' ?>>
                        <?= e($item['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($selectedStudent): ?>
            <hr>
            <h3><?= e($selectedStudent['name']) ?></h3>
            <form method="post" action="<?= e(url('/grades/store')) ?>" class="module-form">
                <?= csrf_field() ?>
                <input type="hidden" name="student_id" value="<?= e((string) $selectedStudent['id']) ?>">

                <table class="table table-striped table-bordered">
                    <thead>
                        <tr><th>مضمون</th><th>نمره (0-100)</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td><?= e($subject['name']) ?></td>
                            <td>
                                <input type="number" min="0" max="100" class="form-control" name="scores[<?= e((string) $subject['id']) ?>]" value="<?= e((string) ($scoreMap[$subject['id']] ?? '')) ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <button class="section-btn btn btn-default" type="submit">ذخیره نمرات</button>
            </form>
        <?php endif; ?>
    </div>
</div>
