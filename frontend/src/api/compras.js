import http from './http'

export const listar = (page = 1) => http.get('/compras', { params: { page } })
export const buscar = (id) => http.get(`/compras/${id}`)
export const criar = (data) => http.post('/compras', data)
