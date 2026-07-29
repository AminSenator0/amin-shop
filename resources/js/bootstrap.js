import axios from 'axios';
import { initCsrf } from './csrf';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

initCsrf();
