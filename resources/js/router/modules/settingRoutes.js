// import SettingsComponent from "../../components/admin/settings/SettingsComponent";
// import CompanyComponent from "../../components/admin/settings/Company/CompanyComponent";
// import SiteComponent from "../../components/admin/settings/Site/SiteComponent";
// import ItemCategoryListComponent from "../../components/admin/settings/ItemCategory/ItemCateogryListComponent";
// import ItemCategoryComponent from "../../components/admin/settings/ItemCategory/ItemCategoryComponent";
// import ItemCategoryShowComponent from "../../components/admin/settings/ItemCategory/ItemCategoryShowComponent";
// import ItemAttributeComponent from "../../components/admin/settings/ItemAttribute/ItemAttributeComponent";
// import ItemAttributeListComponent from "../../components/admin/settings/ItemAttribute/ItemAttributeListComponent";
// import BranchComponent from "../../components/admin/settings/Branch/BranchComponent";
// import BranchListComponent from "../../components/admin/settings/Branch/BranchListComponent";
// import BranchShowComponent from "../../components/admin/settings/Branch/BranchShowComponent";
// import TaxComponent from "../../components/admin/settings/Tax/TaxComponent";
// import TaxListComponent from "../../components/admin/settings/Tax/TaxListComponent";
// import CurrencyComponent from "../../components/admin/settings/Currency/CurrencyComponent";
// import CurrencyListComponent from "../../components/admin/settings/Currency/CurrencyListComponent";
const SettingsComponent = () => import("../../components/admin/settings/SettingsComponent");
const CompanyComponent = () => import("../../components/admin/settings/Company/CompanyComponent");
const SiteComponent = () => import("../../components/admin/settings/Site/SiteComponent");
const ItemCategoryListComponent = () => import("../../components/admin/settings/ItemCategory/ItemCateogryListComponent");
const ItemCategoryComponent = () => import("../../components/admin/settings/ItemCategory/ItemCategoryComponent");
const ItemCategoryShowComponent = () => import("../../components/admin/settings/ItemCategory/ItemCategoryShowComponent");
const ItemAttributeComponent = () => import("../../components/admin/settings/ItemAttribute/ItemAttributeComponent");
const ItemAttributeListComponent = () => import("../../components/admin/settings/ItemAttribute/ItemAttributeListComponent");
const ItemAttributeVariationComponent = () => import("../../components/admin/settings/ItemAttributeVariation/ItemAttributeVariationComponent");
const ItemAttributeVariationListComponent = () => import("../../components/admin/settings/ItemAttributeVariation/ItemAttributeVariationListComponent");
const BatchApplyVariationComponent = () => import("../../components/admin/settings/ItemAttributeVariation/BatchApplyVariationComponent");
const BranchComponent = () => import("../../components/admin/settings/Branch/BranchComponent");
const BranchListComponent = () => import("../../components/admin/settings/Branch/BranchListComponent");
const BranchShowComponent = () => import("../../components/admin/settings/Branch/BranchShowComponent");
const TaxComponent = () => import("../../components/admin/settings/Tax/TaxComponent");
const TaxListComponent = () => import("../../components/admin/settings/Tax/TaxListComponent");
const CurrencyComponent = () => import("../../components/admin/settings/Currency/CurrencyComponent");
const CurrencyListComponent = () => import("../../components/admin/settings/Currency/CurrencyListComponent");
const ExchangeRateComponent = () => import("../../components/admin/settings/ExchangeRate/ExchangeRateComponent");
const ExchangeRateListComponent = () => import("../../components/admin/settings/ExchangeRate/ExchangeRateListComponent");
const ExchangeRateLogComponent = () => import("../../components/admin/settings/ExchangeRateLog/ExchangeRateLogComponent");
const ExchangeRateLogsListComponent = () => import("../../components/admin/settings/ExchangeRateLog/ExchangeRateLogsListComponent");
const OrderStatusComponent = () => import("../../components/admin/settings/OrderStatus/OrderStatusComponent");
const OrderStatusListComponent = () => import("../../components/admin/settings/OrderStatus/OrderStatusListComponent");
const OrderTypeComponent = () => import("../../components/admin/settings/OrderType/OrderTypeComponent");
const OrderTypeListComponent = () => import("../../components/admin/settings/OrderType/OrderTypeListComponent");
import MailComponent from "../../components/admin/settings/Mail/MailComponent";
import PageComponent from "../../components/admin/settings/Page/PageComponent";
import PageListComponent from "../../components/admin/settings/Page/PageListComponent";
import PageShowComponent from "../../components/admin/settings/Page/PageShowComponent";
import OtpComponent from "../../components/admin/settings/Otp/OtpComponent";
// import LicenseComponent from "../../components/admin/settings/License/LicenseComponent";
import AnalyticComponent from "../../components/admin/settings/analytics/AnalyticComponent";
import AnalyticListComponent from "../../components/admin/settings/analytics/AnalyticListComponent";
import AnalyticShowComponent from "../../components/admin/settings/analytics/AnalyticShowComponent";
import RoleComponent from "../../components/admin/settings/Role/RoleComponent";
import RoleListComponent from "../../components/admin/settings/Role/RoleListComponent";
import RoleShowComponent from "../../components/admin/settings/Role/RoleShowComponent";
import ThemeComponent from "../../components/admin/settings/Theme/ThemeComponent";
import LanguageComponent from "../../components/admin/settings/Language/LanguageComponent";
import LanguageListComponent from "../../components/admin/settings/Language/LanguageListComponent";
import LanguageShowComponent from "../../components/admin/settings/Language/LanguageShowComponent";
import PaymentGatewayComponent from "../../components/admin/settings/PaymentGateway/PaymentGatewayComponent";
import PaymentMethodComponent from "../../components/admin/settings/PaymentMethod/PaymentMethodComponent.vue";
import ConnectABAPaymentComponent from "../../components/admin/settings/ConnectABAPayment/ConnectABAPaymentComponent";
import ABAPaymentConnectInfoComponent from "../../components/admin/settings/ConnectABAPayment/ABAPaymentConnectInfoComponent";
import SmsGatewayComponent from "../../components/admin/settings/SmsGateway/SmsGatewayComponent";
import NotificationAlertComponent from "../../components/admin/settings/NotificationAlert/NotificationAlertComponent";
import NotificationComponent from "../../components/admin/settings/Notification/NotificationComponent";

