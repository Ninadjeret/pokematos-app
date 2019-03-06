import Map from './components/Map.vue'
import List from './components/List.vue'
import Settings from './components/Settings.vue'
import Admin from './components/Admin.vue'
import AdminGyms from './components/admin/Gyms.vue'
import AdminGym from './components/admin/Gym.vue'
import AdminZones from './components/admin/Zones.vue'
import AdminZone from './components/admin/Zone.vue'
import AdminAccess from './components/admin/Acces.vue'

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
                     path: 'gyms',
                     name: 'admin.access',
                     meta: {
                         title: 'Gérer les droits d\'accès',
                         parent: 'admin'
                     },
                     component: AdminAccess
               },
                {
                      path: 'gyms',
                      name: 'admin.gyms',
                      meta: {
                          title: 'Gérer les arênes',
                          parent: 'admin'
                      },
                      component: AdminGyms
                },
                {
                      path: 'gyms/add',
                      name: 'admin.gyms.add',
                      meta: {
                          title: 'Nouvelle arêne',
                          parent: 'admin.gyms'
                      },
                      component: AdminGym
                },
                {
                      path: 'gyms/:id',
                      name: 'admin.gyms.edit',
                      meta: {
                          title: 'Modifier l\'arêne',
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
