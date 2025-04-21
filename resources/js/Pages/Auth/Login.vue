<template>
  <Head title="Login" />
  <div class="p-6 min-h-screen flex justify-center items-center light">
      <flash-messages />
      <div class="w-full max-w-md">
          <Link :href="route('home')"><logo class="block w-48 mx-auto fill-white" /></Link>
          <form class="auth mt-8 bg-white dark:bg-slate-900 border border-gray-100 rounded-lg shadow-xl overflow-hidden" @submit.prevent="login">
              <div class="px-8 py-5">
                  <h2 class="text-center font-bold text-xl">{{ __('Login') }}</h2>
                  <div class="mx-auto mt-2 w-24 border-b" />
                  <text-input v-model="form.email" :error="form.errors.email" class="mt-10" label="Email" type="email" autofocus autocapitalize="off" />
                  <text-input v-model="form.password" :error="form.errors.password" class="mt-6" label="Password" type="password" />
                  <label class="mt-6 select-none flex items-center" for="remember">
                      <input id="remember" v-model="form.remember" class="mr-1" type="checkbox" />
                      <span class="text-sm">Remember Me</span>
                  </label>
                  <div class="flex justify-center items-center pt-4">
                      <vue-recaptcha v-if="site_key" :sitekey="site_key"
                          size="normal"
                          theme="light"
                          @verify="recaptchaVerified"
                          @expire="recaptchaExpired"
                          @fail="recaptchaFailed"
                          @error="recaptchaError"
                          ref="vueRecaptcha">
                      </vue-recaptcha>
                  </div>
                  <loading-button :disabled="disable_login_button && site_key" :loading="form.processing" class="ml-auto btn-indigo w-full items-center justify-center mt-4" type="submit" :class="{'opacity-50 cursor-not-allowed': disable_login_button && site_key}">{{ __('Login') }}</loading-button>

                  <div class="mt-5 flex justify-center">
                      <Link class="ml-2 " :href="route('password.reset')">{{ __('Forgot your password?') }}</Link>
                  </div>
                  <div class="mt-5 flex justify-center">Don’t have an account? <Link class="ml-2 " :href="route('register')">{{ __('Register') }}</Link></div>

                  <!-- Google Login Button -->
                  <div class="mt-5 flex justify-center">
                      <a class="btn-google-login" :href="route('auth.google.redirect')">
                          <img src="https://developers.google.com/identity/images/btn_google_signin_dark_normal_web.png" alt="Login with Google" />
                      </a>
                  </div>
              </div>
          </form>
      </div>
  </div>
</template>


<script>
import Logo from '@/Shared/Logo'
import TextInput from '@/Shared/TextInput'
import LoadingButton from '@/Shared/LoadingButton'
import { Head, Link } from '@inertiajs/vue3'
import FlashMessages from '@/Shared/FlashMessages'
import vueRecaptcha from 'vue3-recaptcha2';

export default {
  metaInfo: { title: 'Login' },
  components: {
      FlashMessages,
    LoadingButton,
    Logo,
    TextInput,
      Head,
      Link,
      vueRecaptcha,
  },
    props: {
        is_demo: Number,
        site_key: String
    },
  data() {
    return {
        loadingTimeout: 30000,
        disable_login_button: true,
      form: this.$inertia.form({
        email: '',
        password: '',
        remember: false,
      }),
    }
  },
  methods: {
      recaptchaVerified(response) {
          this.disable_login_button = false
          console.log(response)
      },
      recaptchaExpired() {
          this.$refs.vueRecaptcha.reset();
      },
      recaptchaFailed() {
      },
      recaptchaError(reason) {
          console.log(reason)
      },
      login() {
          this.form.post(this.route('login.store'))
      },
      autofillLogin(e, role, login){
          e.preventDefault()
          const roleEmails = { 'admin': 'john.due.helo@mail.com', 'manager': 'robert.slaughter@mail.com', 'customer' : 'mmarks@example.com'}
          this.form.email = roleEmails[role]
          this.form.password = 'w3bd.com'
          if(login){
              this.login();
          }
      }
  }
}
</script>
