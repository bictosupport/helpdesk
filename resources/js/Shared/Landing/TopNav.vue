<template>
    <!-- New Navigation -->
    <!-- ====== Navbar Section Start -->
    <nav id="topnav" class="ud-header absolute top-0 left-0 z-40 flex w-full items-center bg-transparent">
        <div id="dropdown" />
        <div class="container">
            <div class="relative -mx-4 flex items-center justify-between">
                <div class="w-60 max-w-full px-4">
                    <Link :href="route('home')" class="logo pl-0 mt-2 mb-2">
                        <logo class="help-desk-logo"/>
                        <logo name="white" class="help-desk-logo white"/>
                    </Link>
                </div>
                <div class="flex w-full items-center justify-between px-4">
                    <div>
                        <button
                            id="navbarToggler"
                            class="absolute right-4 top-1/2 block -translate-y-1/2 rounded-lg px-3 py-[6px] ring-primary lg:hidden"
                        >
                <span
                    class="relative my-[6px] block h-[2px] w-[30px] bg-white"
                ></span>
                            <span
                                class="relative my-[6px] block h-[2px] w-[30px] bg-white"
                            ></span>
                            <span
                                class="relative my-[6px] block h-[2px] w-[30px] bg-white"
                            ></span>
                        </button>
                        <nav
                            id="navbarCollapse"
                            class="absolute right-4 top-full hidden w-full max-w-[250px] rounded-lg bg-white py-5 shadow-lg lg:static lg:block lg:w-full lg:max-w-full lg:bg-transparent lg:py-0 lg:px-4 lg:shadow-none xl:px-6"
                        >
                            <ul class="blcok lg:flex">
                                <li class="group relative" :class="{'active' : active_menu === 'home'}" @click="active_menu = 'home'">
                                    <Link :href="route('home')" class="ud-menu-scroll mx-8 flex py-2 text-base text-dark group-hover:text-primary lg:mr-0 lg:inline-flex lg:py-6 lg:px-0 lg:text-white lg:group-hover:text-white lg:group-hover:opacity-70">{{ __('Home') }}</Link>
                                </li>
                                <li v-if="!!this.enable_option && this.enable_option.service" class="group relative" :class="{'active' : active_menu === 'services'}" @click="active_menu = 'services'">
                                    <Link :href="route('services')" class="ud-menu-scroll mx-8 flex py-2 text-base text-dark group-hover:text-primary lg:mr-0 lg:inline-flex lg:py-6 lg:px-0 lg:text-white lg:group-hover:text-white lg:group-hover:opacity-70">{{ __('Services') }}</Link>
                                </li>
                                <li v-if="!!this.enable_option && this.enable_option.kb" class="group relative" :class="{'active' : active_menu === 'kb'}" @click="active_menu = 'kb'">
                                    <Link :href="route('kb')" class="ud-menu-scroll mx-8 flex py-2 text-base text-dark group-hover:text-primary lg:mr-0 lg:inline-flex lg:py-6 lg:px-0 lg:text-white lg:group-hover:text-white lg:group-hover:opacity-70">{{ __('Knowledge') }}</Link>
                                </li>
                                <li v-if="!!this.enable_option && this.enable_option.faq" class="group relative" :class="{'active' : active_menu === 'faq'}" @click="active_menu = 'faq'">
                                    <Link :href="route('faq')" class="ud-menu-scroll mx-8 flex py-2 text-base text-dark group-hover:text-primary lg:mr-0 lg:inline-flex lg:py-6 lg:px-0 lg:text-white lg:group-hover:text-white lg:group-hover:opacity-70">{{ __('FAQs') }}</Link>
                                </li>
                                <li v-if="!!this.enable_option && this.enable_option.blog" class="group relative" :class="{'active' : active_menu === 'blog'}" @click="active_menu = 'blog'">
                                    <Link :href="route('blog')" class="ud-menu-scroll mx-8 flex py-2 text-base text-dark group-hover:text-primary lg:mr-0 lg:inline-flex lg:py-6 lg:px-0 lg:text-white lg:group-hover:text-white lg:group-hover:opacity-70">{{ __('Blog') }}</Link>
                                </li>
                                <li v-if="!!this.enable_option && this.enable_option.contact" class="group relative" :class="{'active' : active_menu === 'contact'}" @click="active_menu = 'contact'">
                                    <Link :href="route('contact')" class="ud-menu-scroll mx-8 flex py-2 text-base text-dark group-hover:text-primary lg:mr-0 lg:inline-flex lg:py-6 lg:px-0 lg:text-white lg:group-hover:text-white lg:group-hover:opacity-70">{{ __('Contact') }}</Link>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <div v-if="$page.props.auth && $page.props.auth.user" class="justify-end pr-16 flex lg:pr-0">
                        <div class="dd__wrapper">
                            <Menu as="div" class="relative inline-block text-left">
                            <!-- Trigger Button -->
                            <div>
                            <MenuButton class="flex items-center cursor-pointer group mt-1 select_user">
                                <div class="flex items-center text-white">
                                <!-- User Image -->
                                <img
                                    v-if="$page.props.auth.user.photo"
                                    class="user_photo w-8 h-8 mr-2" 
                                    :alt="$page.props.auth.user.first_name"
                                    :src="$page.props.auth.user.photo"
                                />
                                <img
                                    v-else
                                    src="/images/svg/profile.svg"
                                    class="w-8 h-8 mr-2"
                                    alt="user profile"
                                />

                                <!-- User Names -->
                                <span class="text-sm font-medium">{{ $page.props.auth.user.first_name }}</span>
                                <span class="ml-1 text-sm font-medium">{{ $page.props.auth.user.last_name }}</span>
                                </div>
                                <icon class="w-5 h-5 drop-down-caret-icon" name="cheveron-down" />
                            </MenuButton>
                            </div>


                            <!-- Dropdown Menu -->
                            <transition
                                enter-active-class="transition duration-100 ease-out"
                                enter-from-class="transform scale-95 opacity-0"
                                enter-to-class="transform scale-100 opacity-100"
                                leave-active-class="transition duration-75 ease-in"
                                leave-from-class="transform scale-100 opacity-100"
                                leave-to-class="transform scale-95 opacity-0"
                            >
                                <MenuItems
                                class="absolute right-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
                                >
                                <div class="px-1 py-1">
                                    <!-- Dashboard -->
                                    <MenuItem v-slot="{ active }">
                                    <Link
                                        :class="[
                                        active ? 'bg-indigo-500 text-white' : 'text-gray-900',
                                        'block px-6 py-2 text-sm',
                                        ]"
                                        :href="route('dashboard')"
                                    >
                                        Dashboard
                                    </Link>
                                    </MenuItem>

                                    <!-- Tickets -->
                                    <MenuItem v-slot="{ active }">
                                    <Link
                                        :class="[
                                        active ? 'bg-indigo-500 text-white' : 'text-gray-900',
                                        'block px-6 py-2 text-sm',
                                        ]"
                                        :href="route('tickets')"
                                    >
                                        Tickets
                                    </Link>
                                    </MenuItem>

                                    <!-- Edit Profile -->
                                    <MenuItem v-slot="{ active }">
                                    <Link
                                        :class="[
                                        active ? 'bg-indigo-500 text-white' : 'text-gray-900',
                                        'block px-6 py-2 text-sm',
                                        ]"
                                        :href="route('users.edit.profile')"
                                    >
                                        Edit Profile
                                    </Link>
                                    </MenuItem>
                                    
                                    <!-- Logout -->
                                    <MenuItem v-slot="{ active }">
                                          <Link
                                            :class="[
                                                active ? 'bg-indigo-500 text-white' : 'text-gray-900',
                                                'block px-6 py-2 text-sm',
                                            ]"
                                             :href="route('logout')"
                                                method="delete"
                                            >
                                            Logout
                                            </Link>
                                    </MenuItem>
                                </div>
                                </MenuItems>
                            </transition>
                            </Menu>
                        </div>
                    </div>
                    <div v-else class="hidden justify-end pr-16 sm:flex lg:pr-0">
                        <a :href="route('login')" class="signUpBtn rounded-lg bg-white bg-opacity-20 py-3 px-6 text-base font-medium text-white duration-300 ease-in-out hover:bg-opacity-100 hover:text-dark">
                            Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
