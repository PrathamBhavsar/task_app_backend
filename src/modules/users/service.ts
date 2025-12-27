import { db } from '@/core/db'
import { users } from '@/core/db/schema'
import { eq, sql, or, ilike } from 'drizzle-orm'
import { NotFoundError, DuplicateError } from '@/core/utils/errors'
import type { UserModel } from './model'

export abstract class UserService {
    static async getAll(limit: number, offset: number) {
        return await db.select({
            userId: users.userId,
            name: users.name,
            email: users.email,
            contactNo: users.contactNo,
            address: users.address,
            role: users.role,
            profileBgColor: users.profileBgColor,
            isActive: users.isActive,
            createdAt: users.createdAt,
            lastLoginAt: users.lastLoginAt
        }).from(users).limit(limit).offset(offset).orderBy(users.createdAt)
    }

    static async search(query: string) {
        const searchPattern = `%${query}%`
        return await db.select({
            userId: users.userId,
            name: users.name,
            email: users.email,
            contactNo: users.contactNo,
            address: users.address,
            role: users.role,
            profileBgColor: users.profileBgColor,
            isActive: users.isActive,
            createdAt: users.createdAt,
            lastLoginAt: users.lastLoginAt
        }).from(users)
            .where(or(
                ilike(users.name, searchPattern),
                ilike(users.email, searchPattern),
                ilike(users.contactNo, searchPattern),
                ilike(users.role, searchPattern)
            ))
            .limit(5)
    }

    static async countAll(): Promise<number> {
        const result = await db.select({ count: sql<number>`count(*)` }).from(users)
        return Number(result[0].count)
    }

    static async getById(id: number) {
        const result = await db.select({
            userId: users.userId,
            name: users.name,
            email: users.email,
            contactNo: users.contactNo,
            address: users.address,
            role: users.role,
            profileBgColor: users.profileBgColor,
            isActive: users.isActive,
            createdAt: users.createdAt,
            lastLoginAt: users.lastLoginAt
        }).from(users).where(eq(users.userId, id)).limit(1)
        if (!result.length) throw new NotFoundError('User not found')
        return result[0]
    }

    static async create(data: UserModel.Create) {
        if (data.email) {
            const existing = await db.select().from(users).where(eq(users.email, data.email)).limit(1)
            if (existing.length) throw new DuplicateError('Email already exists')
        }
        const hashedPassword = data.password ? await Bun.password.hash(data.password) : null
        const [user] = await db.insert(users).values({ ...data, password: hashedPassword }).returning()
        return this.getById(user.userId)
    }

    static async update(id: number, data: UserModel.Update) {
        await this.getById(id)
        await db.update(users).set(data).where(eq(users.userId, id))
        return this.getById(id)
    }

    static async delete(id: number) {
        await this.getById(id)
        await db.delete(users).where(eq(users.userId, id))
    }
}
