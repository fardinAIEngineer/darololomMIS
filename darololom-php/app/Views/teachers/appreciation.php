<div class="section-title">
    <h2>تقدیرنامه استاد</h2>
</div>

<div class="news-thumb certificate-box">
    <div class="news-info">
        <h3>به پاس همکاری حرفه‌ای و آموزشی</h3>
        <p>این تقدیرنامه به <strong><?= e($teacher['name']) ?></strong> اعطا می‌گردد.</p>
        <p>تعداد امتیازهای ثبت‌شده: <strong><?= e((string) $meritCount) ?></strong></p>
        <p>تاریخ صدور: <strong><?= e(date('Y-m-d')) ?></strong></p>

        <div class="signature-row">
            <div>امضای آمر</div>
            <div>مهر دارالعلوم</div>
        </div>

        <button class="btn btn-default" onclick="window.print()">چاپ</button>
    </div>
</div>
