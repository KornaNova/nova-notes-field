<template>
  <div class="nova-notes-multiselect o1-max-w-[260px]">
    <multiselect
      ref="multiselect"
      :value="selected"
      :options="computedOptions"
      track-by="value"
      label="label"
      :multiple="false"
      :close-on-select="true"
      :placeholder="placeholder"
      :allow-empty="true"
      selectLabel=""
      selectedLabel=""
      deselectLabel=""
      tagPlaceholder=""
      @select="onSelect"
      @open="onOpen"
      @close="onClose"
    >
      <template #clear>
        <div v-if="selected" class="multiselect__clear" @mousedown.prevent.stop="clear" />
      </template>

      <template #singleLabel>
        <span>{{ selected ? selected.label : '' }}</span>
      </template>

      <template #noOptions>
        {{ noOptionsText }}
      </template>

      <template #noResult>
        {{ noResultText }}
      </template>
    </multiselect>
  </div>
</template>

<script>
import Multiselect from 'vue-multiselect/src/Multiselect';

export default {
  components: { Multiselect },
  emits: ['update:modelValue'],
  props: {
    modelValue: { default: null },
    users: { type: Array, default: () => [] },
    placeholder: { type: String, default: '' },
    noOptionsText: { type: String, default: '' },
    noResultText: { type: String, default: '' },
  },
  data: () => ({
    overflowHiddenParent: null,
  }),
  computed: {
    computedOptions() {
      return (this.users || []).map(u => ({ value: u.id, label: u.name }));
    },
    selected() {
      if (this.modelValue == null) return null;
      const id = Number(this.modelValue);
      return this.computedOptions.find(o => o.value === id) || null;
    },
  },
  methods: {
    onSelect(option) {
      this.$emit('update:modelValue', option ? option.value : null);
    },
    clear() {
      this.$emit('update:modelValue', null);
    },
    onOpen() {
      // vue-multiselect renders its dropdown inside an overflow-hidden ancestor
      // (the notes field card) which would clip it. Temporarily lift overflow.
      if (!this.overflowHiddenParent) {
        let parent = this.$refs.multiselect.$el.parentElement;
        let found = null;
        while (parent && !found) {
          if (parent.classList.contains('overflow-hidden') || parent.classList.contains('o1-overflow-hidden')) {
            found = parent;
          }
          parent = parent.parentElement;
        }
        this.overflowHiddenParent = found;
      }

      if (this.overflowHiddenParent) this.overflowHiddenParent.style.overflow = 'visible';
    },
    onClose() {
      if (this.overflowHiddenParent) this.overflowHiddenParent.style.overflow = null;
    },
  },
};
</script>

