"""
Serializers for accounts app
"""
from rest_framework import serializers
from django.contrib.auth import authenticate
from django.utils import timezone
from .models import User, AdminPermission


class UserSerializer(serializers.ModelSerializer):
    """
    Serializer for User model
    """
    role_display = serializers.CharField(source='get_role_display', read_only=True)
    gender_display = serializers.CharField(source='get_gender_display', read_only=True)
    approval_status_display = serializers.CharField(source='get_approval_status_display', read_only=True)
    
    class Meta:
        model = User
        fields = [
            'id', 'username', 'email', 'phone_number', 'name', 'father_name',
            'gender', 'gender_display', 'role', 'role_display', 'is_active',
            'is_approved', 'approval_status', 'approval_status_display',
            'approved_by', 'approved_at', 'rejection_reason', 'profile_image',
            'created_at', 'updated_at'
        ]
        read_only_fields = [
            'id', 'is_approved', 'approved_by', 'approved_at', 'created_at', 'updated_at'
        ]
        extra_kwargs = {
            'password': {'write_only': True}
        }


class UserCreateSerializer(serializers.ModelSerializer):
    """
    Serializer for user registration
    """
    password = serializers.CharField(write_only=True, min_length=8)
    password_confirm = serializers.CharField(write_only=True, min_length=8)

    class Meta:
        model = User
        fields = [
            'username', 'password', 'password_confirm', 'email', 'phone_number',
            'name', 'father_name', 'gender', 'role', 'profile_image'
        ]

    def validate(self, data):
        if data['password'] != data['password_confirm']:
            raise serializers.ValidationError({"password": "رمز عبور و تایید آن مطابقت ندارند"})
        return data

    def create(self, validated_data):
        validated_data.pop('password_confirm')
        password = validated_data.pop('password')
        
        # Students can self-register but need approval
        if validated_data.get('role') == 'STUDENT':
            validated_data['approval_status'] = 'pending'
            validated_data['is_approved'] = False
        
        user = User.objects.create_user(password=password, **validated_data)
        return user


class StudentRegistrationSerializer(serializers.ModelSerializer):
    """
    Serializer specifically for student self-registration
    """
    password = serializers.CharField(write_only=True, min_length=8)
    password_confirm = serializers.CharField(write_only=True, min_length=8)

    class Meta:
        model = User
        fields = [
            'email', 'password', 'password_confirm', 'phone_number',
            'name', 'father_name', 'gender', 'profile_image'
        ]

    def validate(self, data):
        if data['password'] != data['password_confirm']:
            raise serializers.ValidationError({"password": "رمز عبور و تایید آن مطابقت ندارند"})
        return data

    def create(self, validated_data):
        validated_data.pop('password_confirm')
        password = validated_data.pop('password')
        validated_data['role'] = 'STUDENT'
        validated_data['approval_status'] = 'pending'
        validated_data['is_approved'] = False
        
        user = User.objects.create_user(password=password, **validated_data)
        return user


class LoginSerializer(serializers.Serializer):
    """
    Serializer for user login
    """
    email = serializers.EmailField()
    password = serializers.CharField(write_only=True)

    def validate(self, data):
        email = data.get('email')
        password = data.get('password')

        if email and password:
            user = authenticate(username=email, password=password)
            
            if not user:
                raise serializers.ValidationError('ایمیل یا رمز عبور نادرست است')
            
            if not user.is_active:
                raise serializers.ValidationError('این حساب غیرفعال شده است')
            
            if user.role == 'STUDENT' and not user.is_approved:
                status_messages = {
                    'pending': 'حساب شما در انتظار تایید است',
                    'rejected': 'حساب شما رد شده است'
                }
                raise serializers.ValidationError(
                    status_messages.get(user.approval_status, 'حساب شما تایید نشده است')
                )
            
            data['user'] = user
        else:
            raise serializers.ValidationError('ایمیل و رمز عبور الزامی است')
        
        return data


class UserApprovalSerializer(serializers.Serializer):
    """
    Serializer for approving/rejecting users
    """
    action = serializers.ChoiceField(choices=['approve', 'reject'])
    rejection_reason = serializers.CharField(required=False, allow_blank=True)

    def validate(self, data):
        if data['action'] == 'reject' and not data.get('rejection_reason'):
            raise serializers.ValidationError(
                {"rejection_reason": "دلیل رد حساب الزامی است"}
            )
        return data


class AdminPermissionSerializer(serializers.ModelSerializer):
    """
    Serializer for admin permissions
    """
    permission_display = serializers.CharField(source='get_permission_type_display', read_only=True)
    admin_username = serializers.CharField(source='admin.username', read_only=True)
    granted_by_username = serializers.CharField(source='granted_by.username', read_only=True)

    class Meta:
        model = AdminPermission
        fields = [
            'id', 'admin', 'admin_username', 'permission_type', 'permission_display',
            'is_granted', 'granted_by', 'granted_by_username', 'created_at', 'updated_at'
        ]
        read_only_fields = ['id', 'granted_by', 'created_at', 'updated_at']


class ChangePasswordSerializer(serializers.Serializer):
    """
    Serializer for changing password
    """
    old_password = serializers.CharField(write_only=True)
    new_password = serializers.CharField(write_only=True, min_length=8)
    new_password_confirm = serializers.CharField(write_only=True, min_length=8)

    def validate(self, data):
        if data['new_password'] != data['new_password_confirm']:
            raise serializers.ValidationError(
                {"new_password": "رمز عبور جدید و تایید آن مطابقت ندارند"}
            )
        return data

    def validate_old_password(self, value):
        user = self.context['request'].user
        if not user.check_password(value):
            raise serializers.ValidationError('رمز عبور فعلی نادرست است')
        return value


class ProfileUpdateSerializer(serializers.ModelSerializer):
    """
    Serializer for updating user profile
    """
    class Meta:
        model = User
        fields = [
            'email', 'phone_number', 'name', 'father_name', 'gender', 'profile_image'
        ]
