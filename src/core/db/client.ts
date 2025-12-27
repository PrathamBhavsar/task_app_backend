import { drizzle } from 'drizzle-orm/postgres-js'
import postgres from 'postgres'
import * as schema from './schema'

const connectionString = process.env.DATABASE_URL || 'postgres://postgres:root@localhost:5432/ds'

const client = postgres(connectionString)
export const db = drizzle(client, { schema })
