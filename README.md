# darolOlomMIS (Offline Docker)

این پروژه طوری تنظیم شده که بتوانی یک Docker image آماده بسازی و برای سیستم بدون اینترنت ارسال کنی.

## 1) ساخت image روی سیستم خودت (با اینترنت)

در ریشه پروژه اجرا کن:

```bash
docker build -t darololommis:offline .
docker save -o darololommis-offline.tar darololommis:offline
```

بعد از این، فایل `darololommis-offline.tar` را همراه `docker-compose.yml` برای دوستت بفرست.

## 2) اجرا روی ویندوز 10 دوستت (بدون اینترنت)

در همان پوشه‌ای که فایل‌ها را کپی کرده:

```powershell
docker load -i .\darololommis-offline.tar
docker compose up -d
```

اگر `docker compose` در سیستمش نبود، این دستورها را بزند:

```powershell
docker volume create darololommis_data
docker run -d --name darololommis -p 8000:8000 -v darololommis_data:/data darololommis:offline
```

باز کردن برنامه در مرورگر:

`http://localhost:8000`

## 3) دستورات مدیریت

توقف:

```powershell
docker compose down
```

شروع دوباره:

```powershell
docker compose up -d
```

لاگ‌ها:

```powershell
docker compose logs -f
```

## نکته مهم داده‌ها

دیتابیس و فایل‌های media داخل volume به نام `darololommis_data` ذخیره می‌شوند و با `down` از بین نمی‌روند.
