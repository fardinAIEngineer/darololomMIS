INSERT INTO users (
    full_name,
    username,
    password_hash,
    role,
    can_register_students,
    can_register_teachers,
    created_by,
    is_active
) VALUES (
    'Super Admin',
    'superadmin',
    '$2y$10$J02cYpH9YyhxMD0KX892ouLMpanb0ORI5xqme9jQXe5wZQi6V9ocO',
    'super_admin',
    1,
    1,
    NULL,
    1
)
ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    password_hash = VALUES(password_hash),
    role = 'super_admin',
    can_register_students = 1,
    can_register_teachers = 1,
    is_active = 1;
