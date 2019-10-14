import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';

import accounts from '@/store/modules/accounts-module';
import statistics from '@/store/modules/statistics-module';

Vue.use(Vuex);

// const debug = process.env.NODE_ENV !== 'production';

export default new Vuex.Store({
  modules: {
    accounts,
    statistics,
  },
  getters: {
    HTTP: () => axios,
  },
  // strict: debug,
  // plugins: debug,
});