<style lang="scss">
// Colors resolve to Nova's theme-driven CSS variables (its dynamic "gray",
// formerly a static "slate") so this control tracks the active Nova theme
// instead of hardcoded slate/white/red values.
.nova-notes-multiselect {
  .multiselect {
    min-height: 36px;
    border: none;
    border-radius: 0;
    background: none;
    display: block;
  }

  .multiselect__tags {
    --tw-border-opacity: 1;
    border-width: 1px;
    border-color: rgba(var(--colors-gray-300));
    background-color: rgba(var(--colors-white));
    color: rgba(var(--colors-gray-600));
    padding: 6px 56px 0 6px;
    min-height: 36px;
    border-radius: 4px;
    overflow: hidden;

    .dark & {
      border-color: rgba(var(--colors-gray-700));
      background-color: rgba(var(--colors-gray-900));
      color: rgba(var(--colors-gray-400));
    }
  }

  .multiselect__input {
    border: none;
    background-color: rgba(var(--colors-white));
    color: rgba(var(--colors-gray-400));
    font-size: 14px;
    line-height: 14px;
    padding-left: 8px;

    .dark & {
      background-color: rgba(var(--colors-gray-900));
      color: rgba(var(--colors-gray-400));
    }
  }

  .multiselect__single {
    background-color: rgba(var(--colors-white));
    color: rgba(var(--colors-gray-600));
    font-size: 14px;
    line-height: 18px;
    font-weight: 700;
    min-height: 18px;
    padding-top: 2px;
    padding-left: 8px;

    .dark & {
      color: rgba(var(--colors-gray-400));
      background-color: rgba(var(--colors-gray-900));
    }
  }

  .multiselect__placeholder {
    margin-bottom: 8px;
    padding-top: 0;
    padding-left: 8px;
    min-height: 16px;
    line-height: 16px;
    cursor: default;
    color: rgba(var(--colors-gray-600));

    .dark & {
      color: rgba(var(--colors-gray-500));
    }
  }

  .multiselect__select {
    height: 34px;
    width: 32px;
  }

  .multiselect__spinner {
    background-color: rgba(var(--colors-white));
    color: rgba(var(--colors-gray-600));
    height: 34px;
    border-radius: 4px;

    .dark & {
      background-color: rgba(var(--colors-gray-900));
      color: rgba(var(--colors-gray-400));
    }

    &:before,
    &:after {
      border-color: rgba(var(--colors-primary-500)) transparent transparent;
    }
  }

  .multiselect__content-wrapper {
    border-color: rgba(var(--colors-gray-300));
    transition: none;

    .dark & {
      border-color: rgba(var(--colors-gray-700));
    }

    li > span.multiselect__option {
      background-color: rgba(var(--colors-white));
      color: rgba(var(--colors-gray-400));
      min-height: 32px;
      font-size: 14px;
      line-height: 14px;

      .dark & {
        background-color: rgba(var(--colors-gray-900));
      }
    }

    .multiselect__element {
      background-color: rgba(var(--colors-white));
      color: rgba(var(--colors-gray-600));

      .dark & {
        background-color: rgba(var(--colors-gray-900));
        color: rgba(var(--colors-gray-400));
      }

      .multiselect__option {
        color: rgba(var(--colors-gray-600));
        padding: 8px 12px;
        min-height: 32px;
        font-size: 14px;
        line-height: 14px;

        .dark & {
          color: rgba(var(--colors-gray-400));
        }

        &.multiselect__option--selected {
          color: rgba(var(--colors-primary-500));
          background-color: rgba(var(--colors-white));

          .dark & {
            background-color: rgba(var(--colors-gray-900));
          }
        }

        &.multiselect__option--highlight {
          background-color: rgba(var(--colors-primary-500));
          color: rgba(var(--colors-white));

          &::after {
            background-color: rgba(var(--colors-primary-500));
            font-weight: 600;
          }

          &.multiselect__option--selected {
            background-color: rgba(var(--colors-red-400));

            .dark & {
              background-color: rgba(var(--colors-red-400));
            }
          }
        }
      }
    }
  }

  .multiselect--disabled {
    opacity: 0.7;

    .multiselect__tags {
      background-color: rgba(var(--colors-gray-50));
      color: rgba(var(--colors-gray-600));

      .dark & {
        background-color: rgba(var(--colors-gray-700));
        color: rgba(var(--colors-gray-400));
      }
    }

    .multiselect__current,
    .multiselect__select {
      background: none;
    }
  }

  .multiselect__clear {
    position: absolute;
    right: 36px;
    top: 8px;
    height: 20px;
    width: 20px;
    display: block;
    cursor: pointer;
    z-index: 2;

    &::before,
    &::after {
      content: '';
      display: block;
      position: absolute;
      width: 2px;
      height: 16px;
      background: rgba(var(--colors-gray-400));
      top: 0;
      right: 0;
      left: 0;
      bottom: 0;
      margin: auto;
    }

    &::before {
      transform: rotate(45deg);
    }

    &::after {
      transform: rotate(-45deg);
    }

    &:hover {
      &::before,
      &::after {
        background: rgba(var(--colors-red-400));
      }
    }
  }
}
</style>
