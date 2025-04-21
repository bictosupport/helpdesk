<template>
    <Head title="Enter Phone Number" />
    <div class="p-6 min-h-screen flex justify-center items-center">
      <div class="w-full max-w-xl">
        <form class="mt-8 bg-white border rounded-lg shadow-xl overflow-hidden" @submit.prevent="submitForm">
          <div class="px-10 py-12">
            <h2 class="text-center font-bold text-xl">{{ __('Enter Your Phone Number') }}</h2>
            <div class="mx-auto mt-2 mb-6 w-24 border-b" />

            <p class="text-gray-500 text-sm text-center">
              {{ __('To ensure a smooth transaction and provide timely updates regarding your ticket, please enter your phone number.') }}
            </p>
  
            <div class="p-3">
              <text-input 
                v-model="form.phone" 
                :error="form.errors.phone" 
                class="pb-8 w-full" 
                :label="__('Phone Number')" 
                type="tel" 
                pattern="[0-9]*"
                inputmode="numeric"
                maxlength="11"
                required
                @keypress="blockNonNumeric"
                @input="validatePhone"
              />

              <!-- Show error if phone number is less than 11 digits -->
              <p v-if="form.phone.length > 0 && form.phone.length < 11" class="text-red-500 text-sm mt-2">
                {{ __('Phone number must be exactly 11 digits.') }}
              </p>

              <loading-button 
                :loading="form.processing" 
                class="ml-auto btn-indigo w-full items-center justify-center mt-4" 
                type="submit"
                :disabled="form.phone.length !== 11"
              >
                {{ __('Save Phone Number') }}
              </loading-button>
            </div>
          </div>
        </form>
      </div>
    </div>
</template>
  
<script>
import { Head } from '@inertiajs/vue3';
import LoadingButton from '@/Shared/LoadingButton';
import TextInput from '@/Shared/TextInput';

export default {
  components: {
    Head,
    LoadingButton,
    TextInput,
  },
  props: {
    user: Object,
  },
  data() {
    return {
      form: this.$inertia.form({
        phone: this.user.phone ?? '',
      }),
    };
  },
  methods: {
    // Block non-numeric characters at the input level
    blockNonNumeric(event) {
      if (!/[0-9]/.test(event.key)) {
        event.preventDefault();
      }
    },

    // Remove non-numeric characters and enforce 11-digit limit
    validatePhone(event) {
      this.form.phone = event.target.value.replace(/\D/g, '').slice(0, 11);
    },

    async submitForm() {
      await this.$inertia.post(route('save-phone'), this.form);
    },
  },
};
</script>
