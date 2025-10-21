import axios from "axios";
import type { ApiResponse } from "../Types/api";

const API_BASE_URL = "http://127.0.0.1:8000/api";

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
  timeout: 10000,
});

// 🟢 GET all
export async function fetchData<T>(resource: string, params?: Record<string, any>): Promise<ApiResponse<T>> {
  const response = await apiClient.get<ApiResponse<T>>(`/${resource}`, { params });
  return response.data;
}

// 🟢 GET by ID
export async function fetchDataById<T>(resource: string, id: string | number, params?: Record<string, any>): Promise<ApiResponse<T>> {
  const response = await apiClient.get<ApiResponse<T>>(`/${resource}/${id}`, { params });
  return response.data;
}

// 🟢 POST
export async function postData<TOut, TIn = unknown>(resource: string, data: TIn): Promise<ApiResponse<TOut>> {
  const response = await apiClient.post<ApiResponse<TOut>>(`/${resource}`, data);
  return response.data;
}

// 🟢 PUT
export async function putData<TOut, TIn = unknown>(resource: string, id: string | number, data: TIn): Promise<ApiResponse<TOut>> {
  const response = await apiClient.put<ApiResponse<TOut>>(`/${resource}/${id}`, data);
  return response.data;
}

// 🟢 PATCH
export async function patchData<TOut, TIn = unknown>(resource: string, id: string | number, data: TIn): Promise<ApiResponse<TOut>> {
  const response = await apiClient.patch<ApiResponse<TOut>>(`/${resource}/${id}`, data);
  return response.data;
}

// 🟢 DELETE
export async function deleteData<TOut = unknown>(resource: string, id: string | number): Promise<ApiResponse<TOut>> {
  const response = await apiClient.delete<ApiResponse<TOut>>(`/${resource}/${id}`);
  return response.data;
}
