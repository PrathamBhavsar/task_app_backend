import { Elysia, t } from 'elysia'
import { TaskServiceService } from './service'
import { TaskServiceModel } from './model'
import { paginationQuery, parsePagination, formatPaginatedResponse } from '@/core/utils/pagination'
import { successResponse } from '@/core/utils/response'
import { requireAuth } from '@/core/middleware/auth'

export const taskServiceController = new Elysia({ prefix: '/task-services', name: 'TaskServices' })
    .use(requireAuth)
    .get('/', async ({ query }) => {
        const { limit, offset } = parsePagination(query)
        const data = await TaskServiceService.getAll(limit, offset)
        const total = await TaskServiceService.countAll()
        return formatPaginatedResponse(data, total, limit, offset, 'Task services retrieved')
    }, { query: paginationQuery })
    .get('/task/:taskId', async ({ params: { taskId }, query }) => {
        const { limit, offset } = parsePagination(query)
        const data = await TaskServiceService.getByTaskId(Number(taskId), limit, offset)
        const total = await TaskServiceService.countByTaskId(Number(taskId))
        return formatPaginatedResponse(data, total, limit, offset, 'Task services retrieved')
    }, { params: t.Object({ taskId: t.Numeric() }), query: paginationQuery })
    .get('/:id', async ({ params: { id } }) => {
        const data = await TaskServiceService.getById(Number(id))
        return successResponse(data, 'Task service retrieved')
    }, { params: t.Object({ id: t.Numeric() }) })
    .post('/', async ({ body, set }) => {
        set.status = 201
        const data = await TaskServiceService.create(body)
        return successResponse(data, 'Task service created')
    }, { body: TaskServiceModel.create })
    .patch('/:id', async ({ params: { id }, body }) => {
        const data = await TaskServiceService.update(Number(id), body)
        return successResponse(data, 'Task service updated')
    }, { params: t.Object({ id: t.Numeric() }), body: TaskServiceModel.update })
    .delete('/:id', async ({ params: { id } }) => {
        await TaskServiceService.delete(Number(id))
        return successResponse(null, 'Task service deleted')
    }, { params: t.Object({ id: t.Numeric() }) })
