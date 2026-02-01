import api from './api';
import { User, PaginatedResponse } from '@/types';

export const userService = {
  getUsers: async (params?: Record<string, any>): Promise<PaginatedResponse<User>> => {
    const response = await api.get<PaginatedResponse<User>>('/api/auth/users/', { params });
    return response.data;
  },

  getUserById: async (id: number): Promise<User> => {
    const response = await api.get<User>(`/api/auth/users/${id}/`);
    return response.data;
  },

  createUser: async (data: any): Promise<User> => {
    const response = await api.post<User>('/api/auth/users/', data);
    return response.data;
  },

  updateUser: async (id: number, data: any): Promise<User> => {
    const response = await api.put<User>(`/api/auth/users/${id}/`, data);
    return response.data;
  },

  deleteUser: async (id: number): Promise<void> => {
    await api.delete(`/api/auth/users/${id}/`);
  },

  getPendingStudents: async (): Promise<User[]> => {
    const response = await api.get<PaginatedResponse<User>>('/api/auth/users/pending-students/');
    // Handle both paginated and non-paginated responses
    return Array.isArray(response.data) ? response.data : response.data.results || [];
  },

  approveRejectUser: async (
    id: number,
    action: 'approve' | 'reject',
    rejection_reason?: string
  ): Promise<{ message: string; user: User }> => {
    const response = await api.post(`/api/auth/users/${id}/approve-reject/`, {
      action,
      rejection_reason,
    });
    return response.data;
  },

  toggleActive: async (id: number): Promise<{ message: string; user: User }> => {
    const response = await api.post(`/api/auth/users/${id}/toggle-active/`);
    return response.data;
  },
};
