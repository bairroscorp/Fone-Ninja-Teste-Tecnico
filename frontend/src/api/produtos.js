import http from './http'

export const listar = () => http.get('/produtos')
export const buscar = (id) => http.get(`/produtos/${id}`)
export const criar = (data) => http.post('/produtos', data)
export const atualizar = (id, data) => http.put(`/produtos/${id}`, data)
export const excluir = (id) => http.delete(`/produtos/${id}`)
