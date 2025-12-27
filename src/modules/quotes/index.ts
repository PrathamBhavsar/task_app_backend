import { Elysia, t } from 'elysia'
import { QuoteService } from './service'
import { QuoteModel } from './model'
import { paginationQuery, parsePagination, formatPaginatedResponse } from '@/core/utils/pagination'
import { successResponse } from '@/core/utils/response'
import { requireAuth } from '@/core/middleware/auth'

export const quoteController = new Elysia({ prefix: '/quotes', name: 'Quotes' })
    .use(requireAuth)
    .get('/', async ({ query }) => {
        const { limit, offset } = parsePagination(query)
        const data = await QuoteService.getAll(limit, offset)
        const total = await QuoteService.countAll()
        return formatPaginatedResponse(data, total, limit, offset, 'Quotes retrieved')
    }, { query: paginationQuery })
    .get('/task/:taskId', async ({ params: { taskId } }) => {
        const data = await QuoteService.getByTaskId(Number(taskId))
        return successResponse(data, 'Quote retrieved')
    }, { params: t.Object({ taskId: t.Numeric() }) })
    .get('/:id', async ({ params: { id } }) => {
        const data = await QuoteService.getById(Number(id))
        return successResponse(data, 'Quote retrieved')
    }, { params: t.Object({ id: t.Numeric() }) })
    .post('/', async ({ body, set }) => {
        set.status = 201
        const data = await QuoteService.create(body)
        return successResponse(data, 'Quote created')
    }, { body: QuoteModel.create })
    .patch('/:id', async ({ params: { id }, body }) => {
        const data = await QuoteService.update(Number(id), body)
        return successResponse(data, 'Quote updated')
    }, { params: t.Object({ id: t.Numeric() }), body: QuoteModel.update })
    .delete('/:id', async ({ params: { id } }) => {
        await QuoteService.delete(Number(id))
        return successResponse(null, 'Quote deleted')
    }, { params: t.Object({ id: t.Numeric() }) })
