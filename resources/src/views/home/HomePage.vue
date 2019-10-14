<template lang="pug">
    v-content
        //v-container(fluid='', fill-height='')
            v-layout(align-center='', justify-center='')
                v-flex(xs12='', sm8='', md4='')
                    router-link(to="register")
                        v-btn(color='primary') {{ $t('authentication.links.to_register') }}
                    router-link(to="login")
                        v-btn(color='primary') {{ $t('authentication.links.to_login') }}
        v-container(fluid='', fill-height='')
            v-layout(row='', wrap='', align-center='')
                v-flex(xs12='', mt-5='')
                    h1.text-xs-center Instagram Analytics
                    blockquote.blockquote.text-xs-center
                        | Its home page
                        br
                        | Laravel, PostgreSQL and Vue JS
                    div.text-xs-center
                        v-btn(color='primary', to='register') {{ $t('buttons.sign_up') }}
                        v-btn(to='login') {{ $t('buttons.sign_in') }}

</template>

<script>
export default {
  name: 'home-page',
};
</script>

<style scoped>

</style>
