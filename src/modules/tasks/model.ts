import { t } from 'elysia'
import { paginatedResponse } from '@/core/utils/pagination'

export namespace TaskModel {
    export const create = t.Object({
        name: t.String({ minLength: 1 }),
        startDate: t.Optional(t.String()),
        dueDate: t.Optional(t.String()),
        priority: t.Optional(t.Union([t.Literal('Low'), t.Literal('Medium'), t.Literal('High'), t.Literal('Urgent')])),
        remarks: t.Optional(t.String()),
        status: t.Optional(t.Union([t.Literal('Created'), t.Literal('Measurement: Done'), t.Literal('Quote: Done'), t.Literal('Approved'), t.Literal('In Progress'), t.Literal('Completed'), t.Literal('Cancelled')])),
        clientId: t.Optional(t.Number()),
        designerId: t.Optional(t.Number()),
        agencyId: t.Optional(t.Number())
    })
    export type Create = typeof create.static

    export const update = t.Partial(create)
    export type Update = typeof update.static

    export const updateStatus = t.Object({
        status: t.Union([t.Literal('Created'), t.Literal('Measurement: Done'), t.Literal('Quote: Done'), t.Literal('Approved'), t.Literal('In Progress'), t.Literal('Completed'), t.Literal('Cancelled')])
    })
    export type UpdateStatus = typeof updateStatus.static

    export const response = t.Object({
        taskId: t.Number(),
        dealNo: t.Union([t.String(), t.Null()]),
        name: t.String(),
        createdAt: t.Date(),
        startDate: t.Union([t.String(), t.Null()]),
        dueDate: t.Union([t.String(), t.Null()]),
        priority: t.Union([t.String(), t.Null()]),
        remarks: t.Union([t.String(), t.Null()]),
        status: t.Union([t.String(), t.Null()]),
        createdBy: t.Union([t.Number(), t.Null()]),
        clientId: t.Union([t.Number(), t.Null()]),
        designerId: t.Union([t.Number(), t.Null()]),
        agencyId: t.Union([t.Number(), t.Null()])
    })
    export type Response = typeof response.static

    export const listResponse = paginatedResponse(response)
}
