import { t } from 'elysia'
import { paginatedResponse } from '@/core/utils/pagination'

export namespace BillModel {
    export const create = t.Object({
        taskId: t.Number(),
        dueDate: t.Optional(t.String()),
        subtotal: t.String(),
        tax: t.String(),
        total: t.String(),
        additionalNotes: t.Optional(t.String()),
        status: t.Optional(t.Union([t.Literal('Pending'), t.Literal('Paid'), t.Literal('Partial'), t.Literal('Overdue')]))
    })
    export type Create = typeof create.static

    export const update = t.Partial(t.Omit(create, ['taskId']))
    export type Update = typeof update.static

    export const response = t.Object({
        billId: t.Number(),
        taskId: t.Number(),
        createdAt: t.Date(),
        dueDate: t.Union([t.String(), t.Null()]),
        subtotal: t.String(),
        tax: t.String(),
        total: t.String(),
        additionalNotes: t.Union([t.String(), t.Null()]),
        status: t.String()
    })
    export type Response = typeof response.static

    export const listResponse = paginatedResponse(response)
}
