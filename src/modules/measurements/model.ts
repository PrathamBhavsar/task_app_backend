import { t } from 'elysia'
import { paginatedResponse } from '@/core/utils/pagination'

export namespace MeasurementModel {
    export const create = t.Object({
        taskId: t.Number(),
        location: t.Optional(t.String()),
        width: t.Optional(t.String()),
        height: t.Optional(t.String()),
        area: t.Optional(t.String()),
        unit: t.Optional(t.String()),
        quantity: t.Optional(t.Number()),
        unitPrice: t.Optional(t.String()),
        discount: t.Optional(t.String()),
        totalPrice: t.Optional(t.String()),
        notes: t.Optional(t.String())
    })
    export type Create = typeof create.static

    export const update = t.Partial(t.Omit(create, ['taskId']))
    export type Update = typeof update.static

    export const response = t.Object({
        measurementId: t.Number(),
        taskId: t.Number(),
        location: t.Union([t.String(), t.Null()]),
        width: t.Union([t.String(), t.Null()]),
        height: t.Union([t.String(), t.Null()]),
        area: t.String(),
        unit: t.String(),
        quantity: t.Number(),
        unitPrice: t.String(),
        discount: t.String(),
        totalPrice: t.String(),
        notes: t.Union([t.String(), t.Null()])
    })
    export type Response = typeof response.static

    export const listResponse = paginatedResponse(response)
}
