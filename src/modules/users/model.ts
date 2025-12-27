import { t } from 'elysia'
import { paginatedResponse } from '@/core/utils/pagination'

export namespace UserModel {
    export const create = t.Object({
        name: t.String({ minLength: 1 }),
        email: t.Optional(t.String({ format: 'email' })),
        password: t.Optional(t.String({ minLength: 6 })),
        contactNo: t.Optional(t.String()),
        address: t.Optional(t.String()),
        role: t.Optional(t.Union([t.Literal('admin'), t.Literal('salesperson'), t.Literal('agent')])),
        profileBgColor: t.Optional(t.String()),
        isActive: t.Optional(t.Boolean())
    })
    export type Create = typeof create.static

    export const update = t.Partial(t.Omit(create, ['password']))
    export type Update = typeof update.static

    export const response = t.Object({
        userId: t.Number(),
        name: t.String(),
        email: t.Union([t.String(), t.Null()]),
        contactNo: t.Union([t.String(), t.Null()]),
        address: t.Union([t.String(), t.Null()]),
        role: t.String(),
        profileBgColor: t.Union([t.String(), t.Null()]),
        isActive: t.Boolean(),
        createdAt: t.Date(),
        lastLoginAt: t.Union([t.Date(), t.Null()])
    })
    export type Response = typeof response.static

    export const listResponse = paginatedResponse(response)
}
