<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>کارت شناسایی دانش‌آموز</title>
    <link rel="stylesheet" href="<?= e(url('/assets/css/modules/student_card_print.css')) ?>">
    <style>
        .id-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .id-actions .btn {
            border: 0;
            border-radius: 10px;
            padding: 10px 18px;
            color: #fff;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
        }
        .id-actions .btn-print {
            background: #059669;
        }
        .id-actions .btn-back {
            background: #334155;
        }

        .id-card-shell {
            width: 440px;
            height: 620px;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 10px 35px rgba(15, 23, 42, 0.18);
            overflow: hidden;
            direction: rtl;
        }
        .id-card-header {
            background-color: #E8572A;
            padding: 24px;
            padding-bottom: 100px;
            position: relative;
        }
        .id-card-logo-circle {
            position: absolute;
            top: 32px;
            right: 40px;
            width: 56px;
            height: 56px;
            border-radius: 999px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18);
        }
        .id-card-logo-circle img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            border-radius: 999px;
        }
        .id-card-title {
            margin-top: 8px;
            text-align: center;
            color: #fff;
        }
        .id-card-title h3 {
            margin: 0;
            font-size: 36px;
            font-weight: 800;
            line-height: 1.4;
        }
        .id-card-curve {
            margin-top: -40px;
        }
        .id-card-photo-wrap {
            display: flex;
            justify-content: center;
            margin-top: -100px;
            margin-bottom: 20px;
            position: relative;
            z-index: 4;
        }
        .id-card-photo {
            width: 145px;
            height: 145px;
            border-radius: 999px;
            border: 5px solid #fff;
            background: #f3f4f6;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0,0,0,.2);
        }
        .id-card-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .id-card-photo svg {
            width: 80px;
            height: 80px;
            color: #9ca3af;
        }
        .id-card-body {
            padding: 0 32px 24px;
            background: #fff;
            height: calc(100% - 229px);
            display: flex;
            flex-direction: column;
        }
        .id-card-name {
            text-align: center;
            margin-bottom: 24px;
        }
        .id-card-name h2 {
            margin: 0;
            font-size: 44px;
            color: #E8572A;
            font-weight: 900;
            line-height: 1.2;
        }
        .id-card-info {
            font-size: 27px;
        }
        .id-card-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 8px;
        }
        .id-card-row .label {
            min-width: 140px;
            color: #1f2937;
            font-weight: 600;
        }
        .id-card-row .sep {
            color: #111827;
            font-weight: 700;
        }
        .id-card-row .value {
            color: #111827;
            font-weight: 700;
        }
        .id-card-footer {
            margin-top: auto;
            background-color: #E8572A;
            color: #fff;
            text-align: center;
            padding: 12px;
            font-size: 18px;
            font-weight: 500;
        }

        @media print {
            .id-actions {
                display: none !important;
            }
        }
    </style>
</head>
<body>
<?php
$imagePath = trim((string) ($student['image_path'] ?? ''));
$issueDateText = trim((string) ($issueDate ?? date('Y-m-d')));
$expiryDateText = trim((string) ($expiryDate ?? date('Y-m-d', strtotime('+1 year'))));
?>
<div class="id-actions">
    <button type="button" class="btn btn-print" onclick="window.print()">چاپ مستقیم</button>
    <a href="<?= e(url('/students')) ?>" class="btn btn-back">بازگشت</a>
</div>

<div id="printCard" class="id-card-shell">
    <div class="id-card-header">
        <div class="id-card-logo-circle">
            <img src="<?= e(url('/assets/images/logo.jpg')) ?>" alt="لوگو" onerror="this.style.display='none';">
        </div>
        <div class="id-card-title">
            <h3>دارالعلوم عالی</h3>
            <h3>الحاج سید منصور نادری</h3>
        </div>
    </div>

    <div class="id-card-curve">
        <svg viewBox="0 0 440 60" width="100%" height="60" preserveAspectRatio="none">
            <path d="M 0,60 Q 220,0 440,60 L 440,60 L 0,60 Z" fill="white"/>
        </svg>
    </div>

    <div class="id-card-photo-wrap">
        <div class="id-card-photo">
            <?php if ($imagePath !== ''): ?>
                <img src="<?= e(url($imagePath)) ?>" alt="<?= e((string) ($student['name'] ?? 'دانش‌آموز')) ?>">
            <?php else: ?>
                <svg fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
            <?php endif; ?>
        </div>
    </div>

    <div class="id-card-body">
        <div class="id-card-name">
            <h2><?= e((string) ($student['name'] ?? 'نام دانش‌آموز')) ?></h2>
        </div>

        <div class="id-card-info">
            <div class="id-card-row">
                <span class="label">صنف</span>
                <span class="sep">:</span>
                <span class="value"><?= e((string) ($student['class_name'] ?? '-')) ?></span>
            </div>
            <div class="id-card-row">
                <span class="label">نام پدر</span>
                <span class="sep">:</span>
                <span class="value"><?= e((string) ($student['father_name'] ?? '-')) ?></span>
            </div>
            <div class="id-card-row">
                <span class="label">شماره تماس</span>
                <span class="sep">:</span>
                <span class="value"><?= e((string) (($student['mobile_number'] ?? '') !== '' ? $student['mobile_number'] : '-')) ?></span>
            </div>
            <div class="id-card-row">
                <span class="label">تاریخ صدور</span>
                <span class="sep">:</span>
                <span class="value"><?= e($issueDateText) ?></span>
            </div>
            <div class="id-card-row">
                <span class="label">تاریخ انقضا</span>
                <span class="sep">:</span>
                <span class="value"><?= e($expiryDateText) ?></span>
            </div>
        </div>

        <div class="id-card-footer">
            چهارراهی پروژه تایمنی، جوار مسجد جامع الحاج سید منصور نادری
        </div>
    </div>
</div>
</body>
</html>
