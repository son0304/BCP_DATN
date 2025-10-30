// File: src/AppRouter.tsx
import { Route, Routes } from 'react-router-dom';

// Pages
import Content from '../Pages/HomePage';
import Ticket from '../Pages/Ticket/Ticket';
import App from '../App';

// Partner
import Index_Partner from '../Pages/Partner/Index_Partner';
import Create_Venue from '../Pages/Venues/Create_Venue';
import Home_Partner from '../Pages/Partner/Home_Partner';

// User
import Detail_User from '../Pages/User/Detail_User';

// Auth
import Login from '../Auth/Login';
import Register from '../Auth/Register';
import VerifyEmail from '../Pages/Mail/VerifyEmail';

// Venues
import List_Venue from '../Pages/Venues/List_Venues';
import Index_Venues from '../Pages/Venues/Index_Venues';
import Detail_Venue from '../Pages/Venues/Detail_Venue';

// Blog
import Home_Blog from '../Pages/Blog/Home_Blog';
import Index_Blog from '../Pages/Blog/Index_Blog';
import Detail_Blog from '../Pages/Blog/Detail_Blog';

const AppRouter = () => {
    return (
        <Routes>
            {/* === AUTH ROUTES === */}
            <Route path="login" element={<Login />} />
            <Route path="register" element={<Register />} />
            <Route path="/verify-email" element={<VerifyEmail />} />

            {/* === MAIN APP LAYOUT (có Header/Footer) === */}
            <Route path="/" element={<App />}>
                {/* Trang chủ */}
                <Route index element={<Content />} />

                {/* === VENUES === */}
                <Route path="venues" element={<Index_Venues />}>
                    <Route index element={<List_Venue />} />
                    <Route path=":id" element={<Detail_Venue />} />
                </Route>

                {/* === BOOKING === */}
                <Route path="booking/:id" element={<Ticket />} />

                {/* === PROFILE === */}
                <Route path="profile" element={<Detail_User />} />

                {/* === PARTNER === */}
                <Route path="partner" element={<Home_Partner />}>
                    <Route index element={<Index_Partner />} />
                    <Route path="create_venue" element={<Create_Venue />} />
                </Route>

                {/* === BLOG === */}
                <Route path="blog" element={<Home_Blog />}>
                    <Route index element={<Index_Blog />} />
                    <Route path=":id" element={<Detail_Blog />} />
                </Route>
            </Route>
        </Routes>
    );
};

export default AppRouter;