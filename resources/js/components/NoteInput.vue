<template>
  <div class="mb-4" :class="fullWidth ? 'w-full' : 'o1-w-3/5'">
    <div class="flex">
      <div v-if="trixEnabled" class="w-full">
        <Trix
          ref="trixEditor"
          @keydown.stop
          @change="val => $emit('update:modelValue', val)"
          :value="modelValue"
          :placeholder="placeholder"
          class="trix-content w-full form-control form-input form-input-bordered py-3 h-auto"
        />
      </div>

      <textarea
        v-else
        rows="3"
        :placeholder="placeholder"
        class="form-control w-full form-input form-input-bordered py-3 h-auto"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        @keydown.enter="onEnter"
      />

      <div class="whitespace-no-wrap ml-2">
        <Button
          class="o1-inline-flex o1-items-center o1-relative o1-ml-auto o1-whitespace-nowrap"
          @click="$emit('onSubmit')"
          type="button"
          :disabled="loading || !modelValue"
        >
          {{ editing ? __('novaNotesField.updateNote') : __('novaNotesField.addNote') }}
        </Button>
      </div>
    </div>

    <div class="flex items-center mt-2 gap-2">
      <label class="o1-text-xs o1-text-gray-600 dark:o1-text-gray-400 o1-mr-1">
        {{ __('novaNotesField.dueDate') }}
      </label>
      <input
        type="date"
        class="form-control form-input form-input-bordered o1-text-sm"
        :value="dueDate || ''"
        @input="$emit('update:dueDate', $event.target.value || null)"
      />

      <label class="o1-text-xs o1-text-gray-600 dark:o1-text-gray-400 o1-ml-2 o1-mr-1">
        {{ __('novaNotesField.assignee') }}
      </label>
      <div class="o1-flex-1">
        <AssigneeSelect
          :modelValue="assignedTo"
          @update:modelValue="value => $emit('update:assignedTo', value)"
          :users="users"
          :placeholder="__('novaNotesField.unassigned')"
          :no-options-text="__('novaNotesField.unassigned')"
          :no-result-text="__('novaNotesField.unassigned')"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { Button } from 'laravel-nova-ui';
import AssigneeSelect from './AssigneeSelect';

export default {
  components: { Button, AssigneeSelect },
  emits: ['update:modelValue', 'update:dueDate', 'update:assignedTo', 'onSubmit'],
  props: {
    placeholder: { type: String, default: '' },
    modelValue: { type: String, default: '' },
    dueDate: { type: String, default: null },
    assignedTo: { default: null },
    users: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    trixEnabled: { type: Boolean, default: false },
    fullWidth: { type: Boolean, default: false },
    editing: { type: Boolean, default: false },
  },
  methods: {
    onEnter(e) {
      if (e.shiftKey) return true;

      e.preventDefault();
      e.stopPropagation();
      this.$emit('onSubmit');
      return true;
    },
  },

  watch: {
    modelValue(newValue, oldValue) {
      if (this.trixEnabled && this.$refs.trixEditor) {
        if (!newValue && !!oldValue) this.$refs.trixEditor.$refs.theEditor.editor.loadHTML('');
      }
    },
  },
};
</script>
