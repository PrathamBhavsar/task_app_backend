import { t } from 'elysia'
import { paginatedResponse } from '@/core/utils/pagination'

export namespace TaskServiceModel {
    export const create = t.Object({
        taskId: t.Number(),
        serviceMasterId: t.Number(),
        quantity: t.Number(),
        unitPrice: t.String(),
        totalAmount: t.String()
    })
    export type Create = typeof create.static

    export const update = t.Partial(t.Omit(create, ['taskId']))
    export type Update = typeof update.static

    export const response = t.Object({
        taskServiceId: t.Number(),
        taskId: t.Number(),
        serviceMasterId: t.Number(),
        quantity: t.Number(),
        unitPrice: t.String(),
        totalAmount: t.String()
    })
    export type Response = typeof response.static

    export const listResponse = paginatedResponse(response)
}
