import React, { useEffect, useState, useMemo } from 'react';

interface Transaction {
  id: number;
  description: string;
  amount: number;
  type: string;
  category_rel: {
    id: number;
    name: string;
  };
  date: string;
  user_id: number;
}

const TransactionList: React.FC = React.memo(() => {
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [filterType, setFilterType] = useState<string>('all');

  useEffect(() => {
    const fetchTransactions = async () => {
      try {
        setLoading(true);
        const backendUrl = import.meta.env.VITE_BACKEND_URL;
        const response = await fetch(`${backendUrl}/api/v1/transactions/`);
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data: Transaction[] = await response.json();
        setTransactions(data);
      } catch (e: unknown) {
        setError(e instanceof Error ? e.message : 'An error occurred');
      } finally {
        setLoading(false);
      }
    };

    fetchTransactions();
  }, []);

  const filteredTransactions = useMemo(() => {
    if (filterType === 'all') {
      return transactions;
    }
    return transactions.filter(t => t.type === filterType);
  }, [transactions, filterType]);

  const renderedTransactions = useMemo(() => {
    return filteredTransactions.map((transaction, index) => (
      <tr key={transaction.id} className={`${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}`}>
        <td className="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
          {new Date(transaction.date).toLocaleDateString()}
        </td>
        <td className="px-6 py-4 whitespace-nowrap text-center">
          <span className={`px-2 inline-flex justify-center text-center text-xs leading-5 font-semibold rounded-full ${
            transaction.type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
          }`}>
            {transaction.type}
          </span>
        </td>
        <td className="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
          {transaction.category_rel.name}
        </td>
        <td className="px-6 py-4 whitespace-nowrap text-left text-sm font-medium text-gray-900">
          {transaction.description}
        </td>
        <td className="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
          ${transaction.amount.toFixed(2)}
        </td>
      </tr>
    ));
  }, [filteredTransactions]);

  if (loading) {
    return <div className="text-gray-700">Loading transactions...</div>;
  }

  if (error) {
    return <div className="text-red-500">Error: {error}</div>;
  }

  return (
    <div className="bg-white shadow overflow-hidden sm:rounded-lg mt-8">
      <div className="px-4 py-5 sm:px-6 flex justify-between items-center">
        <h3 className="text-lg leading-6 font-medium text-gray-900">Recent Transactions</h3>
        <div className="flex items-center gap-2">
          <label htmlFor="type-filter" className="text-sm font-medium text-gray-700">
            Filter by Type:
          </label>
          <select
            id="type-filter"
            value={filterType}
            onChange={(e) => setFilterType(e.target.value)}
            className="block w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
          >
            <option value="all">All</option>
            <option value="credit">Credit</option>
            <option value="debit">Debit</option>
          </select>
        </div>
      </div>
      <div className="border-t border-gray-200">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th scope="col" className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[15%]">Date</th>
              <th scope="col" className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[10%]">Type</th>
              <th scope="col" className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[20%]">Category</th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[35%]">Description</th>
              <th scope="col" className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[20%]">Amount</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {renderedTransactions}
            {filteredTransactions.length === 0 && (
              <tr>
                <td colSpan={5} className="px-6 py-4 text-center text-gray-500">No transactions found.</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
});

TransactionList.displayName = 'TransactionList';

export default TransactionList;