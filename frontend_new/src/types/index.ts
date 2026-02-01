// Core type definitions

export type UserRole = 'SUPER_ADMIN' | 'ADMIN' | 'TEACHER' | 'STUDENT';

export type ApprovalStatus = 'pending' | 'approved' | 'rejected';

export type Gender = 'male' | 'female';

export interface User {
  id: number;
  username: string;
  email?: string;
  phone_number?: string;
  name: string;
  father_name?: string;
  gender: Gender;
  gender_display: string;
  role: UserRole;
  role_display: string;
  is_active: boolean;
  is_approved: boolean;
  approval_status: ApprovalStatus;
  approval_status_display: string;
  approved_by?: number;
  approved_at?: string;
  rejection_reason?: string;
  profile_image?: string;
  created_at: string;
  updated_at: string;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterData {
  email: string;
  password: string;
  password_confirm: string;
  name: string;
  father_name: string;
  gender: Gender;
}

export interface AuthResponse {
  access: string;
  refresh: string;
  user: User;
}

export interface Semester {
  id: number;
  number: number;
  name?: string;
  is_active: boolean;
  class_count?: number;
  subject_count?: number;
  created_at: string;
  updated_at: string;
}

export interface SchoolClass {
  id: number;
  name: string;
  semester?: number;
  semester_display?: string;
  description?: string;
  is_active: boolean;
  student_count?: number;
  created_at: string;
  updated_at: string;
}

export interface Subject {
  id: number;
  name: string;
  code?: string;
  semester: number;
  semester_display?: string;
  credits: number;
  description?: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface Student {
  id: number;
  user: User;
  user_id?: number;
  name: string;
  father_name?: string;
  grandfather_name?: string;
  id_number?: string;
  exam_number?: string;
  gender: Gender;
  gender_display: string;
  current_address?: string;
  permanent_address?: string;
  mobile_number?: string;
  emergency_contact?: string;
  school_class?: number;
  school_class_display?: string;
  semesters: number[];
  time_start?: string;
  time_end?: string;
  image?: string;
  notes?: string;
  is_active: boolean;
  full_name: string;
  created_at: string;
  updated_at: string;
}

export interface Teacher {
  id: number;
  user: User;
  user_id?: number;
  name: string;
  father_name?: string;
  id_number?: string;
  gender: Gender;
  gender_display: string;
  current_address?: string;
  permanent_address?: string;
  mobile_number?: string;
  emergency_contact?: string;
  education_level: string;
  education_level_display: string;
  specialization?: string;
  classes: number[];
  subjects: number[];
  semesters: number[];
  image?: string;
  notes?: string;
  is_active: boolean;
  hire_date?: string;
  full_name: string;
  created_at: string;
  updated_at: string;
}

export interface StudentScore {
  id: number;
  student: number;
  student_name: string;
  subject: number;
  subject_name: string;
  score?: number;
  exam_type: 'midterm' | 'final' | 'quiz' | 'assignment';
  exam_type_display: string;
  exam_date?: string;
  remarks?: string;
  entered_by?: number;
  entered_by_name?: string;
  grade_letter: string;
  is_passing: boolean;
  created_at: string;
  updated_at: string;
}

export interface AttendanceStatus {
  status: 'present' | 'absent' | 'late' | 'excused' | 'leave';
  status_display: string;
}

export interface StudentAttendance extends AttendanceStatus {
  id: number;
  student: number;
  student_name: string;
  date: string;
  school_class?: number;
  class_name?: string;
  subject?: number;
  subject_name?: string;
  remarks?: string;
  marked_by?: number;
  marked_by_name?: string;
  created_at: string;
  updated_at: string;
}

export interface TeacherAttendance extends AttendanceStatus {
  id: number;
  teacher: number;
  teacher_name: string;
  date: string;
  check_in_time?: string;
  check_out_time?: string;
  remarks?: string;
  marked_by?: number;
  marked_by_name?: string;
  created_at: string;
  updated_at: string;
}

export interface Document {
  id: number;
  title: string;
  description?: string;
  document_type: string;
  document_type_display: string;
  file: string;
  file_url?: string;
  file_name: string;
  file_size: number;
  file_extension: string;
  student?: number;
  teacher?: number;
  uploaded_by?: number;
  uploaded_by_name?: string;
  is_verified: boolean;
  verified_by?: number;
  verified_by_name?: string;
  verified_at?: string;
  created_at: string;
  updated_at: string;
}

export interface Permission {
  id: number;
  admin: number;
  admin_username: string;
  permission_type: string;
  permission_display: string;
  is_granted: boolean;
  granted_by?: number;
  granted_by_username?: string;
  created_at: string;
  updated_at: string;
}

export interface PaginatedResponse<T> {
  count: number;
  next: string | null;
  previous: string | null;
  total_pages: number;
  current_page: number;
  results: T[];
}

export interface AttendanceStats {
  total_days: number;
  present_days: number;
  absent_days: number;
  late_days: number;
  excused_days: number;
  attendance_percentage: number;
}

export interface DashboardStats {
  total_students: number;
  total_teachers: number;
  total_classes: number;
  total_subjects: number;
  pending_students: number;
  active_students: number;
  active_teachers: number;
}
