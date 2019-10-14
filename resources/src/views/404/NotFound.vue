<template lang="pug">
  include ../../tools/mixins.pug
  +b.page-404
    +b.not-found-container
      +e.title-404 404
      +e.not-found Page not found
      +b.back_button
        +e.V-ICON.logo.display-1 mdi-arrow-left
        +e.A(href="/").link Go to back
</template>

<script>
export default {
  name: 'notFound',
};
</script>
