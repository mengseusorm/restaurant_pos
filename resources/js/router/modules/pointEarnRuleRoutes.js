// import PointEarnRuleComponent from "../../components/admin/pointEarnRules/PointEarnRuleComponent";
// import PointEarnRuleListComponent from "../../components/admin/pointEarnRules/PointEarnRuleListComponent";
const PointEarnRuleComponent = () => import("../../components/admin/pointEarnRules/PointEarnRuleComponent");
const PointEarnRuleListComponent = () => import("../../components/admin/pointEarnRules/PointEarnRuleListComponent");

export default [
    {
        path: "/admin/point-earn-rules",
        component: PointEarnRuleComponent,
        name: "admin.point-earn-rules",
        redirect: {name: "admin.point-earn-rules.list"},
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "point-earn-rules",
            breadcrumb: "point-earn-rules",
        },
        children: [
            {
                path: "",
                component: PointEarnRuleListComponent,
                name: "admin.point-earn-rules.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "point-earn-rules",
                    breadcrumb: "",
                }
            },
        ],
    },
];
