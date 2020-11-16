<template>
  <li
    class="media-tree-item"
    :class="{active: isActive}"
    role="treeitem"
    :aria-level="level"
    :aria-setsize="size"
    :aria-posinset="counter"
    :tabindex="getTabindex"
  >
    <a @click.stop.prevent="onItemClick()">
      <span class="item-icon"><span :class="iconClass" /></span>
      <span class="item-name">{{ item.name }}</span>
    </a>
    <template v-if="hasPermissions">
      {{console.log(item)}}
      <a>
        <span @click.stop.prevent="onItemPermissionClick()" class="fas fa-unlock" aria-label="permissions"></span>
      </a>
    </template>
    <transition name="slide-fade">
      <media-tree
        v-if="hasChildren"
        v-show="isOpen"
        :aria-expanded="isOpen ? 'true' : 'false'"
        :root="item.path"
        :level="(level+1)"
      />
    </transition>
  </li>
</template>

<script>
import navigable from '../../mixins/navigable.es6';
import * as types from "../../store/mutation-types.es6";

export default {
  name: 'MediaTreeItem',
  mixins: [navigable],
  props: {
    item: {
      type: Object,
      required: true,
    },
    level: {
      type: Number,
      required: true,
    },
    counter: {
      type: Number,
      required: true,
    },
    size: {
      type: Number,
      required: true,
    },
  },
  computed: {
    console: () => console,
    window: () => window,
    hasPermissions() {
      return this.item.adapter && this.item.adapter.startsWith('virtual-')
    },
    isActive() {
      return (this.item.path === this.$store.state.selectedDirectory);
    },
    isOpen() {
      return this.$store.state.selectedDirectory.includes(this.item.path);
    },
    hasChildren() {
      return this.item.directories.length > 0;
    },
    iconClass() {
      return {
        fas: true,
        'fa-folder': !this.isOpen,
        'fa-folder-open': this.isOpen,
      };
    },
    getTabindex() {
      return this.isActive ? 0 : -1;
    },
  },
  methods: {
    onItemClick() {
      this.navigateTo(this.item.path);
    },
    onItemPermissionClick(e) {
      this.$store.commit(types.SHOW_PERMISSIONS_MODAL);
    }
  },
};
</script>
