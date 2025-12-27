import { db } from '@/core/db'
import { users, userTokens, userSessions } from '@/core/db/schema'
import { eq, and, gt, sql } from 'drizzle-orm'
import { UnauthorizedError, ValidationError, DuplicateError } from '@/core/utils/errors'
import type { AuthModel } from './model'

export abstract class AuthService {
    static async login(data: AuthModel.Login, jwt: any, ipAddress?: string, userAgent?: string) {
        try {
            // Use PostgreSQL's crypt function to verify password
            const user = await db.select().from(users)
                .where(and(
                    eq(users.email, data.email),
                    sql`${users.password} = crypt(${data.password}, ${users.password})`
                ))
                .limit(1)

            if (!user.length) throw new UnauthorizedError('Invalid credentials')
            if (!user[0].isActive) throw new UnauthorizedError('Account is disabled')

            // Revoke all existing tokens for this user to prevent duplicates
            await db.update(userTokens)
                .set({ isRevoked: true })
                .where(eq(userTokens.userId, user[0].userId))

            const jwtExpiry = parseInt(process.env.JWT_EXPIRY || '36000')
            const refreshExpiry = parseInt(process.env.JWT_REFRESH_EXPIRY || '604800')

            const accessTokenExpiry = new Date(Date.now() + (jwtExpiry * 1000))
            const refreshTokenExpiry = new Date(Date.now() + (refreshExpiry * 1000))

            // Add timestamp and random value to ensure unique tokens
            const timestamp = Date.now()
            const random = Math.random().toString(36).substring(2)

            const accessToken = await jwt.sign({
                userId: user[0].userId,
                role: user[0].role,
                iat: timestamp
            })
            const refreshToken = await jwt.sign({
                userId: user[0].userId,
                type: 'refresh',
                iat: timestamp,
                nonce: random
            })

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
                expiresIn: jwtExpiry,
                user: {
                    userId: user[0].userId,
                    name: user[0].name,
                    email: user[0].email,
                    role: user[0].role
                }
            }
        } catch (error) {
            console.error('Login error:', error)
            if (error instanceof UnauthorizedError) throw error
            throw new Error('Login failed')
        }
    }

    static async register(data: AuthModel.Register) {
        try {
            const existing = await db.select().from(users).where(eq(users.email, data.email)).limit(1)
            if (existing.length) throw new DuplicateError('Email already registered')

            // Use PostgreSQL's crypt function for password hashing
            const [user] = await db.insert(users).values({
                name: data.name,
                email: data.email,
                password: sql`crypt(${data.password}, gen_salt('bf'))`,
                contactNo: data.contactNo,
                address: data.address,
                role: data.role || 'agent'
            }).returning()

            return {
                userId: user.userId,
                name: user.name,
                email: user.email,
                role: user.role
            }
        } catch (error) {
            console.error('Registration error:', error)
            if (error instanceof DuplicateError) throw error
            throw new Error('Registration failed')
        }
    }

    static async refreshToken(refreshTokenStr: string, jwt: any) {
        try {
            const token = await db.select().from(userTokens)
                .where(and(
                    eq(userTokens.refreshToken, refreshTokenStr),
                    eq(userTokens.isRevoked, false),
                    gt(userTokens.refreshTokenExpiresAt, new Date())
                ))
                .limit(1)

            if (!token.length) throw new UnauthorizedError('Invalid refresh token')

            const user = await db.select().from(users).where(eq(users.userId, token[0].userId)).limit(1)
            if (!user.length || !user[0].isActive) throw new UnauthorizedError('User not found or disabled')

            // Revoke old token
            await db.update(userTokens).set({ isRevoked: true }).where(eq(userTokens.tokenId, token[0].tokenId))

            const jwtExpiry = parseInt(process.env.JWT_EXPIRY || '36000')
            const refreshExpiry = parseInt(process.env.JWT_REFRESH_EXPIRY || '604800')

            const accessTokenExpiry = new Date(Date.now() + (jwtExpiry * 1000))
            const refreshTokenExpiry = new Date(Date.now() + (refreshExpiry * 1000))

            // Add timestamp and random value to ensure unique tokens
            const timestamp = Date.now()
            const random = Math.random().toString(36).substring(2)

            const newAccessToken = await jwt.sign({
                userId: user[0].userId,
                role: user[0].role,
                iat: timestamp
            })
            const newRefreshToken = await jwt.sign({
                userId: user[0].userId,
                type: 'refresh',
                iat: timestamp,
                nonce: random
            })

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
                expiresIn: jwtExpiry,
                user: {
                    userId: user[0].userId,
                    name: user[0].name,
                    email: user[0].email,
                    role: user[0].role
                }
            }
        } catch (error) {
            console.error('Refresh token error:', error)
            if (error instanceof UnauthorizedError) throw error
            throw new Error('Token refresh failed')
        }
    }

    static async logout(accessToken: string) {
        try {
            await db.update(userTokens).set({ isRevoked: true }).where(eq(userTokens.accessToken, accessToken))
            return { message: 'Logged out successfully' }
        } catch (error) {
            console.error('Logout error:', error)
            throw new Error('Logout failed')
        }
    }
}