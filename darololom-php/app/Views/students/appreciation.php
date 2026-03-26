<div class="section-title">
    <h2>تقدیرنامه دانش‌آموز</h2>
</div>

<div class="news-thumb certificate-box">
    <div class="news-info">
        <h3>به پاس تلاش و شایستگی</h3>
        <p>این تقدیرنامه به <strong><?= e($student['name']) ?></strong> فرزند <strong><?= e($student['father_name'] ?: '—') ?></strong> اعطا می‌گردد.</p>
        <p>تعداد امتیازهای ثبت‌شده: <strong><?= e((string) $meritCount) ?></strong></p>
        <p>سطح آموزشی: <strong><?= e($student['level_name'] ?: '—') ?></strong></p>
        <p>تاریخ صدور: <strong><?= e(date('Y-m-d')) ?></strong></p>

        <div class="signature-row">
            <div>امضای مدیریت</div>
            <div>مهر دارالعلوم</div>
        </div>

        <button class="btn btn-default" onclick="window.print()">چاپ</button>
    </div>
</div>
