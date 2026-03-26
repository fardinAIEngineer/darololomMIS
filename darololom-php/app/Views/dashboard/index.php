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
        <div class="news-thumb">
            <div class="news-info">
                <h3>آخرین دانش‌آموزان</h3>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>نام</th>
                        <th>سطح</th>
                        <th>تاریخ</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentStudents as $item): ?>
                        <tr>
                            <td><?= e($item['name']) ?></td>
                            <td><?= e($item['level_name'] ?? '—') ?></td>
                            <td><?= e((string) $item['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="news-thumb">
            <div class="news-info">
                <h3>آخرین اساتید</h3>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>نام</th>
                        <th>سویه</th>
                        <th>تاریخ</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentTeachers as $item): ?>
                        <tr>
                            <td><?= e($item['name']) ?></td>
                            <td><?= e($item['education_level'] ?? '—') ?></td>
                            <td><?= e((string) $item['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
