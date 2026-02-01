import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { LogIn, School } from 'lucide-react';
import { Button, Input, Card, CardContent } from '@/components/ui';
import { authService } from '@/services/authService';
import { useAuthStore } from '@/store/authStore';
import { useTranslation } from '@/hooks/useTranslation';
import { ROUTES } from '@/utils/constants';
import toast from 'react-hot-toast';

const loginSchema = z.object({
  email: z.string().email('ایمیل نامعتبر است').min(1, 'ایمیل الزامی است'),
  password: z.string().min(1, 'رمز عبور الزامی است'),
});

type LoginForm = z.infer<typeof loginSchema>;

export const Login: React.FC = () => {
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const { setAuth } = useAuthStore();
  const { t } = useTranslation();

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<LoginForm>({
    resolver: zodResolver(loginSchema),
  });

  const onSubmit = async (data: LoginForm) => {
    setLoading(true);
    try {
      const response = await authService.login(data);
      setAuth(response.user, response.access, response.refresh);
      toast.success(t('auth.loginSuccess'));
      navigate(ROUTES.DASHBOARD);
    } catch (error: any) {
      toast.error(error.response?.data?.detail || t('auth.loginError'));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100 dark:from-gray-900 dark:to-gray-800 p-4">
      <Card className="w-full max-w-md">
        <CardContent className="pt-6">
          <div className="flex flex-col items-center mb-6">
            <School className="h-16 w-16 text-primary-600 mb-4" />
            <h3 className="text-sm font-bold text-gray-900 dark:text-white">
              سیستم مدیریت دارالعلوم عالی الحاج سید منصور نادری
            </h3>
            <p className="text-gray-600 dark:text-gray-400 mt-1">
              {t('auth.loginTitle')}
            </p>
          </div>

          <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
            <Input
              label={t('students.email')}
              type="email"
              {...register('email')}
              error={errors.email?.message}
              placeholder="example@email.com"
              autoComplete="email"
            />

            <Input
              label={t('auth.password')}
              type="password"
              {...register('password')}
              error={errors.password?.message}
              placeholder={t('auth.password')}
              autoComplete="current-password"
            />

            <Button
              type="submit"
              className="w-full"
              loading={loading}
              leftIcon={<LogIn className="h-4 w-4" />}
            >
              {t('auth.login')}
            </Button>
          </form>

          <div className="mt-6 text-center text-sm">
            <p className="text-gray-600 dark:text-gray-400">
              {t('auth.dontHaveAccount')}{' '}
              <Link
                to={ROUTES.REGISTER}
                className="text-primary-600 hover:text-primary-700 font-medium"
              >
                {t('auth.registerHere')}
              </Link>
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};