<script>
import Logo from '@/Shared/Logo'
import Dropdown from '@/Shared/Dropdown'
import Icon from '@/Shared/Icon'
import { Link } from '@inertiajs/vue3'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue' 

export default {
    components: {
        Logo,
        Icon,
        Dropdown,
        Link,
        Menu,          
        MenuButton,    
        MenuItems,   
        MenuItem,    
    },
    data() {
        return {
            active_menu: 'home',
            enable_option: {}
        }
    },
    computed: {
        selected_language() {
            return this.$page.props.languages.find(language => language.code === this.$page.props.locale)
        },
        languages_except_selected() {
            return this.$page.props.languages.filter(language => language.code !== this.$page.props.locale)
        }
    },
    methods: {
        toggleMenu() {
            document.getElementById('isToggle').classList.toggle('open');
            var isOpen = document.getElementById('navigation')
            if (isOpen.style.display === "block") {
                isOpen.style.display = "none";
            } else {
                isOpen.style.display = "block";
            }
        },
        windowScroll() {
            const navbar = document.getElementById("topnav");
            if (navbar != null) {
                if (
                    document.body.scrollTop >= 50 ||
                    document.documentElement.scrollTop >= 50
                ) {
                    navbar.classList.add("sticky");
                } else {
                    navbar.classList.remove("sticky");
                }
            }
        }
    },
    mounted() {
        this.active_menu = this.$page.url.substr(1) || 'home'
    },
    created() {
        if (this.$page.props.enable_options) {
            let options = JSON.parse(this.$page.props.enable_options.value)
            options.forEach(option => {
                this.enable_option[option.slug] = !!option.value
            })
        }

        window.addEventListener('scroll', (ev) => {
            ev.preventDefault();
            this.windowScroll();
        })

        window.onload = function(){
            const navbarToggler = document.getElementById('navbarToggler')
            const navbarCollapse = document.getElementById("navbarCollapse")
            navbarToggler.onclick = function() {
                navbarToggler.classList.toggle("navbarTogglerActive")
                navbarCollapse.classList.toggle("hidden")
            }
        }
    }
}
</script>
