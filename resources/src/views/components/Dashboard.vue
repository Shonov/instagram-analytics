<template lang="pug">
  include ../../tools/mixins.pug
  div
    v-navigation-drawer.menu-navigation-drawer(
    v-model='drawer', app='', clipped="", fixed, mini-variant, mobile-break-point="500", hide-overlay
    )
      div
        v-list
          template(v-for='(item, i) in menuItems')
            v-layout(row='', v-if='item.heading', align-center='', :key='item.heading')
              v-flex(xs6='')
                v-subheader(v-if='item.heading')
                  | {{ item.heading }}
              v-flex.text-xs-center(xs6='')
                a.body-2.black--text(href='#!') {{ $t('dashboard.edit') }}
            v-list-tile(v-else='', :to='item.link', :key='item.title', :disabled="item.disabled" @click="isOpenCompetitor = false")
              v-list-tile-action
                v-icon {{ item.icon }}
              v-list-tile-content
                v-list-tile-title
                  | {{ $t(item.title) }}
          v-list-group(prepend-icon='mdi-account-multiple', :disabled="viewableCompetitorsAccounts.length === 0", @click="isOpenCompetitor = !isOpenCompetitor")
            v-list-tile(slot='activator')
              v-list-tile-title {{ $t('dashboard.competitors') }}
          <!--v-list-tile(v-for='(competitor, i) in competitors', :key='i', @click='selectCompetitor(competitor)')-->
          //  v-list-tile-action
          //    img.select-accounts__img(:src='competitor.pic_url')
          //  v-list-tile-title(
          //  v-text='competitor.login'
          //  )
    v-navigation-drawer.competitors-navigation-drawer(
    v-model="isOpenCompetitor",
    fixed,
    mobile-break-point="0",
    width="160"
    )
      +b.competitors-navigation-header
        +e.V-ICON.close(@click="isOpenCompetitor = false") mdi-close-circle-outline
        +e.title {{ $t('dashboard.competitors') }}
      +b.competitors-navigation-list
        +b.competitor(v-for="competitor in viewableCompetitorsAccounts", :key="competitor.id", @click="selectCompetitor(competitor)")
          img.competitor__logo(:src="competitor.pic_url || defaultImage")
          +e.info-container
            +e.login {{competitor.login}}
            +e.statistic-container(v-if="competitor.last_statistic !== null")
              +e.statistic-item
                +e.statistic-title {{ $t('dashboard.subscribers') }}
                +e.statistic-number {{competitor.last_statistic.follower_count}}
              +e.statistic-item--last
                +e.statistic-title {{ $t('dashboard.subscriptions') }}
                +e.statistic-number {{competitor.last_statistic.following_count}}
            +e.statistic-container(v-else)
              +e.statistic-item {{ $t('dashboard.statisticsProcess') }}


    v-toolbar(app, fixed, clipped-left, flat, height="100px")
      v-toolbar-side-icon(@click.native='swithMenu')
        v-icon.toolbar__hamburger mdi-menu
      v-toolbar-title.toolbar__logo-container
        img.toolbar__logo-img(src='../../assets/souq_logo.png')
      v-spacer
      v-menu(:nudge-width='50')
        v-toolbar-title.selected-language(slot='activator')
          template(v-if="selectedLocale === 'en'")
            img.selected-language__icon(src="../../assets/uk-logo.png")
          template(v-if="selectedLocale === 'ar'")
            img.selected-language__icon(src="../../assets/uae-logo.png")
          v-icon mdi-menu-down
        v-list
          v-list-tile(
          v-for='locale in locales',
          :key='locale',
          @click='setLocale(locale)'
          )
            v-list-tile-title(v-text='locale')
      v-menu(:nudge-width='0', v-if="viewableSelfAccounts.length !== 0")
        +b.V-TOOLBAR-TITLE(slot='activator').select-accounts
          +e.container
            +e.SPAN.login {{currentAccount.login}}
            +e.SPAN.logout-container(@click.stop="onLogout")
              +e.SPAN.logout {{ $t('dashboard.logout') }}
              +e.V-ICON.logout-icon(small) mdi-exit-to-app
          img.select-accounts__img(:src="currentAccount.pic_url  || defaultImage")
          +e.V-ICON mdi-menu-down
        v-list
          v-list-tile(
          v-for='account in viewableSelfAccounts',
          :key='account.id',
          @click='setCurrentAccount(account)'
          )
            v-list-tile-title(v-text='account.login')
      v-menu(:nudge-width='0', v-else="")
        +b.V-TOOLBAR-TITLE(slot='activator').select-accounts
          +e.container
            +e.SPAN.logout-container(@click.stop="onLogout")
              +e.SPAN.logout {{ $t('dashboard.logout') }}
              +e.V-ICON.logout-icon(small) mdi-exit-to-app
      +b.V-BTN.logout-mobile(icon, @click="onLogout")
        v-icon mdi-exit-to-app
    main
      v-content
        <!--v-container(fluid='')-->
        router-view(:key='$route.fullPath')

        //v-footer(color='indigo', app='', inset='')
      span.white--text © 2018


