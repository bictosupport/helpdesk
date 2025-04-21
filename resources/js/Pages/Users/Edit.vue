<template>
  <div>
    <Head :title="title" />
    <div class="max-w-full bg-white rounded-md shadow overflow-hidden">
      <form @submit.prevent="update">
          <div class="flex flex-wrap -mb-8 -mr-6 p-8">
              <text-input v-model="form.first_name" :error="form.errors.first_name" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('First name')" />
              <text-input v-model="form.last_name" :error="form.errors.last_name" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Last name')" />
              <text-input v-model="form.email" :error="form.errors.email" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Email')" />
              <text-input v-model="form.phone" :error="form.errors.phone" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Phone')" />
              <text-input v-model="form.city" :error="form.errors.city" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('City')" />
              <text-input v-model="form.address" :error="form.errors.address" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Address')" />

              <!-- Country Select -->
              <select-input v-model="form.country_id" :error="form.errors.country_id" class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Country')">
                  <option :value="null" />
                  <option v-for="c in countries" :key="c.id" :value="c.id">{{ __(c.name) }}</option>
              </select-input>

              <!-- Department Select -->
              <select-input 
                  v-model="form.department_id" 
                  :error="form.errors.department_id" 
                  class="pr-6 pb-8 w-full lg:w-1/3"  
                  :label="__('Department')">
                  <option :value="null" />
                  <option v-for="department in departments" :key="department.id" :value="department.id">
                      {{ __(department.name) }}
                  </option>
              </select-input>

              <text-input v-model="form.password" :error="form.errors.password" class="pb-8 pr-6 w-full lg:w-1/3" type="password" autocomplete="new-password" :label="__('Password')" />
              
              <!-- Role Select -->
              <select-input v-if="user.id !== auth.user.id" v-model="form.role_id" :error="form.errors.role" class="pb-8 pr-6 w-full lg:w-1/3" :label="__('Role')">
                  <option :value="null" />
                  <option v-for="role in roles" :key="role.id" :value="role.id">{{ __(role.name) }}</option>
              </select-input>

              <!-- Photo Upload -->
              <file-input v-model="form.photo" :error="form.errors.photo" class="pb-8 pr-6 w-full lg:w-1/3" type="file" accept="image/*" label="Photo" />
              <div class="w-full lg:w-1/3 flex items-center justify-start">
                  <img v-if="user.photo_path" class="block mb-2 w-8 h-8 rounded-full" :src="user.photo_path" />
              </div>

              <div class="flex flex-col lg:flex-row w-full space-y-8 lg:space-y-0 lg:space-x-8">
  
              <!-- LEFT SIDE: Stacked Inputs -->
              <div class="w-full lg:w-2/3 space-y-6">
                <!-- Restriction End Date -->
                <text-input 
                    v-model="form.restrict_until" 
                    :error="form.errors.restrict_until" 
                    class="w-full mb-2" 
                    type="date" 
                    :label="__('Restrict Until (Date)')" 
                />

                <!-- Restriction Message -->
                <textarea-input 
                    v-model="form.restriction_message" 
                    :error="form.errors.restriction_message" 
                    class="w-full mb-2" 
                    :label="__('Restriction Message')" 
                />

                <!-- Remarks -->
                <textarea-input
                    v-model="form.remarks" 
                    :error="form.errors.remarks" 
                    class="w-full mb-2" 
                    :label="__('Remarks')" 
                />
              </div>

              <!-- RIGHT SIDE: History Trail -->
              <div class="w-full lg:w-1/3 ml-2">
                <h2 class="text-lg font-semibold mb-4">{{ __('Restriction History') }}</h2>

                <div v-if="user.restrictions && user.restrictions.length">
                  <ul class="space-y-4">
                    <li v-for="restriction in user.restrictions" :key="restriction.restriction_id" class="border p-4 rounded-md">
                      <p class="text-sm text-gray-700"><strong>Until:</strong> {{ restriction.up_to_date }}</p>
                      <p class="text-sm text-gray-700"><strong>Message:</strong> {{ restriction.restriction_message }}</p>
                      <p class="text-sm text-gray-700"><strong>Remarks:</strong> {{ restriction.remarks }}</p>
                      <p class="text-xs text-gray-500 mt-1">{{ restriction.created_at }}</p>
                    </li>
                  </ul>
                </div>

                <div v-else class="text-gray-500">
                  {{ __('No restriction history found.') }}
                </div>
              </div>
            </div>


          </div>

          <div class="flex items-center px-8 py-4 bg-gray-50 border-t border-gray-100 mt-4">
              <button v-if="user.id !== auth.user.id && user_access.user.delete" class="text-red-600 hover:underline" tabindex="-1" type="button" @click="destroy">
                  {{ __('Delete User') }}
              </button>
              <loading-button :loading="form.processing" class="btn-indigo ml-auto" type="submit">{{ __('Update User') }}</loading-button>
          </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout'
import TextInput from '@/Shared/TextInput'
import FileInput from '@/Shared/FileInput'
import SelectInput from '@/Shared/SelectInput'
import LoadingButton from '@/Shared/LoadingButton'
import TextareaInput from '@/Shared/TextareaInput'

export default {
  components: {
    FileInput,
    Head,
    Link,
    LoadingButton,
    SelectInput,
    TextInput,
    TextareaInput,
  },
  layout: Layout,
  props: {
    user: Object,
    auth: Object,
    countries: Array,
    departments: Array,  // Pass departments from the backend
    roles: Array,
    cities: Array,
    title: String,
  },
  remember: 'form',
  data() {
    return {
        user_access: this.$page.props.auth.user.access,
      form: this.$inertia.form({
        _method: 'put',
        first_name: this.user.first_name,
        last_name: this.user.last_name,
        email: this.user.email,
        phone: this.user.phone,
        city: this.user.city,
        address: this.user.address,
        country_id: this.user.country_id,
        department_id: this.user.department_id,  // Use department_id here
        password: '',
        role: this.user.role,
        role_id: this.user.role_id,
        photo: null,
        restrict_until: this.user.restrict_until,
        restriction_message: this.user.restriction_message || '',
        remarks: this.user.remarks || ''
      }),
      departments: this.$page.props.departments,  
    }
  },
  created() {
    // this.setDefaultValue(this.countries, 'country_id', 'United States')
  },
  methods: {
    setDefaultValue(arr, key, value){
      const find = arr.find(i=>i.name.match(new RegExp(value + ".*")))
      if(find){
        this.form[key] = find['id']
      }
    },
    update() {
      this.form.post(this.route('users.update', this.user.id), {
        onSuccess: () => this.form.reset('password', 'photo'),
      })
    },
    destroy() {
      if (confirm('Are you sure you want to delete this user?')) {
        this.$inertia.delete(route('users.destroy', this.user.id))
      }
    },
    restore() {
      if (confirm('Are you sure you want to restore this user?')) {
        this.$inertia.put(route('users.restore', this.user.id))
      }
    },
  },
}
</script>
