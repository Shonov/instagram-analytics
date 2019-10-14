import { mapGetters } from 'vuex';
import JsPDF from 'jspdf';
import echarts from 'echarts';
import cypherLogo from '../../assets/souq_logo.png';

import amiriFontBase64 from './../helpers/fonts';

export default {
  computed: {
    ...mapGetters({
      isLoading: 'statistics/isLoading',
      currentAccount: 'accounts/currentAccount',
      statistic: 'statistics/statistic',
      followersByOurFollowers: 'statistics/followersByOurFollowers',
      followersByOurFollowing: 'statistics/followersByOurFollowing',
      followersAndFollowing: 'statistics/followersAndFollowing',
      countBots: 'statistics/countBots',
      topNewFollowers: 'statistics/topNewFollowers',
      topLostFollowers: 'statistics/topLostFollowers',
      privateAndOpenAccounts: 'statistics/privateAndOpenAccounts',
      businessAndUsualAccounts: 'statistics/businessAndUsualAccounts',
      mostEngagingPostTypes: 'statistics/mostEngagingPostTypes',
      postTypes: 'statistics/postTypes',
      reachUsers: 'statistics/reachUsers',
      engagementRate: 'statistics/engagementRate',
      profileEngagementRate: 'statistics/profileEngagementRate',
      // genderFollowers: 'statistics/genderFollowers',
      numberOfPosts: 'statistics/numberOfPosts',
      maxChangePerDay: 'statistics/maxChangePerDay',
      averageLikesPerDay: 'statistics/averageLikesPerDay',
      videoViews: 'statistics/videoViews',
      topPosts: 'statistics/topPosts',
      isLoadingTopPosts: 'statistics/isLoadingTopPosts',
      timeToGrabSubs: 'statistics/timeToGrabSubs',
      canViewGraph: 'statistics/canViewGraph',
    }),
    firstDayStatistic() {
      return this.statistic[0];
    },
    lastDayStatistic() {
      return this.statistic[this.statistic.length - 1];
    },
    subs() {
      const data = {
        xAxis: this.statistic.map(e => (this.$moment(e.created_at).format('D. MMM'))),
        valuesYAxis: this.getIntervalsGraph(this.statistic.map(e => e.follower_count)),
        series: {
          data: this.statistic.map(e => e.follower_count),
          type: 'line',
        },
        formatter: '{b} : {c}',
      };
      return this.getOptionsGraph(data);
    },
    activityComments() {
      const data = {
        xAxis: this.statistic.map(e => (this.$moment(e.created_at).format('D. MMM'))),
        series: {
          data: this.statistic.map(e => e.comment_count),
          type: 'line',
        },
        valuesYAxis: this.getIntervalsGraph(this.statistic.map(e => e.comment_count)),
        formatter: '{b} : {c}',
      };
      return this.getOptionsGraph(data);
    },
    activityPerPostImages() {
      const countPosts = (this.postTypes.photos.count + this.postTypes.carousels.count);
      const activity = ((this.postTypes.photos.likes + this.postTypes.carousels.likes) / countPosts) + ((this.postTypes.photos.comments + this.postTypes.carousels.comments) / countPosts);
      const legend = [
        `${this.$t('statistics.graph.likes')} (${((((100 * (this.postTypes.photos.likes + this.postTypes.carousels.likes)) / countPosts) / activity).toFixed(2))}%)`,
        `${this.$t('statistics.graph.comments')} (${((((100 * (this.postTypes.photos.comments + this.postTypes.carousels.comments)) / countPosts) / activity).toFixed(2))}%)`,
      ];
      const data = {
        data: [
          {
            value: ((this.postTypes.photos.likes + this.postTypes.carousels.likes) / countPosts).toFixed(2),
            name: legend[0],
          },
          {
            value: ((this.postTypes.photos.comments + this.postTypes.carousels.comments) / countPosts).toFixed(2),
            name: legend[1],
          },
        ],
        radius: '55%',
        orient: 'vertical',
        bottom: '21%',
        formatter: '{b} ({c})',
      };
      return this.getOptionsCircleGraph(data, legend);
    },
    activityPerPostVideos() {
      // eslint-disable-next-line vue/no-side-effects-in-computed-properties
      const videos = this.postTypes.videos;
      const videosCount = videos.count;
      const activity = (videos.views + videos.likes + videos.comments) / videosCount;
      const legend = [
        `${this.$t('statistics.graph.likes')} (${(((100 * (videos.likes / videosCount)) / activity).toFixed(2))}%)`,
        `${this.$t('statistics.graph.comments')} (${(((100 * (videos.comments / videosCount)) / activity).toFixed(2))}%)`,
        `${this.$t('statistics.graph.views')} (${(((100 * (videos.views / videosCount)) / activity).toFixed(2))}%)`,
      ];
      const data = {
        data: [
          { value: (videos.likes / videosCount).toFixed(2), name: legend[0] },
          { value: (videos.comments / videosCount).toFixed(2), name: legend[1] },
          { value: (videos.views / videosCount).toFixed(2), name: legend[2] },
        ],
        radius: '55%',
        orient: 'vertical',
        bottom: '15%',
        formatter: '{b} ({c})',
      };
      return this.getOptionsCircleGraph(data, legend);
    },
    subsCountGrowth() {
      return this.lastDayStatistic.follower_count - this.firstDayStatistic.follower_count;
    },
    subsPercentGrowth() {
      return +((this.subsCountGrowth / this.lastDayStatistic.follower_count) * 100).toFixed(2);
    },
    subsAverage() {
      return parseInt(this.subsCountGrowth / this.statistic.length, 10);
    },
    likes() {
      const data = {
        xAxis: this.statistic.map(e => (this.$moment(e.created_at).format('D. MMM'))),
        valuesYAxis: this.getIntervalsGraph(this.statistic.map(e => e.like_count)),
        series: {
          data: this.statistic.map(e => e.like_count),
          type: 'line',
        },
        formatter: '{b} : {c}',
      };
      return this.getOptionsGraph(data);
    },
    likesCountGrowthPerPost() {
      return +(this.lastDayStatistic.like_count / this.lastDayStatistic.media_count).toFixed(2);
    },
    likesCountGrowthPerDay() {
      return +(this.lastDayStatistic.like_count / this.statistic.length).toFixed(2);
    },
    totalSub() {
      return this.statistic.map(e => e.total_sub).reduce((previousValue, currentValue) => previousValue + currentValue);
    },
    totalUnsub() {
      return this.statistic.map(e => e.total_unsub).reduce((previousValue, currentValue) => previousValue + currentValue);
    },
    subscribers() {
      const data = {
        legend: [this.$t('statistics.graph.followers'), this.$t('statistics.graph.unsubscribes')],
        xAxis: this.statistic.map(e => (this.$moment(e.created_at).format('D. MMM'))),
        series: [
          this.statistic.map(e => e.total_sub),
          this.statistic.map(e => -e.total_unsub),
        ],
      };
      return {
        legend: {
          data: data.legend,
        },
        tooltip: {},
        xAxis: {
          data: data.xAxis,
        },
        yAxis: {
          type: 'value',
        },
        grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true,
        },
        series: [{
          name: data.legend[0],
          data: data.series[0],
          stack: 'one',
          type: 'bar',
          itemStyle: {
            normal: {
              color: '#37A2DA',
            },
          },
        },
        {
          name: data.legend[1],
          data: data.series[1],
          stack: 'one',
          type: 'bar',
          itemStyle: {
            normal: {
              color: 'red',
            },
          },
        }],
      };
    },
    followersByOurFollowersGraph() {
      const total = this.followersByOurFollowers;
      const legend = [
        `0-10 (${total.less_10})`,
        `10-100 (${total.more_10_less_100})`,
        `100-1K (${total.more_100_less_1k})`,
        `1K-10K (${total.more_1k_less_10k})`,
        `10K-100K (${total.more_10k_less_100k})`,
        `100K-1M (${total.more_100k_less_1m})`,
        `>1M (${total.more_1m})`,
      ];
      const data = {
        data: [
          { value: total.less_10, name: legend[0] },
          { value: total.more_10_less_100, name: legend[1] },
          { value: total.more_100_less_1k, name: legend[2] },
          { value: total.more_1k_less_10k, name: legend[3] },
          { value: total.more_10k_less_100k, name: legend[4] },
          { value: total.more_100k_less_1m, name: legend[5] },
          { value: total.more_1m, name: legend[6] },
        ],
        radius: '55%',
        orient: 'horizontal',
        bottom: '20%',
        formatter: '{b} : ({d}%)',
      };
      return this.getOptionsCircleGraph(data, legend);
    },
    followersByOurFollowingGraph() {
      const total = this.followersByOurFollowing;
      const legend = [
        `<500 (${total.more_0_less_500})`,
        `500-1K (${total.more_500_less_1k})`,
        `1K-2K (${total.more_1k_less_2k})`,
        `>2K (${total.more_2k})`,
      ];
      const data = {
        data: [
          { value: total.more_0_less_500, name: legend[0] },
          { value: total.more_500_less_1k, name: legend[1] },
          { value: total.more_1k_less_2k, name: legend[2] },
          { value: total.more_2k, name: legend[3] },
        ],
        radius: '55%',
        orient: 'horizontal',
        bottom: '26%',
        formatter: '{b} : ({d}%)',
      };
      return this.getOptionsCircleGraph(data, legend);
    },
    followersAndFollowingGraph() {
      const total = this.followersAndFollowing;
      const legend = [
        `${this.$t('statistics.graph.FAFG.massFollower')} (<0.2) (${total.less_02})`,
        `${this.$t('statistics.graph.FAFG.normal')} (0.2-1) (${total.more_02_less_1})`,
        `${this.$t('statistics.graph.FAFG.good')} (1-3) (${total.more_1_less_3})`,
        `${this.$t('statistics.graph.FAFG.popular')} (3-10) (${total.more_3_less_10})`,
        `${this.$t('statistics.graph.FAFG.influencer')} (10+) (${total.more_10})`,
      ];
      const data = {
        data: [
          { value: total.less_02, name: legend[0] },
          { value: total.more_02_less_1, name: legend[1] },
          { value: total.more_1_less_3, name: legend[2] },
          { value: total.more_3_less_10, name: legend[3] },
          { value: total.more_10, name: legend[4] },
        ],
        radius: '55%',
        orient: 'horizontal',
        bottom: '21%',
        formatter: '{c} : ({d}%)',
      };
      return this.getOptionsCircleGraph(data, legend);
    },
    botsGraph() {
      const legend = [
        `${this.$t('statistics.graph.realFolowers')} (${this.lastDayStatistic.follower_count - this.countBots})`,
        `${this.$t('statistics.graph.bots')} (${this.countBots})`,
      ];
      const data = {
        data: [
          { value: this.lastDayStatistic.follower_count - this.countBots, name: legend[0] },
          { value: this.countBots, name: legend[1] },
        ],
        radius: '55%',
        orient: 'vertical',
        bottom: '21%',
        formatter: '{b} : ({d}%)',
      };
      return this.getOptionsCircleGraph(data, legend);
    },
    privateOpenAccounts() {
      const total = this.privateAndOpenAccounts;
      const legend = [
        `${this.$t('statistics.graph.privateFollowers')} (${total.private})`,
        `${this.$t('statistics.graph.openFollowers')}  (${total.open})`,
      ];
      const data = {
        data: [
          { value: total.private, name: legend[0] },
          { value: total.open, name: legend[1] },
        ],
        radius: '55%',
        orient: 'vertical',
        bottom: '21%',
        formatter: '{b} : ({d}%)',
      };
      return this.getOptionsCircleGraph(data, legend);
    },
    businessAndNormalAccounts() {
      const total = this.businessAndUsualAccounts;
      const legend = [
        `${this.$t('statistics.graph.businessAccounts')} (${total.business})`,
        `${this.$t('statistics.graph.regularAccounts')} (${total.normal})`,
      ];
      const data = {
        data: [
          { value: total.business, name: legend[0] },
          { value: total.normal, name: legend[1] },
        ],
        radius: '55%',
        orient: 'vertical',
        bottom: '21%',
        formatter: '{b} : ({d}%)',
      };
      return this.getOptionsCircleGraph(data, legend);
    },
    engagingPostTypes() {
      const interactions = this.mostEngagingPostTypes;
      const legend = [
        `${this.$t('statistics.graph.photo')}       ${((100 * interactions.photos) / this.totalInteractions(interactions)).toFixed(2)}%      ${interactions.photos} ${this.$t('statistics.graph.interactions')}`,
        `${this.$t('statistics.graph.video')}       ${((100 * interactions.videos) / this.totalInteractions(interactions)).toFixed(2)}%      ${interactions.videos} ${this.$t('statistics.graph.interactions')}`,
        `${this.$t('statistics.graph.carousel')}   ${((100 * interactions.carousels) / this.totalInteractions(interactions)).toFixed(2)}%      ${interactions.carousels} ${this.$t('statistics.graph.interactions')}`,
      ];
      const data = {
        data: [
          { value: interactions.photos, name: legend[0], label: { normal: { formatter: 'Photo' } } },
          { value: interactions.videos, name: legend[1], label: { normal: { formatter: 'Video' } } },
          { value: interactions.carousels, name: legend[2], label: { normal: { formatter: 'Carousel' } } },
        ],
        emphasis: {
          show: true,
          textStyle: {
            fontSize: '16',
            fontWeight: 'bold',
          },
        },
        avoidLabelOverlap: false,
        radius: ['25%', '60%'],
        orient: 'vertical',
        bottom: '10%',
        formatter: '{b}',
      };
      return this.getOptionsCircleGraph(data, legend);
    },
    graphPostTypes() {
      const posts = this.postTypes;
      const count = posts.photos.count + posts.videos.count + posts.carousels.count;
      const legend = [
        `${this.$t('statistics.graph.photo')}       ${((100 * posts.photos.count) / count).toFixed(2)}%     ${posts.photos.count} ${this.$t('statistics.graph.posts')}`,
        `${this.$t('statistics.graph.video')}       ${((100 * posts.videos.count) / count).toFixed(2)}%      ${posts.videos.count} ${this.$t('statistics.graph.posts')}`,
        `${this.$t('statistics.graph.carousel')}   ${((100 * posts.carousels.count) / count).toFixed(2)}%      ${posts.carousels.count} ${this.$t('statistics.graph.posts')}`,
      ];
      const data = {
        data: [
          { value: posts.photos.count, name: legend[0], label: { normal: { formatter: 'Photo' } } },
          { value: posts.videos.count, name: legend[1], label: { normal: { formatter: 'Video' } } },
          { value: posts.carousels.count, name: legend[2], label: { normal: { formatter: 'Carousel' } } },
        ],
        avoidLabelOverlap: false,
        radius: ['25%', '60%'],
        bottom: '10%',
        orient: 'vertical',
        formatter: '{b}',
        seriesLabel: {
          normal: {
            show: false,
            position: 'center',
          },
          emphasis: {
            show: true,
            textStyle: {
              fontSize: '16',
              fontWeight: 'bold',
            },
          },
        },
      };
      return this.getOptionsCircleGraph(data, legend);
    },
    reachUsersGraph() {
      const reach = this.reachUsers;
      const totalReach = (this.totalInteractions(reach) === 0 ? 1 : this.totalInteractions(reach)).toFixed(2);
      const legend = [
        `${this.$t('statistics.graph.realReach.easily')} (${((100 * reach.easily) / totalReach).toFixed(2)}%) \n(${this.$t('statistics.graph.relationForLegend')} < 800)`,
        `${this.$t('statistics.graph.realReach.reachable')} (${((100 * reach.reachable) / totalReach).toFixed(2)}%) \n(${this.$t('statistics.graph.relationForLegend')} 800 - 1500)`,
        `${this.$t('statistics.graph.realReach.hardly')} (${((100 * reach.hardly) / totalReach).toFixed(2)}%) \n(${this.$t('statistics.graph.relationForLegend')} 1000 - 3000)`,
        `${this.$t('statistics.graph.realReach.unreachable')} (${((100 * reach.unreachable) / totalReach).toFixed(2)}%) \n(${this.$t('statistics.graph.relationForLegend')} 3000+)`,
      ];
      const data = {
        data: [
          { value: reach.easily, name: legend[0] },
          { value: reach.reachable, name: legend[1] },
          { value: reach.hardly, name: legend[2] },
          { value: reach.unreachable, name: legend[3] },
        ],
        legend: {
          lineHeight: 15,
          itemGap: 17,
        },
        radius: '55%',
        bottom: '-3%',
        orient: 'vertical',
        formatter: '{c} ({d}%)',
      };
      return this.getOptionsCircleGraph(data, legend);
    },
    engagement() {
      const engagement = this.engagementRate;
      const legend = [
        `${engagement}`,
      ];
      const data = {
        data: [
          {
            value: engagement,
            name: 'Engagement rate',
            label: {
              normal: {
                show: true,
                position: 'center',
                formatter: '{c}%',
                textStyle: {
                  color: 'black',
                  fontSize: '44',
                },
              },
            },
          },
          {
            value: 100 - (engagement > 100 ? 100 : engagement),
            name: null,
            disabled: true,
            itemStyle: {
              normal: {
                color: '#F5F5F5',
              },
            },
          },
        ],
        radius: ['50%', '60%'],
        center: ['50%', '50%'],
        bottom: 0,
        avoidLabelOverlap: false,
        silent: true,
      };

      return this.getOptionsCircleGraph(data, legend);
    },
    userTag() {
      const data = {
        xAxis: this.statistic.map(e => (this.$moment(e.created_at).format('D. MMM'))),
        valuesYAxis: this.getIntervalsGraph(this.statistic.map(e => e.usertags_count)),
        series: {
          data: this.statistic.map(e => e.usertags_count),
          type: 'line',
        },
        formatter: '{b} : {c}',
      };
      return this.getOptionsGraph(data);
    },
    profileEngagement() {
      const data = {
        xAxis: this.profileEngagementRate.map(e => (this.$moment(e.created_at.date).format('D. MMM'))),
        valuesYAxis: this.getIntervalsGraph(this.profileEngagementRate.map(e => e.engagement), '%'),
        series: {
          data: this.profileEngagementRate.map(e => e.engagement),
          type: 'line',
        },
        formatter: '{b} : {c}%',
      };
      return this.getOptionsGraph(data);
    },
    videoViewsGraph() {
      const data = {
        xAxis: this.statistic.map(e => (this.$moment(e.created_at).format('D. MMM'))),
        valuesYAxis: this.getIntervalsGraph(this.videoViews),
        series: {
          data: this.videoViews,
          type: 'line',
        },
        formatter: '{b} : {c}',
      };
      return this.getOptionsGraph(data);
    },
    // genderFollowersGraph() {
    //   const legend = [
    //     `${this.$t('statistics.graph.gender.male')} (${this.genderFollowers.male})`,
    //     `${this.$t('statistics.graph.gender.female')} (${this.genderFollowers.female})`,
    //     `${this.$t('statistics.graph.gender.undefined')} (${this.genderFollowers.undefined})`,
    //   ];
    //   const data = {
    //     data: [
    //       { value: this.genderFollowers.male, name: legend[0] },
    //       { value: this.genderFollowers.female, name: legend[1] },
    //       { value: this.genderFollowers.undefined, name: legend[2] },
    //     ],
    //     radius: '55%',
    //     bottom: '15%',
    //     orient: 'vertical',
    //     formatter: '{b} : {d}%',
    //   };
    //   return this.getOptionsCircleGraph(data, legend);
    // },
    countPosts() {
      const photo = [];
      const video = [];
      const carousel = [];
      const data = {
        xAxis: this.numberOfPosts.map(e => (this.$moment(e.posted_at).format('D. MMM'))),
        series: this.numberOfPosts.forEach(((e) => {
          photo.splice(photo.length, 0, e.photo);
          video.splice(video.length, 0, e.video);
          carousel.splice(carousel.length, 0, e.carousel);
        })),
      };
      return {
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: 'shadow',
          },
        },
        toolbox: {
          show: true,
        },
        legend: {
          data: [
            this.$t('statistics.graph.photo'),
            this.$t('statistics.graph.video'),
            this.$t('statistics.graph.carousel'),
          ],
        },
        grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true,
        },
        xAxis: {
          type: 'category',
          data: data.xAxis,
        },
        yAxis: {
          type: 'value',
        },
        series: [
          {
            name: this.$t('statistics.graph.photo'),
            type: 'bar',
            stack: 'posts',
            label: {
              normal: {
                show: false,
              },
            },
            data: photo,
          },
          {
            name: this.$t('statistics.graph.video'),
            type: 'bar',
            stack: 'posts',
            label: {
              normal: {
                show: false,
              },
            },
            data: video,
          },
          {
            name: this.$t('statistics.graph.carousel'),
            type: 'bar',
            stack: 'posts',
            label: {
              normal: {
                show: false,
              },
            },
            data: carousel,
          },
        ],
      };
    },

    CommonEngagementRate() {
      const data = {
        xAxis: this.statistic.map(e => (this.$moment(e.created_at).format('D. MMM'))),
        valuesYAxis: this.getIntervalsGraph(this.statistic.map(e => (((e.like_count + e.comment_count) / e.media_count / e.follower_count) * 100).toFixed(2), '%')),
        series: {
          data: this.statistic.map(e => (((e.like_count + e.comment_count) / e.media_count / e.follower_count) * 100).toFixed(2)),
          type: 'line',
        },
        formatter: '{b} : {c}%',
      };
      return this.getOptionsGraph(data);
    },
    listGraphs() {
      return [
        {
          title: 'followersGrowth',
          options: this.subs,
        },
        {
          title: 'followersAndUnsubscribes',
          options: this.subscribers,
        },
        // {
        //   title: 'GenderOfFollowers',
        //   options: this.genderFollowersGraph,
        // },
        {
          title: 'PrivateAndOpenAccounts',
          options: this.privateOpenAccounts,
        },
        {
          title: 'countBusinessAndRegularAccounts',
          options: this.businessAndNormalAccounts,
        },
        {
          title: 'followersByTheirFollowers',
          options: this.followersByOurFollowersGraph,
        },
        {
          title: 'byNumberAccountFollowed',
          options: this.followersByOurFollowingGraph,
        },
        {
          title: 'byRatioFollowersToFollowing',
          options: this.followersAndFollowingGraph,
        },
        {
          title: 'countBots',
          options: this.botsGraph,
        },
        {
          title: 'CountPosts',
          options: this.countPosts,
        },
        {
          title: 'likesGrowth',
          options: this.likes,
        },
        {
          title: 'commentsGrowth',
          options: this.activityComments,
        },
        {
          title: 'activityPerPostImages',
          options: this.activityPerPostImages,
        },
        {
          title: 'activityPerPostVideos',
          options: this.activityPerPostVideos,
        },
        {
          title: 'engagingPostTypes',
          options: this.engagingPostTypes,
        },
        {
          title: 'postTypes',
          options: this.graphPostTypes,
        },
        {
          title: 'reach',
          options: this.reachUsersGraph,
        },
        {
          title: 'profileEngagingRate',
          options: this.profileEngagement,
        },
        {
          title: 'engagingRate',
          options: this.engagement,
        },
        {
          title: 'commonEngagingRate',
          options: this.CommonEngagementRate,
        },
        {
          title: 'videoViews',
          options: this.videoViewsGraph,
        },
        {
          title: 'usersTagCount',
          options: this.userTag,
        },
        // {
        //   title: '',
        //   options: '',
        // },
      ];
    },
    todayDate() {
      return this.$moment().format('YYYY-MM-DD');
    },
    loading() {
      return 100;
      if (this.value >= 100 || this.isLoading.progress === 0) {
        this.value = 0;
      }
      this.interval = setInterval(() => {
        if (this.value < this.isLoading.progress) {
          this.value += 1;
        }
      }, Math.floor(Math.random() * 3500) + 300);
      return this.value;
    },
    optionsPosts() {
      return [
        { index: 1, text: this.$t('statistics.graph.topPosts.byER') },
        { index: 2, text: this.$t('statistics.graph.topPosts.byLikes') },
        { index: 3, text: this.$t('statistics.graph.topPosts.byComments') },
        { index: 4, text: this.$t('statistics.graph.topPosts.byVideoViews') },
      ];
    },
  },
  data: () => ({
    startDateFormatted: null,
    endDateFormatted: null,
    startDate: null,
    endDate: null,
    menu1: false,
    menu2: false,
    image: '',
    isLoadReport: false,
    interval: {},
    value: 0,
    selectedOption: 0,
    sort: 0,
    dialog: false,
    info: false,
  }),
  watch: {
    currentAccount() {
      this.dispatchStatistic();
    },
    startDate() {
      this.startDateFormatted = this.formatDate(this.startDate);
    },
    endDate() {
      this.endDateFormatted = this.formatDate(this.endDate);
    },
  },
  beforeDestroy() {
    clearInterval(this.interval);
  },
  created() {
    if (this.currentAccount) {
      this.dispatchStatistic();
    }
    this.selectedOption = this.optionsPosts[0];
  },
  methods: {
    dispatchStatistic() {
      if (this.startDate === null) {
        const dayStart = this.$moment().subtract(14, 'days');
        // const dayStart = this.$moment().subtract(1, 'months');
        const accountCreationDate = this.$moment(this.currentAccount.created_at);

        if (accountCreationDate.isBefore(dayStart)) {
          this.startDate = dayStart.format('YYYY-MM-DD');
        } else {
          this.startDate = accountCreationDate.format('YYYY-MM-DD');
        }
      }

      this.$store.dispatch('statistics/getStatistics', {
        accountId: this.currentAccount.id,
        params: {
          startDate: this.startDate,
          endDate: this.endDate,
        },
      });
    },
    dispatchPosts() {
      this.$store.dispatch('statistics/getPosts', {
        accountId: this.currentAccount.id,
        params: {
          startDate: this.startDate,
          endDate: this.endDate,
          filter: this.selectedOption,
          sort: this.sort,
        },
      }).then(() => {
        if (this.selectedOption === 3 && this.sort !== 2) {
          this.sort = 2;
        }
      });
    },
    totalInteractions(...object) {
      let total = 0;
      object.forEach((obj) => {
        total += Object.values(obj).reduce((t, n) => t + n);
      });

      return total;
    },
    formatDate(date) {
      if (!date) return null;

      const [year, month, day] = date.split('-');
      return `${day}-${month}-${year}`;
    },
    parseDate(date) {
      if (!date) {
        return null;
      }

      let [month, day, year] = date.split('/');

      if (day === undefined) {
        [day, month, year] = date.split('-');
      }
      return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
    },
    renderLayout(doc, title, startDate, lastDate, language = 'en') {
      doc.setFontSize(25);
      doc.addImage(cypherLogo, 'PNG', 20, 160, 30, 39);
      if (language === 'ar') {
        doc.text(270, 40, title, { align: 'right', lang: 'ar' });
      } else {
        doc.text(15, 40, title);
      }
      doc.setFontSize(15);
      doc.text(15, 15, `@${this.currentAccount.login}`);
      doc.text(190, 15, `${startDate.format('ddd, MMM D, YYYY')} - ${lastDate.format('ddd, MMM D, YYYY')}`);
    },
    downloadPdf() {
      this.isLoadReport = true;

      const pdfRender = document.querySelector('.pdf-render');

      pdfRender.style.display = 'block';
      pdfRender.querySelectorAll('.pdf-render__container').forEach((e) => {
        e.style.display = 'block';
      });

      setTimeout(() => {
        const startDate = this.$moment(this.firstDayStatistic.created_at);
        const lastDate = this.$moment(this.lastDayStatistic.created_at);

        const language = this.$i18n.locale;

        const doc = new JsPDF({ orientation: 'landscape', filters: ['ASCIIHexEncode'] });

        if (language === 'ar') {
          doc.addFileToVFS('Amiri-Regular.ttf', amiriFontBase64);
          doc.addFont('Amiri-Regular.ttf', 'Amiri', 'normal');

          doc.setFont('Amiri'); // set font
        }


        doc.setFontSize(35);

        doc.text(20, 50, 'Instagram Account Report');

        doc.setFontSize(65);

        doc.text(20, 105, `@${this.currentAccount.login}`);

        doc.addImage(cypherLogo, 'PNG', 20, 160, 30, 39);
        doc.addPage();

        const chartContainers = document.querySelectorAll('.pdf-render__container');

        chartContainers.forEach((chart, page) => {
          const title = chart.querySelector('.pdf-render__title').textContent;
          const graph = echarts.getInstanceByDom(chart.querySelector('.pdf-render__chart'));
          const canvasImg = graph.getDataURL({
            type: 'jpeg',
            pixelRatio: 2,
            backgroundColor: '#fff',
          });
          // doc.addImage(canvasImg, 'JPEG', 10, 35, 280, 150);
          doc.addImage(canvasImg, 'JPEG', 65, 55, 168, 90);
          doc.setFontSize(15);

          if (language === 'ar') {
            doc.text(this.$t(`statistics.description.${title}`), 270, 155, { align: 'right', lang: 'ar' });
          } else {
            doc.text(this.$t(`statistics.description.${title}`), 65, 155);
          }

          this.renderLayout(doc, this.$t(`statistics.titles.${title}`), startDate, lastDate, language);

          if (page !== (chartContainers.length - 1)) {
            doc.addPage();
          }

          if (page === 8) {
            [{
              title: 'topNewFollowers',
              type: 'top_followers',
            }, {
              title: 'topUnsubscribers',
              type: 'top_lost_followers',
            }].forEach((top) => {
              let followers = [];
              if (top.type === 'top_followers') {
                followers = this.topNewFollowers;
              } else if (top.type === 'top_lost_followers') {
                followers = this.topLostFollowers;
              }
              followers.forEach((follower, index) => {
                this.renderLayout(doc, this.$t(`statistics.titles.${top.title}`), startDate, lastDate, language);
                doc.setFontSize(14);
                // doc.addImage(this.getBase64Image(document.querySelector(`#follower_image_${follower.id}`)), 'JPEG', 65, 50 + (index * 10), 12, 12);
                doc.text(`${index + 1}. ${follower.follower.full_name}`, 95, 55 + (index * 8));
                doc.text(follower.follower_count.toString(), 170, 55 + (index * 8));

                doc.setFontSize(15);
              });
              if (language === 'ar') {
                doc.text(this.$t(`statistics.description.${top.title}`), 270, 155, { align: 'right', lang: 'ar' });
              } else {
                doc.text(this.$t(`statistics.description.${top.title}`), 65, 155);
              }

              doc.addPage();
            });
          }
        });


        doc.save(`${this.currentAccount.login}.pdf`);

        pdfRender.style.display = 'none';
        pdfRender.querySelectorAll('.pdf-render__container').forEach((e) => {
          e.style.display = 'none';
        });

        this.isLoadReport = false;
      }, 3500);
    },
    getPrecisionSafe(val) {
      const str = val.toString();
      const eIndex = str.indexOf('e');
      if (eIndex > 0) {
        const precision = +str.slice(eIndex + 1);
        return precision < 0 ? -precision : 0;
      }
      const dotIndex = str.indexOf('.');
      return dotIndex < 0 ? 0 : str.length - 1 - dotIndex;
    },
    checkIsNiceInterval(min, max) {
      const splitNumbers = [];
      const difference = Math.round(max - min);

      for (let i = 5; i <= 10; i += 1) {
        if (i >= difference) {
          if ((i % difference) === 0) {
            splitNumbers.splice(0, 0, i);
            break;
          }
        } else if ((difference % i) === 0) {
          splitNumbers.splice(0, 0, i);
          break;
        }
      }
      if (splitNumbers.length > 0) return Math.max.apply(null, splitNumbers);
      return null;
    },
    isInteger(num) {
      return (num ^ 0) === num; // eslint-disable-line no-bitwise
    },
    getNiceInterval(min, max) {
      let splitNum;
      if (this.checkIsNiceInterval(min, max) !== null) {
        splitNum = this.checkIsNiceInterval(min, max);
      } else {
        splitNum = 5;
      }
      return splitNum;
    },
    fixedNumber(value, precision) {
      return Number(value.toFixed(precision));
    },
    getIntevalValue(min, max, splitNum, precision) {
      for (let i = precision; i < precision + 5; i += 1) {
        if (this.fixedNumber(((max - min) / splitNum), i) !== 0) {
          return this.fixedNumber(((max - min) / splitNum), i);
        }
      }
      return ((max - min) / splitNum);
    },
    getIntervalsGraph(array = []) {
      let min = Math.min.apply(null, array);
      let max = Math.max.apply(null, array);

      if (max === min) {
        return {
          splitNumber: undefined,
          interval: undefined,
          min: null,
          max: null,
        };
      }

      let splitNum = this.getNiceInterval(min, max);
      let precision = this.getPrecisionSafe(min) > this.getPrecisionSafe(max) ? this.getPrecisionSafe(min) : this.getPrecisionSafe(max);
      let intervalValue;

      intervalValue = this.getIntevalValue(min, max, splitNum, precision);

      const valueForMin = this.fixedNumber((intervalValue > (min / 10) ? (min / 10) : intervalValue), precision + 1);
      const valueForMax = this.fixedNumber((intervalValue > (max / 10) ? (max / 10) : intervalValue), precision + 1);

      max = this.fixedNumber((max + valueForMax), precision);
      min = this.fixedNumber((min - valueForMin), precision);

      for (let i = precision; i < precision + 5; i += 1) {
        if (this.fixedNumber(valueForMin, i) !== 0) {
          if (min === Math.min.apply(null, array)) min -= this.fixedNumber(valueForMin, i);
          if (i > precision) precision = i;
          break;
        }
      }

      for (let i = precision; i < precision + 5; i += 1) {
        if (this.fixedNumber(valueForMax, i) !== 0) {
          if (max === Math.max.apply(null, array)) max += this.fixedNumber(valueForMax, i);
          if (i > precision) precision = i;
          break;
        }
      }

      max = this.fixedNumber(max, precision);
      min = this.fixedNumber(min, precision);
      splitNum = this.getNiceInterval(min, max);
      intervalValue = this.getIntevalValue(min, max, splitNum, precision);
      max = this.fixedNumber((min + (splitNum * intervalValue)), precision);

      return {
        splitNumber: splitNum,
        interval: intervalValue,
        min,
        max,
      };
    },
    getOptionsGraph(data) {
      return {
        grid: {
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true,
        },
        xAxis: {
          type: 'category',
          boundaryGap: data.boundaryGap ? data.boundaryGap : false,
          data: data.xAxis,
        },
        yAxis: {
          type: 'value',
          splitNumber: data.valuesYAxis ? data.valuesYAxis.splitNumber : undefined,
          interval: data.valuesYAxis ? data.valuesYAxis.interval : undefined,
          min: data.valuesYAxis ? data.valuesYAxis.min : null,
          max: data.valuesYAxis ? data.valuesYAxis.max : null,
        },
        tooltip: {
          trigger: 'axis',
          label: {
            show: true,
          },
          formatter: data.formatter,
        },
        series: [{
          showAllSymbol: false,
          data: data.series.data,
          type: data.series.type,
          areaStyle: {},
          itemStyle: {
            normal: {
              color: '#37A2DA',
            },
          },
          barWidth: data.barWidth ? data.barWidth : null,
        }],
      };
    },
    getOptionsCircleGraph(data, legend) {
      return {
        tooltip: {
          trigger: 'item',
          formatter: data.formatter ? data.formatter : null,
        },
        legend: {
          orient: data.orient,
          bottom: data.bottom,
          left: 'center',
          data: legend,
          padding: [17, 5],
          itemGap: data.legend ? data.legend.itemGap : 10,
          textStyle: {
            lineHeight: data.legend ? data.legend.lineHeight : 56,
            rich: {
              a: {
                lineHeight: data.legend ? data.legend.lineHeight : 56,
              },
            },
          },
        },
        series: [
          {
            name: null,
            type: 'pie',
            radius: data.radius,
            center: data.center ? data.center : ['50%', '33%'],
            avoidLabelOverlap: data.avoidLabelOverlap ? data.avoidLabelOverlap : null,
            data: data.data,
            silent: data.silent ? data.silent : null,
            label: data.seriesLabel ? data.seriesLabel : {
              normal: {
                show: false,
                position: 'center',
              },
              emphasis: data.emphasis ? data.emphasis : null,
            },
            labelLine: {
              normal: {
                show: false,
              },
            },
          },
        ],
      };
    },
    routeToInstagram(link) {
      return window.open(link, '_blank');
    },
    getInfoText(text) {
      this.info = this.$t(text);
      this.dialog = true;
    },
  },
};

