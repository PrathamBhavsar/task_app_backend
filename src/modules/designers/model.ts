import { t } from 'elysia'
import { paginatedResponse } from '@/core/utils/pagination'

export namespace DesignerModel {
    export const create = t.Object({
        name: t.String({ minLength: 1 }),
        firmName: t.Optional(t.String()),
        contactNo: t.Optional(t.String()),
        address: t.Optional(t.String()),
        profileBgColor: t.Optional(t.String())
    })
    export type Create = typeof create.static

    export const update = t.Partial(create)
    export type Update = typeof update.static

    export const response = t.Object({
        designerId: t.Number(),
        name: t.String(),
        firmName: t.Union([t.String(), t.Null()]),
        contactNo: t.Union([t.String(), t.Null()]),
        address: t.Union([t.String(), t.Null()]),
        profileBgColor: t.Union([t.String(), t.Null()]),
        createdAt: t.Date()
    })
    export type Response = typeof response.static

    export const listResponse = paginatedResponse(response)
}
