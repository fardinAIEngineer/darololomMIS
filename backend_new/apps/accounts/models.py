"""
User models for authentication and authorization
"""
from django.db import models
from django.contrib.auth.models import AbstractBaseUser, BaseUserManager, PermissionsMixin
from django.core.validators import RegexValidator
from apps.core.models import TimeStampedModel


class UserManager(BaseUserManager):
    """
    Custom user manager
    """
    def create_user(self, email, password=None, **extra_fields):
        if not email:
            raise ValueError('Users must have an email address')
        
        email = self.normalize_email(email)
        # Generate username from email if not provided
        if not extra_fields.get('username'):
            extra_fields['username'] = email.split('@')[0]
        
        user = self.model(email=email, **extra_fields)
        user.set_password(password)
        user.save(using=self._db)
        return user

    def create_superuser(self, email, password=None, **extra_fields):
        extra_fields.setdefault('role', 'SUPER_ADMIN')
        extra_fields.setdefault('is_staff', True)
        extra_fields.setdefault('is_superuser', True)
        extra_fields.setdefault('is_active', True)
        extra_fields.setdefault('is_approved', True)

        return self.create_user(email, password, **extra_fields)


class User(AbstractBaseUser, PermissionsMixin, TimeStampedModel):
    """
    Custom User model with role-based access
    """
    ROLE_CHOICES = [
        ('SUPER_ADMIN', 'مدیر اصلی'),
        ('ADMIN', 'مدیر'),
        ('TEACHER', 'استاد'),
        ('STUDENT', 'دانش‌آموز'),
    ]
    
    GENDER_CHOICES = [
        ('male', 'مذکر'),
        ('female', 'مونث'),
    ]
    
    STATUS_CHOICES = [
        ('pending', 'در انتظار تایید'),
        ('approved', 'تایید شده'),
        ('rejected', 'رد شده'),
    ]

    # Authentication fields
    username = models.CharField('نام کاربری', max_length=150, unique=True, blank=True, null=True)
    email = models.EmailField('ایمیل', unique=True)
    phone_regex = RegexValidator(
        regex=r'^\+?1?\d{9,15}$',
        message="شماره تلفن باید در فرمت: '+999999999' باشد. حداکثر 15 رقم مجاز است."
    )
    phone_number = models.CharField('شماره تلفون', validators=[phone_regex], max_length=17, blank=True)
    
    # Profile fields
    name = models.CharField('نام', max_length=255)
    father_name = models.CharField('نام پدر', max_length=255, blank=True)
    gender = models.CharField('جنسیت', max_length=10, choices=GENDER_CHOICES, default='male')
    
    # Role and permissions
    role = models.CharField('نقش', max_length=20, choices=ROLE_CHOICES, default='STUDENT')
    
    # Status fields
    is_active = models.BooleanField('فعال', default=True)
    is_staff = models.BooleanField('کارمند', default=False)
    is_approved = models.BooleanField('تایید شده', default=False)
    approval_status = models.CharField(
        'وضعیت تایید',
        max_length=20,
        choices=STATUS_CHOICES,
        default='pending'
    )
    approved_by = models.ForeignKey(
        'self',
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name='approved_users',
        verbose_name='تایید شده توسط'
    )
    approved_at = models.DateTimeField('تاریخ تایید', null=True, blank=True)
    rejection_reason = models.TextField('دلیل رد', blank=True)
    
    # Profile image
    profile_image = models.ImageField('عکس پروفایل', upload_to='profiles/', blank=True, null=True)

    objects = UserManager()

    USERNAME_FIELD = 'email'
    REQUIRED_FIELDS = ['name']

    class Meta:
        verbose_name = 'کاربر'
        verbose_name_plural = 'کاربران'
        ordering = ['-created_at']

    def __str__(self):
        return f"{self.username} - {self.get_role_display()}"

    @property
    def is_super_admin(self):
        return self.role == 'SUPER_ADMIN'

    @property
    def is_admin(self):
        return self.role == 'ADMIN'

    @property
    def is_teacher(self):
        return self.role == 'TEACHER'

    @property
    def is_student(self):
        return self.role == 'STUDENT'

    def can_approve_students(self):
        """Check if user has permission to approve students"""
        if self.is_super_admin:
            return True
        if self.is_admin:
            return self.admin_permissions.filter(
                permission_type='can_approve_students',
                is_granted=True
            ).exists()
        return False

    def can_create_teachers(self):
        """Check if user has permission to create teachers"""
        if self.is_super_admin:
            return True
        if self.is_admin:
            return self.admin_permissions.filter(
                permission_type='can_create_teachers',
                is_granted=True
            ).exists()
        return False


class AdminPermission(TimeStampedModel):
    """
    Granular permissions for admin users
    """
    PERMISSION_CHOICES = [
        # Student permissions
        ('can_view_students', 'مشاهده دانش‌آموزان'),
        ('can_create_students', 'ایجاد دانش‌آموز'),
        ('can_edit_students', 'ویرایش دانش‌آموز'),
        ('can_delete_students', 'حذف دانش‌آموز'),
        ('can_approve_students', 'تایید دانش‌آموزان'),
        
        # Teacher permissions
        ('can_view_teachers', 'مشاهده اساتید'),
        ('can_create_teachers', 'ایجاد استاد'),
        ('can_edit_teachers', 'ویرایش استاد'),
        ('can_delete_teachers', 'حذف استاد'),
        
        # Academic permissions
        ('can_manage_classes', 'مدیریت صنوف'),
        ('can_manage_subjects', 'مدیریت مضامین'),
        ('can_manage_semesters', 'مدیریت سمسترها'),
        
        # Grade permissions
        ('can_view_grades', 'مشاهده نمرات'),
        ('can_edit_grades', 'ویرایش نمرات'),
        
        # Attendance permissions
        ('can_view_attendance', 'مشاهده حاضری'),
        ('can_mark_attendance', 'ثبت حاضری'),
        
        # Document permissions
        ('can_view_documents', 'مشاهده اسناد'),
        ('can_upload_documents', 'بارگذاری اسناد'),
        ('can_delete_documents', 'حذف اسناد'),
        
        # System permissions
        ('can_view_reports', 'مشاهده گزارشات'),
        ('can_configure_system', 'تنظیمات سیستم'),
    ]

    admin = models.ForeignKey(
        User,
        on_delete=models.CASCADE,
        related_name='admin_permissions',
        limit_choices_to={'role': 'ADMIN'},
        verbose_name='مدیر'
    )
    permission_type = models.CharField('نوع دسترسی', max_length=50, choices=PERMISSION_CHOICES)
    is_granted = models.BooleanField('اعطا شده', default=False)
    granted_by = models.ForeignKey(
        User,
        on_delete=models.SET_NULL,
        null=True,
        related_name='permissions_granted',
        verbose_name='اعطا شده توسط'
    )

    class Meta:
        verbose_name = 'دسترسی مدیر'
        verbose_name_plural = 'دسترسی‌های مدیران'
        unique_together = ['admin', 'permission_type']

    def __str__(self):
        return f"{self.admin.username} - {self.get_permission_type_display()}"
