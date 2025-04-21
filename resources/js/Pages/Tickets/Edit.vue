<template>
    <div>
        <Head :title="__(title)" />
        <div class="flex flex-wrap">
            <div class="max-w-full lg:w-3/5">
                <form @submit.prevent="update" class="bg-white rounded-md shadow overflow-hidden mr-2">
       
                 <div class="p-8 -mr-6 -mb-8 flex flex-wrap">
                    <!-- Restriction Banner Alert -->
                    <div
                        v-if="ticket.is_restricted && auth.user.role.slug == 'admin'"
                        class="w-full mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded text-sm"
                        >
                        <strong class="block mb-1">⚠️ {{ __('This user is currently restricted.') }}</strong>
                        <span>
                            {{ __('They are restricted until') }}:
                            <strong>{{ moment(ticket.restriction_until).format('MMMM D, YYYY') }}</strong>
                        </span>
                    </div>


                    <!--<div class="assigned_user relative pr-6 pb-8 w-full lg:w-1/3 flex flex-col">-->
                    <!--    <div class="font-bold text-sm mb-1">-->
                    <!--        {{ __('Customer') }}-->
                    <!--    </div>-->
                    <!--    <div class="font-light text-sm">-->
                    <!--        {{ ticket.user }}-->
                    <!--    </div>-->
                    <!--</div>-->





                    <div class="w-full "></div> <!-- Breakline -->

                        <!--Super Admin -->
                        <select-edit-input v-if="auth.user.role.slug !== 'customer'" placeholder="Search customer" :onInput="doFilter" :items="customers"
                                             v-model="form.user_id" :error="form.errors.user_id"
                                             class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Customer')"
                                           :value="ticket.user" :editable="user_access.ticket.update && !ticket.closed">
                        </select-edit-input>

                        <select-edit-input placeholder="Search priority" :items="priorities"
                                           v-model="form.priority_id" :error="form.errors.priority_id"
                                           class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Priority')"
                                           :value="ticket.priority" :editable="user_access.ticket.update && !ticket.closed">
                        </select-edit-input>
                        <select-edit-input v-if="!(hidden_fields && hidden_fields.includes('department'))" placeholder="Search department" :items="departments"
                                           v-model="form.department_id" :error="form.errors.department_id"
                                           class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Department')"
                                           :value="ticket.department" :editable="user_access.ticket.update && !ticket.closed">
                        </select-edit-input>

                        <select-edit-input v-if="auth.user.role.slug !== 'customer' && !(hidden_fields && hidden_fields.includes('assigned_to'))" placeholder="Search user" :onInput="doFilterUsersExceptCustomer" :items="usersExceptCustomers"
                                           v-model="form.assigned_to" :error="form.errors.assigned_to"
                                           class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Assigned to')"
                                           :value="ticket.assigned_user??'Not Assigned'" :editable="user_access.ticket.update && !ticket.closed">
                        </select-edit-input>

                        <select-edit-input placeholder="Select status to change" :items="statuses"
                                           v-model="form.status_id" :error="form.errors.status_id"
                                           class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Status')"
                                           :value="ticket.status?ticket.status.name:'N/A'" :editable="auth.user.role.slug !== 'customer' && user_access.ticket.update && !ticket.closed">
                        </select-edit-input>

                        <select-edit-input v-if="!(hidden_fields && hidden_fields.includes('ticket_type'))" placeholder="Search type" :items="types"
                                           v-model="form.type_id" :error="form.errors.type_id"
                                           class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Ticket type')"
                                           :value="ticket.type" :editable="user_access.ticket.update && !ticket.closed">
                        </select-edit-input>
                        

                        <select-edit-input v-if="!(hidden_fields && hidden_fields.includes('category'))" placeholder="Search category" :items="categories"
                                           v-model="form.category_id" :error="form.errors.category_id"
                                           class="pr-6 pb-8 w-full lg:w-1/3" :label="__('Category')"
                                           :value="ticket.category" :editable="user_access.ticket.update && !ticket.closed">
                        </select-edit-input>

                        <div class="assigned_user pr-6 pb-8 w-full lg:w-1/3 flex flex-col">
                            <div class="font-bold text-sm mb-1">{{ __('Created') }} </div>
                            <div class="font-light text-sm">{{ moment(ticket.created_at).fromNow() }}</div>
                        </div>

                        <div class="assigned_user pr-6 pb-8 w-full lg:w-1/3 flex flex-col">
                            <div class="font-bold text-sm mb-1">{{ __('Contact No') }} </div>
                            <div class="font-light text-sm"> {{ ticket.phone }} </div>
                        </div>

                        <text-edit-input :editable="user_access.ticket.update && !ticket.closed" v-model="form.subject" :value="ticket.subject" :error="form.errors.subject" class="pr-6 pb-8 w-full lg:w-2/3" :label="__('Subject')" />

                        <div class="assigned_user pr-6 pb-8 w-full lg:w-full flex flex-col">
                            <div class="w-25 flex gap-3">
                                <label class="form-label" >{{ __('Request Details') }}</label>
                                <icon v-if="!enableEditor && user_access.ticket.update && !ticket.closed" name="edit" @click="enableEditor=!enableEditor" class="w-4 h-4 mr-1 cursor-pointer" />
                            </div>
                            <div v-if="!enableEditor" class="font-light text-sm" v-html="ticket.details"></div>
                            <div v-if="enableEditor" class="w-25 flex items-center">
                                <ckeditor id="ticketDetails" :editor="editor" v-model="form.details" :config="editorConfig"></ckeditor>
                                <div class="w-4 h-4 cursor-pointer">
                                    <icon @click="enableEditor=false" name="close" class="w-4 h-4 ml-2 cursor-pointer" />
                                </div>
                            </div>
                        </div>


                        <!-- Super Admin Comment -->
                        <input ref="file" type="file" accept=".xlsx,.xls,image/*,.doc, .docx,.ppt, .pptx,.txt,.pdf, .zip" class="hidden" multiple="multiple" @change="fileInputChange" />
                        <div class="pr-6 pb-8 w-full lg:w-full flex-col">
                            <button type="button" class="btn flex justify-center items-center pb-3 border-0 pl-0" @click="fileBrowse">
                                <icon name="file" class="flex-shrink-0 h-5 fill-gray-400 pr-1" /> <strong>{{ __('Attach File') }}</strong>
                            </button>
                            <div v-if="attachments.length" class="flex items-center justify-between pr-6 pt-8 w-full" v-for="(file, fi) in attachments" :key="fi">
                                <div class="flex-1 pr-1">
                                    {{ file.name }} <span class="text-gray-500 text-xs">({{ getFileSize(file.size) }})</span> <a v-if="file.user" class="text-sm" :href="this.route('users.edit', file.user.id)">{{ file.user.first_name }} {{ file.user.last_name }}</a> at <span class="text-sm">{{ file.created_at }}</span>
                                </div>
                                <div class="a__buttons flex justify-end items-center ">
                                    <button type="button" class="btn flex items-center " @click="downloadAttachment(file)">
                                        {{ __('Download') }}</button>
                                <button 
                                    v-if="role_id !== 2 && role_id !== 11" 
                                    type="button" 
                                    class="btn flex items-center ml-3" 
                                    @click="removeAttachment(file, fi)">
                                    {{ __('Remove') }}
                                </button>

                                </div>
                            </div>
                            <div v-if="form.files.length" class="flex items-center justify-between pr-6 pt-8 w-full lg:w-1/2" v-for="(file, fi) in form.files" :key="fi">
                                <div class="flex-1 pr-1">
                                    {{ file.name }} <span class="text-gray-500 text-xs">({{ getFileSize(file.size) }})</span>
                                </div>
                                <button v-if="ticket.status && !ticket.closed" type="button" class="btn flex justify-center items-center" @click="fileRemove(file, fi)">
                                    {{ __('Remove') }}</button>
                            </div>
                        </div>

                        <div class="pr-6 pb-8 w-full lg:w-1/3 flex items-center" v-if="ticket.status && ticket.closed && ticket.review">
                            <div class="bg-white shadow-lg rounded-lg p-6 w-full">
                            <!-- Rating -->
                            <div class="flex items-center mb-4">
                                <strong class="text-xl font-semibold text-gray-800">{{ __('Rating') }}:</strong>
                                <div class="ml-2">
                                    <Rating v-model="ticket.review.rating" readonly class="text-yellow-500" />
                                </div>
                            </div>

                            <!-- Review Text -->
                            <p class="text-gray-700 text-md">
                                {{ ticket.review.review }}
                            </p>
                            </div>
                        </div>

                        <div class="flex flex-col w-full" v-if="ticket.status && ticket.closed && !ticket.review && auth.user.role.slug === 'customer'">
                            <TransitionRoot appear show as="template">
                                <Dialog as="div" @close="closeModal" class="relative z-10">
                                    <TransitionChild
                                    as="template"
                                    enter="duration-300 ease-out"
                                    enter-from="opacity-0"
                                    enter-to="opacity-100"
                                    leave="duration-200 ease-in"
                                    leave-from="opacity-100"
                                    leave-to="opacity-0"
                                    >
                                    <div class="fixed inset-0 bg-black/25" />
                                    </TransitionChild>

                                    <div class="fixed inset-0 overflow-y-auto">
                                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                                        <TransitionChild
                                        as="template"
                                        enter="duration-300 ease-out"
                                        enter-from="opacity-0 scale-95"
                                        enter-to="opacity-100 scale-100"
                                        leave="duration-200 ease-in"
                                        leave-from="opacity-100 scale-100"
                                        leave-to="opacity-0 scale-95"
                                        >
                                        <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                                            
                                            <!-- Your Rating and Review Form -->
                                            <div class="assigned_user star__review pb-5 w-full lg: w-full flex flex-col">
                                                <div class="font-bold">{{ __('How do you rate this support service?') }} </div>
                                                <div class="star-rating pt-5 text-center">
                                                    <Rating v-model="form.rating" :stars="5" class="custom-rating" />
                                                </div>
                                            </div>
                                            
                                            <textarea-input v-model="form.review" :error="form.errors.review" class="pr-6 pb-4 w-full " :label="__('Feedback')" />
                                            
                                            <div class="flex lg:w-1/4 mb-4">
                                                <button class="btn-indigo" type="button" @click="submitFormAndCloseModal">{{ __('Submit') }}</button>
                                            </div>

                                        </DialogPanel>
                                        </TransitionChild>
                                    </div>
                                    </div>
                                </Dialog>
                            </TransitionRoot>
                        </div>
                    </div>
                    <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center">
                        <button v-if="user_access.ticket.delete" class="text-red-600 hover:underline" tabindex="-1" type="button" @click="destroy">
                            {{ __('Delete') }}</button>
                        <loading-button :loading="form.processing" class="btn-indigo ml-auto" type="submit">{{ __('Save') }}</loading-button>
                    </div>
                </form>
            </div>
            <div class="max-w-full lg:w-2/5">
                <div class="bg-white rounded-md shadow overflow-hidden ml-2 chat-area comment-box flex-1 flex flex-col">
                    <div class="flex-3">
                        <div class="chat-header flex flex-col pb-3">
                            <h3 class="text-xl">{{ __('Ticket discussion') }}</h3>
                            <p class="text-sm font-light">{{ __('Comment histories for this ticket will be available here.') }}</p>
                        </div>
                    </div>
                    <div class="messages flex-1 overflow-auto reverse__order">
                        <div v-for="(comment, index) in comments.slice().reverse()" :key="index" class="message mb-4 flex">
                            <div v-if="comment.user_id !== user.id" class="flex-2">
                                <div class="w-12 h-12 relative">
                                    <!-- Show user's profile picture if it exists -->
                                    <img v-if="comment.user && comment.user.photo_path" :src="comment.user.photo_path" alt="chat-user" class="w-10 h-10 rounded-full mx-auto">
                                    
                                    <!-- Fallback to showing the user's first name initial if no photo is available -->
                                    <span v-else class="w-12 h-12 rounded-full mx-auto user_icon" alt="chat-user">{{ comment.user ? comment.user.first_name[0] : '' }}</span>
                                </div>
                            </div>


                            <div v-if="comment.user_id !== user.id" class="flex-1 px-2">
                                <h3 class="font-bold pb-2 text-sm pt-1" v-if="comment.user">
                                    {{ comment.user.first_name }} {{ comment.user.last_name }}
                                     <img 
                                          v-if="comment.user.role.is_verified === 1" 
                                          :src="`/images/badges/${comment.user.role.badge_color}`" 
                                          alt="Verified Badge" 
                                          class="verified-badge"
                                      />
                                </h3>
                                <div  v-if="comment.details" class="inline-block bg-gray-300 user-comment-round p-2 px-4 text-gray-700 leading-5">
                                    <span>{{ comment.details }}</span>
                                </div>
                                 <div v-if="comment.image" class="mt-2">
                                    <img
                                        :src="`/files/${comment.image}`"
                                        alt="Uploaded Image"
                                        class="max-w-80 max-h-96 rounded-lg cursor-pointer"
                                        @click="openImageModal(`/files/${comment.image}`)"
                                    />
                                </div>
                                <div class="pl-4"><small class="text-gray-500">{{ moment(comment.updated_at).fromNow(true) }}</small></div>
                            </div>

                            <div v-if="comment.user_id === user.id" class="flex-1 px-2 text-right">
                                <div v-if="comment.details" class="inline-block bg-blue rounded p-2 px-4 text-white leading-5">
                                    <span>{{ comment.details }}</span>
                                </div>
                                <div v-if="comment.image" class="mt-2">
                                    <img
                                        :src="`/files/${comment.image}`"
                                        alt="Uploaded Image"
                                        class="max-w-80 max-h-96 rounded-lg cursor-pointer ml-auto"
                                        @click="openImageModal(`/files/${comment.image}`)"
                                    />
                                </div>

                                <div class="pr-4">
                                    <small class="text-gray-500">{{ moment(comment.updated_at).fromNow(true) }}</small>
                                </div>
                            </div>
                               <!-- Image Modal -->
                                <div
                                v-if="showModal"
                                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-md"
                                >
                                    <!-- Vue Easy Lightbox -->
                                    <vue-easy-lightbox
                                        :visible="lightboxVisible"
                                        :imgs="[selectedImage]"
                                        :zoom-step="0.5"
                                        @hide="closeLightbox"
                                        class="relative"
                                    />
                                    <!-- Close Button -->
                                    <button
                                        class="absolute top-2 right-2 bg-gray-800 text-white rounded-full p-2"
                                        @click="closeLightbox"
                                        aria-label="Close Image Modal"
                                    >
                                    </button>
                                </div>
                        </div>
                    </div>
                    <div class="flex-2 pt-4 pb-3">
                        <div class="write bg-white shadow flex rounded-lg">
                            <div class="flex-1">
                                <div v-if="previewImage" class="relative my-2 ml-2 inline-block">
                                    <img :src="previewImage" alt="Image preview" class="max-w-sm max-h-16 rounded border" />
                                    
                                    <!-- X Button -->
                                    <button 
                                        @click="removePreviewImage" 
                                        class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center shadow-md hover:bg-red-600"
                                        title="Remove Image">
                                        ✕
                                    </button>
                                </div>

                                <textarea v-model="comment" name="message" 
                                    @keydown.enter.exact.prevent="submitComment"
                                    class="w-full block outline-none py-4 px-4 text-sm bg-transparent overflow-hidden" 
                                    rows="1"
                                    :placeholder="__('Write a comment and press enter to send...')" 
                                    autofocus
                                    @input="updateButtonState">
                                </textarea>
                            </div>
                            <div class="flex-2 w-35 p-2 flex content-center items-center">
                                  <!-- Trigger File Upload Button -->
                                <div class="flex-1 text-center" @click="triggerFileUpload">
                                   <span class="text-gray-400 hover:text-gray-800 mr-1">
                                        <span class="inline-block align-text-bottom">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                class="w-6 h-6"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M3 3h18c1.104 0 2 .896 2 2v14c0 1.104-.896 2-2 2H3c-1.104 0-2-.896-2-2V5c0-1.104.896-2 2-2zm16 12l-4-4-3 3-4-4-4 4"
                                                />
                                            </svg>
                                        </span>
                                    </span>

                                    <!-- Hidden File Input -->
                                    <input
                                        type="file"
                                        ref="fileInput"
                                        accept="image/*"
                                        class="hidden"
                                        @change="handleFileUpload"
                                    />
                                </div>
                                <div class="flex-1">
                                    <button 
                                        class="bg-blue w-10 h-10 rounded-full flex justify-center items-center" 
                                        @click="submitComment"
                                        :disabled="isCommentEmpty"
                                        :class="{ 'opacity-50 cursor-not-allowed': isCommentEmpty }"
                                    >
                                        <icon class="w-4 h-4 fill-gray-100" name="send"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import VueEasyLightbox from 'vue-easy-lightbox';

