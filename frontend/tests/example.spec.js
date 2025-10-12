import { mount } from '@vue/test-utils'
import { describe, it, expect } from '@jest/globals'

// Example component for testing
const TestComponent = {
  template: '<div class="test-component">{{ message }}</div>',
  props: ['message'],
  data() {
    return {
      message: this.message || 'Hello Eco Power Tech Global!'
    }
  }
}

describe('Vue 3 Testing Setup', () => {
  it('should mount a component', () => {
    const wrapper = mount(TestComponent, {
      props: {
        message: 'Test message'
      }
    })

    expect(wrapper.text()).toContain('Test message')
    expect(wrapper.classes()).toContain('test-component')
  })

  it('should work with default props', () => {
    const wrapper = mount(TestComponent)
    expect(wrapper.text()).toContain('Hello Eco Power Tech Global!')
  })
})