</template>

<script>
import { mapGetters } from 'vuex';
// import echarts from 'echarts';
import defaultImage from '../../assets/default.jpg';

export default {
  name: 'dashboard-component',
  data: () => ({
    drawer: null,
    selectedLocale: 'en',
    locales: [
      'en', 'ar',
    ],
    isOpenCompetitor: false,
    defaultImage,
  }),
  computed: {
    ...mapGetters({
      selfAccounts: 'accounts/selfAccounts',
      competitors: 'accounts/competitors',
      user: 'accounts/user',
      currentAccount: 'accounts/currentAccount',
    }),
    viewableSelfAccounts() {
      if (this.selfAccounts === undefined) {
        return [];
      }
      return this.selfAccounts.filter(account => this.isAccountsWithStatistics(account));
    },
    viewableCompetitorsAccounts() {
      if (this.competitors === undefined) {
        return [];
      }
      return this.competitors.filter(account => this.isAccountsWithStatistics(account));
    },
    menuItems() {
      return [
        // { icon: 'mdi-home', title: 'menu.dashboard', link: 'dashboard' },
        { icon: 'mdi-account', title: 'menu.accounts', link: 'accounts' },
        { icon: 'mdi-finance', title: 'menu.statistics', link: 'statistics', disabled: this.viewableSelfAccounts.length === 0 },
        // {
        //   icon: 'keyboard_arrow_up',
        //   'icon-alt': 'keyboard_arrow_down',
        //   title: 'Crud',
        //   model: true,
        //   children: [
        //     { icon: 'home', title: 'Party', link: '/party' },
        //     { icon: 'home', title: 'Notes', link: '/notes' },
        //     { icon: 'announcement', title: 'Notes2', link: '/notes2' },
        //   ],
        // },
      ];
    },
  },
  created() {
    if (this.$i18n) {
      this.$i18n.locale = this.selectedLocale.toLowerCase();
    }
    this.$store.dispatch('accounts/setUser', this.$auth.user());
  },
  methods: {
    isAccountsWithStatistics(account) {
      return account.last_statistic !== null;
    },
    swithMenu() {
      this.drawer = !this.drawer;
      if (this.drawer === false) {
        this.isOpenCompetitor = false;
      }
    },
    selectCompetitor(account) {
      this.setCurrentAccount(account);
      this.$router.push({ name: 'statistics' });
      this.isOpenCompetitor = false;
    },
    setCurrentAccount(account) {
      this.$store.dispatch('accounts/setCurrentAccount', account);
    },
    setLocale(locale) {
      this.selectedLocale = locale;
      if (this.$i18n) {
        this.$i18n.locale = this.selectedLocale.toLowerCase();
      }
      this.checkSystemLocale();
      // let graph = echarts.init(document.querySelector('.echarts')[0]);
      // graph._model.option.xAxis.inverse = true;
      // graph._model.option.yAxis.position = 'right';
      // graph.dispatchAction({
      //   inverse: true,
      //   position: 'right',
      // });
    },
    onLogout() {
      this.$auth.logout({
        makeRequest: true,
        data: {
          token: this.$auth.token(),
        },
      });
    },
    checkSystemLocale() {
      const el = document.body;

      if (this.$i18n.locale === 'ar') {
        el.classList.add('ar');
      } else {
        el.classList.remove('ar');
      }
    },
  },
};
</script>
