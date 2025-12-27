import { Elysia, t } from 'elysia'
import { DesignerService } from './service'
import { DesignerModel } from './model'
import { paginationQuery, parsePagination, formatPaginatedResponse, formatSearchResponse } from '@/core/utils/pagination'
import { successResponse } from '@/core/utils/response'
import { requireAdminOrSalesperson } from '@/core/middleware/auth'

export const designerController = new Elysia({ prefix: '/designers', name: 'Designers', detail: { tags: ['Designers'] } })
    .use(requireAdminOrSalesperson)
    .get('/', async ({ query }) => {
        const { limit, offset, search } = parsePagination(query)
        if (search) {
            const data = await DesignerService.search(search)
            return formatSearchResponse(data, 'Designers search results')
        }
        const data = await DesignerService.getAll(limit, offset)
        const total = await DesignerService.countAll()
        return formatPaginatedResponse(data, total, limit, offset, 'Designers retrieved')
    }, { query: paginationQuery, detail: { tags: ['Designers'] } })
    .get('/:id', async ({ params: { id } }) => {
        const data = await DesignerService.getById(Number(id))
        return successResponse(data, 'Designer retrieved')
    }, { params: t.Object({ id: t.Numeric() }), detail: { tags: ['Designers'] } })
    .post('/', async ({ body, set }) => {
        set.status = 201
        const data = await DesignerService.create(body)
        return successResponse(data, 'Designer created')
    }, { body: DesignerModel.create, detail: { tags: ['Designers'] } })
    .patch('/:id', async ({ params: { id }, body }) => {
        const data = await DesignerService.update(Number(id), body)
        return successResponse(data, 'Designer updated')
    }, { params: t.Object({ id: t.Numeric() }), body: DesignerModel.update, detail: { tags: ['Designers'] } })
    .delete('/:id', async ({ params: { id } }) => {
        await DesignerService.delete(Number(id))
        return successResponse(null, 'Designer deleted')
    }, { params: t.Object({ id: t.Numeric() }), detail: { tags: ['Designers'] } })
