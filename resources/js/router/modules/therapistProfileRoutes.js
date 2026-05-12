const TherapistProfileComponent = () => import("../../components/admin/therapistProfile/TherapistProfileComponent");
const TherapistProfileListComponent = () => import("../../components/admin/therapistProfile/TherapistProfileListComponent");

export default [
    {
        path: "/admin/therapist-profile",
        component: TherapistProfileComponent,
        name: "admin.therapist-profile",
        redirect: { name: "admin.therapist-profile.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "therapist_profiles",
            breadcrumb: "therapist_profiles",
        },
        children: [
            {
                path: "",
                component: TherapistProfileListComponent,
                name: "admin.therapist-profile.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "therapist_profiles",
                    breadcrumb: "",
                },
            },
        ],
    },
]
