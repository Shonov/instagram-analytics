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
      type='text'
      )
        +e.input_lable(slot="label") {{ $t('authentication.form.login') }}
      +e.V-TEXT-FIELD#password(
      prepend-icon='mdi-lock',
      v-validate="{required:true, min:6}",
      data-vv-name="password",
      v-model="form.password",
      :error-messages="errors.collect('password')",
      name='password',
      color="white",
      type='password'
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
        color="false",
        @click="submitForm",
        :loading="isLoading"
        ) {{ $t('buttons.sign_up') }}
    +e.buttons-container--last
      +e.V-BTN.select_page(
      flat,
      to="login",
      color="white"
      ) {{ $t('authentication.links.to_login') }}

</template>

<script>
export default {
  name: 'register',
  data: () => ({
    form: {
      email: '',
      password: '',
    },
    errorRegister: false,
    success: false,
    isLoading: false,
  }),
  methods: {
    submitForm() {
      this.$validator.validate().then((valid) => {
        if (valid) {
          this.register();
          return true;
        }
        return false;
      });
    },
    register() {
      this.errorRegister = false;
      this.isLoading = true;
      const app = this;
      this.$auth.register({
        data: {
          email: app.form.email,
          password: app.form.password,
        },
        success() {
          this.isLoading = false;
          app.success = true;
        },
        error(e) {
          this.isLoading = false;
          this.errorRegister = e.response.data.message;
        },
        redirect: 'login',
      });
    },
  },
};
</script>
