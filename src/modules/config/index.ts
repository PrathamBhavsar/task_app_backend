import { Elysia, t } from 'elysia'
import { ConfigService } from './service'
import { ConfigModel } from './model'
import { paginationQuery, parsePagination, formatPaginatedResponse } from '@/core/utils/pagination'
import { successResponse } from '@/core/utils/response'
import { requireAdmin } from '@/core/middleware/auth'

export const configController = new Elysia({ prefix: '/config', name: 'Config' })
    .use(requireAdmin)
    .get('/', async ({ query }) => {
        const { limit, offset } = parsePagination(query)
        const data = await ConfigService.getAll(limit, offset)
        const total = await ConfigService.countAll()
        return formatPaginatedResponse(data, total, limit, offset, 'Config retrieved')
    }, { query: paginationQuery })
    .get('/:key', async ({ params: { key } }) => {
        const data = await ConfigService.getByKey(key)
        return successResponse(data, 'Config retrieved')
    }, { params: t.Object({ key: t.String() }) })
    .post('/', async ({ body, set }) => {
        set.status = 201
        const data = await ConfigService.create(body)
        return successResponse(data, 'Config created')
    }, { body: ConfigModel.create })
    .patch('/:key', async ({ params: { key }, body }) => {
        const data = await ConfigService.update(key, body)
        return successResponse(data, 'Config updated')
    }, { params: t.Object({ key: t.String() }), body: ConfigModel.update })
    .delete('/:key', async ({ params: { key } }) => {
        await ConfigService.delete(key)
        return successResponse(null, 'Config deleted')
    }, { params: t.Object({ key: t.String() }) })
