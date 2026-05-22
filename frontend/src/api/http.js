import axios from 'axios'

const baseURL = import.meta.env.VITE_API_URL || '/api'

const http = axios.create({
  baseURL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

http.interceptors.response.use(
  (response) => response,
  (error) => {
    const message =
      error.response?.data?.message ||
      error.response?.data?.errors?.produtos?.[0] ||
      'Ocorreu um erro inesperado.'
    error.friendlyMessage = message
    return Promise.reject(error)
  }
)

export default http
