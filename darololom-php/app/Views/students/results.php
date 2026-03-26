<div class="section-title">
    <h2>نتایج امتحان: <?= e($student['name']) ?></h2>
</div>

<div class="news-thumb">
    <div class="news-info">
        <p><strong>سطح:</strong> <?= e($student['level_name'] ?? '—') ?></p>
        <table class="table table-striped table-bordered">
            <thead>
                <tr><th>مضمون</th><th>نمره</th></tr>
            </thead>
            <tbody>
            <?php foreach ($scores as $score): ?>
                <tr>
                    <td><?= e($score['subject_name']) ?></td>
                    <td><?= e((string) ($score['score'] ?? '—')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <a class="btn btn-default" href="<?= e(url('/students')) ?>">بازگشت</a>
    </div>
</div>
