import { Elysia, t } from 'elysia'
import { UserService } from './service'
import { UserModel } from './model'
import { paginationQuery, parsePagination, formatPaginatedResponse, formatSearchResponse } from '@/core/utils/pagination'
import { successResponse } from '@/core/utils/response'
import { requireAdmin } from '@/core/middleware/auth'

export const userController = new Elysia({ prefix: '/users', name: 'Users', detail: { tags: ['Users'] } })
    .use(requireAdmin)
    .get('/', async ({ query }) => {
        const { limit, offset, search } = parsePagination(query)
        if (search) {
            const data = await UserService.search(search)
            return formatSearchResponse(data, 'Users search results')
        }
        const data = await UserService.getAll(limit, offset)
        const total = await UserService.countAll()
        return formatPaginatedResponse(data, total, limit, offset, 'Users retrieved')
    }, { query: paginationQuery, detail: { tags: ['Users'] } })
    .get('/:id', async ({ params: { id } }) => {
        const data = await UserService.getById(Number(id))
        return successResponse(data, 'User retrieved')
    }, { params: t.Object({ id: t.Numeric() }), detail: { tags: ['Users'] } })
    .post('/', async ({ body, set }) => {
        set.status = 201
        const data = await UserService.create(body)
        return successResponse(data, 'User created')
    }, { body: UserModel.create, detail: { tags: ['Users'] } })
    .patch('/:id', async ({ params: { id }, body }) => {
        const data = await UserService.update(Number(id), body)
        return successResponse(data, 'User updated')
    }, { params: t.Object({ id: t.Numeric() }), body: UserModel.update, detail: { tags: ['Users'] } })
    .delete('/:id', async ({ params: { id } }) => {
        await UserService.delete(Number(id))
        return successResponse(null, 'User deleted')
    }, { params: t.Object({ id: t.Numeric() }), detail: { tags: ['Users'] } })
