// import FloorPlanComponent from "../../components/admin/floorPlan/FloorPlanComponent";
const FloorPlanComponent = () => import("../../components/admin/floorPlan/FloorPlanComponent");

export default [
    {
        path: "/admin/floor-plan",
        component: FloorPlanComponent,
        name: "admin.floorPlan",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "settings",
            breadcrumb: "floor_plan",
        },
    },
]
