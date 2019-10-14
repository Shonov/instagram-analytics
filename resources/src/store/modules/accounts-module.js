export default {
  namespaced: true,

  state: {
    selfAccounts: [],
    competitors: [],
    error: false,
    user: false,
    currentAccount: false,
  },
  getters: {
    selfAccounts: state => state.selfAccounts,
    competitors: state => state.competitors,
    user: state => state.user,
    currentAccount: state => state.currentAccount,
  },
  mutations: {
    ACCOUNTS_UPDATED: (state, accounts) => {
      state.selfAccounts = accounts.self_accounts;
      state.competitors = accounts.competitors;
    },
    USER_SET: (state, user) => {
      state.user = user;
    },
    SET_ERROR: (state, error) => {
      state.error = error;
    },
    CURRENT_ACCOUNT_UPDATED: (state, account) => {
      state.currentAccount = account;
    },
  },
  actions: {
    getAccounts: ({ commit, rootGetters }) => {
      rootGetters.HTTP.get('/accounts').then((response) => {
        commit('ACCOUNTS_UPDATED', response.data);

        // ToDo: fix hack after ls
        commit('CURRENT_ACCOUNT_UPDATED', response.data.self_accounts[0]);
      }, (err) => {
        commit('SET_ERROR', err);
      });
    },
    setUser: ({ commit, dispatch }, user) => {
      dispatch('getAccounts');

      commit('USER_SET', user);
    },
    setCurrentAccount: ({ commit }, account) => {
      commit('CURRENT_ACCOUNT_UPDATED', account);
    },
    addAccount: ({ commit, rootGetters }, data) => rootGetters.HTTP.post('/accounts', data).then((response) => {
      window.console.log(response.data);

      return response.data;
    }, (err) => {
      commit('SET_ERROR', err);
    }),
    removeAccount: ({ commit, rootGetters }, { accountId }) => rootGetters.HTTP.delete(`/accounts/${accountId}`).then((response) => {
      window.console.log(response.data);

      return response.data;
    }, (err) => {
      commit('SET_ERROR', err);
    }),
  },
};
