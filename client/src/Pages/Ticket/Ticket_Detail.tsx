import { useState } from "react"
import PaymentMomo from "../Payment/PaymentMomo";
import PaymentVNPay from "../Payment/PaymentVNPay";
import { useParams } from "react-router-dom";
import { useFetchDataById } from "../../Hooks/useApi";
import type { Ticket } from "../../Types/tiket";

const Ticket_Detail = () => {
  const [paymentMethod, setPaymentMethod] = useState<string>('')
  const { id } = useParams();
  const {data, isLoading, isError} = useFetchDataById<Ticket>('ticket', id || '');
  if (isLoading) {
    return <div>Loading...</div>;
  }
  if (isError) {
    return <div>Error loading ticket data.</div>;
  }
  const ticket = data?.data;
  console.log(ticket);
  

  return (
    <div className="container max-w-[700px] rounded-2xl shadow-2xl mx-auto border h-50 my-2">
      <div className="rounded-t-2xl bg-green-300 p-4 text-white">
        <h1>Đơn booking số #{ticket?.id}</h1>
      </div>
      <div className="p-4"></div>
      <div></div>
    </div>
  )
}

export default Ticket_Detail;