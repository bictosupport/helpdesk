<template>
  <Head title="Select Department" />
  <div class="p-6 min-h-screen flex justify-center items-center light">
    <div class="w-full max-w-xl">
      <form class="mt-8 bg-white dark:bg-slate-900 border border-gray-100 rounded-lg shadow-xl overflow-hidden" @submit.prevent="submitForm">
        <div class="px-10 py-12">
          <h2 class="text-center font-bold text-xl">{{ __('Select Department') }}</h2>
          <div class="mx-auto mt-2 mb-6 w-24 border-b" />
          <flash-messages />

          <!-- Added message before department selection -->
          <p class="text-justify text-sm text-gray-500 mt-4 p-3">
            {{ __('Assalamualaikum,') }}<br />
            {{ __('In line with our services, we need to know for the records which office or department you belong to. This will help us process your requests more efficiently and avoid the need for you to fill in more details when filing a ticket.') }}
          </p>

          <div class="flex flex-wrap p-3">
            <select-input v-model="form.department_id" :error="form.errors.department_id" class="pb-8 pr-6 w-full" :label="__('Department')" :required="true">
              <option :value="null"></option>
              <option v-for="department in departments" :key="department.id" :value="department.id">
                {{ department.name }}
              </option>
            </select-input>

            <loading-button :loading="form.processing" class="ml-auto btn-indigo w-full items-center justify-center" type="submit">
              {{ __('Save Department') }}
            </loading-button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

  
  <script>
  import { Head } from '@inertiajs/vue3';
  import FlashMessages from '@/Shared/FlashMessages';
  import LoadingButton from '@/Shared/LoadingButton';
  import SelectInput from '@/Shared/SelectInput';
  
  export default {
    metaInfo: { title: 'Select Department' },
    components: {
      Head,
      FlashMessages,
      LoadingButton,
      SelectInput,
    },
    props: {
      departments: Array, // Receive departments as prop
    },
    data() {
      return {
        form: this.$inertia.form({
          department_id: null,
        }),
      };
    },
    methods: {
      async submitForm() {
        try {
          // Submit department selection
          await this.$inertia.post(route('save-department'), this.form);
        } catch (error) {
          console.error(error);
        }
      },
    },
  };
  </script>
  
  <style scoped>
  .container {
    max-width: 600px;
    margin: auto;
    padding-top: 50px;
  }
  </style>
  