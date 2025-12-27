import { t } from 'elysia'
import { paginatedResponse } from '@/core/utils/pagination'

export namespace QuoteModel {
    export const create = t.Object({
        taskId: t.Number(),
        subtotal: t.String(),
        tax: t.String(),
        total: t.String(),
        notes: t.Optional(t.String())
    })
    export type Create = typeof create.static

    export const update = t.Partial(t.Omit(create, ['taskId']))
    export type Update = typeof update.static

    export const response = t.Object({
        quoteId: t.Number(),
        taskId: t.Number(),
        createdAt: t.Date(),
        subtotal: t.String(),
        tax: t.String(),
        total: t.String(),
        notes: t.Union([t.String(), t.Null()])
    })
    export type Response = typeof response.static

    export const listResponse = paginatedResponse(response)
}
