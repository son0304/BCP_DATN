import { Route, Routes } from 'react-router-dom'
import Content from '../Pages/HomePage'
import App from '../App'
import Index_Partner from '../Pages/Partner/Index_Partner'
import Create_Venue from '../Pages/Venues/Create_Venue'
import Home_Partner from '../Pages/Partner/Home_Partner'
import Detail_User from '../Pages/User/Detail_User'
import Login from '../Auth/Login'
import Register from '../Auth/Register'
import VerifyEmail from '../Pages/Mail/VerifyEmail'
import List_Venue from '../Pages/Venues/List_Venues'
// Blog
import Home_Blog from '../Pages/Blog/Home_Blog';
import Index_Blog from '../Pages/Blog/Index_Blog';
import Detail_Blog from '../Pages/Blog/Detail_Blog';
import Index_Detail_Venue from '../Pages/Venues/Detail_Venue/Index_Detail_Venue'
import Ticket_Detail from '../Pages/Ticket/Ticket_Detail'
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
                <Route path='venues' element={<List_Venue />} />
                <Route path='venues/:id' element={<Index_Detail_Venue />} />
                <Route path='booking/:id' element={<Ticket_Detail />} />

                <Route path='profile' element={<Detail_User />}/>
                {/* <Route path='profile/edit' element={< Edit_Profile />} /> */}

                
                <Route path='partner' element={<Home_Partner />}>
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