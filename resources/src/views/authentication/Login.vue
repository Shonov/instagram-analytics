<template lang="pug">
  include ../../tools/mixins.pug
  +b.authentication
    v-form.authentication-form
      +e.title-container
        +e.title
          | {{ $t('authentication.titles.loginPage') }}
      +e.V-TEXT-FIELD(
      prepend-icon='mdi-account',
      v-validate="{required:true, email: true}",
      data-vv-name="email",
      v-model="form.email",
      :error-messages="errors.collect('email')",
      name='login',
      color="white",
      type='text',
      autofocus="autofocus",
      )
        +e.input_lable(slot="label") {{ $t('authentication.form.login') }}
      +e.V-TEXT-FIELD.select_input#password(
      prepend-icon='mdi-lock',
      v-validate="{required:true}",
      data-vv-name="password",
      v-model="form.password",
      :error-messages="errors.collect('password')",
      name='password',
      color="white",
      type='password',
      )
        +e.input_lable(slot="label") {{ $t('authentication.form.password') }}
      +e.V-ALERT.error-login(
        v-if="errorRegister",
        v-model="errorRegister",
        color="error",
        icon="mdi-alert-circle",
        outline,
        ) {{ $t('authentication.' + errorRegister) }}
      +e.buttons-container
        +e.V-BTN.select_button(
        @click="submitForm",
        :loading="isLoading"
        ) {{ $t('buttons.sign_in') }}
    <!--+e.buttons-container&#45;&#45;last-->
      <!--+e.V-BTN.select_page(-->
      <!--flat,-->
      <!--to="register",-->
      <!--color="white"-->
      <!--) {{ $t('authentication.links.to_register') }}-->

</template>

<script>
export default {
  name: 'login',
  data: () => ({
    form: {
      email: null,
      password: null,
    },
    isLoading: false,
    errorRegister: false,
  }),
  mounted() {
    if (this.$auth.ready()) {
      if (this.$auth.user() !== {} && this.$auth.user().email !== undefined) {
        this.$router.push('/accounts');
      }
      window.console.log(this.$auth.user());
    }
  },
  methods: {
    submitForm() {
      this.$validator.validate().then((valid) => {
        if (valid) {
          this.login();
          return true;
        }
        return false;
      });
    },
    login() {
      this.isLoading = true;
      const app = this;
      this.$auth.login({
        data: {
          email: app.form.email,
          password: app.form.password,
        },
        success(res) {
          this.isLoading = false;
          const token = this.$auth.token();
          window.console.log('login success', res, token);
        },
        error(e) {
          this.isLoading = false;
          this.errorRegister = e.response.data.msg;
        },
        rememberMe: true,
        redirect: '/accounts',
        fetchUser: true,
      });
    },
  },
};
</script>
