import { Elysia, t } from 'elysia'
import { jwt } from '@elysiajs/jwt'
import { bearer } from '@elysiajs/bearer'
import { AuthService } from './service'
import { AuthModel } from './model'
import { successResponse } from '@/core/utils/response'

export const authController = new Elysia({ prefix: '/auth', name: 'Auth' })
    .use(jwt({ name: 'jwt', secret: process.env.JWT_SECRET || 'secret' }))
    .use(bearer())
    .post('/login', async ({ body, jwt, request }) => {
        const result = await AuthService.login(body, jwt, request.headers.get('x-forwarded-for') || undefined, request.headers.get('user-agent') || undefined)
        return successResponse(result, 'Login successful')
    }, { body: AuthModel.login })
    .post('/register', async ({ body, set }) => {
        set.status = 201
        const result = await AuthService.register(body)
        return successResponse(result, 'Registration successful')
    }, { body: AuthModel.register })
    .post('/refresh', async ({ body, jwt }) => {
        const result = await AuthService.refreshToken(body.refreshToken, jwt)
        return successResponse(result, 'Token refreshed')
    }, { body: AuthModel.refreshToken })
    .post('/logout', async ({ bearer }) => {
        if (bearer) await AuthService.logout(bearer)
        return successResponse(null, 'Logged out successfully')
    })
