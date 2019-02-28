import Map from './components/Map.vue'
import List from './components/List.vue'
import Settings from './components/Settings.vue'
import Admin from './components/Admin.vue'
import AdminBosses from './components/admin/Bosses.vue'

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
                      path: 'boss',
                      name: 'admin.boss',
                      meta: {
                          title: 'Gérer les Boss'
                      },
                      component: AdminBosses
                }
        ]
        },
];

export default routes;
