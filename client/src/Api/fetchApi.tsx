// fetchApi.tsx
import axios from "axios";
import type { ApiResponse } from "../Types/api";

const API_BASE_URL = "http://127.0.0.1:8000/api";

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    Accept: "application/json",
  },
});

// GET all
export async function fetchData<T>(resource: string, params?: Record<string, any>): Promise<ApiResponse<T>> {
  const response = await apiClient.get<ApiResponse<T>>(`/${resource}`, { params });
  console.log("GET data:", response.data);
  return response.data;
}

// GET by ID
export async function fetchDataById<T>(resource: string, id: number, params?: Record<string, any>): Promise<ApiResponse<T>> {
  const response = await apiClient.get<ApiResponse<T>>(`/${resource}/${id}`, { params });
  console.log("GET by ID data:", response.data);
  return response.data;
}

// POST
export async function postData<TOut, TIn = unknown>(resource: string, data: TIn): Promise<ApiResponse<TOut>> {
  // Nếu data là FormData, để Axios tự set Content-Type
  const isFormData = data instanceof FormData;
  const config = isFormData ? {} : { headers: { "Content-Type": "application/json" } };

  console.log("POST sending:", data instanceof FormData ? "FormData" : JSON.stringify(data, null, 2));

  const response = await apiClient.post<ApiResponse<TOut>>(`/${resource}`, data, config);
  console.log("POST response:", response.data);
  return response.data;
}

// PUT
export async function putData<TOut, TIn = unknown>(resource: string, id: string | number, data: TIn): Promise<ApiResponse<TOut>> {
  const isFormData = data instanceof FormData;
  const config = isFormData ? {} : { headers: { "Content-Type": "application/json" } };

  const response = await apiClient.put<ApiResponse<TOut>>(`/${resource}/${id}`, data, config);
  console.log("PUT response:", response.data);
  return response.data;
}

// PATCH
export async function patchData<TOut, TIn = unknown>(resource: string, id: string | number, data: TIn): Promise<ApiResponse<TOut>> {
  const isFormData = data instanceof FormData;
  const config = isFormData ? {} : { headers: { "Content-Type": "application/json" } };

  const response = await apiClient.patch<ApiResponse<TOut>>(`/${resource}/${id}`, data, config);
  console.log("PATCH response:", response.data);
  return response.data;
}

// DELETE
export async function deleteData<TOut = unknown>(resource: string, id: string | number): Promise<ApiResponse<TOut>> {
  const response = await apiClient.delete<ApiResponse<TOut>>(`/${resource}/${id}`);
  console.log("DELETE response:", response.data);
  return response.data;
}
