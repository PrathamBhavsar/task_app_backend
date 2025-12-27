import { Elysia, t } from 'elysia'
import { ServiceMasterService } from './service'
import { ServiceMasterModel } from './model'
import { paginationQuery, parsePagination, formatPaginatedResponse } from '@/core/utils/pagination'
import { successResponse } from '@/core/utils/response'
import { requireAuth } from '@/core/middleware/auth'

export const serviceMasterController = new Elysia({ prefix: '/service-master', name: 'ServiceMaster', detail: { tags: ['Service Master'] } })
    .use(requireAuth)
    .get('/', async ({ query }) => {
        const { limit, offset } = parsePagination(query)
        const data = await ServiceMasterService.getAll(limit, offset)
        const total = await ServiceMasterService.countAll()
        return formatPaginatedResponse(data, total, limit, offset, 'Services retrieved')
    }, { query: paginationQuery, detail: { tags: ['Service Master'] } })
    .get('/:id', async ({ params: { id } }) => {
        const data = await ServiceMasterService.getById(Number(id))
        return successResponse(data, 'Service retrieved')
    }, { params: t.Object({ id: t.Numeric() }), detail: { tags: ['Service Master'] } })
    .post('/', async ({ body, set }) => {
        set.status = 201
        const data = await ServiceMasterService.create(body)
        return successResponse(data, 'Service created')
    }, { body: ServiceMasterModel.create, detail: { tags: ['Service Master'] } })
    .patch('/:id', async ({ params: { id }, body }) => {
        const data = await ServiceMasterService.update(Number(id), body)
        return successResponse(data, 'Service updated')
    }, { params: t.Object({ id: t.Numeric() }), body: ServiceMasterModel.update, detail: { tags: ['Service Master'] } })
    .delete('/:id', async ({ params: { id } }) => {
        await ServiceMasterService.delete(Number(id))
        return successResponse(null, 'Service deleted')
    }, { params: t.Object({ id: t.Numeric() }), detail: { tags: ['Service Master'] } })
