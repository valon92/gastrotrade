import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set up CSRF token
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Set up API auth: admin token (for /admin/*) or client token (for /client/me, cart)
const adminToken = localStorage.getItem('admin_token');
const clientToken = localStorage.getItem('client_token');
if (adminToken) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${adminToken}`;
} else if (clientToken) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${clientToken}`;
}
