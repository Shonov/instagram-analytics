// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue';
import axios from 'axios';
import VueAxios from 'vue-axios';
import VueI18n from 'vue-i18n';
import Vuetify from 'vuetify';
import VeeValidate from 'vee-validate';
import ECharts from 'vue-echarts/components/ECharts';
import VueMoment from 'vue-moment';
import 'echarts/lib/chart/bar';
import 'echarts/lib/chart/line';
import 'echarts/lib/chart/pie';
import 'echarts/lib/chart/map';
import 'echarts/lib/chart/radar';
import 'echarts/lib/chart/scatter';
import 'echarts/lib/chart/effectScatter';
import 'echarts/lib/component/tooltip';
import 'echarts/lib/component/polar';
import 'echarts/lib/component/geo';
import 'echarts/lib/component/legend';
import 'echarts/lib/component/title';
import 'echarts/lib/component/visualMap';
import 'echarts/lib/component/dataset';
import arLocale from 'vee-validate/dist/locale/ar';
import enLocale from 'vee-validate/dist/locale/en';
// css
import 'vuetify/dist/vuetify.min.css';
import '@mdi/font/css/materialdesignicons.min.css';
import './scss/main.scss';
// files
import router from './router';
import App from './App';
import messages from './i18n';
import store from './store';
import config from './config';


Vue.config.productionTip = false;

Vue.component('echart', ECharts);

Vue.use(VueMoment);

Vue.use(VueAxios, axios);

Vue.use(VueI18n);

const i18n = new VueI18n({
  locale: 'en', // set locale
  fallbackLocale: 'ar',
  messages, // set locale messages
});

Vue.use(Vuetify);

Vue.use(VeeValidate, {
  i18n,
  i18nRootKey: 'validation',
  dictionary: {
    ar: arLocale,
    en: enLocale,
  },
});

axios.defaults.baseURL = config.apiUrl;

Vue.router = router;

Vue.use(require('@websanova/vue-auth/src/index'), {
// eslint-disable-next-line global-require
  auth: require('@websanova/vue-auth/drivers/auth/bearer.js'),
  // eslint-disable-next-line global-require
  http: require('@websanova/vue-auth/drivers/http/axios.1.x.js'),
  // eslint-disable-next-line global-require
  router: require('@websanova/vue-auth/drivers/router/vue-router.2.x.js'),
  authRedirect: { path: '/accounts' },
});

App.router = Vue.router;
App.i18n = i18n;
App.store = store;


// ToDo: Протестировать роутинг работающий без хеша
// ToDo: Сделать билд под public файлы и сразу же gitignore

// eslint-disable-next-line no-new
new Vue(App).$mount('#app');
