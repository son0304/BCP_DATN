import { Route, Routes } from 'react-router-dom'
import Content from '../Pages/HomePage'
import Ticket from '../Pages/Ticket/Ticket'
import App from '../App'
import Index_Partner from '../Pages/Partner/Index_Partner'
import Create_Venue from '../Pages/Venues/Create_Venue'
import Home_Partner from '../Pages/Partner/Home_Partner'
import Detail_User from '../Pages/User/Detail_User'

const AppRouter = () => {
    return (

        <Routes>
            <Route path='/' element={<App />}>
                <Route index element={<Content />} />
                <Route path='booking/:id' element={<Ticket />} />
                <Route path='edit_profile' element={<Detail_User />}/>
                <Route path='partner' element={<Home_Partner />}>
                    <Route index element={<Index_Partner />} />
                    <Route path='create_venue' element={<Create_Venue />} />
                </Route>
            </Route>
        </Routes>

    )
}

export default AppRouter