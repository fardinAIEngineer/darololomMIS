import React, { useState } from 'react';
import { Outlet, Link, useNavigate } from 'react-router-dom';
import {
  Menu,
  X,
  LayoutDashboard,
  Users,
  GraduationCap,
  BookOpen,
  School,
  ClipboardCheck,
  Calendar,
  FileText,
  Settings,
  LogOut,
  ChevronDown,
} from 'lucide-react';
import { useAuthStore } from '@/store/authStore';
import { useTranslation } from '@/hooks/useTranslation';
import { ThemeToggle } from '@/components/ThemeToggle';
import { Avatar } from '@/components/ui';
import { ROUTES } from '@/utils/constants';
import { cn } from '@/utils/cn';
import toast from 'react-hot-toast';

export const MainLayout: React.FC = () => {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [userMenuOpen, setUserMenuOpen] = useState(false);
  const { user, logout, isSuperAdmin, isAdmin, isStudent } = useAuthStore();
  const { t } = useTranslation();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate(ROUTES.LOGIN);
    toast.success(t('auth.logoutSuccess'));
  };

  const navigation = [
    {
      name: t('nav.dashboard'),
      href: ROUTES.DASHBOARD,
      icon: LayoutDashboard,
      show: true,
    },
    {
      name: t('nav.students'),
      href: ROUTES.STUDENTS,
      icon: GraduationCap,
      show: !isStudent(),
    },
    {
      name: t('nav.teachers'),
      href: ROUTES.TEACHERS,
      icon: Users,
      show: isSuperAdmin() || isAdmin(),
    },
    {
      name: t('nav.classes'),
      href: ROUTES.CLASSES,
      icon: School,
      show: !isStudent(),
    },
    {
      name: t('nav.subjects'),
      href: ROUTES.SUBJECTS,
      icon: BookOpen,
      show: !isStudent(),
    },
    {
      name: t('nav.grades'),
      href: ROUTES.GRADES,
      icon: ClipboardCheck,
      show: true,
    },
    {
      name: t('nav.attendance'),
      href: ROUTES.ATTENDANCE_STUDENTS,
      icon: Calendar,
      show: true,
    },
    {
      name: t('nav.documents'),
      href: ROUTES.DOCUMENTS,
      icon: FileText,
      show: true,
    },
    {
      name: t('nav.users'),
      href: ROUTES.USERS,
      icon: Users,
      show: isSuperAdmin() || isAdmin(),
    },
    {
      name: t('nav.pendingApprovals'),
      href: ROUTES.PENDING_APPROVALS,
      icon: ClipboardCheck,
      show: isSuperAdmin() || isAdmin(),
    },
  ].filter((item) => item.show);

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
      {/* Top Navigation */}
      <nav className="fixed top-0 z-50 w-full bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div className="px-3 py-3 lg:px-5 lg:pl-3">
          <div className="flex items-center justify-between">
            <div className="flex items-center justify-start">
              <button
                onClick={() => setSidebarOpen(!sidebarOpen)}
                className="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg lg:hidden hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                {sidebarOpen ? <X /> : <Menu />}
              </button>
              <Link to={ROUTES.DASHBOARD} className="flex mr-2 md:ml-24">
                <School className="h-8 w-8 text-primary-600" />
                <span className="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white mr-2">
                  سیستم مدیریت مدرسه
                </span>
              </Link>
            </div>
            <div className="flex items-center gap-2">
              <ThemeToggle />
              <div className="relative">
                <button
                  onClick={() => setUserMenuOpen(!userMenuOpen)}
                  className="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                  <Avatar src={user?.profile_image} name={user?.name} size="sm" />
                  <div className="hidden md:block text-left">
                    <p className="text-sm font-medium text-gray-900 dark:text-white">
                      {user?.name}
                    </p>
                    <p className="text-xs text-gray-500 dark:text-gray-400">
                      {user?.role_display}
                    </p>
                  </div>
                  <ChevronDown className="h-4 w-4" />
                </button>
                {userMenuOpen && (
                  <div className="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
                    <Link
                      to={ROUTES.PROFILE}
                      className="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-right"
                      onClick={() => setUserMenuOpen(false)}
                    >
                      {t('nav.profile')}
                    </Link>
                    <Link
                      to={ROUTES.SETTINGS}
                      className="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-right"
                      onClick={() => setUserMenuOpen(false)}
                    >
                      {t('nav.settings')}
                    </Link>
                    <button
                      onClick={handleLogout}
                      className="w-full text-right px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                      <LogOut className="inline h-4 w-4 ml-2" />
                      {t('auth.logout')}
                    </button>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </nav>

      {/* Sidebar */}
      <aside
        className={cn(
          'fixed top-0 right-0 z-40 w-64 h-screen pt-20 transition-transform bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700',
          sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'
        )}
      >
        <div className="h-full px-3 pb-4 overflow-y-auto">
          <ul className="space-y-2 font-medium">
            {navigation.map((item) => (
              <li key={item.name}>
                <Link
                  to={item.href}
                  onClick={() => setSidebarOpen(false)}
                  className="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
                >
                  <item.icon className="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                  <span className="mr-3">{item.name}</span>
                </Link>
              </li>
            ))}
          </ul>
        </div>
      </aside>

      {/* Main Content */}
      <main className="p-4 lg:mr-64 pt-20">
        <Outlet />
      </main>
    </div>
  );
};
