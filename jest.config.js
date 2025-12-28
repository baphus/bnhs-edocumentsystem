export default {
  preset: 'ts-jest',
  testEnvironment: 'jsdom',
  roots: ['<rootDir>/resources/js'],
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
      },
    }],
    '^.+\\.js$': 'babel-jest',
  },
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/resources/js/$1',
    '^ziggy-js$': '<rootDir>/vendor/tightenco/ziggy',
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
  ],
  testEnvironmentOptions: {
    customExportConditions: ['node', 'node-addons'],
  },
};

