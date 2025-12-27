import { Elysia } from 'elysia'
import { ValidationError, DuplicateError, NotFoundError, UnauthorizedError, ForbiddenError } from '@/core/utils/errors'

export function errorHandler() {
    return new Elysia({ name: 'ErrorHandler' })
        .onError({ as: 'global' }, ({ error, set }) => {
            if (error instanceof ValidationError) {
                set.status = 422
                return { status: 'error', code: 'VALIDATION_ERROR', message: error.message, details: error.details }
            }
            if (error instanceof DuplicateError) {
                set.status = 409
                return { status: 'error', code: 'DUPLICATE_ERROR', message: error.message }
            }
            if (error instanceof NotFoundError) {
                set.status = 404
                return { status: 'error', code: 'NOT_FOUND', message: error.message }
            }
            if (error instanceof UnauthorizedError) {
                set.status = 401
                return { status: 'error', code: 'UNAUTHORIZED', message: error.message }
            }
            if (error instanceof ForbiddenError) {
                set.status = 403
                return { status: 'error', code: 'FORBIDDEN', message: error.message }
            }
            set.status = 500
            return { status: 'error', code: 'INTERNAL_SERVER_ERROR', message: 'An unexpected error occurred' }
        })
}
