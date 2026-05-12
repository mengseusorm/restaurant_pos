import VueSimpleAlert from "vue3-simple-alert";
import store from "../store";
import statusEnum from "../enums/modules/statusEnum";
import orderStatusEnum from "../enums/modules/orderStatusEnum";
import askEnum from "../enums/modules/askEnum";
import taxTypeEnum from "../enums/modules/taxTypeEnum";
import currencyPositionEnum from "../enums/modules/currencyPositionEnum";
import stockManagementEnum from "../enums/modules/stockManagementEnum";


export default {
    sideDrawerShow: function (id = 'sideDrawer') {
        const drawerDivs = document?.querySelectorAll(".drawer");
        const drawerSets = document?.querySelectorAll("[data-drawer]");
        drawerSets?.forEach((drawerSet) => {
            const targetElm = document?.querySelector(drawerSet?.dataset?.drawer);
            drawerSets?.forEach(drawerBtn => drawerBtn?.classList?.remove("active"));
            drawerDivs?.forEach(drawerDiv => drawerDiv?.classList?.remove("active"));
            targetElm?.classList?.add("active");
            drawerSet?.classList?.add("active");
            document.body.style.overflowY = "hidden";
            document?.querySelector(".backdrop")?.classList?.add("active");
        });
    },
    sideDrawerHide: function (id = 'sideDrawer') {
        const drawerDivs = document?.querySelectorAll(".drawer");
        const drawerSets = document?.querySelectorAll("[data-drawer]");
        document?.querySelectorAll("#sidebar")?.forEach((closeBtn) => {
            drawerSets?.forEach(drawerBtn => drawerBtn?.classList?.remove("active"));
            drawerDivs?.forEach(drawerDiv => drawerDiv?.classList?.remove("active"));
            document?.querySelector(".backdrop")?.classList?.remove("active");
            document.body.style.overflowY = "auto"
        });
    },

    modalShow: function (id = '.modal') {
        const modalTarget = document?.querySelector(id);
        if (modalTarget) {
            modalTarget?.classList?.add("active");
            document.body.style.overflowY = "hidden";
        } else {
            console.error(`Modal element with selector "${id}" not found`);
        }
    },

    modalHide: function (id = ".modal") {
        let modalDivs = document?.querySelectorAll(id);
        document.body.style.overflowY = "auto";
        modalDivs?.forEach((modalDiv) => modalDiv?.classList?.remove("active"));
    },

    confirmDialog: function (title, message, type = "warning", confirmButtonText = "Yes", cancelButtonText = "No", options = {}) {
        return new VueSimpleAlert.confirm(
            title,
            message,
            type,
            {
                confirmButtonText: confirmButtonText,
                cancelButtonText: cancelButtonText,
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#8592a3",
            }
        );
    },

    phoneNumber: function (e) {
        let char = String.fromCharCode(e.keyCode);
        if (/^[+]?[0-9]*$/.test(char)) return true;
        else e.preventDefault();
    },

    onlyNumber: function (e) {
        let res = (e.charCode !== 8 && e.charCode === 0 || (e.charCode >= 48 && e.charCode <= 57));
        if (res)
            return true;
        else
            e.preventDefault();
    },

    floatNumber: function (e) {
        let char = String.fromCharCode(e.keyCode);
        if (/^[.]?[0-9]*$/.test(char)) return true;
        else e.preventDefault();
    },

    currencyFormat(amount, decimal, currency, position) {
        if (position === currencyPositionEnum.LEFT) {
            return currency + parseFloat(amount).toFixed(decimal);
        } else {
            return parseFloat(amount).toFixed(decimal) + currency;
        }
    },

    destroyConfirmation: function () {
        return new VueSimpleAlert.confirm(
            "You will not be able to recover the deleted record!",
            "Are you sure?",
            "warning",
            {
                confirmButtonText: "Yes, Delete it!",
                cancelButtonText: "No, Cancel!",
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#8592a3",
            }
        );
    },
    startSessionConfirmation: function () {
        return new VueSimpleAlert.confirm(
            "The session will be marked as in progress.",
            "Start Session?",
            "question",
            {
                confirmButtonText: "Yes, Start it!",
                cancelButtonText: "No, Cancel!",
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#8592a3",
            }
        );
    },
    completeSessionConfirmation: function () {
        return new VueSimpleAlert.confirm(
            "The session will be marked as completed.",
            "Complete Session?",
            "question",
            {
                confirmButtonText: "Yes, Complete it!",
                cancelButtonText: "No, Cancel!",
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#8592a3",
            }
        );
    },
    tableReleaseSuccess: function () {
        return new VueSimpleAlert.confirm(
            "This will release the table and clear all associated orders.",
            "Release Table?",
            "warning",
            {
                confirmButtonText: "Yes, Release it!",
                cancelButtonText: "No, Cancel!",
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#8592a3",
            }
        );
    },
    saveDraftConfirmation: function () {
        return new VueSimpleAlert.confirm(
            "Your order will be suspended.",
            "Suspend Order?",
            "info",
            {
                confirmButtonText: "Yes, Suspend",
                cancelButtonText: "No, Cancel",
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#8592a3",
            }
        );
    },
    clearCacheAlert: function () {
        return new VueSimpleAlert.confirm(
            "You will go to login again!",
            "Are you sure?",
            "warning",
            {
                confirmButtonText: "Yes, Clear Cache",
                cancelButtonText: "No, Cancel!",
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#8592a3",
            }
        );
    },
    discountOrder: function () {
        return new VueSimpleAlert.confirm(
            "A discount will be applied to this order.",
            "Apply Discount?",
            "info",
            {
                confirmButtonText: "Yes, Apply Discount",
                cancelButtonText: "No, Cancel",
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#8592a3",
            }
        );
    },
    updateOrderInfo: function () {
        return new VueSimpleAlert.confirm(
            "You will update the order information.",
            "Are you sure?",
            "warning",
            {
                confirmButtonText: "Yes, Update it!",
                cancelButtonText: "No, Cancel!",
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#8592a3",
            }
        );
    },
    acceptOrder: function () {
        return new VueSimpleAlert.confirm(
            "You will not be able to cancel the order!",
            "Are you sure?",
            "warning",
            {
                confirmButtonText: "Yes, Accept it!",
                cancelButtonText: "No, Cancel!",
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#8592a3",
            }
        );
    },
    cancelOrder: function () {
        return new VueSimpleAlert.confirm(
            "You will not be able to accept the order!",
            "Are you sure?",
            "warning",
            {
                confirmButtonText: "Yes, Cancel it!",
                cancelButtonText: "No, Cancel",
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#8592a3",
            }
        );
    },

    cancelPayment: function () {
        return new VueSimpleAlert.confirm(
            "The payment will be cancelled.",
            "Are you sure?",
            "warning",
            {
                confirmButtonText: "Yes, Cancel it!",
                cancelButtonText: "No, Keep it",
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#8592a3",
            }
        );
    },
    distance: function (lat1, lng1, lat2, lng2) {
        let radiationLat1 = Math.PI * lat1 / 180
        let radiationLat2 = Math.PI * lat2 / 180
        let theta = lng1 - lng2;
        let radiationTheta = Math.PI * theta / 180
        let distance = Math.sin(radiationLat1) * Math.sin(radiationLat2) + Math.cos(radiationLat1) * Math.cos(radiationLat2) * Math.cos(radiationTheta);
        distance = Math.acos(distance)
        distance = distance * 180 / Math.PI
        distance = distance * 60 * 1.1515
        distance = distance * 1.609344
        return distance;
    },

    recursiveRouter: function (routes, permission) {

        // Check if permission is an array before proceeding
        if (!Array.isArray(permission)) {
            return;
        }
        // Create a Map for O(1) permission lookups
        const permissionMap = new Map(permission.map(p => [p.url, p]));

        // Helper function to apply permissions recursively
        const applyPermissions = (routeList) => {
            routeList.forEach(route => {
                if (route.meta && route.meta.permissionUrl) {
                    const perm = permissionMap.get(route.meta.permissionUrl);
                    if (perm) {
                        route.meta.access = perm.access;
                        route.meta.title = perm.title;
                    }
                }
                if (route.children && route.children.length > 0) {
                    applyPermissions(route.children);
                }
            });
        };

        applyPermissions(routes);
    },

    // recursiveRouterBackup: function (routes, permission) {
    //     let i, j;
    //     for (i = 0; i < routes.length; i++) {
    //         for (j = 0; j < permission.length; j++) {
    //             if (typeof routes[i].meta !== "undefined" && routes[i].meta) {
    //                 if (typeof routes[i].meta.permissionUrl !== "undefined" && routes[i].meta.permissionUrl) {
    //                     if (routes[i].meta.permissionUrl === permission[j].url) {
    //                         routes[i].meta.access = permission[j].access;
    //                         routes[i].meta.title = permission[j].title;
    //                     }

    //                     if (typeof routes[i].children !== "undefined" && routes[i].children) {
    //                         this.recursiveRouter(routes[i].children, permission);
    //                     }
    //                 }
    //             }
    //         }
    //     }
    // },

    textShortener: function (text, number = 30) {
        if (text) {
            if (!(text.length < number)) {
                return text.substring(0, number) + "..";
            }
        }
        return text;
    },
    statusClass: function (status) {
        if (status === statusEnum.ACTIVE || status === 1) {
            return "db-table-badge text-green-600 bg-green-100";
        } else {
            return "db-table-badge text-red-600 bg-red-100";
        }
    },
    booleanStatusClass: function (status) {
        if (status === true) {
            return "db-table-badge text-green-600 bg-green-100";
        } else {
            return "db-table-badge text-red-600 bg-red-100";
        }
    },
    stockRecordTypeClass: function (status) {
         if (status == 'STOCK_IN') {
            return "db-table-badge text-green-600 bg-green-100";
        } else {
            return "db-table-badge text-blue-600 bg-blue-100";
        }
    },
    yesNoClass: function (status) {
        if (status === stockManagementEnum.YES) {
            return "db-table-badge text-blue-500 bg-blue-100";
        } else {
            return "db-table-badge text-orange-500 bg-orange-100";
        }
    },
    orderStatusClass: function (status) {
         if(status == orderStatusEnum.ACCEPT || status == orderStatusEnum.PROCESSING){
            return "py-0.5 px-2 rounded-full text-[10px] font-rubik leading-4 first-letter:capitalize whitespace-nowrap text-[#2AC769] bg-[#CBFFE0]";
        }
        else if(status == orderStatusEnum.PENDING){
            return "py-0.5 px-2 rounded-full text-[10px] font-rubik leading-4 first-letter:capitalize whitespace-nowrap text-[#F6A609] bg-[#FFEEC6]";
        }
        else if(status == orderStatusEnum.OUT_FOR_DELIVERY){
            return "py-0.5 px-2 rounded-full text-[10px] font-rubik leading-4 first-letter:capitalize whitespace-nowrap text-[#008BBA] bg-[#BDEFFF]";
        }
        else if(status == orderStatusEnum.DELIVERED){
            return "py-0.5 px-2 rounded-full text-[10px] font-rubik leading-4 first-letter:capitalize whitespace-nowrap text-primary bg-[#FFD7E7]";
        }
        else if(status == orderStatusEnum.VOID){
            return "py-0.5 px-2 rounded-full text-[10px] font-rubik leading-4 first-letter:capitalize whitespace-nowrap text-[#FB4E4E] bg-[#FFD7E7]";
        }
        else {
            return "py-0.5 px-2 rounded-full text-[10px] font-rubik leading-4 first-letter:capitalize whitespace-nowrap text-[#FB4E4E] bg-[#FFDADA]";
        }
    },

    sessionStatusClass: function (status) {
        if (status === 'pending') {
            return "py-0.5 px-2 rounded-full text-[10px] font-rubik leading-4 first-letter:capitalize whitespace-nowrap text-[#F6A609] bg-[#FFEEC6]";
        } else if (status === 'in_progress') {
            return "py-0.5 px-2 rounded-full text-[10px] font-rubik leading-4 first-letter:capitalize whitespace-nowrap text-[#008BBA] bg-[#BDEFFF]";
        } else if (status === 'completed') {
            return "py-0.5 px-2 rounded-full text-[10px] font-rubik leading-4 first-letter:capitalize whitespace-nowrap text-[#2AC769] bg-[#CBFFE0]";
        } else {
            return "py-0.5 px-2 rounded-full text-[10px] font-rubik leading-4 first-letter:capitalize whitespace-nowrap text-[#FB4E4E] bg-[#FFDADA]";
        }
    },

    askClass: function (ask) {
        if (ask === askEnum.YES) {
            return "db-table-badge text-green-600 bg-green-100";
        } else {
            return "db-table-badge text-red-600 bg-red-100";
        }
    },

    taxTypeClass: function (type) {
        if (type === taxTypeEnum.FIXED) {
            return "db-table-badge text-blue-500 bg-blue-100";
        } else {
            return "db-table-badge text-orange-500 bg-orange-100";
        }
    },

    requestHandler: function (requests) {
        let i = 1;
        let what = "?";
        let response = "";

        for (let request in requests) {
            if (requests[request] !== "" && requests[request] !== null) {
                if (i !== 1) {
                    response += "&";
                }
                response += request + "=" + encodeURIComponent(requests[request]);
            }
            i++;
        }

        if (response) {
            response = what + response;
        }

        return response;
    },

    responsiveLoad: function() {
        let mainHeader = document?.querySelector(".db-header");
        let subHeader = document?.querySelector(".sub-header");
        let mainHeight = mainHeader?.scrollHeight;

        if (subHeader) {
            subHeader.style.top = `${mainHeight}px`;
        }
    },


    permissionChecker: function (permissionName) {
        let i, permissions = store.getters.authPermission;
        for (i = 0; i < permissions.length; i++) {
            if (typeof permissions[i].name !== "undefined" && permissions[i].name) {
                if (permissions[i].name === permissionName) {
                    return permissions[i].access;
                }
            }
        }
    },

    formDataShow: function (formData) {
        for (let pair of formData.entries()) {
            console.log(pair[0] + " : " + pair[1]);
        }
    },

    secondExchangeRate: function (exchangeRate,totalPriceFrom,decimal = 2) { //From first currency to second currency
        const exFrom = exchangeRate?.split(":")[0];
        const exTo = exchangeRate?.split(":")[1];

        let convertedPrice = (totalPriceFrom * exTo / exFrom);
        // return (totalPriceFrom * exTo / exFrom).toFixed(decimal).toString();

        if(decimal >= 0){
            return convertedPrice.toFixed(decimal).toString();
        }else{
            const factor = Math.pow(10, Math.abs(decimal));
            return (Math.floor(convertedPrice / factor) * factor).toString();
        }
    },
    firstExchangeRate: function (exchangeRate,secondPrice,decimal = 2) { //From second currency to first currency
        const exFrom = exchangeRate?.split(":")[0];
        const exTo = exchangeRate?.split(":")[1];

        let convertedPrice = (secondPrice * exFrom / exTo);
        // return (totalPriceFrom * exTo / exFrom).toFixed(decimal).toString();

        if(decimal >= 0){
            return convertedPrice.toFixed(decimal).toString();
        }else{
            const factor = Math.pow(10, Math.abs(decimal));
            return (Math.floor(convertedPrice / factor) * factor).toString();
        }
    },

    formatExchangeRate: function (currency) {
        const exFrom = currency.second_currency_exchange_rate?.split(":")[0];
        const exTo = currency.second_currency_exchange_rate?.split(":")[1];
        return `${exFrom} ${currency.code} : ${exTo} ${currency.second_currency}`;
    },

    timeAgo: function (timestamp) {
        if (!timestamp) return '';

        const now = new Date();
        const past = new Date(timestamp);
        const diffInSeconds = Math.floor((now - past) / 1000);

        if (diffInSeconds < 60) {
            return `${diffInSeconds}s ago`;
        }

        const diffInMinutes = Math.floor(diffInSeconds / 60);
        if (diffInMinutes < 60) {
            return `${diffInMinutes}m ago`;
        }

        const diffInHours = Math.floor(diffInMinutes / 60);
        if (diffInHours < 24) {
            return `${diffInHours}h ago`;
        }

        const diffInDays = Math.floor(diffInHours / 24);
        if (diffInDays < 30) {
            return `${diffInDays}d ago`;
        }

        const diffInMonths = Math.floor(diffInDays / 30);
        if (diffInMonths < 12) {
            return `${diffInMonths}mo ago`;
        }

        const diffInYears = Math.floor(diffInMonths / 12);
        return `${diffInYears}y ago`;
    },

    formatDateByPattern: function (date, pattern = 'Y-m-d') {
        if (!date) return null;

        const d = new Date(date);
        const year = d.getFullYear();
        const yearShort = String(year).slice(-2);
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const monthShort = d.toLocaleString('en', { month: 'short' });
        const monthLong = d.toLocaleString('en', { month: 'long' });
        const day = String(d.getDate()).padStart(2, '0');
        const dayShort = d.toLocaleString('en', { weekday: 'short' });
        const dayLong = d.toLocaleString('en', { weekday: 'long' });

        const ordinal = (value) => {
            if ([11, 12, 13].includes(value % 100)) return `${value}th`;
            return `${value}${{ 1: 'st', 2: 'nd', 3: 'rd' }[value % 10] || 'th'}`;
        };

        const tokens = {
            dS: ordinal(d.getDate()),
            Y: year,
            y: yearShort,
            F: monthLong,
            M: monthShort,
            m: month,
            n: d.getMonth() + 1,
            d: day,
            j: d.getDate(),
            D: dayShort,
            l: dayLong,
        };

        return pattern.replace(/dS|[YyFMmndjDl]/g, token => tokens[token] ?? token);
    },

    formatTimeByPattern: function (date, pattern = 'h:i A') {
        if (!date) return null;

        const d = new Date(date);

        let hours = d.getHours();
        const minutes = String(d.getMinutes()).padStart(2, '0');
        const seconds = String(d.getSeconds()).padStart(2, '0');
        const hour12 = hours % 12 || 12;

        const tokens = {
            H: String(hours).padStart(2, '0'),
            G: hours,
            h: String(hour12).padStart(2, '0'),
            g: hour12,
            i: minutes,
            s: seconds,
            A: hours >= 12 ? 'PM' : 'AM',
            a: hours >= 12 ? 'pm' : 'am',
        };

        return pattern.replace(/[HGhgisaA]/g, token => tokens[token] ?? token);
    },

    phpDateToDatepickerFormat: function (format = 'd/m/Y') {
        const tokens = {
            dS: 'do',
            Y: 'yyyy',
            y: 'yy',
            F: 'MMMM',
            M: 'MMM',
            m: 'MM',
            n: 'M',
            d: 'dd',
            j: 'd',
            D: 'EEE',
            l: 'EEEE',
        };

        return format.replace(/dS|[YyFMmndjDl]/g, token => tokens[token] || token);
    },

    phpTimeToDatepickerFormat: function (format = 'h:i A') {
        const tokens = {
            H: 'HH',
            G: 'H',
            h: 'hh',
            g: 'h',
            i: 'mm',
            s: 'ss',
            A: 'aa',
            a: 'aa',
        };

        return format.replace(/[HGhgisaA]/g, token => tokens[token] || token);
    },

    datepickerDateTimeFormat: function (dateFormat = 'd/m/Y', timeFormat = 'h:i A', separator = ', ') {
        return `${this.phpDateToDatepickerFormat(dateFormat)}${separator}${this.phpTimeToDatepickerFormat(timeFormat)}`;
    },

    is24HourTimeFormat: function (timeFormat = 'h:i A') {
        return timeFormat.includes('H') || timeFormat.includes('G');
    },

    frontendDateFormat: function () {
        return store.getters['frontendSetting/lists']?.site_date_format || 'd/m/Y';
    },

    frontendTimeFormat: function () {
        return store.getters['frontendSetting/lists']?.site_time_format || 'h:i A';
    },

    formatDateTimeForFilter: function (date, separator = ', ') {
        if (!date) return null;

        return this.formatDateTime(date, this.frontendDateFormat(), this.frontendTimeFormat(), separator);
    },

    formatDateTime: function (date, dateFormat = 'Y-m-d', timeFormat = 'h:i:s A', separator = ' ') {
        if (!date) return null;

        return `${this.formatDateByPattern(date, dateFormat)}${separator}${this.formatTimeByPattern(date, timeFormat)}`;
    },

    formatDecimal: function (amount) {
        return parseFloat(amount).toFixed(2);
    }
};
