import { db } from '@/core/db'
import { users, userTokens, userSessions } from '@/core/db/schema'
import { eq, and, gt } from 'drizzle-orm'
import { UnauthorizedError, ValidationError, DuplicateError } from '@/core/utils/errors'
import type { AuthModel } from './model'

export abstract class AuthService {
    static async login(data: AuthModel.Login, jwt: any, ipAddress?: string, userAgent?: string) {
        const user = await db.select().from(users).where(eq(users.email, data.email)).limit(1)
        if (!user.length) throw new UnauthorizedError('Invalid credentials')
        const isValid = await Bun.password.verify(data.password, user[0].password || '')
        if (!isValid) throw new UnauthorizedError('Invalid credentials')
        if (!user[0].isActive) throw new UnauthorizedError('Account is disabled')
        const accessTokenExpiry = new Date(Date.now() + (parseInt(process.env.JWT_EXPIRY || '36000') * 1000))
        const refreshTokenExpiry = new Date(Date.now() + (parseInt(process.env.JWT_REFRESH_EXPIRY || '604800') * 1000))
        const accessToken = await jwt.sign({ userId: user[0].userId, role: user[0].role })
        const refreshToken = await jwt.sign({ userId: user[0].userId, type: 'refresh' })
        const [token] = await db.insert(userTokens).values({
            userId: user[0].userId,
            accessToken,
            refreshToken,
            accessTokenExpiresAt: accessTokenExpiry,
            refreshTokenExpiresAt: refreshTokenExpiry,
            ipAddress,
            userAgent
        }).returning()
        await db.insert(userSessions).values({
            userId: user[0].userId,
            tokenId: token.tokenId,
            ipAddress,
            userAgent,
            deviceInfo: { userAgent }
        })
        await db.update(users).set({ lastLoginAt: new Date() }).where(eq(users.userId, user[0].userId))
        return {
            accessToken,
            refreshToken,
            expiresIn: parseInt(process.env.JWT_EXPIRY || '36000'),
            user: { userId: user[0].userId, name: user[0].name, email: user[0].email, role: user[0].role }
        }
    }

    static async register(data: AuthModel.Register) {
        const existing = await db.select().from(users).where(eq(users.email, data.email)).limit(1)
        if (existing.length) throw new DuplicateError('Email already registered')
        const hashedPassword = await Bun.password.hash(data.password)
        const [user] = await db.insert(users).values({
            name: data.name,
            email: data.email,
            password: hashedPassword,
            contactNo: data.contactNo,
            address: data.address,
            role: data.role || 'agent'
        }).returning()
        return { userId: user.userId, name: user.name, email: user.email, role: user.role }
    }

    static async refreshToken(refreshTokenStr: string, jwt: any) {
        const token = await db.select().from(userTokens)
            .where(and(eq(userTokens.refreshToken, refreshTokenStr), eq(userTokens.isRevoked, false), gt(userTokens.refreshTokenExpiresAt, new Date())))
            .limit(1)
        if (!token.length) throw new UnauthorizedError('Invalid refresh token')
        const user = await db.select().from(users).where(eq(users.userId, token[0].userId)).limit(1)
        if (!user.length || !user[0].isActive) throw new UnauthorizedError('User not found or disabled')
        await db.update(userTokens).set({ isRevoked: true }).where(eq(userTokens.tokenId, token[0].tokenId))
        const accessTokenExpiry = new Date(Date.now() + (parseInt(process.env.JWT_EXPIRY || '36000') * 1000))
        const refreshTokenExpiry = new Date(Date.now() + (parseInt(process.env.JWT_REFRESH_EXPIRY || '604800') * 1000))
        const newAccessToken = await jwt.sign({ userId: user[0].userId, role: user[0].role })
        const newRefreshToken = await jwt.sign({ userId: user[0].userId, type: 'refresh' })
        await db.insert(userTokens).values({
            userId: user[0].userId,
            accessToken: newAccessToken,
            refreshToken: newRefreshToken,
            accessTokenExpiresAt: accessTokenExpiry,
            refreshTokenExpiresAt: refreshTokenExpiry
        })
        return {
            accessToken: newAccessToken,
            refreshToken: newRefreshToken,
            expiresIn: parseInt(process.env.JWT_EXPIRY || '36000'),
            user: { userId: user[0].userId, name: user[0].name, email: user[0].email, role: user[0].role }
        }
    }

    static async logout(accessToken: string) {
        await db.update(userTokens).set({ isRevoked: true }).where(eq(userTokens.accessToken, accessToken))
        return { message: 'Logged out successfully' }
    }
}
