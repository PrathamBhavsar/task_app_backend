import { t } from 'elysia'
import { paginatedResponse } from '@/core/utils/pagination'

export namespace ConfigModel {
    export const create = t.Object({
        key: t.String({ minLength: 1 }),
        value: t.String()
    })
    export type Create = typeof create.static

    export const update = t.Object({
        value: t.String()
    })
    export type Update = typeof update.static

    export const response = t.Object({
        key: t.String(),
        value: t.String()
    })
    export type Response = typeof response.static

    export const listResponse = paginatedResponse(response)
}
