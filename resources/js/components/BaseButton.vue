<template>
  <component
    :is="tag"
    :type="tag === 'button' ? htmlType : undefined"
    :disabled="disabled || loading"
    :autofocus="autofocus"
    :class="buttonClasses"
    @click="handleClick"
    @blur="handleBlur"
    @focus="handleFocus"
    @keydown="handleKeydown"
    @keyup="handleKeyup"
  >
    <!-- Loading spinner -->
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    
    <!-- Icon slot -->
    <slot v-if="!loading" name="icon"></slot>
    
    <!-- Button content -->
    <span v-if="loading && loadingText">{{ loadingText }}</span>
    <slot v-else></slot>
  </component>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'BaseButton',
  props: {
    // Button type
    type: {
      type: String,
      default: 'primary',
      validator: (value) => [
        'primary', 'secondary', 'success', 'danger', 'warning', 
        'info', 'light', 'dark', 'outline', 'ghost'
      ].includes(value)
    },
    
    // HTML attributes
    htmlType: {
      type: String,
      default: 'button',
      validator: (value) => ['button', 'submit', 'reset'].includes(value)
    },
    tag: {
      type: String,
      default: 'button',
      validator: (value) => ['button', 'a', 'router-link'].includes(value)
    },
    
    // State
    disabled: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    loadingText: {
      type: String,
      default: 'Loading...'
    },
    
    // HTML attributes
    autofocus: {
      type: Boolean,
      default: false
    },
    
    // Sizing
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
    },
    
    // Shape
    shape: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'rounded', 'pill', 'square'].includes(value)
    },
    
    // Full width
    block: {
      type: Boolean,
      default: false
    },
    
    // Icon only
    iconOnly: {
      type: Boolean,
      default: false
    }
  },
  
  emits: [
    'click',
    'blur',
    'focus',
    'keydown',
    'keyup'
  ],
  
  setup(props, { emit }) {
    // Computed classes
    const buttonClasses = computed(() => {
      const baseClasses = 'inline-flex items-center justify-center font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2'
      
      // Size classes
      const sizeClasses = {
        xs: props.iconOnly ? 'p-1' : 'px-2 py-1 text-xs',
        sm: props.iconOnly ? 'p-1.5' : 'px-3 py-1.5 text-sm',
        md: props.iconOnly ? 'p-2' : 'px-4 py-2 text-sm',
        lg: props.iconOnly ? 'p-2.5' : 'px-6 py-3 text-base',
        xl: props.iconOnly ? 'p-3' : 'px-8 py-4 text-lg'
      }
      
      // Shape classes
      const shapeClasses = {
        default: 'rounded-md',
        rounded: 'rounded-lg',
        pill: 'rounded-full',
        square: 'rounded-none'
      }
      
      // Type classes
      const typeClasses = {
        primary: 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500 disabled:bg-blue-300',
        secondary: 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500 disabled:bg-gray-300',
        success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 disabled:bg-green-300',
        danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 disabled:bg-red-300',
        warning: 'bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500 disabled:bg-yellow-300',
        info: 'bg-cyan-600 text-white hover:bg-cyan-700 focus:ring-cyan-500 disabled:bg-cyan-300',
        light: 'bg-gray-100 text-gray-900 hover:bg-gray-200 focus:ring-gray-500 disabled:bg-gray-50',
        dark: 'bg-gray-800 text-white hover:bg-gray-900 focus:ring-gray-500 disabled:bg-gray-600',
        outline: 'border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white focus:ring-blue-500 disabled:border-blue-300 disabled:text-blue-300',
        ghost: 'text-blue-600 hover:bg-blue-50 focus:ring-blue-500 disabled:text-blue-300'
      }
      
      // Block class
      const blockClass = props.block ? 'w-full' : ''
      
      // Disabled class
      const disabledClass = (props.disabled || props.loading) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'
      
      return [
        baseClasses,
        sizeClasses[props.size],
        shapeClasses[props.shape],
        typeClasses[props.type],
        blockClass,
        disabledClass
      ].filter(Boolean).join(' ')
    })
    
    // Event handlers
    const handleClick = (event) => {
      if (!props.disabled && !props.loading) {
        emit('click', event)
      }
    }
    
    const handleBlur = (event) => {
      emit('blur', event)
    }
    
    const handleFocus = (event) => {
      emit('focus', event)
    }
    
    const handleKeydown = (event) => {
      emit('keydown', event)
    }
    
    const handleKeyup = (event) => {
      emit('keyup', event)
    }
    
    return {
      buttonClasses,
      handleClick,
      handleBlur,
      handleFocus,
      handleKeydown,
      handleKeyup
    }
  }
}
</script>
