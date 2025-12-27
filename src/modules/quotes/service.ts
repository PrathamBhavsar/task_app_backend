import { db } from '@/core/db'
import { quotes } from '@/core/db/schema'
import { eq, sql } from 'drizzle-orm'
import { NotFoundError } from '@/core/utils/errors'
import type { QuoteModel } from './model'

export abstract class QuoteService {
    static async getAll(limit: number, offset: number) {
        return await db.select().from(quotes).limit(limit).offset(offset).orderBy(quotes.createdAt)
    }

    static async countAll(): Promise<number> {
        const result = await db.select({ count: sql<number>`count(*)` }).from(quotes)
        return Number(result[0].count)
    }

    static async getByTaskId(taskId: number) {
        const result = await db.select().from(quotes).where(eq(quotes.taskId, taskId)).limit(1)
        if (!result.length) throw new NotFoundError('Quote not found')
        return result[0]
    }

    static async getById(id: number) {
        const result = await db.select().from(quotes).where(eq(quotes.quoteId, id)).limit(1)
        if (!result.length) throw new NotFoundError('Quote not found')
        return result[0]
    }

    static async create(data: QuoteModel.Create) {
        const [quote] = await db.insert(quotes).values(data).returning()
        return quote
    }

    static async update(id: number, data: QuoteModel.Update) {
        await this.getById(id)
        const [quote] = await db.update(quotes).set(data).where(eq(quotes.quoteId, id)).returning()
        return quote
    }

    static async delete(id: number) {
        await this.getById(id)
        await db.delete(quotes).where(eq(quotes.quoteId, id))
    }
}