import PrintLabelSettingComponent from "../../components/admin/settings/PrintLabel/PrintLabelSettingComponent"; 
import PrintLableSettingListComponent from "../../components/admin/settings/PrintLabel/PrintLableSettingListComponent";
import ShopCategoryComponent from "../../components/admin/settings/ShopCategory/ShopCategoryComponent";
import ShopCategoryListComponent from "../../components/admin/settings/ShopCategory/ShopCategoryListComponent";
import PrinterComponent from "../../components/admin/settings/Printer/PrinterListComponent.vue";
import OrderPrintLogComponent from "../../components/admin/settings/OrderPrintLogs/OrderPrintLogListComponent";
import ActivityLogComponent from "../../components/admin/settings/ActivityLogs/ActivityLogListComponent";


// import ShopCategoryShowComponent  from "../../components/admin/settings/ShopCategory/ShopCategoryShowComponent ";
export default [
    {
        path: "/admin/settings",
        component: SettingsComponent,
        name: "admin.settings",
        redirect: { name: "admin.settings.company" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "settings",
            breadcrumb: "settings",
        },
        children: [
            {
                path: "company",
                component: CompanyComponent,
                name: "admin.settings.company",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "company",
                },
            },
            {
                path: "site",
                component: SiteComponent,
                name: "admin.settings.site",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "site",
                },
            },
            {
                path: "branches",
                component: BranchComponent,
                name: "admin.settings.branch",
                redirect: { name: "admin.settings.branch.list" },
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "branches",
                },
                children: [
                    {
                        path: "list",
                        component: BranchListComponent,
                        name: "admin.settings.branch.list",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "",
                        },
                    },
                    {
                        path: "show/:id",
                        component: BranchShowComponent,
                        name: "admin.settings.branch.show",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "view",
                        },
                    },
                ],
            },
            {
                path: "mail",
                component: MailComponent,
                name: "admin.settings.mail",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "mail",
                },
            },
            {
                path: "otp",
                component: OtpComponent,
                name: "admin.settings.otp",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "otp",
                },
            },
            {
                path: "analytics",
                component: AnalyticComponent,
                name: "admin.settings.analytic",
                redirect: { name: "admin.settings.analytic.list" },
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "analytics",
                },
                children: [
                    {

                        path: "list",
                        component: AnalyticListComponent,
                        name: "admin.settings.analytic.list",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "",
                        },
                    },
                    {
                        path: "show/:id",
                        component: AnalyticShowComponent,
                        name: "admin.settings.analytic.show",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "view",
                        },
                    },
                ]
            },
            {
                path: "theme",
                component: ThemeComponent,
                name: "admin.settings.theme",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "theme",
                },
            },
            {
                path: "currencies",
                component: CurrencyComponent,
                name: "admin.settings.currency",
                redirect: { name: "admin.settings.currency.list" },
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "currencies",
                },
                children: [
                    {
                        path: "list",
                        component: CurrencyListComponent,
                        name: "admin.settings.currency.list",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "",
                        },
                    },
                ],
            },
            {
                path: "exchange-rates",
                component: ExchangeRateComponent,
                name: "admin.settings.exchangeRate",
                redirect: { name: "admin.settings.exchangeRate.list" },
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "exchange_rates",
                },
                children: [
                    {
                        path: "list",
                        component: ExchangeRateListComponent,
                        name: "admin.settings.exchangeRate.list",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "",
                        },
                    },
                ],
            },
            {
                path: "exchange-rate-logs",
                component: ExchangeRateLogComponent,
                name: "admin.settings.exchangeRateLog",
                redirect: { name: "admin.settings.exchangeRateLog.list" },
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "exchange_rate_logs",
                },
                children: [
                    {
                        path: "list",
                        component: ExchangeRateLogsListComponent,
                        name: "admin.settings.exchangeRateLog.list",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "",
                        },
                    },
                ],
            },
            {
                path: "order-statuses",
                component: OrderStatusComponent,
                name: "admin.settings.orderStatus",
                redirect: { name: "admin.settings.orderStatus.list" },
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "order_statuses",
                },
                children: [
                    {
                        path: "list",
                        component: OrderStatusListComponent,
                        name: "admin.settings.orderStatus.list",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "",
                        },
                    },
                ],
            },
            {
                path: "order-types",
                component: OrderTypeComponent,
                name: "admin.settings.orderType",
                redirect: { name: "admin.settings.orderType.list" },
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "order_types",
                },
                children: [
                    {
                        path: "list",
                        component: OrderTypeListComponent,
                        name: "admin.settings.orderType.list",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "",
                        },
                    },
                ],
            },
            {
                path: "shop-categories",
                component: ShopCategoryComponent,
                name: "admin.settings.shopCategory",
                redirect: { name: "admin.settings.shopCategory.list" },
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "shop_categories",
                },
                children: [
                    {
                        path: "list",
                        component: ShopCategoryListComponent,
                        name: "admin.settings.shopCategory.list",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "",
                        },
                    },
                    // {
                    //     path: "show/:id",
                    //     component: ShopCategoryShowComponent,
                    //     name: "admin.settings.shopCategory.show",
                    //     meta: {
                    //         isFrontend: false,
                    //         auth: true,
                    //         permissionUrl: "settings",
                    //         breadcrumb: "view",
                    //     },
                    // },
                ],
            },
            {
                path: "item-categories",
                component: ItemCategoryComponent,
                name: "admin.settings.itemCategory",
                redirect: { name: "admin.settings.itemCategory.list" },
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "item_categories",
                },
                children: [
                    {
                        path: "list",
                        component: ItemCategoryListComponent,
                        name: "admin.settings.itemCategory.list",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "",
                        },
                    },
                    {
                        path: "show/:id",
                        component: ItemCategoryShowComponent,
                        name: "admin.settings.itemCategory.show",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "view",
                        },
                    },
                ],
            },
            {
                path: "item-attributes",
                component: ItemAttributeComponent,
                name: "admin.settings.itemAttribute",
                redirect: { name: "admin.settings.itemAttribute.list" },
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "item_attributes",
                },
                children: [
                    {
                        path: "list",
                        component: ItemAttributeListComponent,
                        name: "admin.settings.itemAttribute.list",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "",
                        },
                    },
                ],
            },
            {
                path: "item-attribute-variations",
                component: ItemAttributeVariationComponent,
                name: "admin.settings.itemAttributeVariation",
                redirect: { name: "admin.settings.itemAttributeVariation.list" },
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "item_attribute_variations",
                },
                children: [
                    {
                        path: "list",
                        component: ItemAttributeVariationListComponent,
                        name: "admin.settings.itemAttributeVariation.list",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "",
                        },
                    },
                ],
            },
            {
                path: "batch-apply-variations",
                component: BatchApplyVariationComponent,
                name: "admin.settings.batchApplyVariation",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "batch_apply_variations",
                },
            },
            {
                path: "taxes",
                component: TaxComponent,
                name: "admin.settings.tax",
                redirect: { name: "admin.settings.tax.list" },
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "taxes",
                },
                children: [
                    {
                        path: "list",
                        component: TaxListComponent,
                        name: "admin.settings.tax.list",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "",
                        },
                    },
                ],
            },
            {
                path: "pages",
                component: PageComponent,
                name: "admin.settings.page",
                redirect: { name: "admin.settings.page.list" },
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "pages",
                },
                children: [
                    {
                        path: "list",
                        component: PageListComponent,
                        name: "admin.settings.page.list",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "",
                        },
                    },
                    {
                        path: "show/:id",
                        component: PageShowComponent,
                        name: "admin.settings.page.show",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "view",
                        },
                    },
                ],
            },
            {
                path: "role",
                component: RoleComponent,
                name: "admin.settings.role",
                redirect: { name: "admin.settings.role.list" },
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "role_permissions",
                },
                children: [
                    {
                        path: "list",
                        component: RoleListComponent,
                        name: "admin.settings.role.list",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "",
                        },
                    },
                    {
                        path: "show/:id",
                        component: RoleShowComponent,
                        name: "admin.settings.role.show",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "view",
                        },
                    },
                ],
            },
            {
                path: "languages",
                component: LanguageComponent,
                name: "admin.settings.language",
                redirect: { name: "admin.settings.language.list" },
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "languages",
                },
                children: [
                    {
                        path: "list",
                        component: LanguageListComponent,
                        name: "admin.settings.language.list",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "",
                        },
                    },
                    {
                        path: "show/:id",
                        component: LanguageShowComponent,
                        name: "admin.settings.language.show",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "settings",
                            breadcrumb: "view",
                        },
                    },
                ],
            },
            {
                path: "sms-gateway",
                component: SmsGatewayComponent,
                name: "admin.settings.smsGateway",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "sms_gateway",
                },
            },
            {
                path: "payment-method",
                component: PaymentMethodComponent,
                name: "admin.settings.PaymentMethod",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "payment_method",
                },
            },
            {
                path: "payment-gateway",
                component: PaymentGatewayComponent,
                name: "admin.settings.paymentGateway",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "payment_gateway",
                },
            },
            {
                path: "connect-aba-payment",
                component: ConnectABAPaymentComponent,
                name: "admin.settings.connectABAPayment",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "connect_aba_payment",
                },
            },
            {
                path: "aba-payment-info",
                component: ABAPaymentConnectInfoComponent,
                name: "admin.settings.abaPaymentInfo",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "aba_payment_info",
                },
            },
            {
                path: "printer",
                component: PrinterComponent,
                name: "admin.settings.printer",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "printer",
                },
            },
            {
                path: "orderPrintLogs",
                component: OrderPrintLogComponent,
                name: "admin.settings.orderPrintLogs",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "order_print_logs",
                },
            },
            {
                path: "activitylogs",
                component: ActivityLogComponent,
                name: "admin.settings.activitylogs",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "order_print_logs",
                },
            },
            // {
            //     path: "license",
            //     component: LicenseComponent,
            //     name: "admin.settings.license",
            //     meta: {
            //         isFrontend: false,
            //         auth: true,
            //         permissionUrl: "settings",
            //         breadcrumb: "license",
            //     }
            // },
            {
                path: "notification-alert",
                component: NotificationAlertComponent,
                name: "admin.settings.notificationAlert",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "notification_alert",
                }
            },
            {
                path: "notification",
                component: NotificationComponent,
                name: "admin.settings.notification",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "notification",
                },
            },
            {
                path: "printLabel",
                component: PrintLabelSettingComponent,
                name: "admin.settings.printLabel",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "print_label",
                },
                children: [
                    {
                        path: "",
                        component: PrintLableSettingListComponent,
                        name: "admin.settings.printLabel.list",
                        meta: {
                            isFrontend: false,
                            auth: true,
                            permissionUrl: "printLabel",
                            breadcrumb: "",
                        },
                    },
                ],
            },
        ],
    },
];
