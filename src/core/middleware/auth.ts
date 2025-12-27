import { Elysia } from 'elysia'
import { jwt } from '@elysiajs/jwt'
import { bearer } from '@elysiajs/bearer'
import { UnauthorizedError } from '@/core/utils/errors'
import { db } from '@/core/db'
import { userTokens, users } from '@/core/db/schema'
import { eq, and, gt } from 'drizzle-orm'

export const authPlugin = new Elysia({ name: 'AuthPlugin' })
    .use(jwt({ name: 'jwt', secret: process.env.JWT_SECRET || 'secret' }))
    .use(bearer())
    .derive(async ({ jwt, bearer }) => {
        if (!bearer) return { user: null }
        try {
            const payload = await jwt.verify(bearer) as { userId: number; role: string } | false
            if (!payload) return { user: null }
            const token = await db.select().from(userTokens)
                .where(and(eq(userTokens.accessToken, bearer), eq(userTokens.isRevoked, false), gt(userTokens.accessTokenExpiresAt, new Date())))
                .limit(1)
            if (!token.length) return { user: null }
            const user = await db.select().from(users).where(eq(users.userId, payload.userId)).limit(1)
            if (!user.length || !user[0].isActive) return { user: null }
            return { user: { userId: user[0].userId, email: user[0].email, name: user[0].name, role: user[0].role } }
        } catch { return { user: null } }
    })

export const requireAuth = new Elysia({ name: 'RequireAuth' })
    .use(authPlugin)
    .onBeforeHandle(({ user }) => {
        if (!user) throw new UnauthorizedError('Authentication required')
    })

export const requireAdmin = new Elysia({ name: 'RequireAdmin' })
    .use(requireAuth)
    .onBeforeHandle(({ user }) => {
        if (user?.role !== 'admin') throw new UnauthorizedError('Admin access required')
    })

export const requireAdminOrSalesperson = new Elysia({ name: 'RequireAdminOrSalesperson' })
    .use(requireAuth)
    .onBeforeHandle(({ user }) => {
        if (user?.role !== 'admin' && user?.role !== 'salesperson') {
            throw new UnauthorizedError('Admin or Salesperson access required')
        }
    })
