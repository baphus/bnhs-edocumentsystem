import { mount } from '@vue/test-utils';
import EmailVerification from '../EmailVerification.vue';
import { useForm, usePage } from '@inertiajs/vue3';

// Mock Inertia.js composables
jest.mock('@inertiajs/vue3', () => {
  const mockPost = jest.fn();
  const mockForm = {
    post: mockPost,
    email: '',
    otp: '',
    errors: {},
    processing: false,
  };

  return {
    Head: {
      name: 'Head',
      template: '<div><slot /></div>',
      props: ['title'],
    },
    Link: {
      name: 'Link',
      template: '<a :href="href"><slot /></a>',
      props: ['href'],
    },
    useForm: jest.fn(() => mockForm),
    usePage: jest.fn(() => ({
      props: {
        flash: {},
      },
    })),
  };
});

describe('EmailVerification.vue', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('renders correctly', () => {
    const wrapper = mount(EmailVerification);
    expect(wrapper.text()).toContain('Access Your Dashboard');
  });

  it('shows email input form initially', () => {
    const wrapper = mount(EmailVerification);
    expect(wrapper.find('input[type="email"]').exists()).toBe(true);
  });

  it('displays correct heading text', () => {
    const wrapper = mount(EmailVerification);
    const heading = wrapper.find('h1');
    expect(heading.text()).toBe('Access Your Dashboard');
  });

  it('shows email form description initially', () => {
    const wrapper = mount(EmailVerification);
    expect(wrapper.text()).toContain('Verify your email to view your document requests');
  });

  it('has a submit button with correct text', () => {
    const wrapper = mount(EmailVerification);
    const submitButton = wrapper.find('button[type="submit"]');
    expect(submitButton.exists()).toBe(true);
    expect(submitButton.text()).toContain('Send Verification Code');
  });

  it('renders the BNHS logo and navigation', () => {
    const wrapper = mount(EmailVerification);
    expect(wrapper.text()).toContain('BNHS');
    expect(wrapper.text()).toContain('eDocument System');
    expect(wrapper.text()).toContain('Back to Home');
  });
});

