export const routes = [
  { path: '/', redirect: '/dashboard' },
  {
    path: '/',
    component: () => import('@fms/layouts/default.vue'),
    children: [
      {
        path: 'dashboard',
        component: () => import('@fms/pages/dashboard.vue'),
      },
      {
        path: 'departments',
        component: () => import('@fms/pages/departments.vue'),
      },
      {
        path: 'annual-budget',
        component: () => import('@fms/pages/annual-budget.vue'),
      },
      {
        path: 'office-codes',
        component: () => import('@fms/pages/officecodes.vue'),
      },
      {
        path: 'expenditures',
        component: () => import('@fms/pages/expenditures.vue'),
      },
      {
        path: 'obr',
        component: () => import('@fms/pages/obr.vue'),
      },
      {
        path: 'pao',
        component: () => import('@fms/pages/pao.vue'),
      },
      {
        path: 'for-approval',
        component: () => import('@fms/pages/for-approval.vue'),
      },
      {
        path: 'paper-trail-management',
        component: () => import('@fms/pages/paper-trail-management.vue'),
      },
      {
        path: 'audit-logs',
        component: () => import('@fms/pages/audit-log.vue'),
      },
      {
        path: 'obr-archived',
        component: () => import('@fms/pages/obr-archived.vue'),
      },
      {
        path: 'access-management',
        component: () => import('@fms/pages/department-employees.vue'),
      },
      {
        path: 'account-settings',
        component: () => import('@fms/pages/account-settings.vue'),
      },
      {
        path: 'typography',
        component: () => import('@fms/pages/typography.vue'),
      },
      {
        path: 'icons',
        component: () => import('@fms/pages/icons.vue'),
      },
      {
        path: 'cards',
        component: () => import('@fms/pages/cards.vue'),
      },
      {
        path: 'tables',
        component: () => import('@fms/pages/tables.vue'),
      },
      {
        path: 'form-layouts',
        component: () => import('@fms/pages/form-layouts.vue'),
      },
    ],
  },
  {
    path: '/',
    component: () => import('@fms/layouts/blank.vue'),
  },
]
