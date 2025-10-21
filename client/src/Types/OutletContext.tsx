export type OutletContext = {
    setNotification: (notification: { message: string; type: 'success' | 'error' } | null) => void;
}