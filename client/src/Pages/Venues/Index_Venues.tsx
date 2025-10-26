import { Outlet } from 'react-router-dom'

const Index_Venues = () => {
    return (
        <>
            <h1 className="md:text-5xl text-3xl font-bold text-[#2d6a2d] my-4 text-center my-2">Sân thể thao</h1>
            <Outlet />
        </>

    )
}

export default Index_Venues