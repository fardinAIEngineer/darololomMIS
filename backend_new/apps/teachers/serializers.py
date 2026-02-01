"""
Serializers for teachers app
"""
from rest_framework import serializers
from .models import Teacher
from apps.accounts.models import User
from apps.accounts.serializers import UserSerializer


class TeacherSerializer(serializers.ModelSerializer):
    """Full serializer for Teacher model"""
    user = UserSerializer(read_only=True)
    user_id = serializers.PrimaryKeyRelatedField(
        source='user',
        queryset=User.objects.filter(role='TEACHER'),
        write_only=True
    )
    gender_display = serializers.CharField(source='get_gender_display', read_only=True)
    education_level_display = serializers.CharField(source='get_education_level_display', read_only=True)
    full_name = serializers.CharField(read_only=True)

    class Meta:
        model = Teacher
        fields = [
            'id', 'user', 'user_id', 'name', 'father_name', 'id_number', 'gender',
            'gender_display', 'current_address', 'permanent_address', 'mobile_number',
            'emergency_contact', 'education_level', 'education_level_display',
            'specialization', 'classes', 'subjects', 'semesters', 'image', 'notes',
            'is_active', 'hire_date', 'full_name', 'created_at', 'updated_at'
        ]
        read_only_fields = ['id', 'created_at', 'updated_at']


class TeacherListSerializer(serializers.ModelSerializer):
    """Lightweight serializer for listing teachers"""
    user_username = serializers.CharField(source='user.username', read_only=True)
    education_level_display = serializers.CharField(source='get_education_level_display', read_only=True)

    class Meta:
        model = Teacher
        fields = [
            'id', 'user', 'user_username', 'name', 'father_name', 'education_level',
            'education_level_display', 'image', 'is_active'
        ]


class TeacherCreateSerializer(serializers.ModelSerializer):
    """Serializer for creating teachers with user account"""
    username = serializers.CharField(write_only=True)
    password = serializers.CharField(write_only=True, min_length=8)
    email = serializers.EmailField(required=False, allow_blank=True)
    phone_number = serializers.CharField(required=False, allow_blank=True)

    class Meta:
        model = Teacher
        fields = [
            'username', 'password', 'email', 'phone_number', 'name', 'father_name',
            'id_number', 'gender', 'current_address', 'permanent_address', 'mobile_number',
            'emergency_contact', 'education_level', 'specialization', 'classes', 'subjects',
            'semesters', 'image', 'notes', 'hire_date'
        ]

    def create(self, validated_data):
        # Extract user data
        username = validated_data.pop('username')
        password = validated_data.pop('password')
        email = validated_data.pop('email', '')
        phone_number = validated_data.pop('phone_number', '')
        
        # Extract many-to-many fields
        classes = validated_data.pop('classes', [])
        subjects = validated_data.pop('subjects', [])
        semesters = validated_data.pop('semesters', [])
        
        # Create user
        user = User.objects.create_user(
            username=username,
            password=password,
            email=email,
            phone_number=phone_number,
            name=validated_data['name'],
            father_name=validated_data.get('father_name', ''),
            gender=validated_data.get('gender', 'male'),
            role='TEACHER',
            is_approved=True,
            approval_status='approved'
        )
        
        # Create teacher profile
        teacher = Teacher.objects.create(user=user, **validated_data)
        
        # Set many-to-many relationships
        teacher.classes.set(classes)
        teacher.subjects.set(subjects)
        teacher.semesters.set(semesters)
        
        return teacher
