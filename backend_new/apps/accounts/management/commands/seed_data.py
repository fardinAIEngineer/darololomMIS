"""
Management command to seed initial data
"""
from django.core.management.base import BaseCommand
from apps.accounts.models import User
from apps.academics.models import Semester


class Command(BaseCommand):
    help = 'Seed initial data for the application'

    def handle(self, *args, **options):
        # Create super admin if not exists
        if not User.objects.filter(email='admin@school.com').exists():
            User.objects.create_superuser(
                email='admin@school.com',
                password='Admin@123',
                name='Super Admin'
            )
            self.stdout.write(self.style.SUCCESS('✓ Created super admin (email: admin@school.com, password: Admin@123)'))
        else:
            self.stdout.write(self.style.WARNING('✗ Super admin already exists'))

        # Create semesters
        for i in range(1, 9):
            semester, created = Semester.objects.get_or_create(number=i)
            if created:
                self.stdout.write(self.style.SUCCESS(f'✓ Created semester {i}'))
            else:
                self.stdout.write(self.style.WARNING(f'✗ Semester {i} already exists'))

        self.stdout.write(self.style.SUCCESS('\n=== Seeding complete! ==='))
