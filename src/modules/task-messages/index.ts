import { Elysia, t } from 'elysia'
import { TaskMessageService } from './service'
import { TaskMessageModel } from './model'
import { paginationQuery, parsePagination, formatPaginatedResponse } from '@/core/utils/pagination'
import { successResponse } from '@/core/utils/response'
import { requireAuth } from '@/core/middleware/auth'

export const taskMessageController = new Elysia({ prefix: '/task-messages', name: 'TaskMessages', detail: { tags: ['Task Messages'] } })
    .use(requireAuth)
    .get('/', async ({ query }) => {
        const { limit, offset } = parsePagination(query)
        const data = await TaskMessageService.getAll(limit, offset)
        const total = await TaskMessageService.countAll()
        return formatPaginatedResponse(data, total, limit, offset, 'Messages retrieved')
    }, { query: paginationQuery, detail: { tags: ['Task Messages'] } })
    .get('/task/:taskId', async ({ params: { taskId }, query }) => {
        const { limit, offset } = parsePagination(query)
        const data = await TaskMessageService.getByTaskId(Number(taskId), limit, offset)
        const total = await TaskMessageService.countByTaskId(Number(taskId))
        return formatPaginatedResponse(data, total, limit, offset, 'Messages retrieved')
    }, { params: t.Object({ taskId: t.Numeric() }), query: paginationQuery, detail: { tags: ['Task Messages'] } })
    .get('/:id', async ({ params: { id } }) => {
        const data = await TaskMessageService.getById(Number(id))
        return successResponse(data, 'Message retrieved')
    }, { params: t.Object({ id: t.Numeric() }), detail: { tags: ['Task Messages'] } })
    .post('/', async ({ body, set, user }) => {
        set.status = 201
        const data = await TaskMessageService.create(body, user!.userId)
        return successResponse(data, 'Message created')
    }, { body: TaskMessageModel.create, detail: { tags: ['Task Messages'] } })
    .patch('/:id', async ({ params: { id }, body }) => {
        const data = await TaskMessageService.update(Number(id), body)
        return successResponse(data, 'Message updated')
    }, { params: t.Object({ id: t.Numeric() }), body: TaskMessageModel.update, detail: { tags: ['Task Messages'] } })
    .delete('/:id', async ({ params: { id } }) => {
        await TaskMessageService.delete(Number(id))
        return successResponse(null, 'Message deleted')
    }, { params: t.Object({ id: t.Numeric() }), detail: { tags: ['Task Messages'] } })
