import Map from "./components/Map.vue";
import List from "./components/List.vue";
import Profile from "./components/Profile.vue";
import Admin from "./components/Admin.vue";
import AdminSettings from "./components/admin/Settings.vue";
import AdminGyms from "./components/admin/Gyms.vue";
import AdminGym from "./components/admin/Gym.vue";
import AdminZones from "./components/admin/Zones.vue";
import AdminZone from "./components/admin/Zone.vue";
import AdminAccess from "./components/admin/Acces.vue";
import AdminWelcome from "./components/admin/Welcome.vue";
import AdminMap from "./components/admin/Map.vue";
import AdminBosses from "./components/admin/Bosses.vue";
import AdminRaidsEx from "./components/admin/RaidsEx.vue";
import AdminRolesHome from "./components/admin/RolesHome.vue";
import AdminRolesCategories from "./components/admin/roles/Categories.vue";
import AdminRolesCategorie from "./components/admin/roles/Categorie.vue";
import AdminRolesRoles from "./components/admin/roles/Roles.vue";
import AdminRolesRole from "./components/admin/roles/Role.vue";

import AdminQuests from "./components/admin/quests/Quests.vue";
import AdminQuest from "./components/admin/quests/Quest.vue";

import AdminLogs from "./components/admin/Logs.vue";

import AdminRocketBosses from "./components/admin/rocket/Bosses.vue";
import AdminRocketHome from "./components/admin/rocket/Home.vue";
import AdminRocketConnectors from "./components/admin/rocket/Connecteurs.vue";
import AdminRocketConnector from "./components/admin/rocket/Connecteur.vue";

import AdminRaidReportingHome from "./components/admin/RaidReporting.vue";
import AdminRaidsConnectors from "./components/admin/raids/Connecteurs.vue";
import AdminRaidsConnector from "./components/admin/raids/Connecteur.vue";

import AdminQuestReportingHome from "./components/admin/quests/Home.vue";
import AdminQuestsConnectors from "./components/admin/quests/Connecteurs.vue";
import AdminQuestsConnector from "./components/admin/quests/Connecteur.vue";

import Events from "./components/Events.vue";
import Event from "./components/Event.vue";
import AdminEventsHome from "./components/admin/events/Home.vue";
import AdminEvents from "./components/admin/events/Events.vue";
import AdminEvent from "./components/admin/events/Event.vue";
import AdminEventsInvits from "./components/admin/events/Invits.vue";

