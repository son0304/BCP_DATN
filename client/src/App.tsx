// App.tsx
import './App.css';
import Header from './Layouts/Header';
import { Outlet } from 'react-router-dom';
import Footer from './Layouts/Footer';
function App() {

  return (
    <>
      <Header />
      <main className="relative">
        <Outlet />
      </main>
      <Footer />
    </>
  );
}

export default App;
