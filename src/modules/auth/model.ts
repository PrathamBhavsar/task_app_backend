import { t } from 'elysia'

export namespace AuthModel {
    export const login = t.Object({
        email: t.String({ format: 'email' }),
        password: t.String({ minLength: 1 })
    })
    export type Login = typeof login.static

    export const register = t.Object({
        name: t.String({ minLength: 1 }),
        email: t.String({ format: 'email' }),
        password: t.String({ minLength: 6 }),
        contactNo: t.Optional(t.String()),
        address: t.Optional(t.String()),
        role: t.Optional(t.Union([t.Literal('admin'), t.Literal('salesperson'), t.Literal('agent')]))
    })
    export type Register = typeof register.static

    export const refreshToken = t.Object({
        refreshToken: t.String()
    })
    export type RefreshToken = typeof refreshToken.static

    export const tokenResponse = t.Object({
        status: t.Literal('success'),
        data: t.Object({
            accessToken: t.String(),
            refreshToken: t.String(),
            expiresIn: t.Number(),
            user: t.Object({
                userId: t.Number(),
                name: t.String(),
                email: t.Union([t.String(), t.Null()]),
                role: t.String()
            })
        }),
        message: t.String()
    })

    export const errorUnauthorized = t.Object({
        status: t.Literal('error'),
        code: t.Literal('UNAUTHORIZED'),
        message: t.String()
    })
}
