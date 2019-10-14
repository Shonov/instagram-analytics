<template lang="pug">
  include ../../tools/mixins.pug
  +b.page-404
    +b.not-found-container
      +e.title-404 500
      +e.not-found Interval Server Error
</template>

<script>
export default {
  name: 'IntervalServerError',
};
</script>
