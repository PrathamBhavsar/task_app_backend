import { t } from 'elysia'
import { paginatedResponse } from '@/core/utils/pagination'

export namespace ClientModel {
    export const create = t.Object({
        name: t.String({ minLength: 1 }),
        contactNo: t.Optional(t.String()),
        email: t.Optional(t.String({ format: 'email' })),
        address: t.Optional(t.String())
    })
    export type Create = typeof create.static

    export const update = t.Partial(create)
    export type Update = typeof update.static

    export const response = t.Object({
        clientId: t.Number(),
        name: t.String(),
        contactNo: t.Union([t.String(), t.Null()]),
        email: t.Union([t.String(), t.Null()]),
        address: t.Union([t.String(), t.Null()]),
        createdAt: t.Date()
    })
    export type Response = typeof response.static

    export const listResponse = paginatedResponse(response)
}
