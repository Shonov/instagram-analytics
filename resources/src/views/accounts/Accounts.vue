<template lang="pug">
  include ../../tools/mixins.pug
  v-container(fluid='')
    +b.V-LAYOUT(row='', wrap='').statistic-container
      +e.main-title  {{ $t('accounts.accounts') }}
    +b.accounts-list
      +e.V-LAYOUT.list(row='', wrap='')
        +b.V-FLEX.account-card(
        xs12='', sm6='', md4='', xl='',
        v-for="account in selfAccounts",
        :key="account.id",
        @click="selectAccount(account)"
        )
          +e.container
            +e.main-info
              img.account-card__logo(:src="account.pic_url || defaultImage")
              +e.login.title @{{ account.login }}
            +e.statistic-container(v-if="account.last_statistic !== null && !account.is_private")
              +b.account-card-statistic
                +e.title {{ $t('accounts.subscribers') }}
                +e.statistic.subheading {{ account.last_statistic.follower_count }}

              +b.account-card-statistic
                +e.title {{ $t('accounts.subscriptions') }}
                +e.statistic.subheading {{ account.last_statistic.following_count }}

              +b.account-card-statistic
                +e.title {{ $t('accounts.likes') }}
                +e.statistic.subheading {{ account.last_statistic.like_count }}
            +e.statistic-container(v-else-if="account.is_private")
              +e.private-account {{ $t('accounts.privateAccount') }}
            +e.actions-container(v-if="account.last_statistic !== null || account.is_private === true")
              +e.V-BTN.button(depressed='', color='error', @click.stop="changeRemoveDialog(true, account)") {{ $t('buttons.delete') }}
            +e.statistic-container(v-else)
              +e.title.subheading {{ $t('accounts.statisticsCompile') }}
        +b.V-FLEX.account-card--add(
        xs12='',
        sm6='',
        md4='',
        xl='',
        @click="changeStatusDialog(true, 'self_account')"
        )
          +e.add-container
            +e.V-ICON.add-icon.display-3 mdi-account-circle
            +e.title.title {{ $t('accounts.addAccount') }}

    +b.V-LAYOUT(row='', wrap='').statistic-container
      +e.main-title {{ $t('accounts.competitors') }}
    +b.accounts-list
      +e.V-LAYOUT.list(row='', wrap='')
        +b.V-FLEX.account-card(
        xs12='', sm6='', md4='', xl='',
        v-for="account in competitors",
        :key="account.id",
        @click="selectAccount(account)"
        )
          +e.container
            +e.main-info
              img.account-card__logo(:src="account.pic_url || defaultImage")
              +e.login.title @{{ account.login }}
            +e.statistic-container(v-if="account.last_statistic !== null")
              +b.account-card-statistic
                +e.title {{ $t('accounts.subscribers') }}
                +e.statistic.subheading {{ account.last_statistic.follower_count }}

              +b.account-card-statistic
                +e.title {{ $t('accounts.subscriptions') }}
                +e.statistic.subheading {{ account.last_statistic.following_count }}

              +b.account-card-statistic
                +e.title {{ $t('accounts.likes') }}
                +e.statistic.subheading {{ account.last_statistic.like_count }}
            +e.actions-container(v-if="account.last_statistic !== null")
              +e.V-BTN.button(depressed='', color='error', @click.stop="changeRemoveDialog(true, account)") {{ $t('buttons.delete') }}
            +e.statistic-container(v-else)
              +e.title.subheading {{ $t('accounts.statisticsCompile') }}
        +b.V-FLEX.account-card--add(
        xs12='',
        sm6='',
        md4='',
        xl='',
        @click="changeStatusDialog(true, 'competitor')"
        )
          +e.add-container
            +e.V-ICON.add-icon.display-3 mdi-account-circle
            +e.title.title {{ $t('accounts.addAccount') }}

    v-dialog(v-model='addAccountModal.isOpen', persistent='', max-width='500')
      v-card
        v-card-title.headline {{ $t('accounts.addAccount') }}
        v-card-text
          v-container(grid-list-md='')
            v-layout(wrap='')
              v-flex(xs12='', v-if="user.max_accounts !== (selfAccounts.length + competitors.length)")
                form
                  v-text-field(
                  v-model="addAccountModal.account",
                  prepend-icon='mdi-at',
                  :label="$t('accounts.loginAccount')",
                  :loading="loading",
                  v-validate="{ not_in: (addAccountModal.type === 'self_account' ? selfAccounts : competitors).map(e => e.login) }",
                  data-vv-name="account",
                  data-vv-as="login",
                  :error-messages="errors.collect('account')",
                  )
                  v-alert(:value='accountNotFound', type='error', outline)
                    | {{ $t('accounts.accountNotFound') }}
              v-flex(xs12='', v-else, v-html=" $t('accounts.max_reached')")

        v-card-actions
          v-spacer
          v-btn(color='red darken-1', flat='', @click.native='changeStatusDialog(false)') {{ $t('buttons.cancel') }}
          v-btn(color='blue darken-1', flat='', @click.native='addAccount', :loading="loading", :disabled="errors.has('account')", v-if="user.max_accounts !== (selfAccounts.length + competitors.length)") {{ $t('buttons.add') }}

    v-dialog(v-model='removeAccountDialog.isOpen', persistent='', max-width='500')
      v-card
        v-card-title.headline {{ $t('accounts.confirm') }} - {{ removeAccountDialog.account.login }}
        v-card-actions
          v-spacer
          v-btn(color='blue darken-1', flat='', @click.native='changeRemoveDialog(false)') {{ $t('buttons.cancel') }}
          v-btn(color='red darken-1', flat='', @click.native='removeAccount', :loading="loading") {{ $t('buttons.delete') }}
