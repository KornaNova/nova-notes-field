<template>
  <template v-if="isEditing">
    <NoteInput
      v-model.trim="editedText"
      v-model:dueDate="editedDueDate"
      v-model:assignedTo="editedAssignedTo"
      @onSubmit="editNote"
      :loading="loading"
      :fullWidth="fullWidth"
      :trixEnabled="trixEnabled"
      :users="users"
      :editing="true"
    />
  </template>
  <template v-else>
    <div
      class="o1-rounded-md o1-border o1-px-2 o1-py-2 o1-flex o1-mb-2 o1-mt-2"
      :class="{
        'w-full': fullWidth,
        'o1-w-3/5': !fullWidth,
        'o1-border-green-400/40 dark:o1-border-green-400/20 o1-bg-white dark:o1-bg-slate-700':
          !!note.pinned_at || !!note.due_date,
        'o1-border-gray-200 dark:o1-border-gray-700 o1-bg-white dark:o1-bg-slate-900':
          !note.pinned_at && !note.due_date,
        'o1-opacity-80': !!note.completed_at,
      }"
    >
      <div class="o1-rounded-md o1-w-12 o1-h-12 o1-mr-3 o1-overflow-hidden o1-text-center" style="flex-shrink: 0">
        <!-- Image -->
        <div
          v-if="note.system"
          class="o1-w-12 o1-h-12 o1-text-sm o1-font-bold o1-bg-gray-50 o1-text-gray-700 dark:o1-text-gray-400"
          style="line-height: 3rem"
        >
          {{ __('novaNotesField.systemUserAbbreviation') }}
        </div>
        <img class="o1-w-12 o1-h-12" v-else-if="note.created_by_avatar_url" :src="note.created_by_avatar_url" alt="" />
        <div
          v-else
          class="o1-w-12 o1-h-12 o1-text-sm o1-font-bold o1-bg-gray-50 o1-text-gray-700 dark:o1-text-gray-400"
          style="line-height: 3rem"
        >
          {{ !!note.created_by_name ? (note.created_by_name || '').substr(0, 3).toUpperCase() : '??' }}
        </div>
      </div>

      <div class="o1-flex-1 o1-flex o1-flex-col">
        <div class="o1-flex o1-justify-between o1-items-center">
          <!-- Title area -->
          <div class="o1-mb-1">
            <span class="o1-font-bold o1-text-base o1-text-gray-700 o1-mr-2 dark:o1-text-gray-300">
              {{ note.created_by_name ? note.created_by_name : __('novaNotesField.systemUserName') }}
            </span>

            <span class="o1-text-xs o1-text-gray-700 o1-mr-2 dark:o1-text-gray-300">
              {{ formattedCreatedAtDate }}{{ note.system ? ` [${__('novaNotesField.systemUserName')}]` : '' }}
            </span>

            <span
              v-if="!note.system && note.can_edit"
              class="o1-text-xs hover:o1-underline o1-cursor-pointer o1-text-primary-400 o1-mr-2"
              @click="onEditRequested"
              >[{{ __('novaNotesField.edit') }}]</span
            >
            <span
              v-if="!note.system && note.can_pin"
              class="o1-text-xs hover:o1-underline o1-cursor-pointer o1-text-primary-400 o1-mr-2"
              @click="togglePin"
              >[{{ note.pinned_at ? __('novaNotesField.unpin') : __('novaNotesField.pin') }}]</span
            >
            <span
              v-if="!note.system && note.can_complete"
              class="o1-text-xs hover:o1-underline o1-cursor-pointer o1-text-primary-400 o1-mr-2"
              @click="toggleComplete"
              >[{{ note.completed_at ? __('novaNotesField.reopen') : __('novaNotesField.complete') }}]</span
            >
            <span
              v-if="!note.system && note.can_delete"
              class="o1-text-xs hover:o1-underline o1-cursor-pointer"
              style="color: #e74c3c"
              @click="$emit('onDeleteRequested', note)"
              >[{{ __('novaNotesField.delete') }}]</span
            >
          </div>

          <!-- Task meta -->
          <div
            v-if="note.due_date || note.assignee_name || note.pinned_at || note.completed_at"
            class="o1-mb-2 o1-text-xs o1-flex o1-gap-2 dark:o1-text-white o1-font-bold o1-text-slate-700"
          >
            <div :class="badgeClassName" v-if="note.due_date">
              <span :class="{ 'o1-text-red-500 o1-font-semibold': isOverdue }">
                {{ __('novaNotesField.dueDate') }}: {{ formattedDueDate }}
              </span>
            </div>

            <div :class="badgeClassName" class="o1-mr-auto" v-if="note.assignee_name">
              <span>{{ __('novaNotesField.assignee') }}: {{ note.assignee_name }}</span>
            </div>

            <div :class="badgeClassName" v-if="note.pinned_at">
              <span class="o1-text-xs o1-font-semibold o1-text-green-500">
                {{ __('novaNotesField.pinned') }}
              </span>
            </div>

            <div :class="badgeClassName" v-if="note.completed_at">
              <span class="o1-text-xs o1-font-semibold o1-text-green-500"> {{ __('novaNotesField.completed') }} </span>
            </div>
          </div>
        </div>

        <!-- Content -->
        <p
          v-html="note.text"
          class="o1-whitespace-pre-wrap o1-text-sm o1-font-medium o1-text-gray-800 dark:o1-text-slate-200"
        ></p>
      </div>
    </div>
  </template>
