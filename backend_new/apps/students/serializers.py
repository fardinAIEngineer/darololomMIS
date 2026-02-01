"""
Serializers for students app
"""
from rest_framework import serializers
from .models import Student
from apps.accounts.models import User
from apps.accounts.serializers import UserSerializer
from apps.academics.serializers import SchoolClassListSerializer, SemesterSerializer


class StudentSerializer(serializers.ModelSerializer):
    """Full serializer for Student model"""
    user = UserSerializer(read_only=True)
    user_id = serializers.PrimaryKeyRelatedField(
        source='user',
        queryset=User.objects.filter(role='STUDENT'),
        write_only=True
    )
    school_class_display = serializers.StringRelatedField(source='school_class', read_only=True)
    gender_display = serializers.CharField(source='get_gender_display', read_only=True)
    full_name = serializers.CharField(read_only=True)

    class Meta:
        model = Student
        fields = [
            'id', 'user', 'user_id', 'name', 'father_name', 'grandfather_name',
            'id_number', 'exam_number', 'gender', 'gender_display', 'current_address',
            'permanent_address', 'mobile_number', 'emergency_contact', 'school_class',
            'school_class_display', 'semesters', 'time_start', 'time_end', 'image',
            'notes', 'is_active', 'full_name', 'created_at', 'updated_at'
        ]
        read_only_fields = ['id', 'created_at', 'updated_at']


class StudentListSerializer(serializers.ModelSerializer):
    """Lightweight serializer for listing students"""
    user_username = serializers.CharField(source='user.username', read_only=True)
    school_class_name = serializers.CharField(source='school_class.name', read_only=True)

    class Meta:
        model = Student
        fields = [
            'id', 'user', 'user_username', 'name', 'father_name', 'school_class',
            'school_class_name', 'image', 'is_active'
        ]


class StudentCreateSerializer(serializers.ModelSerializer):
    """Serializer for creating students with user account"""
    username = serializers.CharField(write_only=True)
    password = serializers.CharField(write_only=True, min_length=8)
    email = serializers.EmailField(required=False, allow_blank=True)
    phone_number = serializers.CharField(required=False, allow_blank=True)

    class Meta:
        model = Student
        fields = [
            'username', 'password', 'email', 'phone_number', 'name', 'father_name',
            'grandfather_name', 'id_number', 'exam_number', 'gender', 'current_address',
            'permanent_address', 'mobile_number', 'emergency_contact', 'school_class',
            'semesters', 'time_start', 'time_end', 'image', 'notes'
        ]

    def create(self, validated_data):
        # Extract user data
        username = validated_data.pop('username')
        password = validated_data.pop('password')
        email = validated_data.pop('email', '')
        phone_number = validated_data.pop('phone_number', '')
        
        # Extract many-to-many field
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
            role='STUDENT',
            is_approved=True,
            approval_status='approved'
        )
        
        # Create student profile
        student = Student.objects.create(user=user, **validated_data)
        student.semesters.set(semesters)
        return student
