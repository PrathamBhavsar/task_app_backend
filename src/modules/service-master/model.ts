import { t } from 'elysia'
import { paginatedResponse } from '@/core/utils/pagination'

export namespace ServiceMasterModel {
    export const create = t.Object({
        name: t.String({ minLength: 1 }),
        description: t.Optional(t.String()),
        defaultUnitPrice: t.Optional(t.String()),
        unit: t.Optional(t.String())
    })
    export type Create = typeof create.static

    export const update = t.Partial(create)
    export type Update = typeof update.static

    export const response = t.Object({
        serviceMasterId: t.Number(),
        name: t.String(),
        description: t.Union([t.String(), t.Null()]),
        defaultUnitPrice: t.String(),
        unit: t.Union([t.String(), t.Null()]),
        createdAt: t.Date()
    })
    export type Response = typeof response.static

    export const listResponse = paginatedResponse(response)
}
