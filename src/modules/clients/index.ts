import { Elysia, t } from 'elysia'
import { ClientService } from './service'
import { ClientModel } from './model'
import { paginationQuery, parsePagination, formatPaginatedResponse, formatSearchResponse } from '@/core/utils/pagination'
import { successResponse } from '@/core/utils/response'
import { requireAdminOrSalesperson } from '@/core/middleware/auth'

export const clientController = new Elysia({ prefix: '/clients', name: 'Clients', detail: { tags: ['Clients'] } })
    .use(requireAdminOrSalesperson)
    .get('/', async ({ query }) => {
        const { limit, offset, search } = parsePagination(query)
        if (search) {
            const data = await ClientService.search(search)
            return formatSearchResponse(data, 'Clients search results')
        }
        const data = await ClientService.getAll(limit, offset)
        const total = await ClientService.countAll()
        return formatPaginatedResponse(data, total, limit, offset, 'Clients retrieved')
    }, { query: paginationQuery, detail: { tags: ['Clients'] } })
    .get('/:id', async ({ params: { id } }) => {
        const data = await ClientService.getById(Number(id))
        return successResponse(data, 'Client retrieved')
    }, { params: t.Object({ id: t.Numeric() }), detail: { tags: ['Clients'] } })
    .post('/', async ({ body, set }) => {
        set.status = 201
        const data = await ClientService.create(body)
        return successResponse(data, 'Client created')
    }, { body: ClientModel.create, detail: { tags: ['Clients'] } })
    .patch('/:id', async ({ params: { id }, body }) => {
        const data = await ClientService.update(Number(id), body)
        return successResponse(data, 'Client updated')
    }, { params: t.Object({ id: t.Numeric() }), body: ClientModel.update, detail: { tags: ['Clients'] } })
    .delete('/:id', async ({ params: { id } }) => {
        await ClientService.delete(Number(id))
        return successResponse(null, 'Client deleted')
    }, { params: t.Object({ id: t.Numeric() }), detail: { tags: ['Clients'] } })
