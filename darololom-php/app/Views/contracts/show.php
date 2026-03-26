<div class="section-title">
    <h2>قرارداد استاد: <?= e($teacher['name']) ?></h2>
</div>

<div class="news-thumb contract-shell">
    <div class="news-info">
        <div class="contract-meta">
            <div><strong>شماره قرارداد:</strong> <?= e($contract['contract_number'] ?? '—') ?></div>
            <div><strong>تاریخ ثبت استاد:</strong> <?= e((string) ($teacher['created_at'] ?? '—')) ?></div>
            <div><strong>نمبر تذکره:</strong> <?= e($teacher['id_number'] ?: '—') ?></div>
        </div>

        <form method="post" action="<?= e(url('/contracts/' . $teacher['id'] . '/save')) ?>" enctype="multipart/form-data" class="module-form">
            <?= csrf_field() ?>

            <div class="form-group"><label>تاریخ قرارداد</label><input class="form-control" type="date" name="contract_date" value="<?= e((string) ($contract['contract_date'] ?? '')) ?>"></div>
            <div class="form-group"><label>معاش ماهوار</label><input class="form-control" type="text" name="monthly_salary" value="<?= e((string) ($contract['monthly_salary'] ?? '')) ?>"></div>
            <div class="form-group"><label>وظیفه/سمت</label><input class="form-control" type="text" name="position" value="<?= e((string) ($contract['position'] ?? '')) ?>"></div>

            <div class="form-group full">
                <label>متن قرارداد</label>
                <textarea class="form-control" rows="10" name="notes"><?= e((string) ($contract['notes'] ?? '')) ?></textarea>
            </div>

            <div class="form-group">
                <label>فایل امضاشده</label>
                <input class="form-control" type="file" name="signed_file">
                <?php if (!empty($contract['signed_file'])): ?>
                    <a href="<?= e(url($contract['signed_file'])) ?>" target="_blank">مشاهده فایل فعلی</a>
                <?php endif; ?>
            </div>

            <div class="full form-actions">
                <button class="section-btn btn btn-default" type="submit">ذخیره قرارداد</button>
                <a class="btn btn-default" href="<?= e(url('/teachers')) ?>">بازگشت</a>
            </div>
        </form>
    </div>
</div>
