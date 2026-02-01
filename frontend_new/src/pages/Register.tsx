import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { UserPlus, School } from 'lucide-react';
import { Button, Input, Select, Card, CardContent } from '@/components/ui';
import { authService } from '@/services/authService';
import { useTranslation } from '@/hooks/useTranslation';
import { ROUTES, GENDER_OPTIONS } from '@/utils/constants';
import toast from 'react-hot-toast';

const registerSchema = z.object({
  email: z.string().email('ایمیل نامعتبر است').min(1, 'ایمیل الزامی است'),
  password: z.string().min(8, 'رمز عبور باید حداقل ۸ کاراکتر باشد'),
  password_confirm: z.string(),
  name: z.string().min(1, 'نام الزامی است'),
  father_name: z.string().min(1, 'نام پدر الزامی است'),
  gender: z.enum(['male', 'female']),
}).refine((data) => data.password === data.password_confirm, {
  message: "رمز عبور و تایید آن مطابقت ندارند",
  path: ['password_confirm'],
});

type RegisterForm = z.infer<typeof registerSchema>;

export const Register: React.FC = () => {
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const { t } = useTranslation();

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<RegisterForm>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      gender: 'male',
    },
  });

  const onSubmit = async (data: RegisterForm) => {
    setLoading(true);
    try {
      await authService.register(data);
      toast.success(t('auth.registerSuccess'));
      navigate(ROUTES.LOGIN);
    } catch (error: any) {
      toast.error(error.response?.data?.detail || 'خطا در ثبت‌نام');
    } finally {
      setLoading(false);
    }
  };

  const genderOptions = [
    { value: 'male', label: t('students.male') },
    { value: 'female', label: t('students.female') },
  ];

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100 dark:from-gray-900 dark:to-gray-800 p-4">
      <Card className="w-full max-w-2xl">
        <CardContent className="pt-6">
          <div className="flex flex-col items-center mb-6">
            <School className="h-16 w-16 text-primary-600 mb-4" />
            <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
              {t('auth.registerTitle')}
            </h1>
            <p className="text-gray-600 dark:text-gray-400 mt-1">
              {t('auth.registerSuccess')}
            </p>
          </div>

          <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
            <Input
              label={t('students.email')}
              type="email"
              {...register('email')}
              error={errors.email?.message}
              placeholder="example@email.com"
              required
            />

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <Input
                label={t('students.name')}
                {...register('name')}
                error={errors.name?.message}
                placeholder={t('students.name')}
                required
              />

              <Input
                label={t('students.fatherName')}
                {...register('father_name')}
                error={errors.father_name?.message}
                placeholder={t('students.fatherName')}
                required
              />
            </div>

            <Select
              label={t('students.gender')}
              {...register('gender')}
              error={errors.gender?.message}
              options={genderOptions}
              required
            />

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <Input
                label={t('auth.password')}
                type="password"
                {...register('password')}
                error={errors.password?.message}
                placeholder={t('auth.password')}
                required
              />

              <Input
                label={t('auth.confirmPassword')}
                type="password"
                {...register('password_confirm')}
                error={errors.password_confirm?.message}
                placeholder={t('auth.confirmPassword')}
                required
              />
            </div>

            <Button
              type="submit"
              className="w-full"
              loading={loading}
              leftIcon={<UserPlus className="h-4 w-4" />}
            >
              {t('auth.register')}
            </Button>
          </form>

          <div className="mt-6 text-center text-sm">
            <p className="text-gray-600 dark:text-gray-400">
              {t('auth.alreadyHaveAccount')}{' '}
              <Link
                to={ROUTES.LOGIN}
                className="text-primary-600 hover:text-primary-700 font-medium"
              >
                {t('auth.loginHere')}
              </Link>
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};
