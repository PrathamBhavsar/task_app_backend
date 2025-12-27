# 🚀 COMPLETE ELYSIA.JS + DRIZZLE ORM MIGRATION GUIDELINE
## Comprehensive Refactoring Prompt for PHP to Elysia.js Full Migration

**For:** Complete migration of existing PHP affiliate management API to production-grade Elysia.js + Drizzle ORM  
**Target:** Single-pass complete refactoring + pagination implementation  
**Framework:** Elysia.js (Bun runtime) + Drizzle ORM + PostgreSQL  
**Outcome:** Production-ready, fully modular, type-safe API

---

## 📋 TABLE OF CONTENTS

1. [Project Overview](#project-overview)
2. [Folder Structure Architecture](#folder-structure-architecture)
3. [File Generation Rules](#file-generation-rules)
4. [Core Infrastructure Setup](#core-infrastructure-setup)
5. [Module Architecture](#module-architecture)
6. [Pagination Integration](#pagination-integration)
7. [Complete Phase-by-Phase Refactoring](#complete-phase-by-phase-refactoring)
8. [Database Schema & Migrations](#database-schema--migrations)
9. [Type Safety & Validation](#type-safety--validation)
10. [Error Handling Strategy](#error-handling-strategy)
11. [Testing & Verification](#testing--verification)
12. [Deployment Checklist](#deployment-checklist)

---

# 🎯 PROJECT OVERVIEW

## Current State
- PHP-based affiliate management API
- 12 phases implemented (Auth, Settings, Plans, Sites, Affiliates, Assignments, Referral Codes, Payments, Events, Reports, Webhooks)
- ~1200 lines of code spread across multiple files
- Monolithic or semi-modular structure
- No pagination on GET endpoints
- Mixed concerns (routes, business logic, data access)

## Target State
- **Elysia.js** framework (TypeScript, Bun runtime)
- **Drizzle ORM** with PostgreSQL
- **Feature-based modular architecture** (each feature in isolated folder)
- **Complete pagination** on all 15 GET endpoints
- **Separation of concerns** (routes ≠ services ≠ models)
- **Type-safe throughout** (full TypeScript with Elysia.t validators)
- **Production-ready** (error handling, middleware, security)

---

# 🏗️ FOLDER STRUCTURE ARCHITECTURE

## Complete Project Structure

```
my-affiliate-api/
├── src/
│   ├── core/                          ← Shared infrastructure (ALWAYS FIRST)
│   │   ├── db/                        ← Database layer
│   │   │   ├── index.ts               (exports db + schema)
│   │   │   ├── client.ts              (Drizzle connection)
│   │   │   ├── schema.ts              (ALL tables defined here)
│   │   │   └── migrations/            (SQL migration files)
│   │   │       ├── 001_initial.sql
│   │   │       └── 002_...sql
│   │   │
│   │   ├── middleware/                ← Request/response handlers
│   │   │   ├── index.ts               (exports all middleware)
│   │   │   ├── errorHandler.ts        (global error handling)
│   │   │   ├── logger.ts              (request/response logging)
│   │   │   ├── auth.middleware.ts     (JWT verification)
│   │   │   └── security.ts            (CORS, headers, etc)
│   │   │
│   │   ├── utils/                     ← Shared utilities
│   │   │   ├── index.ts               (exports all utilities)
│   │   │   ├── pagination.ts          (pagination utilities - NEW)
│   │   │   ├── validation.ts          (custom validators)
│   │   │   ├── response.ts            (standard response formatter)
│   │   │   ├── crypto.ts              (JWT, HMAC, hashing)
│   │   │   ├── errors.ts              (custom error classes)
│   │   │   └── constants.ts           (enums, constants)
│   │   │
│   │   └── decorators/                ← Request decorators
│   │       └── index.ts               (setupDecorators function)
│   │
│   ├── modules/                       ← Feature modules (CORE + UTILITIES FIRST!)
│   │   ├── auth/                      ← Module template
│   │   │   ├── index.ts               (Elysia controller + routes)
│   │   │   ├── service.ts             (business logic - NO HTTP)
│   │   │   └── model.ts               (Elysia.t validators)
│   │   │
│   │   ├── settings/
│   │   │   ├── index.ts
│   │   │   ├── service.ts
│   │   │   └── model.ts
│   │   │
│   │   ├── plans/
│   │   │   ├── index.ts
│   │   │   ├── service.ts
│   │   │   └── model.ts
│   │   │
│   │   ├── sites/
│   │   │   ├── index.ts
│   │   │   ├── service.ts
│   │   │   └── model.ts
│   │   │
│   │   ├── affiliates/
│   │   │   ├── index.ts
│   │   │   ├── service.ts
│   │   │   └── model.ts
│   │   │
│   │   ├── assignments/
│   │   │   ├── index.ts
│   │   │   ├── service.ts
│   │   │   └── model.ts
│   │   │
│   │   ├── referral-codes/
│   │   │   ├── index.ts
│   │   │   ├── service.ts
│   │   │   └── model.ts
│   │   │
│   │   ├── payments/
│   │   │   ├── index.ts
│   │   │   ├── service.ts
│   │   │   └── model.ts
│   │   │
│   │   ├── events/
│   │   │   ├── index.ts               (Elysia controller)
│   │   │   ├── service.ts
│   │   │   ├── model.ts
│   │   │   ├── listeners.ts           (event handlers)
│   │   │   ├── bus.ts                 (EventEmitter singleton)
│   │   │   └── types.ts               (event types/enums)
│   │   │
│   │   ├── reports/
│   │   │   ├── index.ts
│   │   │   ├── service.ts
│   │   │   ├── model.ts
│   │   │   ├── queries.ts             (complex SQL queries)
│   │   │   └── export.ts              (CSV/JSON export)
│   │   │
│   │   ├── webhooks/
│   │   │   ├── index.ts
│   │   │   ├── service.ts
│   │   │   ├── model.ts
│   │   │   ├── handlers.ts            (webhook handlers)
│   │   │   └── verification.ts        (HMAC verification)
│   │   │
│   │   └── index.ts                   (aggregate + export all modules)
│   │
│   ├── types/                         ← Shared TypeScript types
│   │   ├── index.ts
│   │   ├── common.ts                  (Response, Pagination, etc)
│   │   ├── errors.ts                  (error types)
│   │   └── database.ts                (entity types)
│   │
│   ├── app.ts                         ← Main Elysia app initialization
│   └── index.ts                       ← Server entry point
│
├── tests/
│   ├── integration/
│   │   ├── auth.test.ts
│   │   ├── settings.test.ts
│   │   ├── plans.test.ts
│   │   ├── sites.test.ts
│   │   ├── affiliates.test.ts
│   │   ├── assignments.test.ts
│   │   ├── referral-codes.test.ts
│   │   ├── payments.test.ts
│   │   ├── events.test.ts
│   │   ├── reports.test.ts
│   │   └── webhooks.test.ts
│   │
│   └── e2e/
│       └── conversion-flow.test.ts    (end-to-end 15-step flow)
│
├── .env.example                        ← Environment template
├── bun.lockb                           ← Bun lock file
├── tsconfig.json                       ← TypeScript config
├── bunfig.toml                         ← Bun config
└── package.json                        ← Dependencies

```

### **CRITICAL ORDER: Build in this sequence**

```
1. src/core/db/               ← Database first (foundation)
2. src/core/utils/            ← Utilities second (dependencies)
3. src/core/middleware/       ← Middleware third
4. src/core/decorators/       ← Decorators fourth
5. src/modules/auth/          ← Auth module (users first)
6. src/modules/[others]/      ← Remaining modules
7. src/app.ts                 ← App bootstrap (combines all)
8. src/index.ts               ← Server entry point (last)
```

---

# 📄 FILE GENERATION RULES

## Rule 1: Every Module Has Exactly 3 Files

Each feature module (`src/modules/[module]/`) MUST have exactly these 3 files:

### Pattern: Module Structure

```
src/modules/[module-name]/
├── index.ts        ← Elysia controller with routes
├── service.ts      ← Business logic (HTTP-agnostic)
└── model.ts        ← Elysia.t validators + types
```

**NO EXCEPTIONS** - If a file isn't one of these three, it shouldn't exist in base module.

Special exceptions only:
- `events/` has `listeners.ts`, `bus.ts`, `types.ts` (event-specific infrastructure)
- `reports/` has `queries.ts`, `export.ts` (report-specific logic)
- `webhooks/` has `handlers.ts`, `verification.ts` (webhook-specific logic)

---

## Rule 2: File Responsibilities (STRICT SEPARATION)

### `index.ts` - Elysia Controller
**Responsibility:** HTTP routing only  
**Contains:** Elysia instance, route definitions, parameter/response validation  
**NEVER contains:** Business logic, DB queries, complex calculations  
**MUST use:** Service methods (from service.ts)  
**MUST validate:** Using model validators (from model.ts)

```typescript
// Example: index.ts pattern
import { Elysia } from 'elysia'
import { SettingsService } from './service'
import { SettingsModel } from './model'

export const settingsController = new Elysia({ 
  prefix: '/settings', 
  name: 'Settings' 
})
  .get('/', async ({ query }) => {
    // Parse pagination from query
    const { limit, offset } = parsePagination(query)
    // Call SERVICE (not DB directly!)
    const data = await SettingsService.getAll(limit, offset)
    const total = await SettingsService.countAll()
    // Format response
    return formatPaginatedResponse(data, total, limit, offset, 'Settings retrieved')
  }, {
    query: paginationQuery,
    response: { 200: SettingsModel.listResponse }
  })
```

### `service.ts` - Business Logic
**Responsibility:** ALL business logic, calculations, validations  
**Contains:** Abstract class with static methods (if no state needed)  
**NEVER contains:** HTTP-specific code (Context, Response objects)  
**MUST receive:** Clean, validated parameters only (not entire request)  
**MUST throw:** Custom typed errors (ValidationError, DuplicateError, etc)  
**ONLY accesses:** Database via Drizzle ORM (import db from @/core/db)

```typescript
// Example: service.ts pattern
import { db } from '@/core/db'
import { settings } from '@/core/db/schema'
import { ValidationError, DuplicateError } from '@/core/utils/errors'

export abstract class SettingsService {
  static async getAll(limit: number, offset: number) {
    return await db.query.settings.findMany({
      limit,
      offset,
      orderBy: (s) => [s.createdAt]
    })
  }

  static async countAll(): Promise<number> {
    const result = await db.execute(
      sql`SELECT COUNT(*) as count FROM settings`
    )
    return result[0].count as number
  }

  static async create(data: SettingsModel.Create) {
    if (!data.key) throw new ValidationError('Key is required')
    
    const existing = await db.query.settings.findFirst({
      where: eq(settings.key, data.key)
    })
    if (existing) throw new DuplicateError('Setting key already exists')

    return await db.insert(settings).values(data)
  }
}
```

### `model.ts` - Type-Safe Validators
**Responsibility:** ALL validation schemas  
**Contains:** Elysia.t validators grouped in namespace  
**NEVER contains:** Business logic or DB access  
**MUST export:** Both validator AND inferred type (`typeof validator.static`)  
**Pattern:** Organize as namespace with clear naming

```typescript
// Example: model.ts pattern
import { t } from 'elysia'
import { paginatedResponse } from '@/core/utils/pagination'

export namespace SettingsModel {
  // Request validators
  export const create = t.Object({
    key: t.String({ minLength: 1 }),
    value: t.String(),
    description: t.Optional(t.String())
  })
  export type Create = typeof create.static

  export const update = t.Partial(create)
  export type Update = typeof update.static

  // Response validators (for each status code)
  export const response = t.Object({
    id: t.Number(),
    key: t.String(),
    value: t.String(),
    createdAt: t.Date()
  })
  export type Response = typeof response.static

  // Paginated list response
  export const listResponse = paginatedResponse(response)
  export type ListResponse = typeof listResponse.static

  // Error responses
  export const errorNotFound = t.Object({
    status: t.Literal('error'),
    code: t.Literal('NOT_FOUND'),
    message: t.Literal('Setting not found')
  })
  export type ErrorNotFound = typeof errorNotFound.static
}
```

---

## Rule 3: Naming Conventions

### Files
```
✅ auth/service.ts              (lowercase, .ts extension)
❌ auth/Auth.Service.ts         (PascalCase not used)
❌ auth/authService.ts          (existing PHP convention, don't use)
```

### Exports
```
✅ export const authController     (const for Elysia instances)
✅ export abstract class AuthService (abstract class for services)
✅ export namespace AuthModel       (namespace for model groups)
❌ export class AuthController     (use Elysia instance, not class)
❌ export const authService        (don't instantiate, use static)
```

### Folder Names
```
✅ src/modules/referral-codes/     (kebab-case with hyphens)
❌ src/modules/referralCodes/      (no camelCase)
❌ src/modules/referral_codes/     (no underscores)
```

---

## Rule 4: Import Path Standards

### ALWAYS use absolute imports
```typescript
// ✅ CORRECT
import { db } from '@/core/db'
import { AuthService } from '@/modules/auth/service'
import { ValidationError } from '@/core/utils/errors'

// ❌ WRONG
import { db } from '../../../core/db'
import { AuthService } from '../../modules/auth/service'
import { ValidationError } from '../utils/errors'
```

### tsconfig.json Setup
```json
{
  "compilerOptions": {
    "baseUrl": ".",
    "paths": {
      "@/*": ["./src/*"]
    }
  }
}
```

---

# 🔧 CORE INFRASTRUCTURE SETUP

## Step 1: Database Layer (`src/core/db/`)

### File: `src/core/db/schema.ts` - COMPLETE ALL TABLES HERE

```typescript
import { pgTable, serial, varchar, timestamp, numeric, integer, boolean, text, enum as pgEnum } from 'drizzle-orm/pg-core'
import { relations } from 'drizzle-orm'

// All tables defined in ONE file for easy reference

export const users = pgTable('users', {
  id: serial('id').primaryKey(),
  email: varchar('email', { length: 255 }).unique().notNull(),
  passwordHash: varchar('password_hash', { length: 255 }).notNull(),
  fullName: varchar('full_name', { length: 255 }).notNull(),
  role: varchar('role', { length: 50 }).notNull(), // 'admin', 'affiliate'
  status: varchar('status', { length: 50 }).notNull(), // 'pending', 'approved', 'rejected', 'suspended'
  createdAt: timestamp('created_at').defaultNow().notNull(),
  updatedAt: timestamp('updated_at').defaultNow().notNull()
})

export const affiliates = pgTable('affiliates', {
  id: serial('id').primaryKey(),
  userId: integer('user_id').references(() => users.id).notNull(),
  email: varchar('email', { length: 255 }).notNull(),
  status: varchar('status', { length: 50 }).notNull(), // 'pending', 'approved', 'rejected', 'suspended'
  companyName: varchar('company_name', { length: 255 }).notNull(),
  country: varchar('country', { length: 100 }),
  phone: varchar('phone', { length: 20 }),
  pendingBalance: numeric('pending_balance', { precision: 18, scale: 2 }).default('0'),
  totalEarned: numeric('total_earned', { precision: 18, scale: 2 }).default('0'),
  createdAt: timestamp('created_at').defaultNow().notNull(),
  updatedAt: timestamp('updated_at').defaultNow().notNull()
})

export const settings = pgTable('settings', {
  id: serial('id').primaryKey(),
  key: varchar('key', { length: 255 }).unique().notNull(),
  value: text('value').notNull(),
  description: text('description'),
  createdAt: timestamp('created_at').defaultNow().notNull(),
  updatedAt: timestamp('updated_at').defaultNow().notNull()
})

export const plans = pgTable('plans', {
  id: serial('id').primaryKey(),
  planName: varchar('plan_name', { length: 255 }).unique().notNull(),
  baseCommissionPercentage: numeric('base_commission_percentage', { precision: 5, scale: 2 }).notNull(),
  commissionDurationType: varchar('commission_duration_type', { length: 50 }).notNull(), // 'lifetime', 'limited'
  durationMonths: integer('duration_months'),
  description: text('description'),
  isActive: boolean('is_active').default(true).notNull(),
  createdAt: timestamp('created_at').defaultNow().notNull(),
  updatedAt: timestamp('updated_at').defaultNow().notNull()
})

export const sites = pgTable('sites', {
  id: serial('id').primaryKey(),
  name: varchar('name', { length: 255 }).unique().notNull(),
  baseUrl: varchar('base_url', { length: 255 }).notNull(),
  publicApiKey: varchar('public_api_key', { length: 255 }).unique().notNull(),
  status: varchar('status', { length: 50 }).default('active').notNull(),
  description: text('description'),
  createdAt: timestamp('created_at').defaultNow().notNull(),
  updatedAt: timestamp('updated_at').defaultNow().notNull()
})

export const affiliatePlanAssignments = pgTable('affiliate_plan_assignments', {
  id: serial('id').primaryKey(),
  affiliateId: integer('affiliate_id').references(() => affiliates.id).notNull(),
  planId: integer('plan_id').references(() => plans.id).notNull(),
  baseCommission: numeric('base_commission', { precision: 5, scale: 2 }).notNull(),
  customCommissionOverride: numeric('custom_commission_override', { precision: 5, scale: 2 }),
  effectiveCommission: numeric('effective_commission', { precision: 5, scale: 2 }).notNull(),
  isActive: boolean('is_active').default(true).notNull(),
  assignmentDate: timestamp('assignment_date').defaultNow().notNull()
})

export const affiliateSiteAssignments = pgTable('affiliate_site_assignments', {
  id: serial('id').primaryKey(),
  affiliateId: integer('affiliate_id').references(() => affiliates.id).notNull(),
  siteId: integer('site_id').references(() => sites.id).notNull(),
  isActive: boolean('is_active').default(true).notNull(),
  assignmentDate: timestamp('assignment_date').defaultNow().notNull()
})

export const referralCodes = pgTable('referral_codes', {
  id: serial('id').primaryKey(),
  affiliateId: integer('affiliate_id').references(() => affiliates.id).notNull(),
  siteId: integer('site_id').references(() => sites.id).notNull(),
  code: varchar('code', { length: 12 }).unique().notNull(),
  description: varchar('description', { length: 255 }),
  isActive: boolean('is_active').default(true).notNull(),
  createdAt: timestamp('created_at').defaultNow().notNull(),
  updatedAt: timestamp('updated_at').defaultNow().notNull()
})

export const conversions = pgTable('conversions', {
  id: serial('id').primaryKey(),
  referralCodeId: integer('referral_code_id').references(() => referralCodes.id).notNull(),
  affiliateId: integer('affiliate_id').references(() => affiliates.id).notNull(),
  siteId: integer('site_id').references(() => sites.id).notNull(),
  orderId: varchar('order_id', { length: 255 }),
  customerId: varchar('customer_id', { length: 255 }),
  amount: numeric('amount', { precision: 18, scale: 2 }).notNull(),
  currency: varchar('currency', { length: 10 }).default('USD').notNull(),
  status: varchar('status', { length: 50 }).default('completed').notNull(), // 'completed', 'refunded', 'disputed'
  createdAt: timestamp('created_at').defaultNow().notNull()
})

export const commissionHistory = pgTable('commission_history', {
  id: serial('id').primaryKey(),
  affiliateId: integer('affiliate_id').references(() => affiliates.id).notNull(),
  conversionId: integer('conversion_id').references(() => conversions.id),
  referralCodeId: integer('referral_code_id').references(() => referralCodes.id).notNull(),
  planId: integer('plan_id').references(() => plans.id),
  commissionAmount: numeric('commission_amount', { precision: 18, scale: 2 }).notNull(),
  commissionPercentage: numeric('commission_percentage', { precision: 5, scale: 2 }).notNull(),
  earnedAt: timestamp('earned_at').defaultNow().notNull(),
  createdAt: timestamp('created_at').defaultNow().notNull()
})

export const payments = pgTable('payments', {
  id: serial('id').primaryKey(),
  affiliateId: integer('affiliate_id').references(() => affiliates.id).notNull(),
  amount: numeric('amount', { precision: 18, scale: 2 }).notNull(),
  status: varchar('status', { length: 50 }).notNull(), // 'pending', 'completed', 'failed'
  paymentMethod: varchar('payment_method', { length: 50 }).notNull(), // 'bank', 'paypal', etc
  notes: text('notes'),
  paidAt: timestamp('paid_at'),
  createdAt: timestamp('created_at').defaultNow().notNull(),
  updatedAt: timestamp('updated_at').defaultNow().notNull()
})

export const eventLogs = pgTable('event_logs', {
  id: serial('id').primaryKey(),
  eventType: varchar('event_type', { length: 100 }).notNull(),
  affiliateId: integer('affiliate_id').references(() => affiliates.id),
  payload: text('payload'), // JSON string
  createdAt: timestamp('created_at').defaultNow().notNull()
})

export const webhookDeliveries = pgTable('webhook_deliveries', {
  id: serial('id').primaryKey(),
  eventType: varchar('event_type', { length: 100 }).notNull(),
  siteId: integer('site_id').references(() => sites.id),
  payload: text('payload').notNull(), // JSON string
  signature: varchar('signature', { length: 255 }).notNull(),
  status: varchar('status', { length: 50 }).notNull(), // 'pending', 'completed', 'failed'
  attempts: integer('attempts').default(0).notNull(),
  lastAttemptAt: timestamp('last_attempt_at'),
  processedAt: timestamp('processed_at'),
  idempotencyKey: varchar('idempotency_key', { length: 255 }).unique(),
  createdAt: timestamp('created_at').defaultNow().notNull()
})

// Relations
export const usersRelations = relations(users, ({ one, many }) => ({
  affiliate: one(affiliates, {
    fields: [users.id],
    references: [affiliates.userId]
  })
}))

export const affiliatesRelations = relations(affiliates, ({ one, many }) => ({
  user: one(users, {
    fields: [affiliates.userId],
    references: [users.id]
  }),
  planAssignments: many(affiliatePlanAssignments),
  siteAssignments: many(affiliateSiteAssignments),
  referralCodes: many(referralCodes),
  commissionHistory: many(commissionHistory),
  payments: many(payments)
}))

// ... continue for all relations
```

### File: `src/core/db/client.ts`

```typescript
import { drizzle } from 'drizzle-orm/node-postgres'
import { Pool } from 'pg'
import * as schema from './schema'

const pool = new Pool({
  connectionString: process.env.DATABASE_URL || 'postgresql://user:password@localhost:5432/affiliate_db'
})

export const db = drizzle(pool, { schema })
```

### File: `src/core/db/index.ts`

```typescript
export { db } from './client'
export * from './schema'
```

---

## Step 2: Error Handling (`src/core/utils/errors.ts`)

```typescript
// All custom error classes
export class ValidationError extends Error {
  constructor(message: string, public details?: Record<string, any>) {
    super(message)
    this.name = 'ValidationError'
  }
}

export class DuplicateError extends Error {
  constructor(message: string) {
    super(message)
    this.name = 'DuplicateError'
  }
}

export class NotFoundError extends Error {
  constructor(message: string) {
    super(message)
    this.name = 'NotFoundError'
  }
}

export class UnauthorizedError extends Error {
  constructor(message: string) {
    super(message)
    this.name = 'UnauthorizedError'
  }
}

export class ForbiddenError extends Error {
  constructor(message: string) {
    super(message)
    this.name = 'ForbiddenError'
  }
}

export class ConflictError extends Error {
  constructor(message: string) {
    super(message)
    this.name = 'ConflictError'
  }
}
```

---

## Step 3: Middleware (`src/core/middleware/errorHandler.ts`)

```typescript
import { Elysia } from 'elysia'
import {
  ValidationError,
  DuplicateError,
  NotFoundError,
  UnauthorizedError,
  ForbiddenError
} from '@/core/utils/errors'

export function errorHandler() {
  return new Elysia({ name: 'ErrorHandler' })
    .onError({ as: 'global' }, ({ error, set }) => {
      if (error instanceof ValidationError) {
        set.status = 422
        return {
          status: 'error',
          code: 'VALIDATION_ERROR',
          message: error.message,
          details: error.details
        }
      }

      if (error instanceof DuplicateError) {
        set.status = 409
        return {
          status: 'error',
          code: 'DUPLICATE_ERROR',
          message: error.message
        }
      }

      if (error instanceof NotFoundError) {
        set.status = 404
        return {
          status: 'error',
          code: 'NOT_FOUND',
          message: error.message
        }
      }

      if (error instanceof UnauthorizedError) {
        set.status = 401
        return {
          status: 'error',
          code: 'UNAUTHORIZED',
          message: error.message
        }
      }

      if (error instanceof ForbiddenError) {
        set.status = 403
        return {
          status: 'error',
          code: 'FORBIDDEN',
          message: error.message
        }
      }

      // Default error
      set.status = 500
      return {
        status: 'error',
        code: 'INTERNAL_SERVER_ERROR',
        message: 'An unexpected error occurred'
      }
    })
}
```

---

## Step 4: Pagination Utilities (`src/core/utils/pagination.ts`)

**COMPLETE CODE - Copy this exactly:**

```typescript
import { t } from 'elysia'

export const PAGINATION_DEFAULTS = {
  LIMIT: 20,
  LIMIT_MIN: 1,
  LIMIT_MAX: 100,
  OFFSET_MIN: 0
}

export const paginationQuery = t.Object({
  limit: t.Optional(t.Numeric({
    minimum: PAGINATION_DEFAULTS.LIMIT_MIN,
    maximum: PAGINATION_DEFAULTS.LIMIT_MAX
  })),
  offset: t.Optional(t.Numeric({
    minimum: PAGINATION_DEFAULTS.OFFSET_MIN
  }))
})

export type PaginationQuery = typeof paginationQuery.static

export interface PaginationParams {
  limit: number
  offset: number
  page: number
}

export interface PaginationMeta {
  limit: number
  offset: number
  page: number
  total: number
  totalPages: number
  hasNextPage: boolean
  hasPreviousPage: boolean
}

export interface PaginatedResponse<T> {
  status: 'success'
  data: T[]
  pagination: PaginationMeta
  message: string
}

export function parsePagination(query: Partial<PaginationQuery>): PaginationParams {
  let limit = query.limit ?? PAGINATION_DEFAULTS.LIMIT
  let offset = query.offset ?? PAGINATION_DEFAULTS.OFFSET_MIN

  if (limit < PAGINATION_DEFAULTS.LIMIT_MIN) limit = PAGINATION_DEFAULTS.LIMIT_MIN
  if (limit > PAGINATION_DEFAULTS.LIMIT_MAX) limit = PAGINATION_DEFAULTS.LIMIT_MAX
  if (offset < PAGINATION_DEFAULTS.OFFSET_MIN) offset = PAGINATION_DEFAULTS.OFFSET_MIN

  const page = Math.floor(offset / limit) + 1

  return { limit, offset, page }
}

export function calculatePaginationMeta(
  limit: number,
  offset: number,
  total: number
): PaginationMeta {
  const page = Math.floor(offset / limit) + 1
  const totalPages = Math.ceil(total / limit)
  const hasNextPage = offset + limit < total
  const hasPreviousPage = offset > 0

  return { limit, offset, page, total, totalPages, hasNextPage, hasPreviousPage }
}

export function formatPaginatedResponse<T>(
  data: T[],
  total: number,
  limit: number,
  offset: number,
  message: string = 'Data retrieved'
): PaginatedResponse<T> {
  const pagination = calculatePaginationMeta(limit, offset, total)
  return { status: 'success', data, pagination, message }
}

export function paginatedResponse<T extends any>(dataModel: T) {
  return t.Object({
    status: t.Literal('success'),
    data: t.Array(dataModel),
    pagination: t.Object({
      limit: t.Number(),
      offset: t.Number(),
      page: t.Number(),
      total: t.Number(),
      totalPages: t.Number(),
      hasNextPage: t.Boolean(),
      hasPreviousPage: t.Boolean()
    }),
    message: t.String()
  })
}
```

---

# 📊 MODULE ARCHITECTURE

## Standard Module Pattern (Copy for ALL 10 modules)

### Template: `src/modules/[module-name]/index.ts`

```typescript
import { Elysia } from 'elysia'
import { [Module]Service } from './service'
import { [Module]Model } from './model'
import { paginationQuery, parsePagination, formatPaginatedResponse } from '@/core/utils/pagination'

export const [module]Controller = new Elysia({ 
  prefix: '/[module]', 
  name: '[Module]' 
})
  
  // GET all with pagination
  .get('/', async ({ query }) => {
    const { limit, offset } = parsePagination(query)
    const data = await [Module]Service.getAll(limit, offset)
    const total = await [Module]Service.countAll()
    return formatPaginatedResponse(data, total, limit, offset, '[Module]s retrieved')
  }, {
    query: paginationQuery,
    response: { 200: [Module]Model.listResponse }
  })

  // GET by ID
  .get('/:id', async ({ params: { id } }) => 
    [Module]Service.getById(Number(id))
  , {
    params: t.Object({ id: t.Numeric() }),
    response: { 200: [Module]Model.response, 404: [Module]Model.errorNotFound }
  })

  // POST create
  .post('/', async ({ body, set }) => {
    set.status = 201
    return [Module]Service.create(body)
  }, {
    body: [Module]Model.create,
    response: { 201: [Module]Model.response, 409: [Module]Model.errorDuplicate }
  })

  // PATCH update
  .patch('/:id', async ({ params: { id }, body }) => 
    [Module]Service.update(Number(id), body)
  , {
    params: t.Object({ id: t.Numeric() }),
    body: [Module]Model.update,
    response: { 200: [Module]Model.response, 404: [Module]Model.errorNotFound }
  })

  // DELETE
  .delete('/:id', async ({ params: { id } }) => {
    await [Module]Service.delete(Number(id))
    return { message: '[Module] deleted' }
  }, {
    params: t.Object({ id: t.Numeric() }),
    response: { 200: t.Object({ message: t.String() }) }
  })
```

### Template: `src/modules/[module-name]/service.ts`

```typescript
import { db } from '@/core/db'
import { [table] } from '@/core/db/schema'
import { eq, sql } from 'drizzle-orm'
import { ValidationError, DuplicateError, NotFoundError } from '@/core/utils/errors'
import type { [Module]Model } from './model'

export abstract class [Module]Service {
  // Paginated list
  static async getAll(limit: number, offset: number) {
    return await db.query.[table].findMany({
      limit,
      offset,
      orderBy: (t) => [t.createdAt]
    })
  }

  // Count for pagination
  static async countAll(): Promise<number> {
    const result = await db.execute(
      sql`SELECT COUNT(*) as count FROM [table]`
    )
    return result[0].count as number
  }

  // Get by ID
  static async getById(id: number) {
    const item = await db.query.[table].findFirst({
      where: eq([table].id, id)
    })
    if (!item) throw new NotFoundError('[Module] not found')
    return item
  }

  // Create
  static async create(data: [Module]Model.Create) {
    if (!data.[required]) throw new ValidationError('Field is required')
    
    // Check for duplicates if needed
    const existing = await db.query.[table].findFirst({
      where: eq([table].[unique_field], data.[unique_field])
    })
    if (existing) throw new DuplicateError('[Module] already exists')

    const result = await db.insert([table]).values(data)
    return result[0]
  }

  // Update
  static async update(id: number, data: [Module]Model.Update) {
    await this.getById(id) // Verify exists
    const result = await db.update([table])
      .set(data)
      .where(eq([table].id, id))
    return result[0]
  }

  // Delete
  static async delete(id: number) {
    await this.getById(id) // Verify exists
    await db.delete([table]).where(eq([table].id, id))
  }
}
```

### Template: `src/modules/[module-name]/model.ts`

```typescript
import { t } from 'elysia'
import { paginatedResponse } from '@/core/utils/pagination'

export namespace [Module]Model {
  // Create validator
  export const create = t.Object({
    field1: t.String({ minLength: 1 }),
    field2: t.String(),
    field3: t.Optional(t.String())
  })
  export type Create = typeof create.static

  // Update validator
  export const update = t.Partial(create)
  export type Update = typeof update.static

  // Response validator
  export const response = t.Object({
    id: t.Number(),
    field1: t.String(),
    field2: t.String(),
    createdAt: t.Date()
  })
  export type Response = typeof response.static

  // Paginated list response
  export const listResponse = paginatedResponse(response)
  export type ListResponse = typeof listResponse.static

  // Error responses
  export const errorNotFound = t.Object({
    status: t.Literal('error'),
    code: t.Literal('NOT_FOUND'),
    message: t.Literal('[Module] not found')
  })
  export type ErrorNotFound = typeof errorNotFound.static

  export const errorDuplicate = t.Object({
    status: t.Literal('error'),
    code: t.Literal('DUPLICATE_ERROR'),
    message: t.String()
  })
  export type ErrorDuplicate = typeof errorDuplicate.static
}
```

---

# 🌍 APP BOOTSTRAP

## File: `src/app.ts`

```typescript
import Elysia from 'elysia'
import { errorHandler } from '@/core/middleware'
import { setupDecorators } from '@/core/decorators'
import {
  authController,
  settingsController,
  plansController,
  sitesController,
  affiliatesController,
  assignmentsController,
  referralCodesController,
  paymentsController,
  eventsController,
  reportsController,
  webhooksController
} from '@/modules'

export const app = new Elysia({ name: 'AffiliateAPI', prefix: '/api/v1' })
  // Global middleware first
  .use(errorHandler())
  .use(setupDecorators)
  
  // All module controllers
  .use(authController)
  .use(settingsController)
  .use(plansController)
  .use(sitesController)
  .use(affiliatesController)
  .use(assignmentsController)
  .use(referralCodesController)
  .use(paymentsController)
  .use(eventsController)
  .use(reportsController)
  .use(webhooksController)
```

## File: `src/index.ts`

```typescript
import { app } from './app'

app.listen(3000, () => {
  console.log('🚀 Affiliate API running on http://localhost:3000/api/v1')
})
```

---

# 📋 COMPLETE IMPLEMENTATION CHECKLIST

## Phase 0: Setup Foundation (DO FIRST)

- [ ] Create `src/core/db/schema.ts` (ALL tables)
- [ ] Create `src/core/db/client.ts` (Drizzle connection)
- [ ] Create `src/core/db/index.ts` (exports)
- [ ] Create `src/core/utils/errors.ts` (custom errors)
- [ ] Create `src/core/utils/pagination.ts` (pagination)
- [ ] Create `src/core/utils/index.ts` (exports all utils)
- [ ] Create `src/core/middleware/errorHandler.ts` (error handling)
- [ ] Create `src/core/middleware/index.ts` (exports)
- [ ] Create `src/core/decorators/index.ts` (auth decorators)

## Phase 2: Auth Module

- [ ] Create `src/modules/auth/index.ts` (routes)
- [ ] Create `src/modules/auth/service.ts` (register, login, verify)
- [ ] Create `src/modules/auth/model.ts` (validators)

## Phase 3: Settings

- [ ] Create `src/modules/settings/index.ts` (CRUD + pagination)
- [ ] Create `src/modules/settings/service.ts` (getAll + countAll)
- [ ] Create `src/modules/settings/model.ts` (listResponse)

## Phase 4: Plans

- [ ] Create `src/modules/plans/index.ts` (CRUD + pagination)
- [ ] Create `src/modules/plans/service.ts` (getAll + countAll)
- [ ] Create `src/modules/plans/model.ts` (listResponse)

## Phase 5: Sites

- [ ] Create `src/modules/sites/index.ts` (CRUD + pagination + filter)
- [ ] Create `src/modules/sites/service.ts` (getAll with optional filter + countAll)
- [ ] Create `src/modules/sites/model.ts` (listResponse)

## Phase 6: Affiliates

- [ ] Create `src/modules/affiliates/index.ts` (CRUD + pagination)
- [ ] Create `src/modules/affiliates/service.ts` (getAll + countAll + status actions)
- [ ] Create `src/modules/affiliates/model.ts` (listResponse)

## Phase 7: Assignments

- [ ] Create `src/modules/assignments/index.ts` (plans + sites assignments + pagination)
- [ ] Create `src/modules/assignments/service.ts` (getByAffiliate + countByAffiliate)
- [ ] Create `src/modules/assignments/model.ts` (listResponse)

## Phase 8: Referral Codes

- [ ] Create `src/modules/referral-codes/index.ts` (CRUD + pagination)
- [ ] Create `src/modules/referral-codes/service.ts` (getByAffiliate + countByAffiliate)
- [ ] Create `src/modules/referral-codes/model.ts` (listResponse)

## Phase 9: Payments

- [ ] Create `src/modules/payments/index.ts` (commission history + payments + pagination)
- [ ] Create `src/modules/payments/service.ts` (getCommissionHistory + getAll + countAll + countByAffiliate)
- [ ] Create `src/modules/payments/model.ts` (listResponse)

## Phase 10: Events

- [ ] Create `src/modules/events/index.ts` (3 endpoints with pagination)
- [ ] Create `src/modules/events/service.ts` (getAll + getByType + getByAffiliate + all count methods)
- [ ] Create `src/modules/events/model.ts` (listResponse)
- [ ] Create `src/modules/events/bus.ts` (EventEmitter singleton)
- [ ] Create `src/modules/events/types.ts` (EventType enum)
- [ ] Create `src/modules/events/listeners.ts` (event handlers)

## Phase 11: Reports

- [ ] Create `src/modules/reports/index.ts` (top lists + export endpoints + pagination)
- [ ] Create `src/modules/reports/service.ts` (getTopAffiliates + getTopCodes + count methods)
- [ ] Create `src/modules/reports/model.ts` (listResponse)
- [ ] Create `src/modules/reports/queries.ts` (complex SQL)
- [ ] Create `src/modules/reports/export.ts` (CSV/JSON export)

## Phase 12: Webhooks

- [ ] Create `src/modules/webhooks/index.ts` (webhook endpoints + pagination)
- [ ] Create `src/modules/webhooks/service.ts` (getHistory + countHistory)
- [ ] Create `src/modules/webhooks/model.ts` (listResponse)
- [ ] Create `src/modules/webhooks/handlers.ts` (webhook handlers)
- [ ] Create `src/modules/webhooks/verification.ts` (HMAC verification)

## Module Aggregation

- [ ] Create `src/modules/index.ts` (export all controllers)
- [ ] Create `src/app.ts` (bootstrap all modules)
- [ ] Create `src/index.ts` (server entry point)

## Testing

- [ ] Create `tests/integration/` (all endpoint tests)
- [ ] Create `tests/e2e/` (full flow test)

---

# ✅ FINAL VALIDATION

After complete implementation:

- [ ] All 15 GET endpoints have pagination working
- [ ] All responses include pagination metadata
- [ ] Service layer has NO HTTP logic
- [ ] Model.ts has all validators
- [ ] Database schema is complete
- [ ] Error handling middleware catches all errors
- [ ] All TypeScript compiles without errors
- [ ] All 15 endpoints tested and working
- [ ] Folder structure matches specification exactly
- [ ] All imports use `@/` absolute paths

---

**DONE! Your PHP API is now Elysia.js + Drizzle ORM with production-grade pagination!** 🚀