const routes = [
    {
        path: "/",
        name: "map",
        meta: {
            title: "Map"
        },
        component: Map
    },
    {
        path: "/list",
        name: "list",
        meta: {
            title: "Listes"
        },
        component: List
    },
    {
        path: "/profile",
        name: "profile",
        meta: {
            title: "Profil"
        },
        component: Profile
    },
    {
        path: "/events",
        name: "events",
        meta: {
            title: "Évents"
        },
        component: Events
    },
    {
        path: "/events/:event_id",
        name: "events.event",
        meta: {
            title: "Détail de l'évent",
            parent: "events"
        },
        component: Event
    },
    {
        path: "/admin",
        name: "admin",
        meta: {
            title: "Administration"
        },
        component: Admin,
        children: [
            {
                path: ":id/access",
                name: "admin.access",
                meta: {
                    title: "Gérer les droits d'accès",
                    parent: "admin"
                },
                component: AdminAccess
            },
            {
                path: ":id/settings",
                name: "admin.settings",
                meta: {
                    title: "Réglages généraux",
                    parent: "admin"
                },
                component: AdminSettings
            },
            {
                path: ":id/welcome",
                name: "admin.welcome",
                meta: {
                    title: "Message de bienvenue",
                    parent: "admin"
                },
                component: AdminWelcome
            },
            {
                path: ":id/events",
                name: "admin.events.home",
                meta: {
                    title: "Évents",
                    parent: "admin"
                },
                component: AdminEventsHome,
                children: [
                    {
                        path: "events",
                        name: "admin.events",
                        meta: {
                            title: "Évents",
                            parent: "admin.events.home"
                        },
                        component: AdminEvents
                    },
                    {
                        path: "add",
                        name: "admin.events.add",
                        meta: {
                            title: "Ajouter un évent",
                            parent: "admin.events"
                        },
                        component: AdminEvent
                    },
                    {
                        path: "events/:event_id",
                        name: "admin.events.edit",
                        meta: {
                            title: "Modifier l'évent",
                            parent: "admin.events"
                        },
                        component: AdminEvent
                    },
                    {
                        path: "invits",
                        name: "admin.events.invits",
                        meta: {
                            title: "Invitations",
                            parent: "admin.events.home"
                        },
                        component: AdminEventsInvits
                    },
                ]
            },
            {
                path: ":id/roles",
                name: "admin.roles",
                meta: {
                    title: "Gérer les roles personnalisés",
                    parent: "admin"
                },
                component: AdminRolesHome,
                children: [
                    {
                        path: "categories",
                        name: "admin.roles.categories",
                        meta: {
                            title: "Gérer les catégories de roles",
                            parent: "admin.roles"
                        },
                        component: AdminRolesCategories
                    },
                    {
                        path: "categories/add",
                        name: "admin.roles.categories.add",
                        meta: {
                            title: "Nouvelle catégorie",
                            parent: "admin.roles.categories"
                        },
                        component: AdminRolesCategorie
                    },
                    {
                        path: "categories/:category_id",
                        name: "admin.roles.categories.edit",
                        meta: {
                            title: "Modifier la catégorie",
                            parent: "admin.roles.categories"
                        },
                        component: AdminRolesCategorie
                    },
                    {
                        path: "roles",
                        name: "admin.roles.roles",
                        meta: {
                            title: "Gérer les roles",
                            parent: "admin.roles"
                        },
                        component: AdminRolesRoles
                    },
                    {
                        path: "roles/add",
                        name: "admin.roles.roles.add",
                        meta: {
                            title: "Nouveau role",
                            parent: "admin.roles.roles"
                        },
                        component: AdminRolesRole
                    },
                    {
                        path: "roles/:role_id",
                        name: "admin.roles.roles.edit",
                        meta: {
                            title: "Modifier le role",
                            parent: "admin.roles.roles"
                        },
                        component: AdminRolesRole
                    }
                ]
            },
            {
                path: ":id/raids",
                name: "admin.raids",
                meta: {
                    title: "Signalement de raids",
                    parent: "admin"
                },
                component: AdminRaidReportingHome,
                children: [
                    {
                        path: "connectors",
                        name: "admin.raids.annonces",
                        meta: {
                            title: "Gérer les connecteurs",
                            parent: "admin.raids"
                        },
                        component: AdminRaidsConnectors
                    },
                    {
                        path: "connectors/add",
                        name: "admin.raids.annonces.add",
                        meta: {
                            title: "Nouveau connecteur",
                            parent: "admin.raids.annonces"
                        },
                        component: AdminRaidsConnector
                    },
                    {
                        path: "connectors/:connector_id",
                        name: "admin.raids.annonces.edit",
                        meta: {
                            title: "Modifier le connecteur",
                            parent: "admin.raids.annonces"
                        },
                        component: AdminRaidsConnector
                    }
                ]
            },
            {
                path: ":id/raidsex",
                name: "admin.raidsex",
                meta: {
                    title: "Signalement de raids EX",
                    parent: "admin"
                },
                component: AdminRaidsEx
            },
            {
                path: ":id/quests",
                name: "admin.quests.home",
                meta: {
                    title: "Signalement de quêtes",
                    parent: "admin"
                },
                component: AdminQuestReportingHome,
                children: [
                    {
                        path: "connectors",
                        name: "admin.quests.annonces",
                        meta: {
                            title: "Gérer les connecteurs",
                            parent: "admin.quests.home"
                        },
                        component: AdminQuestsConnectors
                    },
                    {
                        path: "connectors/add",
                        name: "admin.quests.annonces.add",
                        meta: {
                            title: "Nouveau connecteur",
                            parent: "admin.quests.annonces"
                        },
                        component: AdminQuestsConnector
                    },
                    {
                        path: "connectors/:quest_connector_id",
                        name: "admin.quests.annonces.edit",
                        meta: {
                            title: "Modifier le connecteur",
                            parent: "admin.quests.annonces"
                        },
                        component: AdminQuestsConnector
                    }
                ]
            },
            {
                path: ":id/rocket",
                name: "admin.rocket.home",
                meta: {
                    title: "Signalement de boss Rocket",
                    parent: "admin"
                },
                component: AdminRocketHome,
                children: [
                    {
                        path: "connectors",
                        name: "admin.rocket.annonces",
                        meta: {
                            title: "Gérer les connecteurs",
                            parent: "admin.rocket.home"
                        },
                        component: AdminRocketConnectors
                    },
                    {
                        path: "connectors/add",
                        name: "admin.rocket.annonces.add",
                        meta: {
                            title: "Nouveau connecteur",
                            parent: "admin.rocket.annonces"
                        },
                        component: AdminRocketConnector
                    },
                    {
                        path: "connectors/:invasion_connector_id",
                        name: "admin.rocket.annonces.edit",
                        meta: {
                            title: "Modifier le connecteur",
                            parent: "admin.rocket.annonces"
                        },
                        component: AdminRocketConnector
                    }
                ]
            },
            {
                path: "map",
                name: "admin.map",
                meta: {
                    title: "Paramètres de la carte",
                    parent: "admin"
                },
                component: AdminMap
            },
            {
                path: "gyms",
                name: "admin.gyms",
                meta: {
                    title: "Gérer les POIs",
                    parent: "admin"
                },
                component: AdminGyms
            },
            {
                path: "gyms/add",
                name: "admin.gyms.add",
                meta: {
                    title: "Nouveau POI",
                    parent: "admin.gyms"
                },
                component: AdminGym
            },
            {
                path: "gyms/:poi_id",
                name: "admin.gyms.edit",
                meta: {
                    title: "Modifier le POI",
                    parent: "admin.gyms"
                },
                component: AdminGym
            },
            {
                path: "zones",
                name: "admin.zones",
                meta: {
                    title: "Gérer les zones géographiques",
                    parent: "admin"
                },
                component: AdminZones
            },
            {
                path: "zones/add",
                name: "admin.zones.add",
                meta: {
                    title: "Nouvelle zone",
                    parent: "admin.zones"
                },
                component: AdminZone
            },
            {
                path: "zones/:id",
                name: "admin.zones.edit",
                meta: {
                    title: "Modifier la zone",
                    parent: "admin.zones"
                },
                component: AdminZone
            },
            {
                path: "logs",
                name: "admin.logs",
                meta: {
                    title: "Logs",
                    parent: "admin"
                },
                component: AdminLogs
            },
            {
                path: "bosses",
                name: "admin.bosses",
                meta: {
                    title: "Gérer les boss",
                    parent: "admin"
                },
                component: AdminBosses
            },
            {
                path: "quests",
                name: "admin.quests",
                meta: {
                    title: "Gérer les quêtes",
                    parent: "admin"
                },
                component: AdminQuests
            },
            {
                path: "quests/add",
                name: "admin.quests.add",
                meta: {
                    title: "Nouvelle quête",
                    parent: "admin.quests"
                },
                component: AdminQuest
            },
            {
                path: "quests/:id",
                name: "admin.quests.edit",
                meta: {
                    title: "Modifier la quête",
                    parent: "admin.quests"
                },
                component: AdminQuest
            },
            {
                path: "rocket/bosses",
                name: "admin.rocket.bosses",
                meta: {
                    title: "Boss Rocket",
                    parent: "admin"
                },
                component: AdminRocketBosses
            }
        ]
    }
    /*{
            path: '/admin/boss',
            name: 'admin.boss',
            meta: {
                title: 'Gérer les Boss'
            },
            component: AdminBosses
        },*/
];

export default routes;
