<template>
  <div class="w-full">
    <label 
      v-if="label" 
      :for="id" 
      class="block text-sm font-medium text-gray-700 dark:text-[#EDEDEC] mb-2"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    
    <div class="relative">
      <input
        :id="id"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :readonly="readonly"
        :maxlength="maxlength"
        :minlength="minlength"
        :min="min"
        :max="max"
        :step="step"
        :autocomplete="autocomplete"
        :autofocus="autofocus"
        :class="inputClasses"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
        @keydown="handleKeydown"
        @keyup="handleKeyup"
        @change="handleChange"
      />
      
      <!-- Icon slot -->
      <div v-if="$slots.icon" class="absolute inset-y-0 right-0 pr-3 flex items-center">
        <slot name="icon"></slot>
      </div>
    </div>
    
    <!-- Help text -->
    <p v-if="helpText" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
      {{ helpText }}
    </p>
    
    <!-- Error message -->
    <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </p>
  </div>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'BaseInput',
  props: {
    // v-model
    modelValue: {
      type: [String, Number],
      default: ''
    },
    
    // Basic props
    type: {
      type: String,
      default: 'text',
      validator: (value) => [
        'text', 'email', 'password', 'number', 'tel', 'url', 'search', 
        'date', 'datetime-local', 'time', 'month', 'week', 'color', 
        'file', 'hidden', 'checkbox', 'radio'
      ].includes(value)
    },
    
    // Form props
    label: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: ''
    },
    helpText: {
      type: String,
      default: ''
    },
    error: {
      type: String,
      default: ''
    },
    
    // Validation props
    required: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    readonly: {
      type: Boolean,
      default: false
    },
    
    // Input attributes
    id: {
      type: String,
      default: ''
    },
    maxlength: {
      type: Number,
      default: null
    },
    minlength: {
      type: Number,
      default: null
    },
    min: {
      type: [String, Number],
      default: null
    },
    max: {
      type: [String, Number],
      default: null
    },
    step: {
      type: [String, Number],
      default: null
    },
    autocomplete: {
      type: String,
      default: ''
    },
    autofocus: {
      type: Boolean,
      default: false
    },
    
    // Styling
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'filled', 'outlined'].includes(value)
    }
  },
  
  emits: [
    'update:modelValue',
    'blur',
    'focus',
    'input',
    'change',
    'keydown',
    'keyup'
  ],
  
  setup(props, { emit }) {
    // Computed classes
    const inputClasses = computed(() => {
      const baseClasses = 'w-full border rounded-md shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0'
      
      const sizeClasses = {
        sm: 'px-2 py-1 text-sm',
        md: 'px-3 py-2 text-sm',
        lg: 'px-4 py-3 text-base'
      }
      
      const variantClasses = {
        default: 'border-gray-300 focus:border-blue-500 focus:ring-blue-500',
        filled: 'border-gray-300 bg-gray-50 focus:border-blue-500 focus:ring-blue-500',
        outlined: 'border-2 border-gray-300 focus:border-blue-500 focus:ring-blue-500'
      }
      
      const stateClasses = props.error 
        ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
        : variantClasses[props.variant]
      
      const disabledClasses = props.disabled 
        ? 'bg-gray-100 cursor-not-allowed opacity-50' 
        : 'bg-white dark:bg-gray-800'
      
      return [
        baseClasses,
        sizeClasses[props.size],
        stateClasses,
        disabledClasses,
        'dark:text-white dark:border-gray-600'
      ].join(' ')
    })
    
    // Event handlers
    const handleInput = (event) => {
      emit('update:modelValue', event.target.value)
      emit('input', event)
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
    
    const handleChange = (event) => {
      emit('change', event)
    }
    
    return {
      inputClasses,
      handleInput,
      handleBlur,
      handleFocus,
      handleKeydown,
      handleKeyup,
      handleChange
    }
  }
}
</script>
