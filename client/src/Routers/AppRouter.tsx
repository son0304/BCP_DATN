import { Route, Routes } from 'react-router-dom'
import Content from '../Pages/HomePage'
import Ticket from '../Pages/Ticket/Ticket'
import App from '../App'
import Index_Partner from '../Pages/Partner/Index_Partner'
import Create_Venue from '../Pages/Venues/Create_Venue'
import Home_Partner from '../Pages/Partner/Home_Partner'
import Detail_User from '../Pages/User/Detail_User'
import Login from '../Auth/Login'
import Register from '../Auth/Register'
import VerifyEmail from '../Pages/Mail/VerifyEmail'
import List_Venue from '../Pages/Venues/List_Venues'
import Detail_Venue from '../Pages/Venues/Detail_Venue'

const AppRouter = () => {
    return (



        <Routes>

            {/* Auth */}
            <Route path='login' element={<Login />} />
            <Route path='register' element={<Register />} />
            <Route path="/verify-email" element={<VerifyEmail />} />

            {/* Content */}
            <Route path='/' element={<App />}>
                <Route index element={<Content />} />
                <Route path='venues' element={<List_Venue />} />
                <Route path='venues/:id' element={<Detail_Venue />} />
                <Route path='booking/:id' element={<Ticket />} />
                <Route path='profile' element={<Detail_User />} />
                <Route path='partner' element={<Home_Partner />}>
                    <Route index element={<Index_Partner />} />
                    <Route path='create_venue' element={<Create_Venue />} />
                </Route>





            </Route>
        </Routes>

    )
}

export default AppRouter