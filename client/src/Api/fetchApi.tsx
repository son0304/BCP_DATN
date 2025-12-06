// fetchApi.tsx
import axios from "axios";
import type { ApiResponse } from "../Types/api";

const API_BASE_URL = "http://127.0.0.1:8000/api";

// ==================== TẠO INSTANCE AXIOS ====================
export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    Accept: "application/json",
  },
});

// ==================== INTERCEPTOR JWT ====================
// Tự động thêm Authorization header nếu có token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("token");
    if (token) {
      // Axios v1 expects headers to be AxiosHeaders or cast to any
      config.headers = {
        ...(config.headers as any),
        Authorization: `Bearer ${token}`,
      };
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// ==================== GET ALL ====================
export async function fetchData<T>(
  resource: string,
  params?: Record<string, any>
): Promise<ApiResponse<T>> {
  const response = await apiClient.get<ApiResponse<T>>(`/${resource}`, { params });
  return response.data;
}

// ==================== GET BY ID ====================
export async function fetchDataById<T>(
  resource: string,
  id: string | number,
  params?: Record<string, any>
): Promise<ApiResponse<T>> {
  const response = await apiClient.get<ApiResponse<T>>(`/${resource}/${id}`, { params });
  return response.data;
}

// ==================== POST ====================
export async function postData<TOut, TIn = unknown>(
  resource: string,
  data: TIn
): Promise<ApiResponse<TOut>> {
  const isFormData = data instanceof FormData;
  const config = isFormData ? {} : { headers: { "Content-Type": "application/json" } };
  const response = await apiClient.post<ApiResponse<TOut>>(`/${resource}`, data, config);
  console.log(response);

  return response.data;
}

// ==================== PUT ====================
export async function putData<TOut, TIn = unknown>(
  resource: string,
  id: string | number,
  data: TIn
): Promise<ApiResponse<TOut>> {
  const isFormData = data instanceof FormData;
  const config = isFormData ? {} : { headers: { "Content-Type": "application/json" } };

  const response = await apiClient.put<ApiResponse<TOut>>(`/${resource}/${id}`, data, config);
  return response.data;
}

// ==================== PATCH ====================
export async function patchData<TOut, TIn = unknown>(
  resource: string,
  id: string | number,
  data: TIn
): Promise<ApiResponse<TOut>> {
  const isFormData = data instanceof FormData;
  const config = isFormData ? {} : { headers: { "Content-Type": "application/json" } };

  const response = await apiClient.patch<ApiResponse<TOut>>(`/${resource}/${id}`, data, config);
  return response.data;
}

// ==================== DELETE ====================
export async function deleteData<TOut = unknown>(
  resource: string,
  id: string | number
): Promise<ApiResponse<TOut>> {
  const response = await apiClient.delete<ApiResponse<TOut>>(`/${resource}/${id}`);
  return response.data;
}
