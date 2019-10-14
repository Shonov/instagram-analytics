export default {
  namespaced: true,

  state: {
    isLoading: {
      progress: 0,
      queries: {
        statistic: false,
        'top-new-followers': false,
        'top-lost-followers': false,
        // 'gender-followers': false,
        'private-and-open-accounts': false,
        'business-and-usual-accounts': false,
        'followers-by-our-followers': false,
        'followers-by-our-following': false,
        'followers-and-following': false,
        bots: false,
        reach: false,
        engagement: false,
        posts: false,
        'number-posts': false,
        'most-engaging-post-types': false,
        'post-types': false,
      },
    },
    statistic: false,
    followersByOurFollowers: false,
    followersByOurFollowing: false,
    followersAndFollowing: false,
    countBots: false,
    topNewFollowers: false,
    topLostFollowers: false,
    privateAndOpenAccounts: false,
    businessAndUsualAccounts: false,
    mostEngagingPostTypes: false,
    postTypes: false,
    reachUsers: false,
    engagementRate: false,
    profileEngagementRate: false,
    genderFollowers: false,
    numberOfPosts: false,
    maxChangePerDay: false,
    averageLikesPerDay: false,
    videoViews: false,
    topPosts: false,
    isLoadingTopPosts: false,
    timeToGrabSubs: false,
    canViewGraph: false,
    error: false,
  },
  getters: {
    isLoading: state => ({
      load: state.isLoading.progress !== 100,
      progress: state.isLoading.progress,
      queries: state.isLoading.queries,
    }),
    statistic: state => state.statistic,
    followersByOurFollowers: state => state.followersByOurFollowers,
    followersByOurFollowing: state => state.followersByOurFollowing,
    followersAndFollowing: state => state.followersAndFollowing,
    countBots: state => state.countBots,
    topNewFollowers: state => state.topNewFollowers,
    topLostFollowers: state => state.topLostFollowers,
    privateAndOpenAccounts: state => state.privateAndOpenAccounts,
    businessAndUsualAccounts: state => state.businessAndUsualAccounts,
    mostEngagingPostTypes: state => state.mostEngagingPostTypes,
    postTypes: state => state.postTypes,
    reachUsers: state => state.reachUsers,
    engagementRate: state => state.engagementRate,
    profileEngagementRate: state => state.profileEngagementRate,
    genderFollowers: state => state.genderFollowers,
    numberOfPosts: state => state.numberOfPosts,
    maxChangePerDay: state => state.maxChangePerDay,
    averageLikesPerDay: state => state.averageLikesPerDay,
    videoViews: state => state.videoViews,
    topPosts: state => state.topPosts,
    isLoadingTopPosts: state => state.isLoadingTopPosts,
    timeToGrabSubs: state => state.timeToGrabSubs,
    canViewGraph: state => state.canViewGraph,
  },
  mutations: {
    STATISTIC_RESET: (state) => {
      state.isLoading = {
        queries: {
          statistic: false,
          'top-new-followers': false,
          'top-lost-followers': false,
          // 'gender-followers': false,
          'private-and-open-accounts': false,
          'business-and-usual-accounts': false,
          'followers-by-our-followers': false,
          'followers-by-our-following': false,
          'followers-and-following': false,
          bots: false,
          reach: false,
          engagement: false,
          posts: false,
          'number-posts': false,
          'most-engaging-post-types': false,
          'post-types': false,
        },
        load: false,
        progress: 0,
      };
      state.statistic = false;
      state.followersByOurFollowers = false;
      state.followersByOurFollowing = false;
      state.followersAndFollowing = false;
      state.countBots = false;
      state.topNewFollowers = false;
      state.topLostFollowers = false;
      state.privateAndOpenAccounts = false;
      state.businessAndUsualAccounts = false;
      state.mostEngagingPostTypes = false;
      state.postTypes = false;
      state.reachUsers = false;
      state.engagementRate = false;
      state.profileEngagementRate = false;
      state.genderFollowers = false;
      state.numberOfPosts = false;
      state.maxChangePerDay = false;
      state.averageLikesPerDay = false;
      state.videoViews = false;
      state.topPosts = false;
      state.isLoadingTopPosts = false;
      state.timeToGrabSubs = false;
      state.canViewGraph = false;
    },
    POSTS_RESET: (state) => {
      state.topPosts = true;
    },
    SET_ERROR: (state, error) => {
      state.error = error;
    },
    SORTED_POSTS_UPDATED: (state, response) => {
      state.topPosts = response.topPosts;
    },
    UPDATE_IS_LOADING: (state, { progress, queries }) => {
      state.isLoading.progress = progress;
      state.isLoading.queries = queries || state.isLoading.queries;
    },
    UPDATE_IS_LOADING_TOP_POSTS: (state, { value }) => {
      state.isLoadingTopPosts = value;
    },
    UPDATE_STATISTIC_FIELD(state, { field, value }) {
      if (field === 'statistic') {
        state.statistic = value.days.reverse();
        state.maxChangePerDay = value.maxChangePerDay;
        state.averageLikesPerDay = value.averageLikesPerDay;
        state.videoViews = value.videoViews;
        state.timeToGrabSubs = value.time_to_grab_subs;
        state.canViewGraph = value.can_view;
      } else if (field === 'engagementRate') {
        state.engagementRate = value.engagement;
        state.profileEngagementRate = value.profileEngagement;
      } else state[field] = value;
    },
  },
  actions: {
    getPosts: ({ commit, rootGetters, rootState }, { params, accountId }) => {
      commit('POSTS_RESET');
      commit('UPDATE_IS_LOADING_TOP_POSTS', {
        value: true,
      });
      rootGetters.HTTP.get(`statistics/${accountId}/sorted-posts`, {
        params,
      })
        .then((response) => {
          if (rootState.accounts.currentAccount.id === accountId) {
            commit('SORTED_POSTS_UPDATED', response.data);
          }
          commit('UPDATE_IS_LOADING_TOP_POSTS', {
            value: false,
          });
        }, (err) => {
          commit('SET_ERROR', err);
        });
    },
    getStatistics: ({ commit, state, rootGetters, rootState }, { params, accountId }) => {
      commit('STATISTIC_RESET');
      const queryNames = state.isLoading.queries;

      rootGetters.HTTP.get(`statistics/${accountId}/statistic`, {
        params,
      }).then((response) => {
        if (rootState.accounts.currentAccount.id === accountId) {
          queryNames.statistic = true;
          commit('UPDATE_IS_LOADING', {
            queries: queryNames,
          });


          const newParams = {
            ...params,
            statistic_id: Object.values(response.data)[0].days[0].id,
          };

          commit('UPDATE_STATISTIC_FIELD', {
            field: Object.keys(response.data)[0],
            value: Object.values(response.data)[0],
          });

          console.log(newParams);

          Object.keys(queryNames).forEach((value) => {
            if (value === 'statistic') {
              return;
            }
            rootGetters.HTTP.get(`statistics/${accountId}/${value}`, {
              params: newParams,
            }).then((res) => {
              if (rootState.accounts.currentAccount.id === accountId) {
                queryNames[value] = true;
                commit('UPDATE_IS_LOADING', {
                  progress: (100 / Object.keys(queryNames).length) * Object.values(queryNames).reduce((count, element) => count + (element === true)),
                  queries: queryNames,
                });
                commit('UPDATE_STATISTIC_FIELD', {
                  field: Object.keys(res.data)[0],
                  value: Object.values(res.data)[0],
                });
              }
            }, (err) => {
              commit('SET_ERROR', err);
            });
          });
        }
      }, (err) => {
        commit('SET_ERROR', err);
      });
    },
  },
};
