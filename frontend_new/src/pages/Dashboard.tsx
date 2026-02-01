import React from 'react';
import { Users, GraduationCap, School, BookOpen, Calendar, CheckCircle } from 'lucide-react';
import { Card, CardHeader, CardContent } from '@/components/ui';
import { useAuthStore } from '@/store/authStore';
import { useTranslation } from '@/hooks/useTranslation';
import { toPersianNumber } from '@/utils/persianNumbers';

const StatCard: React.FC<{
  title: string;
  value: string | number;
  icon: React.ReactNode;
  color: string;
}> = ({ title, value, icon, color }) => {
  return (
    <Card>
      <CardContent className="flex items-center justify-between py-6">
        <div>
          <p className="text-sm font-medium text-gray-600 dark:text-gray-400">{title}</p>
          <p className="text-2xl font-bold text-gray-900 dark:text-white mt-1">
            {toPersianNumber(value)}
          </p>
        </div>
        <div className={`p-3 rounded-lg ${color}`}>{icon}</div>
      </CardContent>
    </Card>
  );
};

export const Dashboard: React.FC = () => {
  const { user, isSuperAdmin, isAdmin, isStudent } = useAuthStore();
  const { t } = useTranslation();

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
          {t('dashboard.title')}
        </h1>
        <p className="text-gray-600 dark:text-gray-400 mt-1">
          {t('dashboard.welcome')}، {user?.name}!
        </p>
      </div>

      {/* Stats Grid */}
      {(isSuperAdmin() || isAdmin()) && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <StatCard
            title={t('dashboard.totalStudents')}
            value="0"
            icon={<GraduationCap className="h-6 w-6 text-white" />}
            color="bg-blue-500"
          />
          <StatCard
            title={t('dashboard.totalTeachers')}
            value="0"
            icon={<Users className="h-6 w-6 text-white" />}
            color="bg-green-500"
          />
          <StatCard
            title={t('dashboard.totalClasses')}
            value="0"
            icon={<School className="h-6 w-6 text-white" />}
            color="bg-purple-500"
          />
          <StatCard
            title={t('dashboard.totalSubjects')}
            value="0"
            icon={<BookOpen className="h-6 w-6 text-white" />}
            color="bg-orange-500"
          />
        </div>
      )}

      {/* Quick Actions */}
      <Card>
        <CardHeader>
          <h2 className="text-xl font-semibold">{t('dashboard.quickActions')}</h2>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {!isStudent() && (
              <>
                <button className="p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-primary-500 dark:hover:border-primary-400 transition-colors text-right">
                  <Calendar className="h-6 w-6 text-primary-600 mb-2" />
                  <p className="font-medium">{t('dashboard.markAttendance')}</p>
                  <p className="text-sm text-gray-500 dark:text-gray-400">
                    {t('attendance.markAttendance')}
                  </p>
                </button>
                <button className="p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-primary-500 dark:hover:border-primary-400 transition-colors text-right">
                  <CheckCircle className="h-6 w-6 text-primary-600 mb-2" />
                  <p className="font-medium">{t('dashboard.enterGrades')}</p>
                  <p className="text-sm text-gray-500 dark:text-gray-400">
                    {t('grades.enterGrades')}
                  </p>
                </button>
              </>
            )}
            {(isSuperAdmin() || isAdmin()) && (
              <button className="p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-primary-500 dark:hover:border-primary-400 transition-colors text-right">
                <Users className="h-6 w-6 text-primary-600 mb-2" />
                <p className="font-medium">{t('dashboard.pendingApprovals')}</p>
                <p className="text-sm text-gray-500 dark:text-gray-400">
                  {t('users.pendingApprovals')}
                </p>
              </button>
            )}
          </div>
        </CardContent>
      </Card>

      {/* Recent Activity */}
      <Card>
        <CardHeader>
          <h2 className="text-xl font-semibold">{t('dashboard.recentActivity')}</h2>
        </CardHeader>
        <CardContent>
          <p className="text-gray-500 dark:text-gray-400">
            {t('dashboard.noRecentActivity')}
          </p>
        </CardContent>
      </Card>
    </div>
  );
};
