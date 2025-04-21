<template>
  <Head title="OTP Verification" />
  <div class="p-6 min-h-screen flex justify-center items-center light">
    <div class="w-full max-w-xl">
      <form
        class="mt-8 bg-white dark:bg-slate-900 border border-gray-100 rounded-lg shadow-xl overflow-hidden"
        @submit.prevent="submitOtp"
      >
        <div class="px-10 py-12">
          <h2 class="text-center font-bold text-xl">{{ __('OTP Verification') }}</h2>
          <div class="mx-auto mt-2 mb-6 w-24 border-b" />

          <flash-messages />

          <p class="text-justify text-sm text-gray-500 mt-4 p-3">
            {{ __('We have sent a 6-digit OTP code to your email address.') }}<br />
            {{ __('Please enter it below. The code is valid for 2 minutes.') }}
          </p>

          <div class="flex flex-wrap p-3">
  <!-- ✅ Center the OTP Inputs -->
            <div class="w-full pb-2 flex justify-center">
              <InputOtp
                v-model="form.otp"
                :length="6"
                :autoFocus="true"
                :invalid="!!form.errors.otp"
                class="otp-inputs"
                size="large"
                integerOnly 
              />
            </div>

            <!-- ✅ Error message BELOW the inputs -->
            <div v-if="form.errors.otp" class="text-red-500 text-sm mt-1 text-center w-full">
              {{ form.errors.otp }}
            </div>

            <div class="flex justify-end items-center w-full mt-4">
              <loading-button
                :loading="resending"
                :disabled="!resendEnabled"
                @click.prevent="resendOtp"
                class="btn-outline"
              >
                {{ resendEnabled ? __('Resend OTP') : formattedTime }}
              </loading-button>
            </div>

            <loading-button
              :loading="form.processing"
              class="ml-auto btn-indigo w-full items-center justify-center mt-4"
              type="submit"
            >
              {{ __('Verify OTP') }}
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
// ✅ PrimeVue OTP input import
import InputOtp from 'primevue/inputotp';
import axios from 'axios';

export default {
  metaInfo: { title: 'OTP Verification' },
  components: {
    Head,
    FlashMessages,
    LoadingButton,
    InputOtp, // ✅ Register the PrimeVue OTP component
  },
  props: {
    otpExpiresAt: String, // ISO8601 format from backend
  },
  data() {
    return {
      form: this.$inertia.form({
        otp: '',
      }),
      timerInterval: null,
      remainingTime: 0,
      resending: false,
    };
  },
  computed: {
    resendEnabled() {
      return this.remainingTime <= 0;
    },
    formattedTime() {
      if (this.remainingTime <= 0) {
        return this.__('Resend OTP');
      }
      const minutes = String(Math.floor(this.remainingTime / 60)).padStart(2, '0');
      const seconds = String(this.remainingTime % 60).padStart(2, '0');
      return `${minutes}:${seconds}`;
    },
  },
  methods: {
    submitOtp() {
      this.form.post(route('otp.submit'), {
        onError: () => {
          console.error('OTP submission failed');
        },
      });
    },
    resendOtp() {
      if (!this.resendEnabled) return;

      this.resending = true;

      axios.post(route('otp.resend'))
        .then(response => {
          const newExpiry = response.data.otpExpiresAt;

          if (newExpiry) {
            this.setOtpExpiry(newExpiry);
          } else {
            console.warn('No otpExpiresAt returned');
          }
        })
        .catch(error => {
          console.error('Error resending OTP', error);
        })
        .finally(() => {
          this.resending = false;
        });
    },
    setOtpExpiry(expiryTime) {
      const now = new Date();
      const expiresAt = new Date(expiryTime);
      const diffInSeconds = Math.floor((expiresAt - now) / 1000);

      this.remainingTime = diffInSeconds > 0 ? diffInSeconds : 0;

      if (this.timerInterval) clearInterval(this.timerInterval);
      this.startTimer();
    },
    startTimer() {
      this.timerInterval = setInterval(() => {
        if (this.remainingTime > 0) {
          this.remainingTime--;
        } else {
          clearInterval(this.timerInterval);
        }
      }, 1000);
    },
  },
  mounted() {
    if (this.otpExpiresAt) {
      this.setOtpExpiry(this.otpExpiresAt);
    } else {
      console.warn('otpExpiresAt prop is missing!');
    }
  },
  beforeUnmount() {
    if (this.timerInterval) clearInterval(this.timerInterval);
  },
};
</script>

<style scoped>
.btn-outline {
  @apply border border-indigo-500 text-indigo-500 px-4 py-2 rounded hover:bg-indigo-500 hover:text-white transition duration-150 ease-in-out;
}

.btn-outline:disabled {
  @apply opacity-50 cursor-not-allowed;
}
.otp-inputs {
  display: flex;
  justify-content: center;
  gap: 0.75rem; /* more space between inputs */
}

.otp-inputs input {
  width: 50px;
  height: 50px;
  font-size: 1.5rem;
  text-align: center;
}
</style>
