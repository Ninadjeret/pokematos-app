import Map from './components/Map.vue'
import List from './components/List.vue'
import Settings from './components/Settings.vue'
import Admin from './components/Admin.vue'
import AdminGyms from './components/admin/Gyms.vue'
import AdminGym from './components/admin/Gym.vue'
import AdminZones from './components/admin/Zones.vue'
import AdminZone from './components/admin/Zone.vue'
import AdminAccess from './components/admin/Acces.vue'
import AdminBosses from './components/admin/Bosses.vue'
import AdminRaidsEx from './components/admin/RaidsEx.vue'
import AdminRolesHome from './components/admin/RolesHome.vue'
import AdminRolesCategories from './components/admin/roles/Categories.vue'
import AdminRolesCategorie from './components/admin/roles/Categorie.vue'
import AdminRolesRoles from './components/admin/roles/Roles.vue'
import AdminRolesRole from './components/admin/roles/Role.vue'

import AdminQuests from './components/admin/quests/Quests.vue'
import AdminQuest from './components/admin/quests/Quest.vue'

import AdminRaidReportingHome from './components/admin/RaidReporting.vue'
import AdminRaidsConnectors from './components/admin/raids/Connecteurs.vue'
import AdminRaidsConnector from './components/admin/raids/Connecteur.vue'

const routes = [
    {
        path: '/',
        name: 'map',
        meta: {
            title: 'Map'
        },
        component: Map
     },
     {
         path: '/list',
         name: 'list',
         meta: {
             title: 'Liste'
         },
         component: List
      },
      {
          path: '/settings',
          name: 'settings',
          meta: {
              title: 'Réglages'
          },
          component: Settings
       },
       {
           path: '/admin',
           name: 'admin',
           meta: {
               title: 'Administration'
           },
           component: Admin,
           children: [
               {
                     path: ':id/access',
                     name: 'admin.access',
                     meta: {
                         title: 'Gérer les droits d\'accès',
                         parent: 'admin'
                     },
                     component: AdminAccess
                },
                {
                      path: ':id/roles',
                      name: 'admin.roles',
                      meta: {
                          title: 'Gérer les roles personnalisés',
                          parent: 'admin'
                      },
                      component: AdminRolesHome,
                      children: [
                          {
                                path: 'categories',
                                name: 'admin.roles.categories',
                                meta: {
                                    title: 'Gérer les catégories de roles',
                                    parent: 'admin.roles'
                                },
                                component: AdminRolesCategories
                           },
                           {
                                 path: 'categories/add',
                                 name: 'admin.roles.categories.add',
                                 meta: {
                                     title: 'Nouvelle catégorie',
                                     parent: 'admin.roles.categories'
                                 },
                                 component: AdminRolesCategorie
                            },
                           {
                                 path: 'categories/:category_id',
                                 name: 'admin.roles.categories.edit',
                                 meta: {
                                     title: 'Modifier la catégorie',
                                     parent: 'admin.roles.categories'
                                 },
                                 component: AdminRolesCategorie
                            },
                            {
                                  path: 'roles',
                                  name: 'admin.roles.roles',
                                  meta: {
                                      title: 'Gérer les roles',
                                      parent: 'admin.roles'
                                  },
                                  component: AdminRolesRoles
                             },
                             {
                                   path: 'roles/add',
                                   name: 'admin.roles.roles.add',
                                   meta: {
                                       title: 'Nouveau role',
                                       parent: 'admin.roles.roles'
                                   },
                                   component: AdminRolesRole
                              },
                             {
                                   path: 'roles/:role_id',
                                   name: 'admin.roles.roles.edit',
                                   meta: {
                                       title: 'Modifier le role',
                                       parent: 'admin.roles.roles'
                                   },
                                   component: AdminRolesRole
                              },
                       ]
                 },
                {
                      path: ':id/raids',
                      name: 'admin.raids',
                      meta: {
                          title: 'Signalement de raids',
                          parent: 'admin'
                      },
                      component: AdminRaidReportingHome,
                      children: [
                          {
                                path: 'connectors',
                                name: 'admin.raids.annonces',
                                meta: {
                                    title: 'Gérer les connecteurs',
                                    parent: 'admin.raids'
                                },
                                component: AdminRaidsConnectors
                           },
                           {
                                 path: 'connectors/add',
                                 name: 'admin.raids.annonces.add',
                                 meta: {
                                     title: 'Nouveau connecteur',
                                     parent: 'admin.raids.annonces'
                                 },
                                 component: AdminRaidsConnector
                            },
                           {
                                 path: 'connectors/:connector_id',
                                 name: 'admin.raids.annonces.edit',
                                 meta: {
                                     title: 'Modifier le connecteur',
                                     parent: 'admin.raids.annonces'
                                 },
                                 component: AdminRaidsConnector
                            },
                       ]
                 },
                 {
                       path: ':id/raidsex',
                       name: 'admin.raidsex',
                       meta: {
                           title: 'Signalement de raids EX',
                           parent: 'admin'
                       },
                       component: AdminRaidsEx
                  },
                {
                      path: 'gyms',
                      name: 'admin.gyms',
                      meta: {
                          title: 'Gérer les POIs',
                          parent: 'admin'
                      },
                      component: AdminGyms
                },
                {
                      path: 'gyms/add',
                      name: 'admin.gyms.add',
                      meta: {
                          title: 'Nouveau POI',
                          parent: 'admin.gyms'
                      },
                      component: AdminGym
                },
                {
                      path: 'gyms/:id',
                      name: 'admin.gyms.edit',
                      meta: {
                          title: 'Modifier le POI',
                          parent: 'admin.gyms'
                      },
                      component: AdminGym
                },
                {
                      path: 'zones',
                      name: 'admin.zones',
                      meta: {
                          title: 'Gérer les zones géographiques',
                          parent: 'admin'
                      },
                      component: AdminZones
                },
                {
                      path: 'zones/add',
                      name: 'admin.zones.add',
                      meta: {
                          title: 'Nouvelle zone',
                          parent: 'admin.zones'
                      },
                      component: AdminZone
                },
                {
                      path: 'zones/:id',
                      name: 'admin.zones.edit',
                      meta: {
                          title: 'Modifier la zone',
                          parent: 'admin.zones'
                      },
                      component: AdminZone
                },
                {
                      path: 'bosses',
                      name: 'admin.bosses',
                      meta: {
                          title: 'Gérer les boss',
                          parent: 'admin'
                      },
                      component: AdminBosses
                },
                {
                      path: 'quests',
                      name: 'admin.quests',
                      meta: {
                          title: 'Gérer les quêtes',
                          parent: 'admin'
                      },
                      component: AdminQuests
                },
                {
                      path: 'quests/add',
                      name: 'admin.quests.add',
                      meta: {
                          title: 'Nouvelle quête',
                          parent: 'admin.quests'
                      },
                      component: AdminQuest
                },
                {
                      path: 'quests/:id',
                      name: 'admin.quests.edit',
                      meta: {
                          title: 'Modifier la quête',
                          parent: 'admin.quests'
                      },
                      component: AdminQuest
                },
        ]
        },
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
