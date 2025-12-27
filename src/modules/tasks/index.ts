import { Elysia, t } from 'elysia'
import { TaskService } from './service'
import { TaskModel } from './model'
import { paginationQuery, parsePagination, formatPaginatedResponse } from '@/core/utils/pagination'
import { successResponse } from '@/core/utils/response'
import { requireAuth } from '@/core/middleware/auth'

export const taskController = new Elysia({ prefix: '/tasks', name: 'Tasks' })
    .use(requireAuth)
    .get('/', async ({ query }) => {
        const { limit, offset } = parsePagination(query)
        const data = await TaskService.getAll(limit, offset)
        const total = await TaskService.countAll()
        return formatPaginatedResponse(data, total, limit, offset, 'Tasks retrieved')
    }, { query: paginationQuery })
    .get('/:id', async ({ params: { id } }) => {
        const data = await TaskService.getById(Number(id))
        return successResponse(data, 'Task retrieved')
    }, { params: t.Object({ id: t.Numeric() }) })
    .post('/', async ({ body, set, user }) => {
        set.status = 201
        const data = await TaskService.create(body, user!.userId)
        return successResponse(data, 'Task created')
    }, { body: TaskModel.create })
    .patch('/:id', async ({ params: { id }, body }) => {
        const data = await TaskService.update(Number(id), body)
        return successResponse(data, 'Task updated')
    }, { params: t.Object({ id: t.Numeric() }), body: TaskModel.update })
    .patch('/:id/status', async ({ params: { id }, body, user }) => {
        const data = await TaskService.updateStatus(Number(id), body.status, user!.userId)
        return successResponse(data, 'Task status updated')
    }, { params: t.Object({ id: t.Numeric() }), body: TaskModel.updateStatus })
    .delete('/:id', async ({ params: { id } }) => {
        await TaskService.delete(Number(id))
        return successResponse(null, 'Task deleted')
    }, { params: t.Object({ id: t.Numeric() }) })
