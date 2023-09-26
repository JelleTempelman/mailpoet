import { BlockInsertionPoint } from './state-types';
import { CustomField } from './form-data-types';

export type ToggleAction = {
  type: string;
  toggleTo: boolean;
};

export type ToggleBlockInserterAction = {
  type: string;
  value: boolean | BlockInsertionPoint;
};

export type CustomFieldStartedAction = {
  type: 'CREATE_CUSTOM_FIELD_STARTED';
  customField: CustomField;
};

export type ToggleSidebarPanelAction = {
  type: 'TOGGLE_SIDEBAR_PANEL';
  id: string;
  toggleTo?: boolean;
};
