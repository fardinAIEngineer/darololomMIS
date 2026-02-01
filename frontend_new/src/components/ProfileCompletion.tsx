/**
 * Profile Completion Component
 * Reusable component for completing student profile after approval
 * Will be used later for adding additional profile information
 */

import React from 'react';
import { Card, CardHeader, CardContent } from './ui';
import { useTranslation } from '@/hooks/useTranslation';

interface ProfileCompletionProps {
  onComplete?: () => void;
}

export const ProfileCompletion: React.FC<ProfileCompletionProps> = ({ onComplete }) => {
  const { t } = useTranslation();

  return (
    <Card>
      <CardHeader>
        <h2 className="text-xl font-semibold">{t('profile.title')}</h2>
        <p className="text-sm text-gray-600 dark:text-gray-400">
          لطفاً اطلاعات پروفایل خود را تکمیل کنید
        </p>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          <p className="text-gray-500 dark:text-gray-400">
            در اینجا می‌توانید اطلاعات بیشتری درباره خود اضافه کنید:
          </p>
          <ul className="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400">
            <li>نمبر تذکره</li>
            <li>نمبر امتحان کانکور</li>
            <li>نشانی فعلی و دایمی</li>
            <li>شماره موبایل</li>
            <li>تماس اضطراری</li>
            <li>عکس پروفایل</li>
          </ul>
          {/* TODO: Add form for completing profile */}
          <p className="text-sm text-primary-600 dark:text-primary-400 mt-4">
            این بخش به زودی فعال خواهد شد
          </p>
        </div>
      </CardContent>
    </Card>
  );
};
