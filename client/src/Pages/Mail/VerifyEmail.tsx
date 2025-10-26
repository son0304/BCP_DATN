import { useEffect, useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import axios from "axios";

const VerifyEmail = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const [message, setMessage] = useState("Đang xác thực email...");

  const query = new URLSearchParams(location.search);
  const token = query.get("token");

  useEffect(() => {
    if (!token) {
      setMessage("Token không hợp lệ!");
      return;
    }

    axios
      .post("http://localhost:8000/api/verify-email", { token })
      .then((res) => {
        setMessage(res.data.message || "Xác nhận email thành công!");
        setTimeout(() => navigate("/login"), 3000);
      })
      .catch((err) => {
        setMessage(err.response?.data?.message || "Xác thực thất bại!");
      });
  }, [token, navigate]);

  return (
    <div className="min-h-screen flex justify-center items-center">
      <p>{message}</p>
    </div>
  );
};

export default VerifyEmail;
