<template>
  <div class="p-6 rounded-lg shadow-md">
    <h2 class="mb-1 font-medium dark:text-[#EDEDEC] mb-4">Create New Deal</h2>
    
    <form @submit.prevent="submit" class="text-[13px] leading-[20px] flex-1 p-6 pb-12 lg:p-20 bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-bl-lg rounded-br-lg lg:rounded-tl-lg lg:rounded-br-none">
      <div class="space-y-6">
        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-[#EDEDEC] mb-4">Customer Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <BaseInput
              v-model="customer.first_name"
              label="First Name"
              placeholder="Enter first name"
              name="first_name"
              id="first_name"
              type="text"
              required
              :error="errors.first_name"
              @blur="validateField('first_name', customer.first_name)"
            />
            
            <BaseInput
              v-model="customer.last_name"
              label="Last Name"
              placeholder="Enter last name"
              name="last_name"
              id="last_name"
              type="text"
              required
              :error="errors.last_name"
              @blur="validateField('last_name', customer.last_name)"
            />
            
            <div class="md:col-span-2">
              <BaseInput
                v-model="customer.email"
                type="email"
                label="Email"
                placeholder="Enter email address"
                name="email"
                id="email"
                required
                :error="errors.email"
                @blur="validateField('email', customer.email)"
              />
            </div>
          </div>
        </div>

        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-[#EDEDEC] mb-4">Deal Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <BaseInput
              v-model="deal.name"
              label="Deal Name"
              placeholder="Enter deal name"
              name="deal_name"
              id="deal_name"
              type="text"
              required
              :error="errors.deal_name"
              @blur="validateField('deal_name', deal.name)"
            />
            
            <BaseSelect
              v-model="deal.source"
              label="Lead Source"
              placeholder="Select a source"
              name="source"
              id="source"
              type="select"
              :options="sourceOptions"
              required
              :error="errors.source"
              @blur="validateField('source', deal.source)"
            />
          </div>
        </div>

        <div class="flex justify-end space-x-4">  
          <BaseButton
            html-type="submit"
            type="primary"
            size="md"
            :loading="loading"
            loading-text="Creating Deal..."
            :disabled="!isFormValid"
          >
            Create Deal
          </BaseButton>
        </div>
        
        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
          <div class="flex">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <div class="ml-3">
              <p class="text-sm font-medium">{{ error }}</p>
            </div>
          </div>
        </div>
        
        <div v-if="created" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
          <div class="flex">
            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <div class="ml-3">
              <p class="text-sm font-medium">
                Deal created successfully! 
                <span class="font-semibold">{{ created.name }} (ID: {{ created.id }})</span>
              </p>
              <p class="text-sm mt-1">Manager: {{ created.manager }}</p>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import BaseInput from '../components/BaseInput.vue'
import BaseSelect from '../components/BaseSelect.vue'
import BaseButton from '../components/BaseButton.vue'

export default {
  name: 'CreateDeal',
  components: {
    BaseInput,
    BaseSelect,
    BaseButton
  },
  setup() {
    const customer = ref({ 
      first_name: '', 
      last_name: '', 
      email: '' 
    })
    const deal = ref({ 
      name: '', 
      source: '' 
    })
    
    const loading = ref(false)
    const error = ref(null)
    const created = ref(null)
    const errors = ref({})
    
    const sourceOptions = ref([
      { value: 'Source 1', label: 'Source 1' },
      { value: 'Source 2', label: 'Source 2' },
      { value: 'Source 3', label: 'Source 3' },
      { value: 'Source 4', label: 'Source 4' },
      { value: 'Source 5', label: 'Source 5' },
    ])
    
    const validateField = (field, value) => {
      const validationRules = {
        first_name: (val) => val.length >= 2 || 'First name must be at least 2 characters',
        last_name: (val) => val.length >= 2 || 'Last name must be at least 2 characters',
        email: (val) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) || 'Please enter a valid email address',
        deal_name: (val) => val.length >= 3 || 'Deal name must be at least 3 characters',
        source: (val) => val.length > 0 || 'Please select a source'
      }
      
      const rule = validationRules[field];

      if (rule) {
        const result = rule(value);
        errors.value[field] = result === true ? '' : result;
      }
    }
    
    const isFormValid = computed(() => {
      return customer.value.first_name.length >= 2 &&
             customer.value.last_name.length >= 2 &&
             /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(customer.value.email) &&
             deal.value.name.length >= 3 &&
             deal.value.source.length > 0
    })
    
    const submit = async () => {
      if (!isFormValid.value) {
        error.value = 'Please fill in all required fields correctly'
        return
      }
      
      loading.value = true
      error.value = null
      errors.value = {}
      
      try {
        const res = await axios.post('/deal/create', {
          customer: customer.value,
          deal: deal.value
        })
        
        resetForm();
        created.value = res.data.data;
      } catch (e) {
        error.value = e.response?.data?.message || e.message
      } finally {
        loading.value = false
      }
    }
    
    // Reset form
    const resetForm = () => {
      customer.value = { first_name: '', last_name: '', email: '' }
      deal.value = { name: '', source: '' }
      error.value = null
      errors.value = {}
      created.value = null
    }
    
    onMounted(() => {
      console.log('CreateDeal component mounted!')
    })
    
    return {
      customer,
      deal,
      loading,
      error,
      created,
      errors,
      sourceOptions,
      isFormValid,
      validateField,
      submit,
      resetForm
    }
  }
}
</script>
