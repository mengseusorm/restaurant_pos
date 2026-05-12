import { telegramMiniAppCart } from './telegramMiniApp/telegramMiniAppCart';
import { telegramMiniAppOrder } from './telegramMiniApp/telegramMiniAppOrder';
import { telegramMiniAppBranch } from './telegramMiniApp/telegramMiniAppBranch';
import { telegramMiniAppItemCategory } from './telegramMiniApp/telegramMiniAppItemCategory';
import { telegramMiniAppPage } from './telegramMiniApp/telegramMiniAppPage';
import { telegramMiniAppPayway } from './telegramMiniApp/telegramMiniAppPayway';
import { telegramMiniAppExchangeRate } from './telegramMiniApp/telegramMiniAppExchangeRate';
import { telegramMiniAppCurrency } from './telegramMiniApp/telegramMiniAppCurrency';

export const telegramMiniApp = {
    namespaced: true,
    modules: {
        cart: telegramMiniAppCart,
        order: telegramMiniAppOrder,
        branch: telegramMiniAppBranch,
        itemCategory: telegramMiniAppItemCategory,
        page: telegramMiniAppPage,
        payway: telegramMiniAppPayway,
        exchangeRate: telegramMiniAppExchangeRate,
        currency: telegramMiniAppCurrency
    }
};

export default telegramMiniApp;