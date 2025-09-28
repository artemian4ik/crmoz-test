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
      <select
        :id="id"
        :value="modelValue"
        :required="required"
        :disabled="disabled"
        :multiple="multiple"
        :size="size"
        :autofocus="autofocus"
        :class="selectClasses"
        @change="handleChange"
        @blur="handleBlur"
        @focus="handleFocus"
      >
        <!-- Placeholder option -->
        <option v-if="placeholder && !multiple" value="" disabled>
          {{ placeholder }}
        </option>
        
        <!-- Options -->
        <template v-if="options && options.length > 0">
          <option
            v-for="option in options"
            :key="getOptionValue(option)"
            :value="getOptionValue(option)"
            :disabled="getOptionDisabled(option)"
          >
            {{ getOptionLabel(option) }}
          </option>
        </template>
        
        <!-- Slot for custom options -->
        <slot></slot>
      </select>
      
      <!-- Custom arrow icon -->
      <div v-if="!multiple" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
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
  name: 'BaseSelect',
  props: {
    // v-model
    modelValue: {
      type: [String, Number, Array],
      default: ''
    },
    
    // Basic props
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
    
    // Options
    options: {
      type: Array,
      default: () => []
    },
    optionValue: {
      type: String,
      default: 'value'
    },
    optionLabel: {
      type: String,
      default: 'label'
    },
    optionDisabled: {
      type: String,
      default: 'disabled'
    },
    
    // Form props
    required: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    multiple: {
      type: Boolean,
      default: false
    },
    
    // Select attributes
    id: {
      type: String,
      default: ''
    },
    size: {
      type: Number,
      default: null
    },
    autofocus: {
      type: Boolean,
      default: false
    },
    
    // Styling
    selectSize: {
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
    'change',
    'blur',
    'focus'
  ],
  
  setup(props, { emit }) {
    // Computed classes
    const selectClasses = computed(() => {
      const baseClasses = 'w-full border rounded-md shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0 appearance-none'
      
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
      
      const multipleClasses = props.multiple ? 'pr-8' : 'pr-10'
      
      return [
        baseClasses,
        sizeClasses[props.selectSize],
        stateClasses,
        disabledClasses,
        multipleClasses,
        'dark:text-white dark:border-gray-600'
      ].join(' ')
    })
    
    // Helper methods for options
    const getOptionValue = (option) => {
      if (typeof option === 'string' || typeof option === 'number') {
        return option
      }
      return option[props.optionValue]
    }
    
    const getOptionLabel = (option) => {
      if (typeof option === 'string' || typeof option === 'number') {
        return option
      }
      return option[props.optionLabel]
    }
    
    const getOptionDisabled = (option) => {
      if (typeof option === 'string' || typeof option === 'number') {
        return false
      }
      return option[props.optionDisabled] || false
    }
    
    // Event handlers
    const handleChange = (event) => {
      const value = props.multiple 
        ? Array.from(event.target.selectedOptions, option => option.value)
        : event.target.value
      
      emit('update:modelValue', value)
      emit('change', event)
    }
    
    const handleBlur = (event) => {
      emit('blur', event)
    }
    
    const handleFocus = (event) => {
      emit('focus', event)
    }
    
    return {
      selectClasses,
      getOptionValue,
      getOptionLabel,
      getOptionDisabled,
      handleChange,
      handleBlur,
      handleFocus
    }
  }
}
</script>
