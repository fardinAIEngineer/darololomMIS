"""
Management command to create a super admin user
"""
from django.core.management.base import BaseCommand
from apps.accounts.models import User


class Command(BaseCommand):
    help = 'Create a super admin user'

    def add_arguments(self, parser):
        parser.add_argument('--email', type=str, help='Email for super admin', default='admin@school.com')
        parser.add_argument('--password', type=str, help='Password for super admin', default='Admin@123')
        parser.add_argument('--name', type=str, help='Name for super admin', default='Super Admin')

    def handle(self, *args, **options):
        email = options['email']
        password = options['password']
        name = options['name']

        if User.objects.filter(email=email).exists():
            self.stdout.write(self.style.WARNING(f'User {email} already exists'))
            return

        user = User.objects.create_superuser(
            email=email,
            password=password,
            name=name
        )

        self.stdout.write(self.style.SUCCESS(f'Successfully created super admin: {email}'))
        self.stdout.write(self.style.SUCCESS(f'Email: {email}'))
        self.stdout.write(self.style.SUCCESS(f'Password: {password}'))
