import React, { useState, useRef, useEffect } from 'react';

const UserPopover: React.FC = () => {
  const [open, setOpen] = useState(false);
  const popoverRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (popoverRef.current && !popoverRef.current.contains(event.target as Node)) {
        setOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  return (
    <div className="" ref={popoverRef}>
      <button
        onClick={() => setOpen(!open)}
        className="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700 transition"
      >
        Username
      </button>

      {open && (
        <div className="absolute right-0 mt-2 w-48 bg-gray-900 text-white shadow-lg rounded border border-gray-700 z-50">
          <div className="px-4 py-2 font-medium border-b border-gray-700">
            Username
          </div>
          <button
            className="w-full text-left px-4 py-2 hover:bg-gray-800 transition"
            onClick={() => console.log('Edit Profile')}
          >
            Edit Profile
          </button>
          <button
            className="w-full text-left px-4 py-2 hover:bg-gray-800 transition text-red-400"
            onClick={() => console.log('Logout')}
          >
            Logout
          </button>
        </div>
      )}
    </div>
  );
};

export default UserPopover;
