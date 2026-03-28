<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سرتفیکت فراغت ابتداییه</title>
    <link rel="stylesheet" href="<?= e(url('/assets/css/modules/student_certificate_print.css')) ?>">
</head>
<body>
<div class="certificate-topbar no-print">
    <div class="certificate-topbar-title">
        <h1>سرتفیکت فراغت ابتداییه</h1>
        <p>دانش‌آموز: <?= e((string) ($student['name'] ?? '-')) ?></p>
    </div>
    <div class="certificate-topbar-actions">
        <button type="button" class="print-btn" onclick="window.print()">چاپ سرتفیکت</button>
        <a class="back-btn" href="<?= e(url('/students/' . ((int) ($student['id'] ?? 0)) . '/results')) ?>">بازگشت</a>
    </div>
</div>

<div id="certificateContent" class="certificate-shell">
    <div class="certificate-frame">
        <div class="certificate-pattern"></div>
        <div class="certificate-inner">
            <div class="certificate-head">
                <div class="certificate-head-col">
                    <img src="<?= e(url('/assets/images/emirate.png')) ?>" alt="لوگوی امارت" class="certificate-logo" onerror="this.style.display='none';">
                    <span class="certificate-head-note">تاریخ: <?= e((string) ($currentDate ?? date('Y-m-d'))) ?></span>
                </div>

                <div class="certificate-head-center">
                    <h2>دارالعلوم عالی الحاج سید منصور نادری</h2>
                    <h3>مدیریت تدریسی</h3>
                    <h4>تصدیق نامه فراغت سطح ابتداییه</h4>
                </div>

                <div class="certificate-head-col">
                    <img src="<?= e(url('/assets/images/logo.jpg')) ?>" alt="لوگو" class="certificate-logo" onerror="this.style.display='none';">
                    <span class="certificate-head-note">شماره سرتفیکت: <?= e((string) (($student['certificate_number'] ?? '') !== '' ? $student['certificate_number'] : '-')) ?></span>
                </div>
            </div>

            <div class="certificate-body">
                <p>
                    این تصدیق نامه به نام <strong><?= e((string) ($student['name'] ?? '-')) ?></strong>
                    فرزند <strong><?= e((string) (($student['father_name'] ?? '') !== '' ? $student['father_name'] : '-')) ?></strong>
                    با نمبر تذکره <strong><?= e((string) (($student['id_number'] ?? '') !== '' ? $student['id_number'] : '-')) ?></strong>
                    صادر می‌گردد.
                    <br>
                    موصوف/موصوفه با زحمت پیگیر، نظم تعلیمی و اخلاق نیکو، سطح ابتداییه را به موفقیت به پایان رسانیده است.
                    <br>
                    اداره دارالعلوم مراتب تشویق و تمجید خویش را تقدیم نموده و برای وی در مراحل بعدی توفیقات بیشتر آرزو می‌نماید.
                    <br>
                    این تصدیق نامه به منظور ثبت و تأیید فراغت و شایستگی ارتقا به سطح بالاتر صادر گردیده است.
                </p>
            </div>

            <div class="certificate-signatures">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <p>استاد مربوطه</p>
                </div>
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <p>مدیر تدریسی</p>
                </div>
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <p>آمر دارالعلوم</p>
                </div>
            </div>

            <div class="certificate-footer">
                <p>چهارراهی پروژه تایمنی، جوار مسجد جامع الحاج سید منصور نادری</p>
                <?php if (!empty($qrCodeDataUri)): ?>
                    <img src="<?= e((string) $qrCodeDataUri) ?>" alt="QR" class="certificate-qr">
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
