<template>
  <div class="sel__filter" :class="$attrs.class" ref="sel__filter">
    <div class="font-bold text-sm mb-1">{{ __(label) }} </div>
    <div v-if="itemValue && !editItem" class="font-light text-sm flex gap-3">
      {{ itemValue }}
      <icon
        v-if="editable"
        name="edit"
        @click="editItem = !editItem"
        class="w-4 h-4 mr-1 cursor-pointer"
      />
    </div>
    <div class="w-25 flex items-center">
      <input
        v-if="editItem"
        :id="id"
        ref="input"
        class="form-select w-25"
        :class="{ error: error }"
        type="text"
        v-bind="{ ...$attrs, class: null }"
        :placeholder="placeholder"
        @input="onInput"
        @focus="onFocus"
        @keydown.down.prevent="onArrowDown"
        @keydown.up.prevent="onArrowUp"
        @keydown.enter.tab.prevent="selectCurrentSelection"
        autocomplete="off"
      />
      <icon
        v-if="editItem"
        @click="editItem = false"
        name="close"
        class="w-4 h-4 ml-2 cursor-pointer"
      />
    </div>
    <div v-if="error" class="form-error">{{ error }}</div>
    <!-- Add class to the dropdown list -->
    <div
      v-if="isListVisible && items.length"
      class="i__filter__list scrollable-dropdown"
    >
      <ul>
        <li
          v-for="(item, index) in items"
          :key="index"
          @click="selectItem(item)"
        >
          {{ item.name }}
        </li>
      </ul>
    </div>
  </div>
</template>


<script>
import { v4 as uuid } from 'uuid'
import Icon from '@/Shared/Icon'

export default {
    components:{
        Icon
    },
  inheritAttrs: false,
  props: {
    placeholder: {
      type: String,
      default: '',
    },
    onInput: {
      type: Function,
    },
    items: {
      type: Array
    },
    id: {
      type: String,
      default() {
        return `select-input-filter-${uuid()}`
      },
    },
      editable: [Number, Boolean],
    error: String,
    label: String,
    value: String,
    modelValue: [String, Number, Boolean],
  },
  emits: ['update:modelValue'],
  data() {
    return {
      selectedValue: '',
      selected: this.modelValue,
      isListVisible: false,
        editItem: false,
        itemValue: this.value
    }
  },
  watch: {
    selected(selected) {
      this.$emit('update:modelValue', selected)
        this.editItem = false
    },
  },
  created() {

  },
  mounted() {
    document.addEventListener('click', this.close)
  },
  methods: {
    close (e) {
      if (!this.$el.contains(e.target)) {
        this.isListVisible = false
      }
    },
    onFocus(){
      this.isListVisible = true
    },
    selectItem(item){
      this.$refs.input.value = item.name
      this.selected = item.id
      this.selectedValue = item.name
      this.isListVisible = false
        this.editItem = false
        this.itemValue = item.name
    },
    focus() {
      this.$refs.input.focus()
    },
    select() {
      this.$refs.input.select()
    },
  },
}
</script>
<style>
.scrollable-dropdown {
  max-height: 200px; /* Set the maximum height for the dropdown */
  overflow-y: auto; /* Enable vertical scrolling */
  overflow-x: hidden; /* Hide horizontal scrolling (optional) */
  border: 1px solid #ddd; /* Optional: Add a border for clarity */
  border-radius: 4px; /* Optional: Rounded corners */
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Optional: Dropdown shadow */
}
</style>