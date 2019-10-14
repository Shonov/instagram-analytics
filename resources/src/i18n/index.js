import arLocale from 'vee-validate/dist/locale/ar';
import enLocale from 'vee-validate/dist/locale/en';
import * as authentication from './modules/authentication';
import * as buttons from './modules/buttons';
import * as menu from './modules/menu';
import * as statistics from './modules/statistics';
import * as accounts from './modules/accounts';
import * as dashboard from './modules/dashboard';

const messages = {
  en: {
    validation: { ...enLocale },
  },
  ar: {
    validation: { ...arLocale },
  },
};

const modules = {
  authentication, buttons, menu, statistics, accounts, dashboard,
};

// eslint-disable-next-line guard-for-in,no-restricted-syntax
for (const moduleName in modules) {
  messages.en[moduleName] = modules[moduleName].en;
  messages.ar[moduleName] = modules[moduleName].ar;
}

export default messages;
