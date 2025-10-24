// App.tsx
import './App.css';
import Header from './Layouts/Header';
import { Outlet } from 'react-router-dom';
import Footer from './Layouts/Footer';
import { useState } from 'react';
import Notification from './Components/Notification';

function App() {
  const [notification, setNotification] = useState<{ message: string; type: 'success' | 'error' } | null>(null);

  return (
    <>
      <Header />

      {notification && (
        <Notification
          message={notification.message}
          type={notification.type}
          onClose={() => setNotification(null)}
        />
      )}

      <main className="relative">
        <Outlet context={{ setNotification }} />
      </main>
      <Footer />
    </>
  );
}

export default App;
