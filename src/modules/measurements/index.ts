import { Elysia, t } from 'elysia'
import { MeasurementService } from './service'
import { MeasurementModel } from './model'
import { paginationQuery, parsePagination, formatPaginatedResponse } from '@/core/utils/pagination'
import { successResponse } from '@/core/utils/response'
import { requireAuth } from '@/core/middleware/auth'

export const measurementController = new Elysia({ prefix: '/measurements', name: 'Measurements', detail: { tags: ['Measurements'] } })
    .use(requireAuth)
    .get('/', async ({ query }) => {
        const { limit, offset } = parsePagination(query)
        const data = await MeasurementService.getAll(limit, offset)
        const total = await MeasurementService.countAll()
        return formatPaginatedResponse(data, total, limit, offset, 'Measurements retrieved')
    }, { query: paginationQuery, detail: { tags: ['Measurements'] } })
    .get('/task/:taskId', async ({ params: { taskId }, query }) => {
        const { limit, offset } = parsePagination(query)
        const data = await MeasurementService.getByTaskId(Number(taskId), limit, offset)
        const total = await MeasurementService.countByTaskId(Number(taskId))
        return formatPaginatedResponse(data, total, limit, offset, 'Measurements retrieved')
    }, { params: t.Object({ taskId: t.Numeric() }), query: paginationQuery, detail: { tags: ['Measurements'] } })
    .get('/:id', async ({ params: { id } }) => {
        const data = await MeasurementService.getById(Number(id))
        return successResponse(data, 'Measurement retrieved')
    }, { params: t.Object({ id: t.Numeric() }), detail: { tags: ['Measurements'] } })
    .post('/', async ({ body, set }) => {
        set.status = 201
        const data = await MeasurementService.create(body)
        return successResponse(data, 'Measurement created')
    }, { body: MeasurementModel.create, detail: { tags: ['Measurements'] } })
    .patch('/:id', async ({ params: { id }, body }) => {
        const data = await MeasurementService.update(Number(id), body)
        return successResponse(data, 'Measurement updated')
    }, { params: t.Object({ id: t.Numeric() }), body: MeasurementModel.update, detail: { tags: ['Measurements'] } })
    .delete('/:id', async ({ params: { id } }) => {
        await MeasurementService.delete(Number(id))
        return successResponse(null, 'Measurement deleted')
    }, { params: t.Object({ id: t.Numeric() }), detail: { tags: ['Measurements'] } })
