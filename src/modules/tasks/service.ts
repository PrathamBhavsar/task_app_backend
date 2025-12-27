import { db } from '@/core/db'
import { tasks, config, taskTimelines } from '@/core/db/schema'
import { eq, sql } from 'drizzle-orm'
import { NotFoundError } from '@/core/utils/errors'
import type { TaskModel } from './model'

export abstract class TaskService {
    static async getAll(limit: number, offset: number) {
        return await db.select().from(tasks).limit(limit).offset(offset).orderBy(tasks.createdAt)
    }

    static async countAll(): Promise<number> {
        const result = await db.select({ count: sql<number>`count(*)` }).from(tasks)
        return Number(result[0].count)
    }

    static async getById(id: number) {
        const result = await db.select().from(tasks).where(eq(tasks.taskId, id)).limit(1)
        if (!result.length) throw new NotFoundError('Task not found')
        return result[0]
    }

    static async generateDealNo(): Promise<string> {
        const configResult = await db.select().from(config).where(eq(config.key, 'latest_deal_no')).limit(1)
        let currentNo = configResult.length ? parseInt(configResult[0].value.replace(/-/g, '')) : 0
        currentNo++
        const newDealNo = String(currentNo).padStart(8, '0').replace(/(\d{4})(\d{4})/, '$1-$2')
        if (configResult.length) {
            await db.update(config).set({ value: newDealNo }).where(eq(config.key, 'latest_deal_no'))
        } else {
            await db.insert(config).values({ key: 'latest_deal_no', value: newDealNo })
        }
        return newDealNo
    }

    static async create(data: TaskModel.Create, userId: number) {
        const dealNo = await this.generateDealNo()
        const [task] = await db.insert(tasks).values({ ...data, dealNo, createdBy: userId }).returning()
        await db.insert(taskTimelines).values({ taskId: task.taskId, status: 'Created', userId })
        return task
    }

    static async update(id: number, data: TaskModel.Update) {
        await this.getById(id)
        const [task] = await db.update(tasks).set(data).where(eq(tasks.taskId, id)).returning()
        return task
    }

    static async updateStatus(id: number, status: string, userId: number) {
        await this.getById(id)
        const [task] = await db.update(tasks).set({ status: status as any }).where(eq(tasks.taskId, id)).returning()
        await db.insert(taskTimelines).values({ taskId: id, status, userId })
        return task
    }

    static async delete(id: number) {
        await this.getById(id)
        await db.delete(tasks).where(eq(tasks.taskId, id))
    }
}
