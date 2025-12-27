import { db } from '@/core/db'
import { bills } from '@/core/db/schema'
import { eq, sql } from 'drizzle-orm'
import { NotFoundError } from '@/core/utils/errors'
import type { BillModel } from './model'

export abstract class BillService {
    static async getAll(limit: number, offset: number) {
        return await db.select().from(bills).limit(limit).offset(offset).orderBy(bills.createdAt)
    }

    static async countAll(): Promise<number> {
        const result = await db.select({ count: sql<number>`count(*)` }).from(bills)
        return Number(result[0].count)
    }

    static async getByTaskId(taskId: number) {
        const result = await db.select().from(bills).where(eq(bills.taskId, taskId)).limit(1)
        if (!result.length) throw new NotFoundError('Bill not found')
        return result[0]
    }

    static async getById(id: number) {
        const result = await db.select().from(bills).where(eq(bills.billId, id)).limit(1)
        if (!result.length) throw new NotFoundError('Bill not found')
        return result[0]
    }

    static async create(data: BillModel.Create) {
        const [bill] = await db.insert(bills).values(data).returning()
        return bill
    }

    static async update(id: number, data: BillModel.Update) {
        await this.getById(id)
        const [bill] = await db.update(bills).set(data).where(eq(bills.billId, id)).returning()
        return bill
    }

    static async delete(id: number) {
        await this.getById(id)
        await db.delete(bills).where(eq(bills.billId, id))
    }
}
