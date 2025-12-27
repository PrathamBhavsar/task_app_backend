import { Elysia, t } from 'elysia'
import { UserService } from './service'
import { UserModel } from './model'
import { paginationQuery, parsePagination, formatPaginatedResponse } from '@/core/utils/pagination'
import { successResponse } from '@/core/utils/response'
import { requireAuth } from '@/core/middleware/auth'

export const userController = new Elysia({ prefix: '/users', name: 'Users' })
    .use(requireAuth)
    .get('/', async ({ query }) => {
        const { limit, offset } = parsePagination(query)
        const data = await UserService.getAll(limit, offset)
        const total = await UserService.countAll()
        return formatPaginatedResponse(data, total, limit, offset, 'Users retrieved')
    }, { query: paginationQuery })
    .get('/:id', async ({ params: { id } }) => {
        const data = await UserService.getById(Number(id))
        return successResponse(data, 'User retrieved')
    }, { params: t.Object({ id: t.Numeric() }) })
    .post('/', async ({ body, set }) => {
        set.status = 201
        const data = await UserService.create(body)
        return successResponse(data, 'User created')
    }, { body: UserModel.create })
    .patch('/:id', async ({ params: { id }, body }) => {
        const data = await UserService.update(Number(id), body)
        return successResponse(data, 'User updated')
    }, { params: t.Object({ id: t.Numeric() }), body: UserModel.update })
    .delete('/:id', async ({ params: { id } }) => {
        await UserService.delete(Number(id))
        return successResponse(null, 'User deleted')
    }, { params: t.Object({ id: t.Numeric() }) })
