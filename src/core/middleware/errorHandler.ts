import { Elysia } from 'elysia'
import { ValidationError, DuplicateError, NotFoundError, UnauthorizedError, ForbiddenError } from '@/core/utils/errors'

export function errorHandler() {
    return new Elysia({ name: 'ErrorHandler' })
        .onError({ as: 'global' }, ({ error, set }) => {
            if (error instanceof ValidationError) {
                set.status = 422
                return { message: error.message }
            }
            if (error instanceof DuplicateError) {
                set.status = 409
                return { message: error.message }
            }
            if (error instanceof NotFoundError) {
                set.status = 404
                return { message: error.message }
            }
            if (error instanceof UnauthorizedError) {
                set.status = 401
                return { message: error.message }
            }
            if (error instanceof ForbiddenError) {
                set.status = 403
                return { message: error.message }
            }
            set.status = 500
            return { message: 'An unexpected error occurred' }
        })
}
