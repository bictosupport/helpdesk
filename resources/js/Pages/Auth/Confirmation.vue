<template>
    <Head title="Confirmation Code" />
    <div class="p-6 min-h-screen flex justify-center items-center light">
      <flash-messages />
      <div class="w-full max-w-md">
        <Link :href="route('home')">
          <logo class="block w-48 mx-auto fill-white" />
        </Link>
        <form class="auth mt-8 bg-white dark:bg-slate-900 border border-gray-100 rounded-lg shadow-xl overflow-hidden" @submit.prevent="submitCode">
          <div class="px-8 py-5">
            <h2 class="text-center font-bold text-xl">{{ __('Enter Confirmation Code') }}</h2>
            <div class="mx-auto mt-2 w-24 border-b"></div>
            <text-input
              v-model="form.code"
              :error="form.errors.code"
              class="mt-10"
              label="Confirmation Code"
              type="text"
              autofocus
            />
            <loading-button
              :disabled="form.processing"
              :loading="form.processing"
              class="ml-auto btn-indigo w-full items-center justify-center mt-4"
              type="submit"
            >
              {{ __('Submit') }}
            </loading-button>
            <div class="mt-5 flex justify-center">
              Didn’t receive the code? 
              <button
                class="ml-2 text-indigo-500 hover:underline"
                type="button"
                @click="resendCode"
                :disabled="form.processing"
              >
                {{ __('Resend Code') }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script>
  import Logo from '@/Shared/Logo';
  import TextInput from '@/Shared/TextInput';
  import LoadingButton from '@/Shared/LoadingButton';
  import { Head, Link } from '@inertiajs/vue3';
  import FlashMessages from '@/Shared/FlashMessages';
  
  export default {
    metaInfo: { title: 'Confirmation Code' },
    components: {
      FlashMessages,
      LoadingButton,
      Logo,
      TextInput,
      Head,
      Link,
    },
    data() {
      return {
        form: this.$inertia.form({
          code: '',
        }),
      };
    },
    methods: {
      submitCode() {
        this.form.post(this.route('confirmation.verify'), {
          onFinish: () => {
            this.form.reset('code'); // Optionally reset code input after submission
          },
        });
      },
      resendCode() {
        this.$inertia.post(this.route('confirmation.resend'), {}, {
          onSuccess: () => {
            this.$toast.success('Confirmation code resent successfully!');
          },
        });
      },
    },
  };
  </script>
  