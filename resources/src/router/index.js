import Vue from 'vue';
import Router from 'vue-router';

import Authentication from '@/views/components/Authentication';
import Register from '@/views/authentication/Register';
import Login from '@/views/authentication/Login';
import Dashboard from '@/views/components/Dashboard';
import Accounts from '@/views/accounts/Accounts';
import Statistics from '@/views/statistics/Statistics';
import NotFound from '@/views/404/NotFound';
import IntervalServerError from "../views/500/IntervalServerError";


Vue.use(Router);

export default new Router({
  routes: [
    /* {
      path: '/',
      name: 'home',
      component: HomePage,
      // meta: {
      //   auth: false,
      // },
    }, */
    {
      path: '/',
      name: 'authentication',
      component: Authentication,
      redirect: '/login',
      children: [{
        path: '/register',
        name: 'register',
        component: Register,
        // meta: {
        //   auth: false,
        // },
      }, {
        path: '/login',
        name: 'login',
        component: Login,
        // meta: {
        //   auth: false,
        // },
      }],
      // meta: {
      //   auth: false,
      // },
    }, {
      path: '/dashboard',
      name: 'dashboard',
      component: Dashboard,
      children: [{
        path: '/accounts',
        name: 'accounts',
        component: Accounts,
      }, {
        path: '/statistics',
        name: 'statistics',
        component: Statistics,
      }],
      meta: {
        auth: true,
      },
    }, {
      path: '/500',
      name: 'IntervalServerError',
      component: IntervalServerError,
    }, {
      path: '/404',
      name: 'notFound',
      component: NotFound,
      // meta: {
      //   auth: false,
      // },
    }, {
      path: '*',
      redirect: '/404',
    }],

  // routes: [{
  //   path: '/',
  //   name: 'home',
  //   component: HelloWorld,
  // }, {
});
