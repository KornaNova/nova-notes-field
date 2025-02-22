<template>
  <Modal
    :show="show"
    @close="$emit('close')"
    @close-via-escape="$emit('close')"
    role="alertdialog"
    size="2xl"
    :use-focus-trap="false"
  >
    <div class="mx-auto bg-white dark:bg-gray-800 overflow-hidden rounded">
      <ModalHeader class="flex items-center">
        <span>
          {{ __('novaNotesField.deleteNoteTitle') }}
        </span>
      </ModalHeader>

      <ModalContent class="px-8 divide-y divide-gray-100 dark:divide-gray-800">
        <span>
          {{ __('novaNotesField.deleteConfirmation') }}
        </span>
      </ModalContent>

      <ModalFooter>
        <form class="ml-auto flex gap-2" @submit.prevent="handleConfirm">
          <Button type="button" variant="link" state="mellow" @click="$emit('close')">
            {{ __('novaNotesField.cancel') }}
          </Button>

          <Button id="confirm-delete-button" state="danger" ref="confirmButton" type="submit">
            {{ __('novaNotesField.delete') }}
          </Button>
        </form>
      </ModalFooter>
    </div>
  </Modal>
</template>

<script>
import { Button } from 'laravel-nova-ui';

export default {
  components: { Button },

  props: ['show'],

  methods: {
    handleConfirm() {
      this.$emit('confirm');
    },
  },
};
</script>
