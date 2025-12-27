import { t } from 'elysia'

export const PAGINATION_DEFAULTS = {
    LIMIT: 20,
    LIMIT_MIN: 1,
    LIMIT_MAX: 100,
    OFFSET_MIN: 0,
    SEARCH_LIMIT: 5
}

export const paginationQuery = t.Object({
    limit: t.Optional(t.Numeric({ minimum: PAGINATION_DEFAULTS.LIMIT_MIN, maximum: PAGINATION_DEFAULTS.LIMIT_MAX })),
    offset: t.Optional(t.Numeric({ minimum: PAGINATION_DEFAULTS.OFFSET_MIN })),
    search: t.Optional(t.String())
})

export type PaginationQuery = typeof paginationQuery.static

export interface PaginationParams {
    limit: number
    offset: number
    page: number
    search?: string
}

export interface PaginationMeta {
    limit: number
    offset: number
    page: number
    total: number
    totalPages: number
    hasNextPage: boolean
    hasPreviousPage: boolean
}

export interface PaginatedResponse<T> {
    status: 'success'
    data: T[]
    pagination: PaginationMeta
    message: string
}

export interface SearchResponse<T> {
    status: 'success'
    data: T[]
    message: string
}

export function parsePagination(query: Partial<PaginationQuery>): PaginationParams {
    let limit = query.limit ?? PAGINATION_DEFAULTS.LIMIT
    let offset = query.offset ?? PAGINATION_DEFAULTS.OFFSET_MIN
    if (limit < PAGINATION_DEFAULTS.LIMIT_MIN) limit = PAGINATION_DEFAULTS.LIMIT_MIN
    if (limit > PAGINATION_DEFAULTS.LIMIT_MAX) limit = PAGINATION_DEFAULTS.LIMIT_MAX
    if (offset < PAGINATION_DEFAULTS.OFFSET_MIN) offset = PAGINATION_DEFAULTS.OFFSET_MIN
    const page = Math.floor(offset / limit) + 1
    return { limit, offset, page, search: query.search }
}

export function calculatePaginationMeta(limit: number, offset: number, total: number): PaginationMeta {
    const page = Math.floor(offset / limit) + 1
    const totalPages = Math.ceil(total / limit)
    const hasNextPage = offset + limit < total
    const hasPreviousPage = offset > 0
    return { limit, offset, page, total, totalPages, hasNextPage, hasPreviousPage }
}

export function formatPaginatedResponse<T>(data: T[], total: number, limit: number, offset: number, message: string = 'Data retrieved'): PaginatedResponse<T> {
    const pagination = calculatePaginationMeta(limit, offset, total)
    return { status: 'success', data, pagination, message }
}

export function formatSearchResponse<T>(data: T[], message: string = 'Search results'): SearchResponse<T> {
    return { status: 'success', data, message }
}

export function paginatedResponse<T extends any>(dataModel: T) {
    return t.Object({
        status: t.Literal('success'),
        data: t.Array(dataModel),
        pagination: t.Object({
            limit: t.Number(),
            offset: t.Number(),
            page: t.Number(),
            total: t.Number(),
            totalPages: t.Number(),
            hasNextPage: t.Boolean(),
            hasPreviousPage: t.Boolean()
        }),
        message: t.String()
    })
}
