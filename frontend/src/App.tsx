import React, { useState } from 'react';
import './App.css';
import Header from './components/Header';
import TransactionList from './components/TransactionList';
import TransactionGrid from './components/TransactionGrid';

function App() {
  const [userActivityCount, setUserActivityCount] = useState(0);

  const handleUserProfileClick = () => {
    console.log("User profile clicked!");
    setUserActivityCount(prev => prev + 1);
  };

  return (
    <div className="App min-h-screen bg-gray-100 flex flex-col">
      <Header onUserProfileClick={handleUserProfileClick} />
      <main className="flex-grow container mx-auto p-4">
        <h1 className="text-3xl font-bold text-gray-800 mb-6">FinTech Dashboard</h1>
        <p className="text-gray-600">User Activity: {userActivityCount}</p>
        <TransactionList />
        <TransactionGrid />
      </main>
    </div>
  );
}

export default App;