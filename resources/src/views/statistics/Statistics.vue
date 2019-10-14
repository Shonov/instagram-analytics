<template lang="pug">
  include ../../tools/mixins.pug

  v-container(fluid='')
    <!--+b.V-LAYOUT.statistic-loading(v-if="statistic.length === 0")-->
    <!--v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')-->
    <!--+e.title {{ $t('statistics.tags.statisticsProccess') }}-->
    <!--+b.V-LAYOUT.statistic-loading(v-else-if="value !== 100 || isLoading.load === true || !statistic || currentAccount.lastStatistic === null")-->
    <!--v-progress-circular(:size='70', :width='7', color='purple', indeterminate='', :value="loading")-->
    <!--+e.title {{ $t('statistics.tags.loading') }} {{loading}}%-->

    v-dialog(v-model="dialog" width="500")
      v-card.info-graph
        v-card-title(class="" primary-title)
          span.card__title-text {{ $t('statistics.tags.aboutGraph') }}
          v-spacer
          v-menu(bottom, left)
            v-btn(slot="activator", icon, small, @click="dialog = false")
              v-icon(color="white", small).info-icon mdi-close
        v-card-text {{ info }}

    v-layout(row='', wrap='')
      +b.V-LAYOUT(row='', wrap='').statistic-container.its-border
        +b.V-FLEX(xs12='', sm4='').period-container
          +e.V-MENU(
          ref="menu1",
          :close-on-content-click="false",
          v-model="menu1",
          :nudge-right="40",
          lazy,
          transition="scale-transition",
          offset-y,
          full-width,
          max-width="290px",
          min-width="290px"
          )
            +e.V-TEXT-FIELD(
            slot="activator",
            v-model="startDateFormatted",
            :label="$t('statistics.tags.startDate')",
            persistent-hint,
            prepend-icon="mdi-calendar",
            @blur="startDate = parseDate(startDateFormatted)"
            )
            +e.V-DATE-PICKER(
            v-model="startDate",
            next-icon="mdi-menu-right",
            prev-icon="mdi-menu-left",
            :min="currentAccount ? currentAccount.created_at.split(' ', 1).join() : false",
            :max="todayDate",
            no-title
            )
        +b.V-FLEX(xs12='', sm4='').period-container
          +e.V-MENU(
          ref="menu1",
          :close-on-content-click="false",
          v-model="menu2",
          :nudge-right="40",
          lazy,
          transition="scale-transition",
          offset-y,
          full-width,
          max-width="290px",
          min-width="290px"
          )
            +e.V-TEXT-FIELD(
            slot="activator",
            v-model="endDateFormatted",
            :label="$t('statistics.tags.endDate')",
            persistent-hint,
            prepend-icon="mdi-calendar",
            @сlick="endDate = parseDate(endDateFormatted)"
            )
            +e.V-DATE-PICKER(
            v-model="endDate",
            next-icon="mdi-menu-right",
            prev-icon="mdi-menu-left",
            :min="currentAccount ? currentAccount.created_at.split(' ', 1).join() : false",
            :max="todayDate",
            no-title
            )
        +b.V-FLEX(xs6='', sm1='').period-container
          +e.buttons-container
            v-btn(color='primary', @click="dispatchStatistic") {{ $t('statistics.tags.apply') }}
        +b.V-FLEX(xs6='', sm3='').period-container
          +e.buttons-container
            v-btn(color='primary', @click="downloadPdf", :loading="isLoadReport || isLoading.progress !== 100") {{ $t('statistics.tags.exportPDF') }}
      +b.V-LAYOUT(row='', wrap='').statistic-container
      +e.main-title {{ $t('statistics.sections.audience') }}

      +b.V-LAYOUT(row='', wrap='').statistic-container
        +b.V-FLEX(xs12='', sm3='').statistic-info
          +e.container
            +e.title {{ $t('statistics.titles.countFollowers') }}
            v-progress-circular(v-if="!statistic" :size='30', :width='5', color='purple', indeterminate='')
            +e(v-else="").statistic {{ lastDayStatistic.follower_count }}
        +b.V-FLEX(xs12='', sm3='').statistic-info
          +e.container
            +e.title {{ $t('statistics.titles.growthFollowersPeriod') }}
            v-progress-circular(v-if="!statistic" :size='30', :width='5', color='purple', indeterminate='')
            +e(v-else="").statistic {{ subsCountGrowth }}
        +b.V-FLEX(xs12='', sm3='').statistic-info
          +e.container
            +e.title {{ $t('statistics.titles.maxChangePerDay') }}
            v-progress-circular(v-if="!statistic" :size='30', :width='5', color='purple', indeterminate='')
            +e(v-else="").statistic {{ maxChangePerDay }}
        +b.V-FLEX(xs12='', sm3='').statistic-info
          +e.container
            +e.title {{ $t('statistics.titles.averagePerDay') }}
            v-progress-circular(v-if="!statistic" :size='30', :width='5', color='purple', indeterminate='')
            +e(v-else="").statistic {{ subsAverage }}
      +b.V-FLEX(xs12='', sm12='', mt-3='' :style="[(!statistic || statistic.length <= 1) ? {'height': '300px'} : '']").graph-container
        +e.title-block
          +e.title.headline {{ $t('statistics.titles.followersGrowth') }}
          v-btn(icon, @click="getInfoText('statistics.description.followersGrowth')")
            v-icon.display-1 mdi-information-outline
        +b(v-if="!statistic").loader
          v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
        +b(v-else-if="statistic.length === 1").loader--is_updating
          | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
        +b(v-else-if="statistic.length === 0").loader {{ $t('statistics.tags.noData') }}
        +e.ECHART(v-else-if="statistic && statistic.length !== 0" :options="subs", :auto-resize="!!true").chart
      +b.V-LAYOUT(row='', wrap='').statistic-container
        +b.V-FLEX(xs12='', sm6='').statistic-info
          +e.container
            +e.title {{ $t('statistics.titles.totalNumberFollowers') }}
            v-progress-circular(v-if="!statistic" :size='30', :width='5', color='purple', indeterminate='')
            +e(v-else="").statistic {{ totalSub }}
        +b.V-FLEX(xs12='', sm6='').statistic-info
          +e.container
            +e.title {{ $t('statistics.titles.totalNumberUnsubscribes') }}
            v-progress-circular(v-if="!statistic" :size='30', :width='5', color='purple', indeterminate='')
            +e(v-else="").statistic {{ Math.abs(totalUnsub) }}
      +b.V-FLEX(xs12='', sm12='', mt-3='' :style="[(!isSubsGrabbed || isNeedToGrabSubs || !statistic) ? {'height': '300px'} : '']").graph-container
        +e.title-block
          +e.title.headline {{ $t('statistics.titles.followersAndUnsubscribes') }}
          v-btn(icon, @click="getInfoText('statistics.description.followersAndUnsubscribes')")
            v-icon.display-1 mdi-information-outline
        +b(v-if="!isLoading.queries.statistic || !statistic").loader
          v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
        +b(v-else-if="!isSubsGrabbed").loader--is_updating
          | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
        +b(v-else-if="isNeedToGrabSubs").loader.loader--is_updating
          | {{ $t('statistics.tags.statisticIsGrabbing.first') }}
          | {{ timeToGrabSubs }}
          | {{ $t('statistics.tags.statisticIsGrabbing.last') }}
        +e.ECHART(v-else-if="statistic" :options="subscribers", :auto-resize="!!true").chart
      +b.V-LAYOUT(row='', wrap='').statistic-container
        +b.V-FLEX(xs12='', sm6='' mt-3='').graph
          +e.container.graph-container(:style="[(isNeedToGrabSubs || !isSubsGrabbed || !privateAndOpenAccounts || (privateAndOpenAccounts && Object.values(privateAndOpenAccounts).reduce((a, b) => a + b) === 0)) ? {'height': '300px'} : '']")
            +e.title-block
              +e.title.headline {{ $t('statistics.titles.PrivateAndOpenAccounts') }}
              v-btn(icon, @click="getInfoText('statistics.description.PrivateAndOpenAccounts')")
                v-icon.display-1 mdi-information-outline
            +b(v-if="!isLoading.queries['private-and-open-accounts']").loader
              v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
            +b(v-else-if="!isSubsGrabbed").loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
            +b(v-else-if="isNeedToGrabSubs").loader.loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.first') }}
              | {{ timeToGrabSubs }}
              | {{ $t('statistics.tags.statisticIsGrabbing.last') }}
            +b(v-else-if="privateAndOpenAccounts && Object.values(privateAndOpenAccounts).reduce((a, b) => a + b) === 0").loader {{ $t('statistics.tags.noData') }}
            +e.ECHART(v-else-if="privateAndOpenAccounts" :options="privateOpenAccounts", :auto-resize="!!true")
        +b.V-FLEX(xs12='', sm6='' mt-3='').graph
          +e.container.graph-container(:style="[(isNeedToGrabSubs || !isSubsGrabbed || !businessAndUsualAccounts || (businessAndUsualAccounts && Object.values(businessAndUsualAccounts).reduce((a, b) => a + b) === 0)) ? {'height': '300px'} : '']")
            +e.title-block
              +e.title.headline {{ $t('statistics.titles.countBusinessAndRegularAccounts') }}
              v-btn(icon, @click="getInfoText('statistics.description.countBusinessAndRegularAccounts')")
                v-icon.display-1 mdi-information-outline
            +b(v-if="!isLoading.queries['business-and-usual-accounts']").loader
              v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
            +b(v-else-if="!isSubsGrabbed").loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
            +b(v-else-if="isNeedToGrabSubs").loader.loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.first') }}
              | {{ timeToGrabSubs }}
              | {{ $t('statistics.tags.statisticIsGrabbing.last') }}
            +b(v-else-if="businessAndUsualAccounts && Object.values(businessAndUsualAccounts).reduce((a, b) => a + b) === 0").loader {{ $t('statistics.tags.noData') }}
            +e.ECHART(v-else-if="businessAndUsualAccounts" :options="businessAndNormalAccounts", :auto-resize="!!true").chart

      <!--+b.V-LAYOUT(row='', wrap='').statistic-container-->
      <!--+b.V-FLEX(xs12='', sm6='', mt-3='').graph-->
      <!--+e.container.graph-container-->
      <!--+b(v-if="Object.values(genderFollowers).reduce((a, b) => a + b) === 0").loader {{ $t('statistics.tags.noData') }}-->
      <!--+e.title-block-->
      <!--+e.title.headline {{ $t('statistics.titles.GenderOfFollowers') }}-->
      <!--v-btn(icon, @click="getInfoText('statistics.description.GenderOfFollowers')")-->
      <!--v-icon.display-1 mdi-information-outline-->
      <!--+e.ECHART(:options="genderFollowersGraph", :auto-resize="!!true").chart-->
      <!--+b.V-FLEX(xs12='', sm12='', mt-3='' :style="[countBots === false ? {'height': '300px'} : '']").graph-container-->
      <!--+e.title-block-->
      <!--+e.title.headline {{ $t('statistics.titles.countBots') }}-->
      <!--v-btn(icon, @click="getInfoText('statistics.description.countBots')")-->
      <!--v-icon.display-1 mdi-information-outline-->
      <!--+b(v-if="countBots === false").loader-->
      <!--v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')-->
      <!--+e.ECHART(v-else-if="statistic" :options="botsGraph", :auto-resize="!!true").chart-->

      +b.V-LAYOUT(row='', wrap='').statistic-container
        +b.V-FLEX(xs12='', sm6='').graph
          +e.container.graph-container(:style="[(isNeedToGrabSubs || !isSubsGrabbed || !topNewFollowers || topNewFollowers.length === 0) ? {'height': '300px'} : '']")
            +e.title.headline.for-list-sub-unsub {{ $t('statistics.titles.topNewFollowers') }}
            +b(v-if="!topNewFollowers").loader
              v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
            +b(v-else-if="!isSubsGrabbed").loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
            +b(v-else-if="isNeedToGrabSubs").loader.loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.first') }}
              | {{ timeToGrabSubs }}
              | {{ $t('statistics.tags.statisticIsGrabbing.last') }}
            +b(v-else-if="topNewFollowers.length === 0").loader {{ $t('statistics.tags.noData') }}
            +b(v-else="" v-for="(item, index) in topNewFollowers").followers
              +e.container
                +e.count
                  +e {{index + 1}}
                img(:src="item.follower.profile_pic_url" @error="setDefaultImage(item)").followers__pic
                +e.A(:href="'https://www.instagram.com/'+item.follower.full_name", @click.prevent="routeToInstagram('https://www.instagram.com/'+item.follower.full_name)").follower @{{item.follower.full_name}}
              +e.count_followers {{item.follower_count}}
        +b.V-FLEX(xs12='', sm6='').graph
          +e.container.graph-container(:style="[(isNeedToGrabSubs || !isSubsGrabbed || !topLostFollowers || topLostFollowers.length === 0) ? {'height': '300px'} : '']")
            +e.title.headline.for-list-sub-unsub {{ $t('statistics.titles.topUnsubscribers') }}
            +b(v-if="!topLostFollowers").loader
              v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
            +b(v-else-if="!isSubsGrabbed").loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
            +b(v-else-if="isNeedToGrabSubs").loader.loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.first') }}
              | {{ timeToGrabSubs }}
              | {{ $t('statistics.tags.statisticIsGrabbing.last') }}
            +b(v-else-if="topLostFollowers.length === 0").loader {{ $t('statistics.tags.noData') }}
            +b(v-else="" :style="[!topLostFollowers ? {'height': '300px'} : '']" v-for="(item, index) in topLostFollowers").followers
              +e.container
                +e.count
                  +e {{index + 1}}
                img(:src="item.follower.profile_pic_url" @error="setDefaultImage(item)").followers__pic
                +e.A(:href="'https://www.instagram.com/'+item.follower.full_name", @click.prevent="routeToInstagram('https://www.instagram.com/'+item.follower.full_name)").follower @{{item.follower.full_name}}
              +e.count_followers {{item.follower_count}}

      +b.V-LAYOUT(row='', wrap='').statistic-container
        +e.main-title {{ $t('statistics.sections.posts') }}

      +b.V-FLEX(xs12='', sm12='', mt-3='' :style="[!isPostsGrabbed || (!numberOfPosts || numberOfPosts.length === 0) ? {'height': '300px'} : '']").graph-container
        +e.title-block
          +e.title.headline {{ $t('statistics.titles.CountPosts') }}
          v-btn(icon, @click="getInfoText('statistics.description.CountPosts')")
            v-icon.display-1 mdi-information-outline
        +b(v-if="!isLoading.queries['number-posts']").loader
          v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
        +b(v-else-if="!isPostsGrabbed").loader--is_updating
          | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
        +b(v-else-if="numberOfPosts.length === 0").loader {{ $t('statistics.tags.noData') }}
        +e.ECHART(v-else-if="numberOfPosts.length !== 0" :options="countPosts", :auto-resize="!!true").chart

      +b.V-FLEX(xs12='', sm12='', mt-3='').graph-container
        +b.V-FLEX(xs12='', sm12='').header
          +e.block-option
            +e.title.headline {{ $t('statistics.titles.topPosts') }}
            +e.V-SELECT(
            v-model="selectedOption",
            :items="optionsPosts",
            append-icon="mdi-menu-down",
            item-value="index",
            item-text="text",
            @input="dispatchPosts()"
            ).options
          +e.btn-list
            v-btn(color="indigo" @click="sort = 0, dispatchPosts()" :class="0 === sort ? 'header__btn-active' : '' ").header__btn {{ $t('statistics.graph.topPosts.all') }}
            v-btn(color="indigo" @click="sort = 1, dispatchPosts()" :class="1 === sort ? 'header__btn-active' : '' ").header__btn {{ $t('statistics.graph.topPosts.photo') }}
            v-btn(color="indigo" @click="sort = 2, dispatchPosts()" :class="2 === sort ? 'header__btn-active' : '' ").header__btn {{ $t('statistics.graph.topPosts.video') }}
            v-btn(color="indigo" @click="sort = 8, dispatchPosts()" :class="8 === sort ? 'header__btn-active' : '' ").header__btn {{ $t('statistics.graph.topPosts.carousel') }}
        +b.posts(v-if="topPosts.length !== 0")
          +e(v-for="item in topPosts" @click.prevent="routeToInstagram('//www.instagram.com/p/' + item.code)").post
            +e.full-image
              img.posts__image(:src="item.pic_url")
            +e.list
              +e.item
                +e.V-ICON.icon-orange.display-0 mdi-fire
                +e.alt {{ (((item.like_count+item.comment_count)/lastDayStatistic.follower_count) * 100).toFixed(2) }}%
              +e.item
                +e.V-ICON.icon-red.display-0 mdi-cards-heart
                +e.alt {{item.like_count}}
              +e.item
                +e.V-ICON.icon.display-0 mdi-comment
                +e.alt {{item.comment_count}}
            +e.posted-at {{ $moment(item.posted_at).format('ddd, MMMM D, YYYY') }}
        +b.V-LAYOUT.statistic-loading(v-if="!isLoading.queries.posts || isLoadingTopPosts")
          v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
          +e.title {{ $t('statistics.tags.loading') }}
        +b(v-else-if="!isPostsGrabbed").posts-loading
          | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
        +b(v-else-if="topPosts.length === 0").posts-loading {{ $t('statistics.tags.noData') }}

      +b.V-LAYOUT(row='', wrap='', v-if="isPostsGrabbed").statistic-container
        +b.V-FLEX(xs12='', sm4='').statistic-info
          +e.container
            +e.title {{ $t('statistics.titles.totalNumberLikes') }}
            v-progress-circular(v-if="!statistic" :size='30', :width='5', color='purple', indeterminate='')
            +e(v-else="").statistic {{ lastDayStatistic.like_count }}
        +b.V-FLEX(xs12='', sm4='').statistic-info
          +e.container
            +e.title {{ $t('statistics.titles.likesCountGrowthPerPost') }}
            v-progress-circular(v-if="!statistic" :size='30', :width='5', color='purple', indeterminate='')
            +e(v-else="").statistic {{ likesCountGrowthPerPost }}
        +b.V-FLEX(xs12='', sm4='').statistic-info
          +e.container
            +e.title {{ $t('statistics.titles.likesCountGrowthPerDay') }}
            v-progress-circular(v-if="!averageLikesPerDay" :size='30', :width='5', color='purple', indeterminate='')
            +e(v-else="").statistic {{ averageLikesPerDay }}

      +b.V-FLEX(xs12='', sm12='', mt-3='' :style="[!isPostsGrabbed || (!statistic || statistic.length === 0) ? {'height': '300px'} : '']").graph-container
        +e.title-block
          +e.title.headline {{ $t('statistics.titles.likesGrowth') }}
          v-btn(icon, @click="getInfoText('statistics.description.likesGrowth')")
            v-icon.display-1 mdi-information-outline
        +b(v-if="!isLoading.queries.statistic").loader
          v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
        +b(v-else-if="!isPostsGrabbed").loader--is_updating
          | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
        +b(v-else-if="statistic.length === 0").loader {{ $t('statistics.tags.noData') }}
        +e.ECHART(v-else-if="statistic" :options="likes", :auto-resize="!!true").chart

      +b.V-FLEX(xs12='', sm12='', mt-3='' :style="[!isPostsGrabbed || (!statistic || statistic.length === 0) ? {'height': '300px'} : '']").graph-container
        +e.title-block
          +e.title.headline {{ $t('statistics.titles.commentsGrowth') }}
          v-btn(icon, @click="getInfoText('statistics.description.commentsGrowth')")
            v-icon.display-1 mdi-information-outline
        +b(v-if="!isLoading.queries.statistic").loader
          v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
        +b(v-else-if="!isPostsGrabbed").loader--is_updating
          | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
        +b(v-else-if="statistic.length === 0").loader {{ $t('statistics.tags.noData') }}
        +e.ECHART(v-else-if="statistic" :options="activityComments", :auto-resize="!!true").chart

      +b.V-LAYOUT(row='', wrap='').statistic-container
        +b.V-FLEX(xs12='', sm6='', mt-3='').graph
          +b.graph-container(:style="[!isPostsGrabbed || (!postTypes || (postTypes && postTypes.photos.count + postTypes.carousels.count === 0)) ? {'height': '300px'} : '']")
            +e.title-block
              +e.title.headline {{ $t('statistics.titles.activityPerPostImages') }}
              v-btn(icon, @click="getInfoText('statistics.description.activityPerPostImages')")
                v-icon.display-1 mdi-information-outline
            +b(v-if="!isLoading.queries['post-types']").loader
              v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
            +b(v-else-if="!isPostsGrabbed", :style="{'height': '300px'}").loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
            +b(v-else-if="postTypes && postTypes.photos.count + postTypes.carousels.count === 0" :style="{'height': '300px'}").loader {{ $t('statistics.tags.noData') }}
            +e.ECHART(v-else-if="postTypes || (postTypes && postTypes.photos.count + postTypes.carousels.count !== 0)" :options="activityPerPostImages", :auto-resize="!!true").chart
        +b.V-FLEX(xs12='', sm6='', mt-3='').graph
          +b.graph-container(:style="[!isPostsGrabbed || (!postTypes || (postTypes && postTypes.videos.count === 0)) ? {'height': '300px'} : '']")
            +e.title-block
              +e.title.headline {{ $t('statistics.titles.activityPerPostVideos') }}
              v-btn(icon, @click="getInfoText('statistics.description.activityPerPostVideos')")
                v-icon.display-1 mdi-information-outline
            +b(v-if="!isLoading.queries['post-types']").loader
              v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
            +b(v-else-if="!isPostsGrabbed", :style="{'height': '300px'}").loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
            +b(v-else-if="postTypes && postTypes.videos.count === 0").loader  {{ $t('statistics.tags.noData') }}
            +e.ECHART(v-else-if="postTypes || (postTypes && postTypes.videos.count !== 0)" :options="activityPerPostVideos", :auto-resize="!!true").chart

      +b.V-LAYOUT(row='', wrap='').statistic-container
        +b.V-FLEX(xs12='', sm6='' mt-3='').graph
          +e.container.graph-container(:style="[!isPostsGrabbed|| (!mostEngagingPostTypes || (mostEngagingPostTypes && Object.values(mostEngagingPostTypes).reduce((a, b) => a + b) === 0)) ? {'height': '300px'} : '']")
            +e.title-block
              +e.title.headline {{ $t('statistics.titles.engagingPostTypes') }}
              v-btn(icon, @click="getInfoText('statistics.description.engagingPostTypes')")
                v-icon.display-1 mdi-information-outline
            +b(v-if="!isLoading.queries['most-engaging-post-types']").loader
              v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
            +b(v-else-if="!isPostsGrabbed", :style="{'height': '300px'}").loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
            +b(v-else-if="mostEngagingPostTypes && Object.values(mostEngagingPostTypes).reduce((a, b) => a + b) === 0").loader {{ $t('statistics.tags.noData') }}
            +e.ECHART(v-else-if="mostEngagingPostTypes || (mostEngagingPostTypes && Object.values(mostEngagingPostTypes).reduce((a, b) => a + b) !== 0)" :options="engagingPostTypes", :auto-resize="!!true").chart
        +b.V-FLEX(xs12='', sm6='' mt-3='').graph
          +e.container.graph-container(:style="[!isPostsGrabbed || (!isLoading.queries['post-types'] || (postTypes && [postTypes.photos.count, postTypes.videos.count, postTypes.carousels.count].reduce((a, b) => a + b) === 0)) ? {'height': '300px'} : '']")
            +e.title-block
              +e.title.headline {{ $t('statistics.titles.postTypes') }}
              v-btn(icon, @click="getInfoText('statistics.description.postTypes')")
                v-icon.display-1 mdi-information-outline
            +b(v-if="!isLoading.queries['post-types']").loader
              v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
            +b(v-else-if="!isPostsGrabbed", :style="{'height': '300px'}").loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
            +b(v-else-if="postTypes && [postTypes.photos.count, postTypes.videos.count, postTypes.carousels.count].reduce((a, b) => a + b) === 0").loader {{ $t('statistics.tags.noData') }}
            +e.ECHART(v-else-if="postTypes && [postTypes.photos.count, postTypes.videos.count, postTypes.carousels.count].reduce((a, b) => a + b) !== 0" :options="graphPostTypes", :auto-resize="!!true").chart

      +b.V-FLEX(xs12='', sm12='', mt-3='' :style="[(!isPostsGrabbed || !isLoading.queries.statistic) ? {'height': '300px'} : '']").graph-container
        +e.title-block
          +e.title.headline {{ $t('statistics.titles.videoViews') }}
          v-btn(icon, @click="getInfoText('statistics.description.videoViews')")
            v-icon.display-1 mdi-information-outline
        +b(v-if="!isLoading.queries.statistic").loader
          v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
        +b(v-else-if="!isPostsGrabbed").loader--is_updating
          | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
        +e.ECHART(v-else-if="statistic" :options="videoViewsGraph", :auto-resize="!!true").chart

      +b.V-LAYOUT(row='', wrap='').statistic-container
        +e.main-title {{ $t('statistics.sections.optimization') }}

      +b.V-LAYOUT(row='', wrap='')
        +b.V-FLEX(xs12='', sm6='', mt-3='').graph
          +e.container.graph-container(:style="[(!isSubsGrabbed || isNeedToGrabSubs || !isLoading.queries['followers-by-our-followers'] || (isLoading.queries['followers-by-our-followers'] && Object.values(followersByOurFollowers).reduce((a, b) => a + b) === 0)) ? {'height': '300px'} : '']")
            +e.title-block
              +e.title.headline {{ $t('statistics.titles.followersByTheirFollowers') }}
              v-btn(icon, @click="getInfoText('statistics.description.followersByTheirFollowers')")
                v-icon.display-1 mdi-information-outline
            +b(v-if="!isLoading.queries['followers-by-our-followers']").loader
              v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
            +b(v-else-if="!isSubsGrabbed").loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
            +b(v-else-if="isNeedToGrabSubs").loader.loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.first') }}
              | {{ timeToGrabSubs }}
              | {{ $t('statistics.tags.statisticIsGrabbing.last') }}
            +b(v-else-if="isLoading.queries['followers-by-our-followers'] && Object.values(followersByOurFollowers).reduce((a, b) => a + b) === 0").loader {{ $t('statistics.tags.noData') }}
            +e.ECHART(v-else-if="followersByOurFollowers" :options="followersByOurFollowersGraph", :auto-resize="!!true").chart
        +b.V-FLEX(xs12='', sm6='', mt-3='').graph
          +e.container.graph-container(:style="[(!isSubsGrabbed || isNeedToGrabSubs || !isLoading.queries['followers-by-our-following'] || (isLoading.queries['followers-and-following'] && Object.values(followersByOurFollowing).reduce((a, b) => a + b) === 0)) ? {'height': '300px'} : '']")
            +e.title-block
              +e.title.headline {{ $t('statistics.titles.byNumberAccountFollowed') }}
              v-btn(icon, @click="getInfoText('statistics.description.byNumberAccountFollowed')")
                v-icon.display-1 mdi-information-outline
            +b(v-if="!isLoading.queries['followers-by-our-following']").loader
              v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
            +b(v-else-if="!isSubsGrabbed").loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
            +b(v-else-if="isNeedToGrabSubs").loader.loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.first') }}
              | {{ timeToGrabSubs }}
              | {{ $t('statistics.tags.statisticIsGrabbing.last') }}
            +b(v-else-if="isLoading.queries['followers-by-our-following'] && Object.values(followersByOurFollowing).reduce((a, b) => a + b) === 0").loader {{ $t('statistics.tags.noData') }}
            +e.ECHART(v-else-if="followersByOurFollowing" :options="followersByOurFollowingGraph", :auto-resize="!!true").chart

      +b.V-FLEX(xs12='', sm12='', mt-3='' :style="[(!isSubsGrabbed || isNeedToGrabSubs || !isLoading.queries['followers-and-following'] || (followersAndFollowing && Object.values(followersAndFollowing).reduce((a, b) => a + b) === 0)) ? {'height': '300px'} : '']").graph-container
        +e.title-block
          +e.title.headline {{ $t('statistics.titles.byRatioFollowersToFollowing') }}
          v-btn(icon, @click="getInfoText('statistics.description.byRatioFollowersToFollowing')")
            v-icon.display-1 mdi-information-outline
        +b(v-if="!isLoading.queries['followers-and-following']").loader
          v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
        +b(v-else-if="!isSubsGrabbed").loader--is_updating
          | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
        +b(v-else-if="isNeedToGrabSubs").loader.loader--is_updating
          | {{ $t('statistics.tags.statisticIsGrabbing.first') }}
          | {{ timeToGrabSubs }}
          | {{ $t('statistics.tags.statisticIsGrabbing.last') }}
        +b(v-else-if="isLoading.queries['followers-and-following'] && Object.values(followersAndFollowing).reduce((a, b) => a + b) === 0").loader {{ $t('statistics.tags.noData') }}
        +e.ECHART(v-else-if="followersAndFollowing" :options="followersAndFollowingGraph", :auto-resize="!!true").chart

      +b.V-LAYOUT(row='', wrap='').statistic-container
        +b.V-FLEX(xs12='', sm6='' mt-3='').graph
          +e.container.graph-container(:style="[(!isSubsGrabbed || isNeedToGrabSubs ||  !isLoading.queries.reach || (reachUsers && Object.values(reachUsers).reduce((a, b) => a + b) === 0)) ? {'height': '300px'} : '']")
            +e.title-block
              +e.title.headline {{ $t('statistics.titles.reach') }}
              v-btn(icon, @click="getInfoText('statistics.description.reach')")
                v-icon.display-1 mdi-information-outline
            +b(v-if="!isLoading.queries.reach").loader
              v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
            +b(v-else-if="!isSubsGrabbed").loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.common') }}
            +b(v-else-if="isNeedToGrabSubs").loader.loader--is_updating
              | {{ $t('statistics.tags.statisticIsGrabbing.first') }}
              | {{ timeToGrabSubs }}
              | {{ $t('statistics.tags.statisticIsGrabbing.last') }}
            +b(v-else-if="reachUsers && Object.values(reachUsers).reduce((a, b) => a + b) === 0").loader {{ $t('statistics.tags.noData') }}
            +e.ECHART(v-else-if="reachUsers" :options="reachUsersGraph", :auto-resize="!!true").chart
        +b.V-FLEX(xs12='', sm6='' mt-3='').graph
          +e.container.graph-container(:style="[(!engagementRate || !isLoading.queries.engagement) ? {'height': '300px'} : '']")
            +e.title-block
              +e.title.headline {{ $t('statistics.titles.engagingRate') }}
              v-btn(icon, @click="getInfoText('statistics.description.engagingRate')")
                v-icon.display-1 mdi-information-outline
            +b(v-if="!isLoading.queries.engagement").loader
              v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
            +b(v-else-if="!engagementRate").loader {{ $t('statistics.tags.noData') }}
            +e.ECHART(v-else-if="engagementRate" :options="engagement", :auto-resize="!!true").chart

      +b.V-FLEX(xs12='', sm12='', mt-3='' :style="[(!statistic || !isLoading.queries.statistic) ? {'height': '300px'} : '']").graph-container
        +e.title-block
          +e.title.headline {{ $t('statistics.titles.usersTagCount') }}
          v-btn(icon, @click="getInfoText('statistics.description.usersTagCount')")
            v-icon.display-1 mdi-information-outline
        +b(v-if="!isLoading.queries.statistic").loader
          v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
        +b(v-else-if="!statistic").loader {{ $t('statistics.tags.noData') }}
        +e.ECHART(v-else-if="statistic" :options="userTag", :auto-resize="!!true").chart

      +b.V-FLEX(xs12='', sm12='', mt-3='' :style="[(profileEngagementRate.length === 0 || !isLoading.queries.engagement)? {'height': '300px'} : '']").graph-container
        +e.title-block
          +e.title.headline {{ $t('statistics.titles.profileEngagingRate') }}
          v-btn(icon, @click="getInfoText('statistics.description.profileEngagingRate')")
            v-icon.display-1 mdi-information-outline
        +b(v-if="!isLoading.queries.engagement").loader
          v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
        +b(v-else-if="profileEngagementRate.length === 0").loader {{ $t('statistics.tags.noData') }}
        +e.ECHART(v-else-if="profileEngagementRate" :options="profileEngagement", :auto-resize="!!true").chart

      +b.V-FLEX(xs12='', sm12='', mt-3='' :style="[(!statistic || !isLoading.queries.statistic) ? {'height': '300px'} : '']").graph-container
        +e.title-block
          +e.title.headline {{ $t('statistics.titles.commonEngagingRate') }}
          v-btn(icon, @click="getInfoText('statistics.description.commonEngagingRate')")
            v-icon.display-1 mdi-information-outline
        +b(v-if="!isLoading.queries.statistic").loader
          v-progress-circular(:size='70', :width='7', color='purple', indeterminate='')
        +b(v-else-if="!statistic").loader {{ $t('statistics.tags.noData') }}
        +e.ECHART(v-else-if="statistic" :options="CommonEngagementRate", :auto-resize="!!true").chart

      +b.pdf-render(v-if="isLoading.progress === 100")
        +e.container(v-for="item in listGraphs")
          +e.title.headline {{ item.title }}
          +e.ECHART(:options="item.options", :auto-resize="!!true").chart
      //+e.image_container
        img.pdf-render__image(v-for="post in [topPosts[0], topPosts[1], topPosts[2]]", :key="post.id", :id="'post_image_' + post.id", :src="post.pic_url", crossorigin="anonymous")
      //+e.image_container
        img.pdf-render__image(v-for="follower in topNewFollowers", :key="follower.id", :id="'follower_image_' + follower.id", :src="follower.profile_pic_url", crossorigin="anonymous")
</template>

<script>
import statistic from '@/mixins/statistics';
import defaultImage from '../../assets/default.jpg';

export default {
  name: 'statistics',
  mixins: [statistic],
  data: () => ({
    defaultImage,
  }),
  computed: {
    isPostsGrabbed() {
      return this.statistic && this.lastDayStatistic.is_posts_loaded === true;
    },
    isSubsGrabbed() {
      return this.statistic && this.lastDayStatistic.is_subscribers_loaded === true;
    },
    isNeedToGrabSubs() {
      return this.timeToGrabSubs !== 0 && this.timeToGrabSubs !== null && this.timeToGrabSubs !== false && !this.canViewGraph;
    },
  },
  methods: {
    setDefaultImage(e) {
      e.follower.profile_pic_url = this.defaultImage;
    },
  },
};
</script>
<style scoped>
  .echarts {
    width: 100%;
  }
</style>
