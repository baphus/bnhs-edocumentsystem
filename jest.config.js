export default {
  preset: 'ts-jest',
  testEnvironment: 'jsdom',
  roots: ['<rootDir>/resources/js', '<rootDir>/tests'],
  moduleFileExtensions: ['js', 'ts', 'vue', 'json'],
  transform: {
    '^.+\\.vue$': '@vue/vue3-jest',
    '^.+\\.ts$': ['ts-jest', {
      useESM: true,
      tsconfig: {
        module: 'ESNext',
        target: 'ES2020',
        esModuleInterop: true,
        skipLibCheck: true,
        jsx: 'preserve',
      },
    }],
    '^.+\\.js$': 'babel-jest',
  },
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/resources/js/$1',
    '^ziggy-js$': '<rootDir>/vendor/tightenco/ziggy',
    '\\.(css|less|scss|sass)$': 'identity-obj-proxy',
  },
  setupFilesAfterEnv: ['<rootDir>/resources/js/tests/setup.ts'],
  testMatch: [
    '**/__tests__/**/*.(ts|js)',
    '**/*.(test|spec).(ts|js)',
  ],
  collectCoverageFrom: [
    'resources/js/**/*.{ts,js,vue}',
    '!resources/js/**/*.d.ts',
    '!resources/js/app.ts',
    '!resources/js/bootstrap.ts',
    '!resources/js/ssr.ts',
    '!resources/js/**/index.ts',
  ],
  coveragePathIgnorePatterns: [
    '/node_modules/',
    '/vendor/',
  ],
  testEnvironmentOptions: {
    customExportConditions: ['node', 'node-addons'],
  },
  // Performance thresholds for production
  coverageThreshold: {
    global: {
      branches: 50,
      functions: 50,
      lines: 50,
      statements: 50,
    },
  },
};

