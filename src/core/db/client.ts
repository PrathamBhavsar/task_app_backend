import { drizzle } from 'drizzle-orm/postgres-js'
import postgres from 'postgres'
import * as schema from './schema'

const connectionString = `postgres://${process.env.DB_USER || 'root'}:${process.env.DB_PASS || 'root'}@${process.env.DB_HOST || 'localhost'}:${process.env.DB_PORT || 5432}/${process.env.DB_NAME || 'ds'}`

const client = postgres(connectionString)
export const db = drizzle(client, { schema })
