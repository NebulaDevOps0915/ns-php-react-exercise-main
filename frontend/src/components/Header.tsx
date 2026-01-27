import React from 'react';

interface HeaderProps {
  onUserProfileClick: () => void;
}

const Header: React.FC<HeaderProps> = ({ onUserProfileClick }) => {
  return (
    <header className="bg-white shadow p-4 flex justify-between items-center">
      <div className="text-2xl font-bold text-gray-800">FinTech Dashboard</div>
      <nav>
        <button
          onClick={onUserProfileClick}
          className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
        >
          User Profile
        </button>
      </nav>
    </header>
  );
};

export default React.memo(Header);