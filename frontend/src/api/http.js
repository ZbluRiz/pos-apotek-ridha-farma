import axios from 'axios'

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'
export const apiOrigin = new URL(apiBaseUrl).origin

const http = axios.create({
    baseURL: apiBaseUrl,
    withCredentials: true,
    withXSRFToken: true,
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
})

function getCookie(name) {
    return document.cookie
        .split('; ')
        .find((row) => row.startsWith(`${name}=`))
        ?.split('=')[1]
}

http.interceptors.request.use((config) => {
    const xsrfToken = getCookie('XSRF-TOKEN')

    if (xsrfToken) {
        config.headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrfToken)
    }

    return config
})

http.interceptors.response.use(
    (response) => response,
    (error) => Promise.reject(error),
)

export default http
