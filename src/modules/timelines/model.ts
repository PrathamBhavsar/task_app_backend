import { t } from 'elysia'
import { paginatedResponse } from '@/core/utils/pagination'

export namespace TimelineModel {
    export const create = t.Object({
        taskId: t.Number(),
        status: t.Optional(t.String())
    })
    export type Create = typeof create.static

    export const response = t.Object({
        timelineId: t.Number(),
        taskId: t.Number(),
        status: t.Union([t.String(), t.Null()]),
        userId: t.Number(),
        createdAt: t.Date()
    })
    export type Response = typeof response.static

    export const listResponse = paginatedResponse(response)
}
