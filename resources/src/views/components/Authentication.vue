<template lang="pug">
    v-content.authentication-page
        v-container(fluid='', fill-height='')
            v-layout(align-center='', justify-center='')
                v-flex(xs10='', sm9='', md6='')
                    .main-title {{ $t('authentication.titles.title') }}
                    router-view
</template>

<script>
export default {
  name: '',
};
</script>
