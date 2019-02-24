import Map from './components/Map.vue'
import List from './components/List.vue'
import Settings from './components/Settings.vue'

const routes = [
    {
        path: '/',
        name: 'Map',
        meta: {
            id: 'map'
        },
        component: Map
     },
     {
         path: '/list',
         name: 'Liste',
         meta: {
             id: 'list'
         },
         component: List
      },
      {
          path: '/settings',
          name: 'Réglages',
          meta: {
              id: 'settings'
          },
          component: Settings
       },
];

export default routes;
