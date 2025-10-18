import { Route, Routes } from 'react-router-dom'
import Content from '../Pages/HomePage'
import Ticket from '../Pages/Booking/Ticket'
import App from '../App'

const AppRouter = () => {
    return (

        <Routes>
            <Route path='/' element={<App />}>
                <Route index element={<Content />} />
                <Route path='booking/:id' element={<Ticket />} />
            </Route>
        </Routes>

    )
}

export default AppRouter