import { Link, Head } from '@inertiajs/vue3'
import Icon from '@/Shared/Icon'
import Layout from '@/Shared/Layout'
import TextInput from '@/Shared/TextInput'
import TextEditInput from '@/Shared/TextEditInput'
import SelectInput from '@/Shared/SelectInput'
import TextareaInput from '@/Shared/TextareaInput'
import LoadingButton from '@/Shared/LoadingButton'
import SelectInputFilter from '@/Shared/SelectInputFilter'
import SelectEditInput from '@/Shared/SelectEditInput'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import UploadAdapter from '@/Shared/UploadAdapter';
import moment from 'moment'
import axios from 'axios'
import CKEditor from '@ckeditor/ckeditor5-vue';
import { ref } from 'vue'
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from '@headlessui/vue';
import Rating from 'primevue/rating';
const value = ref(null);

const isOpen = ref(true);

export default {
    components: {
        LoadingButton,
        SelectInput,
        TextInput,
        TextEditInput,
        TextareaInput,
        Link,
        Head,
        Icon,
        SelectInputFilter,
        SelectEditInput,
        ckeditor: CKEditor.component,
        VueEasyLightbox,
        TransitionRoot,
        TransitionChild,
        Dialog,
        DialogPanel,
        DialogTitle,
        Rating,
    },
    layout: Layout,
    props: {
        title: String,
        ticket: Object,
        priorities: Array,
        statuses: Array,
        types: Array,
        departments: Array,
        categories: Array,
        customers: Array,
        usersExceptCustomers: Array,
        attachments: Array,
        comments: Array,
        auth: Object,
        hidden_fields: Object,
        role_id: Number,
    },
    remember: false,
    data() {
        return {
            user: this.$page.props.auth.user,
            type_status: [],
            comment: '',
            isCommentEmpty: true, // Tracks if the comment is empty
            previewImage: null,
            showModal: false, // Modal visibility
            lightboxVisible: false, // Lightbox visibility
            selectedImage: '', // Selected image URL
            editCustomer: false,
            enableEditor: false,
            user_access: this.$page.props.auth.user.access,
            editor: ClassicEditor,
            editorConfig: {
                toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'insertTable', 'blockQuote', '|', 'imageUpload', 'mediaEmbed', '|', 'undo', 'redo' ],
                table: {
                    toolbar: [ 'tableColumn', 'tableRow', 'mergeTableCells' ]
                },
                extraPlugins: [this.uploader],
            },
            form: this.$inertia.form({
                user_id: this.ticket.user_id,
                priority_id: this.ticket.priority_id,
                status_id: this.ticket.status_id,
                department_id: this.ticket.department_id,
                category_id: this.ticket.category_id,
                assigned_to: this.ticket.assigned_to,
                type_id: this.ticket.type_id,
                subject: this.ticket.subject,
                details: this.ticket.details,
                files: this.ticket.files,
                comments: this.ticket.comments,
                created_at: this.ticket.created_at,
                removedFiles: [],
                rating: 0,
                review: '',
            }),
        }
    },
    created() {
        if(this.auth.user.role.slug === 'customer' && this.statuses.length){
            this.type_status = this.statuses.filter(status=> (status.id === this.form.status_id) || status.name.match(/Close.*/))
        }else{
            this.type_status = this.statuses
        }
        this.moment = moment;
    },
    methods: {
        removePreviewImage() {
            this.previewImage = null;
            this.selectedFile = null; // Also clear the selected file if necessary
        },
        uploader(editor) {
            editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
                return new UploadAdapter( loader );
            };
        },
        doFilter(e){
            axios.get(this.route('filter.customers', {search: e.target.value})).then((res)=>{
                this.customers.splice(0, this.customers.length, ...res.data);
            })
        },
        doFilterUsersExceptCustomer(e){
            axios.get(this.route('filter.users_except_customer', {search: e.target.value})).then((res)=>{
                this.usersExceptCustomers.splice(0, this.usersExceptCustomers.length, ...res.data);
            })
        },
        fileInputChange(e) {
            let selectedFiles = e.target.files;
            for (let i = 0; i < selectedFiles.length; i++) {
                this.form.files.push(selectedFiles[i]);
            }
        },
        fileRemove(image, index) {
            this.form.files.splice(index, 1);
        },
        fileBrowse() {
            this.$refs.file.click()
        },
        downloadAttachment(file) {
            const link = document.createElement("a");
            link.href = window.location.origin + '/files/' + file.path;
            link.download = file.name;
            link.click();
        },
        removeAttachment(file, index) {
            this.attachments.splice(index, 1);
            this.form.removedFiles.push(file.id)
        },
        getFileSize(size) {
            const i = Math.floor(Math.log(size) / Math.log(1024))
            return (size / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'KB', 'MB', 'GB', 'TB'][i]
        },
        uploadFiles() {
            this.form.files = this.$refs.files.files
        },
        update() {
            this.form.post(this.route('tickets.update', this.ticket.id))
            this.form.files = []
            this.form.comment = ''
        },
        destroy() {
            if (confirm('Are you sure you want to delete this ticket?')) {
                this.$inertia.delete(this.route('tickets.destroy', this.ticket.id))
            }
        },
        restore() {
            if (confirm('Are you sure you want to restore this ticket?')) {
                this.$inertia.put(this.route('tickets.restore', this.ticket.id))
            }
        },
       // Trigger the file input dialog
        triggerFileUpload() {
            this.$refs.fileInput.click();
        },

        // Handle file input change
       handleFileUpload(event) {
            const file = event.target.files[0];
            if (file) {
                // Validate the file type (ensure it's an image)
                const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!validImageTypes.includes(file.type)) {
                    alert('Only image files are allowed!');
                    this.$refs.fileInput.value = ''; // Clear the input field
                    return;
                }

                this.selectedFile = file; // Set the selected file
                this.previewImage = URL.createObjectURL(file); // Show a preview of the image
            }
        },
        updateButtonState() {
            this.isCommentEmpty = this.comment.trim().length === 0;
        },
        submitComment() {
            if (this.isCommentEmpty) return; // Prevent submission if comment is empty

            const formData = new FormData();
            formData.append('comment', this.comment);
            formData.append('user_id', this.user.id);
            formData.append('ticket_id', this.ticket.id);

            if (this.selectedFile) {
                formData.append('file', this.selectedFile);
            }

            axios.post(this.route('ticket.comment'), formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            }).then((response) => {
                if (response.data) {
                    this.comments.push({ ...response.data, user_id: this.user.id });

                    // Reset the comment and disable the button again
                    this.comment = '';
                    this.isCommentEmpty = true;
                    this.previewImage = null;
                    this.selectedFile = null;

                    // Scroll to the bottom or trigger any UI update
                    this.$nextTick(() => {
                        const messagesContainer = this.$refs.messagesContainer;
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    });
                }
            }).catch(error => console.error('Error submitting comment:', error));
        },
        openImageModal(imageUrl) {
            this.selectedImage = imageUrl;
            this.showModal = true;
            this.lightboxVisible = true;
        },
        closeLightbox() {
        this.lightboxVisible = false;
        this.showModal = false;
        },
        submitFormAndCloseModal() {
            this.update(); // Call the update method to submit the form
            this.closeModal(); // Close the modal after submission
        },
        closeModal() {
            this.showModal = false; // Set showModal to false to hide the modal
        }
    },
}
</script>
<style>
    .verified-badge {
        width: 16px; /* Adjust size if necessary */
        height: 16px;
        vertical-align: middle; /* Aligns badge with the text */
        display: inline; /* Ensures badge stays inline with text */
    }
    /* Optional: reduce padding in parent h3 to make space */
    .font-bold.pb-2.text-sm.pt-1 {
        padding-bottom: 1px; /* Adjust padding as needed */
        padding-top: 1px;
    }
</style>
<style scoped>
/* Modal Styling */
.fixed {
  position: fixed;
}
.inset-0 {
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}
.backdrop-blur-md {
  backdrop-filter: blur(10px);
}

button {
  transition: background-color 0.2s ease-in-out;
}
button:hover {
  background-color: #555555;
}

/* Star Rating Styling */
.p-rating {
  display: inline-flex;
  gap: 0; /* No gap between stars */
}

/* Set star color to purple (same as btn-indigo) */
.p-rating-star-icon {
  color: #4c51bf; /* Indigo color */
}

/* Optionally, change the hover color for the stars */
.p-rating-star-icon:hover {
  color: #6b5b95; /* Slightly darker shade of purple */
}

/* Adjust the size of the stars if needed */
.p-rating-star {
  width: 30px;
  height: 30px;
}

</style>
