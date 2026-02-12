from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('core', '0018_student_teacher_behavior'),
    ]

    operations = [
        migrations.AddField(
            model_name='student',
            name='area',
            field=models.CharField(blank=True, max_length=150, verbose_name='ناحیه'),
        ),
        migrations.AddField(
            model_name='student',
            name='district',
            field=models.CharField(blank=True, max_length=150, verbose_name='ولسوالی'),
        ),
        migrations.AddField(
            model_name='student',
            name='village',
            field=models.CharField(blank=True, max_length=150, verbose_name='قریه'),
        ),
        migrations.AddField(
            model_name='teacher',
            name='area',
            field=models.CharField(blank=True, max_length=150, verbose_name='ناحیه'),
        ),
        migrations.AddField(
            model_name='teacher',
            name='district',
            field=models.CharField(blank=True, max_length=150, verbose_name='ولسوالی'),
        ),
        migrations.AddField(
            model_name='teacher',
            name='village',
            field=models.CharField(blank=True, max_length=150, verbose_name='قریه'),
        ),
    ]
