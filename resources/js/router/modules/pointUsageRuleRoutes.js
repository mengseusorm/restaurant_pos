// import PointUsageRuleComponent from "../../components/admin/pointUsageRules/PointUsageRuleComponent";
// import PointUsageRuleListComponent from "../../components/admin/pointUsageRules/PointUsageRuleListComponent";
const PointUsageRuleComponent = () => import("../../components/admin/pointUsageRules/PointUsageRuleComponent");
const PointUsageRuleListComponent = () => import("../../components/admin/pointUsageRules/PointUsageRuleListComponent");

export default [
    {
        path: "/admin/point-usage-rules",
        component: PointUsageRuleComponent,
        name: "admin.point-usage-rules",
        redirect: {name: "admin.point-usage-rules.list"},
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "point-usage-rules",
            breadcrumb: "point-usage-rules",
        },
        children: [
            {
                path: "",
                component: PointUsageRuleListComponent,
                name: "admin.point-usage-rules.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "point-usage-rules",
                    breadcrumb: "",
                }
            },
        ],
    },
];
