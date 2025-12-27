import { Elysia } from 'elysia'
import { DashboardService } from './service'
import { successResponse } from '@/core/utils/response'
import { requireAuth } from '@/core/middleware/auth'

export const dashboardController = new Elysia({ prefix: '/dashboard', name: 'Dashboard', detail: { tags: ['Dashboard'] } })
    .use(requireAuth)
    .get('/overview', async () => {
        const data = await DashboardService.getOverview()
        return successResponse(data, 'Dashboard overview retrieved')
    }, { detail: { tags: ['Dashboard'] } })