import apiFetch from '@wordpress/api-fetch';
import { Button } from '@wordpress/components';
import { StoreDescriptor, useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { confirmAlert } from '../../../../common/confirm_alert';
import { store } from '../../store';
import { Workflow } from '../workflow/types';
import { MailPoet } from '../../../../mailpoet';

export function TrashButton(): JSX.Element {
  const { createErrorNotice } = useDispatch(noticesStore as StoreDescriptor);
  const { workflow } = useSelect(
    (select) => ({
      workflow: select(store).getWorkflowData(),
    }),
    [],
  );

  const trash = () => {
    apiFetch({
      path: `/workflows/${workflow.id}`,
      method: 'PUT',
      data: {
        ...workflow,
        status: 'trash',
      },
    })
      .then(({ data }: { data: Workflow }) => {
        if (data.status !== 'trash') {
          void createErrorNotice('An error occurred!', {
            explicitDismiss: true,
          });
          return;
        }

        window.location.href = MailPoet.urls.automationListing;
      })
      .catch((): void => {
        void createErrorNotice('An error occurred!', {
          explicitDismiss: true,
        });
      });

    return true;
  };

  return (
    <Button
      isSecondary
      isDestructive
      onClick={() => {
        confirmAlert({
          title: 'Delete workflow',
          message: `You are about to delete the “${workflow.name}” workflow`,
          cancelLabel: 'Cancel',
          confirmLabel: 'Yes, delete',
          onConfirm: () => {
            trash();
          },
        });
      }}
    >
      Move to Trash
    </Button>
  );
}
