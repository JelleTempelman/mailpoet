import { mainSidebarEmailTab } from './constants';
import { State } from './types';
import {
  getEditorLayout,
  getEditorSettings,
  getEmailStyles,
  getCdnUrl,
} from './settings';

export function getInitialState(): State {
  const searchParams = new URLSearchParams(window.location.search);
  const postId = parseInt(searchParams.get('postId'), 10);
  return {
    inserterSidebar: {
      isOpened: false,
    },
    listviewSidebar: {
      isOpened: false,
    },
    settingsSidebar: {
      activeTab: mainSidebarEmailTab,
    },
    postId,
    editorSettings: getEditorSettings(),
    styles: getEmailStyles(),
    layout: getEditorLayout(),
    autosaveInterval: 60,
    cdnUrl: getCdnUrl(),
    preview: {
      deviceType: 'Desktop',
      toEmail: window.MailPoetEmailEditor.current_wp_user_email,
      isModalOpened: false,
      isSendingPreviewEmail: false,
      sendingPreviewStatus: null,
    },
  };
}
