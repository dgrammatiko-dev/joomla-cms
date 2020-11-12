<template>
  <media-modal
    v-if="$store.state.showPermissionsModal"
    :size="'md'"
    label-element="createFolderTitle"
    @close="close()"
  >
    <template #header>
      <h3
        id="createFolderTitle"
        class="modal-title"
      >
        {{ translate('COM_MEDIA_EDIT_PERMISSIONS') }}
      </h3>
    </template>
    <template #body>
        <form
          class="form"
          novalidate
          @submit.prevent="save"
        >
          <details v-for="group in groups" :key="group">
            <summary>{{ group.title }}</summary>
            <div class="form-group" v-for="action in actions">
              <label>{{action.name}}</label>
              <select v-bind:aria-controls="action.name">
                <option value="">Inherit</option>
                <option value="-1">Disallow</option>
                <option value="1">Allow</option>
              </select>
            </div>
          </details>
        </form>
    </template>
    <template #footer>
      <div>
        <button
          class="btn btn-secondary"
          @click="close()"
        >
          {{ translate('JCANCEL') }}
        </button>
        <button
          class="btn btn-success"
          :disabled="!isValid()"
          @click="save()"
        >
          {{ translate('JACTION_CREATE') }}
        </button>
      </div>
    </template>
  </media-modal>
</template>

<script>
import * as types from '../../store/mutation-types.es6';

export default {
  name: 'MediaPermissionsModal',
  data() {
    return {
      folder: '',
    };
  },
  computed: {
    groups() {
      let groups = {};
      const groupsElement = document.getElementById('default-groups');
      try {
        groups = JSON.parse(groupsElement.innerText);
      } catch (err) {
        console.log(err)
      }
      console.log(groups)
      return groups;
    },
    actions() {
      let actions = [];
      const actionsElement = document.getElementById('default-permission-actions');
      try {
        actions = JSON.parse(actionsElement.innerText);
      } catch (err) {
        console.log(err)
      }
      console.log(actions)
      return actions;
    },
  },
  methods: {
    /* Check if the the form is valid */
    isValid() {
      return (this.folder);
    },
    /* Close the modal instance */
    close() {
      this.reset();
      this.$store.commit(types.HIDE_PERMISSIONS_MODAL);
    },
    /* Save the form and create the folder */
    save() {
      // Check if the form is valid
      if (!this.isValid()) {
        // TODO show an error message to user for insert a folder name
        // TODO mark the field as invalid
        return;
      }

      // Create the directory
      this.$store.dispatch('createDirectory', {
        name: this.folder,
        parent: this.$store.state.selectedDirectory,
      });
      this.reset();
    },
    /* Reset the form */
    reset() {
      this.folder = '';
    },
  },
};
</script>
