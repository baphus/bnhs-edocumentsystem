import { config } from '@vue/test-utils';

// Mock route helper (Laravel Ziggy)
const mockRoute = (name: string, params?: any) => {
  if (params) {
    const paramValues = Object.values(params);
    if (paramValues.length > 0) {
      return `/${name}/${paramValues.join('/')}`;
    }
  }
  return `/${name}`;
};

// Make route available globally
(global as any).route = mockRoute;

// Mock Inertia.js components
config.global.stubs = {
  Head: {
    name: 'Head',
    template: '<div><slot /></div>',
    props: ['title'],
  },
  Link: {
    name: 'Link',
    template: '<a :href="href" @click.prevent><slot /></a>',
    props: ['href', 'as'],
  },
};

// Add route to Vue global properties
config.global.mocks = {
  route: mockRoute,
};

// Mock window.location
Object.defineProperty(window, 'location', {
  value: {
    href: '',
    origin: 'http://localhost',
    pathname: '/',
  },
  writable: true,
});

// Mock IntersectionObserver
global.IntersectionObserver = class IntersectionObserver {
  constructor() {}
  disconnect() {}
  observe() {}
  takeRecords() { return []; }
  unobserve() {}
} as any;

