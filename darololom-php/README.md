# DarolOlom PHP Rewrite

این پروژه بازنویسی سیستم Django به `PHP + MySQL + HTML/CSS` است با طراحی و استایل مبتنی بر فولدر `health`.

## ساختار پروژه

- `app/Core` هسته‌ی برنامه (Router, Controller, View, Database)
- `app/Controllers` کنترلر هر بخش به‌صورت جداگانه
- `app/Views` ویوهای ماژولار (`students`, `teachers`, `classes`, `subjects`, `grades`, `contracts`, `dashboard`)
- `public/assets/health` تمام فایل‌های اصلی استایل/فونت/JS قالب health
- `public/assets/css/modules` استایل اختصاصی هر ماژول
- `database/schema.sql` دیتابیس کامل MySQL

## نصب و اجرا

1. ساخت دیتابیس:

```bash
mysql -u root -p < database/schema.sql
```

2. تنظیم env (اختیاری):

```bash
cp .env.example .env
```

3. اجرای سرور توسعه:

```bash
cd public
php -S localhost:8080 router.php
```

4. آدرس برنامه:

- `http://localhost:8080`

## ماژول‌های پیاده‌سازی‌شده

- داشبورد
- مدیریت دانش‌آموزان (CRUD + رفتار + سرتفیکت + نتایج + تقدیرنامه + ارتقا به متوسطه)
- مدیریت اساتید (CRUD + رفتار + تقدیرنامه)
- مدیریت صنوف (CRUD)
- مدیریت مضامین (CRUD)
- ثبت نمرات
- قرارداد اساتید
- API جستجوی صنف

## نکته طراحی

استایل تمام صفحات با CSS/JS و visual language قالب `health` یک‌دست شده و RTL برای فارسی/دری تنظیم شده است.
