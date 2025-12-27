import { db } from '@/core/db'
import { taskMessages } from '@/core/db/schema'
import { eq, sql } from 'drizzle-orm'
import { NotFoundError } from '@/core/utils/errors'
import type { TaskMessageModel } from './model'

export abstract class TaskMessageService {
    static async getAll(limit: number, offset: number) {
        return await db.select().from(taskMessages).limit(limit).offset(offset).orderBy(taskMessages.createdAt)
    }

    static async countAll(): Promise<number> {
        const result = await db.select({ count: sql<number>`count(*)` }).from(taskMessages)
        return Number(result[0].count)
    }

    static async getByTaskId(taskId: number, limit: number, offset: number) {
        return await db.select().from(taskMessages).where(eq(taskMessages.taskId, taskId)).limit(limit).offset(offset).orderBy(taskMessages.createdAt)
    }

    static async countByTaskId(taskId: number): Promise<number> {
        const result = await db.select({ count: sql<number>`count(*)` }).from(taskMessages).where(eq(taskMessages.taskId, taskId))
        return Number(result[0].count)
    }

    static async getById(id: number) {
        const result = await db.select().from(taskMessages).where(eq(taskMessages.messageId, id)).limit(1)
        if (!result.length) throw new NotFoundError('Message not found')
        return result[0]
    }

    static async create(data: TaskMessageModel.Create, userId: number) {
        const [message] = await db.insert(taskMessages).values({ ...data, userId }).returning()
        return message
    }

    static async update(id: number, data: TaskMessageModel.Update) {
        await this.getById(id)
        const [message] = await db.update(taskMessages).set(data).where(eq(taskMessages.messageId, id)).returning()
        return message
    }

    static async delete(id: number) {
        await this.getById(id)
        await db.delete(taskMessages).where(eq(taskMessages.messageId, id))
    }
}
