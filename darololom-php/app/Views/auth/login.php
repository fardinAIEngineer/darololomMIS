<?php $oldIdentity = (string) old('identity', ''); ?>

<div class="section-title">
    <h2>ورود به سیستم</h2>
</div>

<div class="row">
    <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="news-thumb auth-card">
            <div class="news-info">
                <p class="auth-note">برای دسترسی به سیستم، ایمیل یا نام کاربری همراه با رمز عبور خود را وارد کنید.</p>

                <form method="post" action="<?= e(url('/login')) ?>" class="auth-form">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label>ایمیل یا نام کاربری</label>
                        <input
                            type="text"
                            name="identity"
                            class="form-control"
                            value="<?= e($oldIdentity) ?>"
                            placeholder="مثال: superadmin یا user@example.com"
                            required
                            autocomplete="username"
                        >
                    </div>

                    <div class="form-group">
                        <label>رمز عبور</label>
                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="رمز عبور را وارد کنید"
                            required
                            autocomplete="current-password"
                        >
                    </div>

                    <div class="auth-actions">
                        <button type="submit" class="section-btn btn btn-default">ورود</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
