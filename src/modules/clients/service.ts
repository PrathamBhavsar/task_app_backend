import { db } from '@/core/db'
import { clients } from '@/core/db/schema'
import { eq, sql, or, ilike } from 'drizzle-orm'
import { NotFoundError } from '@/core/utils/errors'
import type { ClientModel } from './model'

export abstract class ClientService {
    static async getAll(limit: number, offset: number) {
        return await db.select().from(clients).limit(limit).offset(offset).orderBy(clients.createdAt)
    }

    static async search(query: string) {
        const searchPattern = `%${query}%`
        return await db.select().from(clients)
            .where(or(
                ilike(clients.name, searchPattern),
                ilike(clients.email, searchPattern),
                ilike(clients.contactNo, searchPattern),
                ilike(clients.address, searchPattern)
            ))
            .limit(5)
    }

    static async countAll(): Promise<number> {
        const result = await db.select({ count: sql<number>`count(*)` }).from(clients)
        return Number(result[0].count)
    }

    static async getById(id: number) {
        const result = await db.select().from(clients).where(eq(clients.clientId, id)).limit(1)
        if (!result.length) throw new NotFoundError('Client not found')
        return result[0]
    }

    static async create(data: ClientModel.Create) {
        const [client] = await db.insert(clients).values(data).returning()
        return client
    }

    static async update(id: number, data: ClientModel.Update) {
        await this.getById(id)
        const [client] = await db.update(clients).set(data).where(eq(clients.clientId, id)).returning()
        return client
    }

    static async delete(id: number) {
        await this.getById(id)
        await db.delete(clients).where(eq(clients.clientId, id))
    }
}
