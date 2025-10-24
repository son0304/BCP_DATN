import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import type { ApiResponse } from "../Types/api";
import {
    fetchData,
    fetchDataById,
    postData,
    putData,
    patchData,
    deleteData,
} from "../Api/fetchApi";

// 🟢 GET all
export function useFetchData<T>(resource: string, params?: Record<string, any>) {
    return useQuery<ApiResponse<T>>({
        queryKey: [resource, params],
        queryFn: () => fetchData<T>(resource, params),
    });
}

// 🟢 GET by ID
export function useFetchDataById<T>(resource: string, id: string | number, params?: Record<string, any>) {
    return useQuery<ApiResponse<T>>({
        queryKey: [resource, id, params],
        queryFn: () => fetchDataById<T>(resource, id, params),
        enabled: !!id,
    });
}

// 🟢 POST
export function usePostData<TOut, TIn = unknown>(resource: string) {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (data: TIn) => postData<TOut, TIn>(resource, data),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: [resource] }),
        onError: (error)=> console.log(error)
        
    });
}

// 🟢 PUT
export function usePutData<TOut, TIn = unknown>(resource: string) {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: ({ id, data }: { id: string | number; data: TIn }) => putData<TOut, TIn>(resource, id, data),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: [resource] }),
    });
}

// 🟢 PATCH
export function usePatchData<TOut, TIn = unknown>(resource: string) {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: ({ id, data }: { id: string | number; data: TIn }) => patchData<TOut, TIn>(resource, id, data),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: [resource] }),
    });
}

// 🟢 DELETE
export function useDeleteData<TOut = unknown>(resource: string) {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (id: string | number) => deleteData<TOut>(resource, id),
        onSuccess: () => queryClient.invalidateQueries({ queryKey: [resource] }),
    });
}
