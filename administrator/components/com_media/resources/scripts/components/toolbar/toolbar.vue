<template>
  <div
    class="media-toolbar"
    role="toolbar"
    :aria-label="translate('COM_MEDIA_TOOLBAR_LABEL')"
  >
    <div
      v-if="isLoading"
      class="media-loader"
    />
    <div class="media-view-icons">
      <a
        href="#"
        class="media-toolbar-icon media-toolbar-select-all"
        :aria-label="translate('COM_MEDIA_SELECT_ALL')"
        @click.stop.prevent="toggleSelectAll()"
      >
        <span
          :class="toggleSelectAllBtnIcon"
          aria-hidden="true"
        />
      </a>
    </div>
    <media-breadcrumb />
    <div
      class="media-view-search-input"
      role="search"
    >
      <label
        for="media_search"
        class="sr-only"
      >{{ translate('COM_MEDIA_SEARCH') }}</label>
      <input
        id="media_search"
        type="text"
        :placeholder="translate('COM_MEDIA_SEARCH')"
        @input="changeSearch"
      >
    </div>
    <div class="media-view-icons">
      <button
        v-if="isGridView"
        type="button"
        class="media-toolbar-icon media-toolbar-decrease-grid-size"
        :class="{disabled: isGridSize('xs')}"
        :aria-label="translate('COM_MEDIA_DECREASE_GRID')"
        @click.stop.prevent="decreaseGridSize()"
      >
        <span
          class="fas fa-search-minus"
          aria-hidden="true"
        />
      </button>
      <button
        v-if="isGridView"
        type="button"
        class="media-toolbar-icon media-toolbar-increase-grid-size"
        :class="{disabled: isGridSize('xl')}"
        :aria-label="translate('COM_MEDIA_INCREASE_GRID')"
        @click.stop.prevent="increaseGridSize()"
      >
        <span
          class="fas fa-search-plus"
          aria-hidden="true"
        />
      </button>
      <button
        type="button"
        href="#"
        class="media-toolbar-icon media-toolbar-list-view"
        :aria-label="translate('COM_MEDIA_TOGGLE_LIST_VIEW')"
        @click.stop.prevent="changeListView()"
      >
        <span
          :class="toggleListViewBtnIcon"
          aria-hidden="true"
        />
      </button>
      <button
        type="button"
        href="#"
        class="media-toolbar-icon media-toolbar-info"
        :aria-label="translate('COM_MEDIA_TOGGLE_INFO')"
        @click.stop.prevent="toggleInfoBar"
      >
        <span
          class="fas fa-info"
          aria-hidden="true"
        />
      </button>
    </div>
  </div>
</template>

<script>
import * as types from '../../store/mutation-types.es6';

export default {
  name: 'MediaToolbar',
  computed: {
    toggleListViewBtnIcon() {
      return (this.isGridView) ? 'fas fa-list' : 'fas fa-th';
    },
    toggleSelectAllBtnIcon() {
      return (this.allItemsSelected) ? 'fas fa-check-square' : 'fas fa-square';
    },
    isLoading() {
      return this.$store.state.isLoading;
    },
    atLeastOneItemSelected() {
      return this.$store.state.selectedItems.length > 0;
    },
    isGridView() {
      return (this.$store.state.listView === 'grid');
    },
    allItemsSelected() {
      // eslint-disable-next-line max-len
      return (this.$store.getters.getSelectedDirectoryContents.length === this.$store.state.selectedItems.length);
    },
  },
  methods: {
    toggleInfoBar() {
      if (this.$store.state.showInfoBar) {
        this.$store.commit(types.HIDE_INFOBAR);
      } else {
        this.$store.commit(types.SHOW_INFOBAR);
      }
    },
    decreaseGridSize() {
      if (!this.isGridSize('xs')) {
        this.$store.commit(types.DECREASE_GRID_SIZE);
      }
    },
    increaseGridSize() {
      if (!this.isGridSize('xl')) {
        this.$store.commit(types.INCREASE_GRID_SIZE);
      }
    },
    changeListView() {
      if (this.$store.state.listView === 'grid') {
        this.$store.commit(types.CHANGE_LIST_VIEW, 'table');
      } else {
        this.$store.commit(types.CHANGE_LIST_VIEW, 'grid');
      }
    },
    toggleSelectAll() {
      if (this.allItemsSelected) {
        this.$store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
      } else {
        // eslint-disable-next-line max-len
        this.$store.commit(types.SELECT_BROWSER_ITEMS, this.$store.getters.getSelectedDirectoryContents);
      }
    },
    isGridSize(size) {
      return (this.$store.state.gridSize === size);
    },
    changeSearch(query) {
      this.$store.commit(types.SET_SEARCH_QUERY, query.target.value);
    },
  },
};
</script>