</template>

<script>
import NoteInput from './NoteInput';
import { format } from 'date-fns';

export default {
  components: { NoteInput },
  props: {
    note: { type: Object, required: true },
    dateFormat: { type: String, default: 'dd MMM yyyy HH:mm' },
    fullWidth: { type: Boolean, default: false },
    trixEnabled: { type: Boolean, default: false },
    users: { type: Array, default: () => [] },
  },
  emits: ['noteEdited', 'pinChanged', 'completeChanged', 'onDeleteRequested'],
  data: () => ({
    isEditing: false,
    editedText: '',
    editedDueDate: null,
    editedAssignedTo: null,
    loading: false,
  }),
  computed: {
    badgeClassName() {
      return 'o1-bg-slate-100 dark:o1-bg-black/40 o1-border-black/5 dark:o1-border-white/15 o1-border o1-rounded o1-px-2 o1-py-1';
    },
    formattedCreatedAtDate() {
      return format(new Date(this.note.created_at), this.dateFormat);
    },
    dueDateParts() {
      if (!this.note.due_date) return null;
      const datePart = String(this.note.due_date).slice(0, 10);
      const [y, m, d] = datePart.split('-').map(Number);
      if (!y || !m || !d) return null;
      return { y, m, d };
    },
    formattedDueDate() {
      const parts = this.dueDateParts;
      if (!parts) return '';
      return format(new Date(parts.y, parts.m - 1, parts.d), 'dd.MM.yyyy');
    },
    isOverdue() {
      const parts = this.dueDateParts;
      if (!parts || this.note.completed_at) return false;
      const due = new Date(parts.y, parts.m - 1, parts.d);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      return due < today;
    },
  },
  methods: {
    onEditRequested() {
      this.editedText = this.note.text;
      this.editedDueDate = this.note.due_date ? String(this.note.due_date).slice(0, 10) : null;
      this.editedAssignedTo = this.note.assigned_to != null ? Number(this.note.assigned_to) : null;
      this.isEditing = true;
    },
    async editNote() {
      this.loading = true;

      try {
        await Nova.request().patch(`/nova-vendor/nova-notes/notes/${this.note.id}`, {
          note: this.editedText,
          due_date: this.editedDueDate,
          assigned_to: this.editedAssignedTo,
        });
        this.isEditing = false;
        this.$emit('noteEdited', { note: this.note, editedText: this.editedText });
        Nova.$emit('metric-refresh');
      } catch (e) {
        Nova.error('Unknown error when trying to edit the note.');
      }

      this.loading = false;
    },
    async togglePin() {
      this.loading = true;

      try {
        await Nova.request().patch(`/nova-vendor/nova-notes/notes/${this.note.id}/pin`, {
          pinned: !this.note.pinned_at,
        });
        this.$emit('pinChanged', this.note);
        Nova.$emit('metric-refresh');
      } catch (e) {
        Nova.error('Unknown error when trying to pin the note.');
      }

      this.loading = false;
    },
    async toggleComplete() {
      this.loading = true;

      try {
        await Nova.request().patch(`/nova-vendor/nova-notes/notes/${this.note.id}/complete`, {
          completed: !this.note.completed_at,
        });
        this.$emit('completeChanged', this.note);
        Nova.$emit('metric-refresh');
      } catch (e) {
        Nova.error('Unknown error when trying to update the note.');
      }

      this.loading = false;
    },
  },
};
</script>
