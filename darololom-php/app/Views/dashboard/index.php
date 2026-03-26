<div class="section-title wow fadeInUp" data-wow-delay="0.1s">
    <h2>داشبورد مدیریتی</h2>
</div>

<div class="row card-grid">
    <div class="col-md-3 col-sm-6">
        <div class="news-thumb stat-card">
            <div class="news-info">
                <span>تعداد دانش‌آموزان</span>
                <h3><?= e((string) $cards['students']) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="news-thumb stat-card">
            <div class="news-info">
                <span>تعداد اساتید</span>
                <h3><?= e((string) $cards['teachers']) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="news-thumb stat-card">
            <div class="news-info">
                <span>تعداد صنوف</span>
                <h3><?= e((string) $cards['classes']) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="news-thumb stat-card">
            <div class="news-info">
                <span>تعداد مضامین</span>
                <h3><?= e((string) $cards['subjects']) ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row dashboard-lists">
    <div class="col-md-6">
        <div class="news-thumb chart-card">
            <div class="news-info">
                <h3>تعداد شاگردان به اساس جنسیت</h3>

                <?php
                $genderTotal = (int) ($genderStats['total'] ?? 0);
                $maleCount = (int) ($genderStats['male_count'] ?? 0);
                $femaleCount = (int) ($genderStats['female_count'] ?? 0);
                $malePercent = (float) ($genderStats['male_percent'] ?? 0.0);
                $femalePercent = (float) ($genderStats['female_percent'] ?? 0.0);
                ?>

                <?php if ($genderTotal === 0): ?>
                    <p class="chart-empty">تا هنوز معلوماتی برای نمایش موجود نیست.</p>
                <?php else: ?>
                    <div class="pie-chart-block">
                        <div class="gender-pie" style="background: conic-gradient(#2f7de1 0% <?= e((string) $malePercent) ?>%, #ef5f8f <?= e((string) $malePercent) ?>% 100%);">
                            <div class="gender-pie-center">
                                <strong><?= e((string) $genderTotal) ?></strong>
                                <span>شاگرد</span>
                            </div>
                        </div>

                        <div class="pie-legend">
                            <div class="pie-legend-item">
                                <span class="legend-dot chart-fill-male"></span>
                                <span class="legend-label">مذکر</span>
                                <span class="legend-value"><?= e((string) $maleCount) ?> نفر (<?= e((string) $malePercent) ?>٪)</span>
                            </div>
                            <div class="pie-legend-item">
                                <span class="legend-dot chart-fill-female"></span>
                                <span class="legend-label">مونث</span>
                                <span class="legend-value"><?= e((string) $femaleCount) ?> نفر (<?= e((string) $femalePercent) ?>٪)</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="news-thumb chart-card">
            <div class="news-info">
                <h3>تعداد شاگردان به اساس سطح تحصیلی</h3>

                <?php if ($levelStats === []): ?>
                    <p class="chart-empty">تا هنوز معلوماتی برای نمایش موجود نیست.</p>
                <?php else: ?>
                    <div class="chart-list">
                        <?php foreach ($levelStats as $item): ?>
                            <?php
                            $percent = (float) $item['percent'];
                            $barWidth = $item['count'] > 0 ? max($percent, 5) : 0;
                            ?>
                            <div class="chart-row">
                                <div class="chart-meta">
                                    <span class="chart-label"><?= e((string) $item['label']) ?></span>
                                    <span class="chart-value"><?= e((string) $item['count']) ?> نفر (<?= e((string) $item['percent']) ?>٪)</span>
                                </div>
                                <div class="chart-track">
                                    <span class="chart-fill <?= e((string) $item['color_class']) ?>" style="width: <?= e((string) $barWidth) ?>%;"></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
