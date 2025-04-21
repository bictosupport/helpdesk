<template>
    <Head title="Select Department & Phone" />
    <div class="p-6 min-h-screen flex justify-center items-center">
      <div class="w-full max-w-xl">
        <form class="mt-8 bg-white border rounded-lg shadow-xl overflow-hidden" @submit.prevent="submitForm">
          <div class="px-10 py-12">
            <h2 class="text-center font-bold text-xl">{{ __('Update Your Information') }}</h2>
            <div class="mx-auto mt-2 mb-6 w-24 border-b" />
  
            <p class="text-gray-500 text-sm text-center">
              {{ __('To proceed, please select your department and enter your phone number.') }}
            </p>
  
            <div class="p-3">
              <!-- Select Department -->
              <select-input v-model="form.department_id" :error="form.errors.department_id" class="pb-8 w-full" :label="__('Department')" required>
                <option :value="null"></option>
                <option v-for="department in departments" :key="department.id" :value="department.id">
                  {{ department.name }}
                </option>
              </select-input>
  
              <!-- Enter Phone Number -->
              <text-input v-model="form.phone" :error="form.errors.phone" class="pb-8 w-full" :label="__('Phone Number')" type="tel" required />
  
              <loading-button :loading="form.processing" class="ml-auto btn-indigo w-full items-center justify-center" type="submit">
                {{ __('Update Information') }}
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
  import SelectInput from '@/Shared/SelectInput';
  import TextInput from '@/Shared/TextInput';
  
  export default {
    components: {
      Head,
      LoadingButton,
      SelectInput,
      TextInput,
    },
    props: {
      departments: Array,
      user: Object,
    },
    data() {
      return {
        form: this.$inertia.form({
          department_id: this.user.department_id ?? null,
          phone: this.user.phone ?? '',
        }),
      };
    },
    methods: {
      async submitForm() {
        await this.$inertia.post(route('save-department-phone'), this.form);
      },
    },
  };
  </script>
  