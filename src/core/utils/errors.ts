export class ValidationError extends Error {
    constructor(message: string, public details?: Record<string, any>) {
        super(message)
        this.name = 'ValidationError'
    }
}

export class DuplicateError extends Error {
    constructor(message: string) {
        super(message)
        this.name = 'DuplicateError'
    }
}

export class NotFoundError extends Error {
    constructor(message: string) {
        super(message)
        this.name = 'NotFoundError'
    }
}

export class UnauthorizedError extends Error {
    constructor(message: string) {
        super(message)
        this.name = 'UnauthorizedError'
    }
}

export class ForbiddenError extends Error {
    constructor(message: string) {
        super(message)
        this.name = 'ForbiddenError'
    }
}

export class ConflictError extends Error {
    constructor(message: string) {
        super(message)
        this.name = 'ConflictError'
    }
}
