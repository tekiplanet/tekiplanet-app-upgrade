import { api } from '@/lib/api';

export interface Transaction {
  id: string;
  user_id: string;
  amount: string;
  type: 'credit' | 'debit';
  category: 'withdrawal' | 'deposit' | 'refund' | 'payment';
  status: 'pending' | 'completed' | 'failed';
  description: string;
  payment_method: string;
  reference_number: string;
  created_at: string;
  updated_at: string;
}

export interface TransactionStats {
  total_transactions: number;
  this_month: number;
  total_received: number;
  total_spent: number;
  this_month_received: number;
  this_month_spent: number;
}

export const transactionService = {
  async getUserTransactions() {
    const response = await api.get<Transaction[]>('/transactions');
    return response.data;
  },

  async getTransaction(id: string) {
    const response = await api.get<Transaction>(`/transactions/${id}`);
    return response.data;
  },

  async getTransactionStats() {
    const response = await api.get<{ success: boolean; data: TransactionStats }>('/transactions/stats');
    return response.data;
  }
}; 