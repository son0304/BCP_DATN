import { useLocation } from "react-router-dom";

const Check_Rgister_VenueProfile = () => {
    const location = useLocation();
    console.log('Location Venue Profile:', location.state);
    

    return (
        <div>Check_Rgister_VenueProfile</div>
    )
}

export default Check_Rgister_VenueProfile