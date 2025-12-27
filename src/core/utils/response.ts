export function successResponse<T>(data: T, message: string = 'Success') {
    return { status: 'success' as const, data, message }
}

export function errorResponse(code: string, message: string, details?: any) {
    return { status: 'error' as const, code, message, details }
}
