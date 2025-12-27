import { db } from '@/core/db'
import { designers } from '@/core/db/schema'
import { eq, sql, or, ilike } from 'drizzle-orm'
import { NotFoundError } from '@/core/utils/errors'
import type { DesignerModel } from './model'

export abstract class DesignerService {
    static async getAll(limit: number, offset: number) {
        return await db.select().from(designers).limit(limit).offset(offset).orderBy(designers.createdAt)
    }

    static async search(query: string) {
        const searchPattern = `%${query}%`
        return await db.select().from(designers)
            .where(or(
                ilike(designers.name, searchPattern),
                ilike(designers.firmName, searchPattern),
                ilike(designers.contactNo, searchPattern),
                ilike(designers.address, searchPattern)
            ))
            .limit(5)
    }

    static async countAll(): Promise<number> {
        const result = await db.select({ count: sql<number>`count(*)` }).from(designers)
        return Number(result[0].count)
    }

    static async getById(id: number) {
        const result = await db.select().from(designers).where(eq(designers.designerId, id)).limit(1)
        if (!result.length) throw new NotFoundError('Designer not found')
        return result[0]
    }

    static async create(data: DesignerModel.Create) {
        const [designer] = await db.insert(designers).values(data).returning()
        return designer
    }

    static async update(id: number, data: DesignerModel.Update) {
        await this.getById(id)
        const [designer] = await db.update(designers).set(data).where(eq(designers.designerId, id)).returning()
        return designer
    }

    static async delete(id: number) {
        await this.getById(id)
        await db.delete(designers).where(eq(designers.designerId, id))
    }
}
