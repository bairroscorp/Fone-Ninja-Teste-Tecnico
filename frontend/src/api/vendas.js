import http from './http'

export const listar = (page = 1) => http.get('/vendas', { params: { page } })
export const buscar = (id) => http.get(`/vendas/${id}`)
export const criar = (data) => http.post('/vendas', data)
export const cancelar = (id) => http.post(`/vendas/${id}/cancelar`)
