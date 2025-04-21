<template>
    <div>
        <Head :title="__(title)" />
        <div class="bg-white rounded-md shadow overflow-hidden w-full">
            <form @submit.prevent="update">
                <div class="p-8 -mr-6 -mb-8 flex flex-wrap">
                    <text-input v-model="form.name" :error="form.errors.name" class="pr-6 pb-8 w-full lg:w-1/2" :label="__('Name')" />
                    <text-input v-model="form.slug" :error="form.errors.slug" class="pr-6 pb-8 w-full lg:w-1/2" :label="__('Slug')" />

                   <div class="pr-6 pb-8 w-full lg:w-1/2">
                        <label class="font-bold text-sm">{{ __('Is Verified') }}</label>
                        <label class="flex toggle_swtich items-center cursor-pointer mt-2">
                            <div class="relative">
                                <!-- Checkbox toggles between true and false -->
                                <input 
                                    type="checkbox" 
                                    class="sr-only" 
                                    v-model="form.is_verified" 
                                    @change="toggleUpdate"
                                />
                                <div class="w-10 h-4 bg-gray-400 rounded-full shadow-inner"></div>
                                <div class="dot absolute w-6 h-6 bg-white rounded-full shadow -left-1 -top-1 transition"></div>
                            </div>
                            <span class="ml-3">{{ form.is_verified ? 'Verified' : 'Not Verified' }}</span>
                        </label>
                        <div v-if="form.errors.is_verified" class="text-red-500 text-xs mt-1">
                            {{ form.errors.is_verified }}
                        </div>
                    </div>

                  
                    <!-- Badge Selection -->
                    <div class="pr-6 pb-8 w-full lg:w-1/2">
                        <label class="font-bold text-sm">{{ __('Verified Badge') }}</label>
                        <div class="mt-2">
                          <select v-model="form.badge_color" class="p-2 border rounded" @change="updateBadge">
                              <option value="blue-verified.png">Blue</option>
                              <option value="dark-blue-verified.png">Dark Blue</option>
                              <option value="green-verified.png">Green</option>
                              <option value="orange-verified.png">Orange</option>
                              <option value="pink-verified.png">Pink</option>
                              <option value="purple-verified.png">Purple</option>
                              <option value="red-verified.png">Red</option>
                              <option value="teal-verified.png">Teal</option>
                              <option value="yellow-verified.png">Yellow</option>
                          </select>
                        </div>
                    </div>
                </div>

                <div class="py-8 -mr-6 -mb-8 flex flex-wrap overflow-x-auto">
                    <div class="py-2 inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="overflow-hidden">
                            <table class="min-w-full">
                                <thead class="border-b">
                                    <tr>
                                        <th scope="col" class="text-sm font-bold text-gray-900 px-6 py-4 text-left">Functions</th>
                                        <th scope="col" class="text-sm font-bold text-gray-900 px-6 py-4 text-left">Read</th>
                                        <th scope="col" class="text-sm font-bold text-gray-900 px-6 py-4 text-left">Update</th>
                                        <th scope="col" class="text-sm font-bold text-gray-900 px-6 py-4 text-left">Create</th>
                                        <th scope="col" class="text-sm font-bold text-gray-900 px-6 py-4 text-left">Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b" v-for="(value, name, index) in form.access" :key="index">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm capitalize text-gray-900">{{ name }}</td>
                                        <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                            <label class="flex toggle_swtich items-center cursor-pointer">
                                                <div class="relative">
                                                    <input type="checkbox" class="sr-only" v-model="value.read" @change="toggleUpdate" />
                                                    <div class="w-10 h-4 bg-gray-400 rounded-full shadow-inner"></div>
                                                    <div class="dot absolute w-6 h-6 bg-white rounded-full shadow -left-1 -top-1 transition"></div>
                                                </div>
                                            </label>
                                        </td>
                                        <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                            <label class="flex toggle_swtich items-center cursor-pointer">
                                                <div class="relative">
                                                    <input type="checkbox" class="sr-only" v-model="value.update" @change="toggleUpdate" />
                                                    <div class="w-10 h-4 bg-gray-400 rounded-full shadow-inner"></div>
                                                    <div class="dot absolute w-6 h-6 bg-white rounded-full shadow -left-1 -top-1 transition"></div>
                                                </div>
                                            </label>
                                        </td>
                                        <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                            <label class="flex toggle_swtich items-center cursor-pointer">
                                                <div class="relative">
                                                    <input type="checkbox" class="sr-only" v-model="value.create" @change="toggleUpdate" />
                                                    <div class="w-10 h-4 bg-gray-400 rounded-full shadow-inner"></div>
                                                    <div class="dot absolute w-6 h-6 bg-white rounded-full shadow -left-1 -top-1 transition"></div>
                                                </div>
                                            </label>
                                        </td>
                                        <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                            <label class="flex toggle_swtich items-center cursor-pointer">
                                                <div class="relative">
                                                    <input type="checkbox" class="sr-only" v-model="value.delete" @change="toggleUpdate" />
                                                    <div class="w-10 h-4 bg-gray-400 rounded-full shadow-inner"></div>
                                                    <div class="dot absolute w-6 h-6 bg-white rounded-full shadow -left-1 -top-1 transition"></div>
                                                </div>
                                            </label>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center">
                    <button v-if="!['admin','manager','customer'].includes(role.slug)" class="text-red-600 hover:underline" tabindex="-1" type="button" @click="destroy">{{ __('Delete') }}</button>
                    <loading-button :loading="form.processing" class="btn-indigo ml-auto" type="submit">{{ __('Update') }}</loading-button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import Layout from '@/Shared/Layout'
import { Link, Head } from '@inertiajs/vue3'
import TextInput from '@/Shared/TextInput'
import LoadingButton from '@/Shared/LoadingButton'

export default {
    metaInfo() {
        return { title: this.form.name }
    },
    components: {
        LoadingButton,
        TextInput,
        Link,
        Head,
    },
    layout: Layout,
    props: {
        title: String,
        role: Object,
    },
    remember: 'form',
    data() {
        return {
            form: this.$inertia.form({
                name: this.role.name,
                slug: this.role.slug,
                access: JSON.parse(this.role.access),
                is_verified: Boolean(this.role.is_verified),
                badge_color: this.role.badge_color || 'blue-verified.png', // Use the provided badge color or default
            }),
        };
    },
    methods: {
        update() {
            this.form.put(this.route('roles.update', this.role.id));
        },
        toggleUpdate() {
            this.form.put(this.route('roles.update', this.role.id), { preserveState: true });
        },
        updateBadge() {
            // Save the selected badge color
            this.form.put(this.route('roles.update', this.role.id), { preserveState: true });
        },
        destroy() {
            if (confirm('Are you sure you want to delete this role?')) {
                this.$inertia.delete(this.route('roles.destroy', this.role.id));
            }
        }
    }
}
</script>

<style>
    .verified-badge {
        width: 24px; /* Adjust size if necessary */
        height: 24px;
        vertical-align: middle; /* Aligns badge with the text */
        display: inline; /* Ensures badge stays inline with text */
    }
    /* Optional: reduce padding in parent h3 to make space */
    .font-bold.pb-2.text-sm.pt-1 {
        padding-bottom: 1px; /* Adjust padding as needed */
        padding-top: 1px;
    }
</style>
