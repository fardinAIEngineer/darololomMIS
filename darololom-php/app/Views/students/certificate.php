<div class="section-title">
    <h2>سرتفیکت دانش‌آموز</h2>
</div>

<div class="news-thumb certificate-box">
    <div class="news-info">
        <h3>دارالعلوم عالی الحاج سید منصور نادری</h3>
        <p>این سند به نام <strong><?= e($student['name']) ?></strong> فرزند <strong><?= e($student['father_name'] ?: '—') ?></strong> صادر می‌گردد.</p>
        <p>سطح آموزشی: <strong><?= e($student['level_name'] ?? '—') ?></strong></p>
        <p>صنف: <strong><?= e($student['class_name'] ?? '—') ?></strong></p>
        <p>تاریخ: <strong><?= e(date('Y-m-d')) ?></strong></p>

        <div class="signature-row">
            <div>امضای آمر</div>
            <div>امضای مدیریت</div>
        </div>

        <a class="btn btn-default" href="<?= e(url('/students')) ?>">بازگشت</a>
    </div>
</div>