</template>

<script>
import { mapGetters } from 'vuex';
import defaultImage from '../../assets/default.jpg';

export default {
  name: 'accounts',
  computed: {
    ...mapGetters({
      selfAccounts: 'accounts/selfAccounts',
      competitors: 'accounts/competitors',
      user: 'accounts/user',
      currentAccount: 'accounts/currentAccount',
    }),
  },
  data: () => ({
    accountNotFound: false,
    loading: false,
    addAccountModal: {
      isOpen: false,
      account: null,
      type: null,
    },
    removeAccountDialog: {
      isOpen: false,
      account: {},
    },
    defaultImage,
  }),
  methods: {
    isAccountsWithStatistics(account) {
      return account.last_statistic !== null;
    },
    changeStatusDialog(isOpen = false, type = null) {
      this.addAccountModal.isOpen = isOpen;
      this.addAccountModal.type = type;
      this.addAccountModal.account = null;
      this.accountNotFound = false;
    },
    changeRemoveDialog(isOpen = false, account = {}) {
      this.removeAccountDialog.isOpen = isOpen;
      this.removeAccountDialog.account = account;
    },
    async addAccount() {
      this.accountNotFound = false;
      this.loading = true;
      const response = await this.$store.dispatch('accounts/addAccount', {
        login: this.addAccountModal.account,
        type: this.addAccountModal.type,
      });

      this.accountNotFound = !response.status;

      await this.$store.dispatch('accounts/getAccounts');
      if (response.status) {
        this.changeStatusDialog(false);
      }
      this.loading = false;
    },
    async removeAccount() {
      this.loading = true;
      await this.$store.dispatch('accounts/removeAccount', {
        accountId: this.removeAccountDialog.account.id,
      });
      await this.$store.dispatch('accounts/getAccounts');
      this.changeRemoveDialog(false);
      this.loading = false;
    },
    setCurrentAccount(account) {
      this.$store.dispatch('accounts/setCurrentAccount', account);
    },
    selectAccount(account) {
      if (!this.isAccountsWithStatistics(account)) {
        return;
      }
      this.setCurrentAccount(account);
      this.$router.push({ name: 'statistics' });
    },
  },
  created() {
    this.$store.dispatch('accounts/getAccounts');
  },
};
</script>
