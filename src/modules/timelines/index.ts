import { Elysia, t } from 'elysia'
import { TimelineService } from './service'
import { TimelineModel } from './model'
import { paginationQuery, parsePagination, formatPaginatedResponse } from '@/core/utils/pagination'
import { successResponse } from '@/core/utils/response'
import { requireAuth } from '@/core/middleware/auth'

export const timelineController = new Elysia({ prefix: '/timelines', name: 'Timelines', detail: { tags: ['Timelines'] } })
    .use(requireAuth)
    .get('/', async ({ query }) => {
        const { limit, offset } = parsePagination(query)
        const data = await TimelineService.getAll(limit, offset)
        const total = await TimelineService.countAll()
        return formatPaginatedResponse(data, total, limit, offset, 'Timelines retrieved')
    }, { query: paginationQuery, detail: { tags: ['Timelines'] } })
    .get('/task/:taskId', async ({ params: { taskId }, query }) => {
        const { limit, offset } = parsePagination(query)
        const data = await TimelineService.getByTaskId(Number(taskId), limit, offset)
        const total = await TimelineService.countByTaskId(Number(taskId))
        return formatPaginatedResponse(data, total, limit, offset, 'Timelines retrieved')
    }, { params: t.Object({ taskId: t.Numeric() }), query: paginationQuery, detail: { tags: ['Timelines'] } })
    .get('/:id', async ({ params: { id } }) => {
        const data = await TimelineService.getById(Number(id))
        return successResponse(data, 'Timeline retrieved')
    }, { params: t.Object({ id: t.Numeric() }), detail: { tags: ['Timelines'] } })
    .post('/', async ({ body, set, user }) => {
        set.status = 201
        const data = await TimelineService.create(body, user!.userId)
        return successResponse(data, 'Timeline created')
    }, { body: TimelineModel.create, detail: { tags: ['Timelines'] } })
    .delete('/:id', async ({ params: { id } }) => {
        await TimelineService.delete(Number(id))
        return successResponse(null, 'Timeline deleted')
    }, { params: t.Object({ id: t.Numeric() }), detail: { tags: ['Timelines'] } })
