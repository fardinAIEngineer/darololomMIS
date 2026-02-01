/**
 * Pending Approvals Page
 * For SuperAdmin and permitted Admins to approve/reject student registrations
 */

import React, { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Check, X, Eye, Calendar, Mail, User as UserIcon } from 'lucide-react';
import {
  Card,
  CardHeader,
  CardContent,
  Button,
  Badge,
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
  Loading,
  Modal,
  Textarea,
} from '@/components/ui';
import { useTranslation } from '@/hooks/useTranslation';
import { userService } from '@/services/userService';
import { User } from '@/types';
import { formatPersianDate } from '@/utils/persianDate';
import { toPersianNumber } from '@/utils/persianNumbers';
import toast from 'react-hot-toast';

export const PendingApprovals: React.FC = () => {
  const { t } = useTranslation();
  const queryClient = useQueryClient();
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [showRejectModal, setShowRejectModal] = useState(false);
  const [rejectionReason, setRejectionReason] = useState('');
  const [showDetailsModal, setShowDetailsModal] = useState(false);

  // Fetch pending students
  const { data: pendingStudents, isLoading } = useQuery({
    queryKey: ['pendingStudents'],
    queryFn: () => userService.getPendingStudents(),
  });

  // Approve mutation
  const approveMutation = useMutation({
    mutationFn: (userId: number) =>
      userService.approveRejectUser(userId, 'approve'),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['pendingStudents'] });
      toast.success(t('users.approveSuccess'));
    },
    onError: () => {
      toast.error(t('errors.somethingWentWrong'));
    },
  });

  // Reject mutation
  const rejectMutation = useMutation({
    mutationFn: ({ userId, reason }: { userId: number; reason: string }) =>
      userService.approveRejectUser(userId, 'reject', reason),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['pendingStudents'] });
      setShowRejectModal(false);
      setRejectionReason('');
      setSelectedUser(null);
      toast.success(t('users.rejectSuccess'));
    },
    onError: () => {
      toast.error(t('errors.somethingWentWrong'));
    },
  });

  const handleApprove = (user: User) => {
    if (confirm(`آیا از تایید حساب ${user.name} اطمینان دارید؟`)) {
      approveMutation.mutate(user.id);
    }
  };

  const handleRejectClick = (user: User) => {
    setSelectedUser(user);
    setShowRejectModal(true);
  };

  const handleRejectConfirm = () => {
    if (!selectedUser) return;
    if (!rejectionReason.trim()) {
      toast.error(t('users.rejectionReason') + ' ' + t('validation.required'));
      return;
    }
    rejectMutation.mutate({
      userId: selectedUser.id,
      reason: rejectionReason,
    });
  };

  const handleViewDetails = (user: User) => {
    setSelectedUser(user);
    setShowDetailsModal(true);
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <Loading size="lg" text={t('common.loading')} />
      </div>
    );
  }

  const students = pendingStudents || [];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
          {t('users.pendingApprovals')}
        </h1>
        <p className="text-gray-600 dark:text-gray-400 mt-1">
          بررسی و تایید ثبت‌نام‌های دانش‌آموزان
        </p>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card>
          <CardContent className="py-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600 dark:text-gray-400">
                  {t('users.pendingApprovals')}
                </p>
                <p className="text-2xl font-bold text-orange-600">
                  {toPersianNumber(students.length)}
                </p>
              </div>
              <Calendar className="h-8 w-8 text-orange-600" />
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Pending Students Table */}
      <Card>
        <CardHeader>
          <h2 className="text-xl font-semibold">
            {t('users.pendingApprovals')} ({toPersianNumber(students.length)})
          </h2>
        </CardHeader>
        <CardContent>
          {students.length === 0 ? (
            <div className="text-center py-8">
              <UserIcon className="h-12 w-12 text-gray-400 mx-auto mb-4" />
              <p className="text-gray-500 dark:text-gray-400">
                {t('users.noPendingApprovals')}
              </p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>#</TableHead>
                    <TableHead>{t('students.name')}</TableHead>
                    <TableHead>{t('students.fatherName')}</TableHead>
                    <TableHead>{t('students.email')}</TableHead>
                    <TableHead>{t('students.gender')}</TableHead>
                    <TableHead>{t('common.status')}</TableHead>
                    <TableHead>تاریخ ثبت‌نام</TableHead>
                    <TableHead className="text-center">{t('common.actions')}</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {students.map((student, index) => (
                    <TableRow key={student.id}>
                      <TableCell>{toPersianNumber(index + 1)}</TableCell>
                      <TableCell className="font-medium">{student.name}</TableCell>
                      <TableCell>{student.father_name || '-'}</TableCell>
                      <TableCell>
                        <div className="flex items-center gap-2">
                          <Mail className="h-4 w-4 text-gray-400" />
                          {student.email}
                        </div>
                      </TableCell>
                      <TableCell>{student.gender_display}</TableCell>
                      <TableCell>
                        <Badge variant="warning">
                          {student.approval_status_display}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        {formatPersianDate(student.created_at)}
                      </TableCell>
                      <TableCell>
                        <div className="flex items-center justify-center gap-2">
                          <Button
                            size="sm"
                            variant="ghost"
                            onClick={() => handleViewDetails(student)}
                            title={t('common.view')}
                          >
                            <Eye className="h-4 w-4" />
                          </Button>
                          <Button
                            size="sm"
                            variant="primary"
                            onClick={() => handleApprove(student)}
                            loading={approveMutation.isPending}
                            title={t('users.approve')}
                          >
                            <Check className="h-4 w-4" />
                          </Button>
                          <Button
                            size="sm"
                            variant="danger"
                            onClick={() => handleRejectClick(student)}
                            loading={rejectMutation.isPending}
                            title={t('users.reject')}
                          >
                            <X className="h-4 w-4" />
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Details Modal */}
      <Modal
        isOpen={showDetailsModal}
        onClose={() => setShowDetailsModal(false)}
        title="جزئیات دانش‌آموز"
      >
        {selectedUser && (
          <div className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="text-sm font-medium text-gray-600 dark:text-gray-400">
                  {t('students.name')}
                </label>
                <p className="text-lg font-semibold">{selectedUser.name}</p>
              </div>
              <div>
                <label className="text-sm font-medium text-gray-600 dark:text-gray-400">
                  {t('students.fatherName')}
                </label>
                <p className="text-lg font-semibold">
                  {selectedUser.father_name || '-'}
                </p>
              </div>
              <div>
                <label className="text-sm font-medium text-gray-600 dark:text-gray-400">
                  {t('students.email')}
                </label>
                <p className="text-lg">{selectedUser.email}</p>
              </div>
              <div>
                <label className="text-sm font-medium text-gray-600 dark:text-gray-400">
                  {t('students.gender')}
                </label>
                <p className="text-lg">{selectedUser.gender_display}</p>
              </div>
              <div>
                <label className="text-sm font-medium text-gray-600 dark:text-gray-400">
                  {t('common.status')}
                </label>
                <p>
                  <Badge variant="warning">
                    {selectedUser.approval_status_display}
                  </Badge>
                </p>
              </div>
              <div>
                <label className="text-sm font-medium text-gray-600 dark:text-gray-400">
                  تاریخ ثبت‌نام
                </label>
                <p className="text-lg">
                  {formatPersianDate(selectedUser.created_at)}
                </p>
              </div>
            </div>

            <div className="flex gap-2 pt-4 border-t">
              <Button
                variant="primary"
                onClick={() => {
                  handleApprove(selectedUser);
                  setShowDetailsModal(false);
                }}
                leftIcon={<Check className="h-4 w-4" />}
                className="flex-1"
              >
                {t('users.approve')}
              </Button>
              <Button
                variant="danger"
                onClick={() => {
                  setShowDetailsModal(false);
                  handleRejectClick(selectedUser);
                }}
                leftIcon={<X className="h-4 w-4" />}
                className="flex-1"
              >
                {t('users.reject')}
              </Button>
            </div>
          </div>
        )}
      </Modal>

      {/* Reject Modal */}
      <Modal
        isOpen={showRejectModal}
        onClose={() => {
          setShowRejectModal(false);
          setRejectionReason('');
          setSelectedUser(null);
        }}
        title={t('users.reject')}
      >
        {selectedUser && (
          <div className="space-y-4">
            <p className="text-gray-600 dark:text-gray-400">
              آیا از رد کردن حساب <strong>{selectedUser.name}</strong> اطمینان دارید؟
            </p>

            <Textarea
              label={t('users.rejectionReason')}
              value={rejectionReason}
              onChange={(e) => setRejectionReason(e.target.value)}
              placeholder="لطفاً دلیل رد کردن را وارد کنید..."
              rows={4}
              required
            />

            <div className="flex gap-2">
              <Button
                variant="danger"
                onClick={handleRejectConfirm}
                loading={rejectMutation.isPending}
                className="flex-1"
              >
                {t('users.reject')}
              </Button>
              <Button
                variant="ghost"
                onClick={() => {
                  setShowRejectModal(false);
                  setRejectionReason('');
                  setSelectedUser(null);
                }}
                className="flex-1"
              >
                {t('common.cancel')}
              </Button>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
};
