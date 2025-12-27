import { t } from 'elysia'
import { paginatedResponse } from '@/core/utils/pagination'

export namespace TaskMessageModel {
    export const create = t.Object({
        taskId: t.Number(),
        message: t.String({ minLength: 1 })
    })
    export type Create = typeof create.static

    export const update = t.Object({
        message: t.String({ minLength: 1 })
    })
    export type Update = typeof update.static

    export const response = t.Object({
        messageId: t.Number(),
        taskId: t.Number(),
        message: t.String(),
        userId: t.Number(),
        createdAt: t.Date()
    })
    export type Response = typeof response.static

    export const listResponse = paginatedResponse(response)
}
