import { Elysia, t } from 'elysia'
import { BillService } from './service'
import { BillModel } from './model'
import { paginationQuery, parsePagination, formatPaginatedResponse } from '@/core/utils/pagination'
import { successResponse } from '@/core/utils/response'
import { requireAuth } from '@/core/middleware/auth'

export const billController = new Elysia({ prefix: '/bills', name: 'Bills', detail: { tags: ['Bills'] } })
    .use(requireAuth)
    .get('/', async ({ query }) => {
        const { limit, offset } = parsePagination(query)
        const data = await BillService.getAll(limit, offset)
        const total = await BillService.countAll()
        return formatPaginatedResponse(data, total, limit, offset, 'Bills retrieved')
    }, { query: paginationQuery, detail: { tags: ['Bills'] } })
    .get('/task/:taskId', async ({ params: { taskId } }) => {
        const data = await BillService.getByTaskId(Number(taskId))
        return successResponse(data, 'Bill retrieved')
    }, { params: t.Object({ taskId: t.Numeric() }), detail: { tags: ['Bills'] } })
    .get('/:id', async ({ params: { id } }) => {
        const data = await BillService.getById(Number(id))
        return successResponse(data, 'Bill retrieved')
    }, { params: t.Object({ id: t.Numeric() }), detail: { tags: ['Bills'] } })
    .post('/', async ({ body, set }) => {
        set.status = 201
        const data = await BillService.create(body)
        return successResponse(data, 'Bill created')
    }, { body: BillModel.create, detail: { tags: ['Bills'] } })
    .patch('/:id', async ({ params: { id }, body }) => {
        const data = await BillService.update(Number(id), body)
        return successResponse(data, 'Bill updated')
    }, { params: t.Object({ id: t.Numeric() }), body: BillModel.update, detail: { tags: ['Bills'] } })
    .delete('/:id', async ({ params: { id } }) => {
        await BillService.delete(Number(id))
        return successResponse(null, 'Bill deleted')
    }, { params: t.Object({ id: t.Numeric() }), detail: { tags: ['Bills'] } })
