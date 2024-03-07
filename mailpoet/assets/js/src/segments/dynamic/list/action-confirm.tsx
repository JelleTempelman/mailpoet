import { __experimentalConfirmDialog as ConfirmDialog } from '@wordpress/components';
import { dispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { __, _n, sprintf } from '@wordpress/i18n';
import { DynamicSegment, DynamicSegmentAction } from '../types';
import { MailPoet } from '../../../mailpoet';
import { storeName } from '../store';
import { DynamicSegmentResponse, isErrorResponse } from '../../../ajax';

async function bulkAction(
  action: DynamicSegmentAction,
  segments: DynamicSegment[],
) {
  if (!action) {
    return;
  }
  try {
    const response: DynamicSegmentResponse = await MailPoet.Ajax.post({
      api_version: 'v1',
      endpoint: 'dynamic_segments',
      action: 'bulk_action',
      data: {
        action,
        listing: {
          selection: segments.map((segment) => segment.id),
        },
      },
    });

    if (response.meta.errors && response.meta.errors.length > 0) {
      response.meta.errors.forEach(
        (error: string) => void dispatch(noticesStore).createErrorNotice(error),
      );
    }

    const count = response.meta.count;
    if (count > 0) {
      let successMessage = '';
      switch (action) {
        case 'trash':
          successMessage = sprintf(
            /* translators: %d - number of segments */
            _n(
              'Segment moved to trash.',
              '%d segments moved to trash.',
              count,
              'mailpoet',
            ),
            count,
          );
          break;
        case 'delete':
          successMessage = sprintf(
            /* translators: %d - number of segments */
            _n(
              'Segment permanently deleted.',
              '%d segments permanently deleted.',
              count,
              'mailpoet',
            ),
            count,
          );
          break;
        case 'restore':
          successMessage = sprintf(
            /* translators: %d - number of segments */
            _n('Segment restored.', '%d segments restored.', count, 'mailpoet'),
            count,
          );
          break;
        default:
          break;
      }
      void dispatch(noticesStore).createSuccessNotice(successMessage);
      void dispatch(storeName).loadDynamicSegments();
    }
  } catch (errorResponse: unknown) {
    if (isErrorResponse(errorResponse)) {
      let errorMessage = '';
      if (errorResponse.errors) {
        errorResponse.errors.forEach(
          (error) =>
            void dispatch(noticesStore).createErrorNotice(error.message),
          { explicitDismiss: true },
        );
      } else {
        switch (action) {
          case 'trash':
            errorMessage = __('Error moving segment to trash.', 'mailpoet');
            break;
          case 'delete':
            errorMessage = __('Error deleting segment.', 'mailpoet');
            break;
          case 'restore':
            errorMessage = __('Error restoring segment.', 'mailpoet');
            break;
          default:
            break;
        }
        void dispatch(noticesStore).createErrorNotice(errorMessage, {
          explicitDismiss: true,
        });
      }
    }
  }
}

type ActionConfirmProps = {
  action: DynamicSegmentAction;
  selected: DynamicSegment[];
  onClose: () => void;
};

export function ActionConfirm({
  action,
  selected,
  onClose,
}: ActionConfirmProps): JSX.Element | null {
  if (action === null) {
    return null;
  }

  let title = '';
  let message: string | JSX.Element = '';
  let confirmText = '';
  const lastSelected =
    selected.length > 0 ? `"${selected[selected.length - 1].name}"` : '';
  const firstSelected =
    selected.length > 1
      ? selected
          .slice(0, -1)
          .map((segment) => `"${segment.name}"`)
          .join(', ')
      : lastSelected;

  switch (action) {
    case 'trash':
      title = _n(
        'Trash selected segment',
        'Trash selected segments',
        selected.length,
        'mailpoet',
      );
      message = sprintf(
        _n(
          'Are you sure you want to trash the selected segment %s?',
          'Are you sure you want to trash the selected segments %s, and %s?',
          selected.length,
          'mailpoet',
        ),
        firstSelected,
        lastSelected,
      );
      confirmText = __('Trash', 'mailpoet');
      break;
    case 'restore':
      title = _n(
        'Restore selected segment',
        'Restore selected segments',
        selected.length,
        'mailpoet',
      );
      message = sprintf(
        _n(
          'Are you sure you want to restore the selected segment %s?',
          'Are you sure you want to restore segments %s, and %s?',
          selected.length,
          'mailpoet',
        ),
        firstSelected,
        lastSelected,
      );
      confirmText = __('Restore', 'mailpoet');
      break;
    case 'delete':
      title = _n(
        'Delete selected segment permanently',
        'Delete selected segments permanently',
        selected.length,
        'mailpoet',
      );
      message = (
        <>
          {sprintf(
            _n(
              'Are you sure you want to delete the selected segment %s permanently?',
              'Are you sure you want to delete the selected segments %s, and %s permanently?',
              selected.length,
              'mailpoet',
            ),
            firstSelected,
            lastSelected,
          )}{' '}
          <strong>{__('This action can not be reversed.', 'mailpoet')}</strong>
        </>
      );
      confirmText = __('Delete permanently', 'mailpoet');
      break;
    default:
      break;
  }

  return (
    <ConfirmDialog
      className="mailpoet-confirm-dialog"
      isOpen
      title={title}
      confirmButtonText={confirmText}
      __experimentalHideHeader={false}
      onConfirm={() => {
        void bulkAction(action, selected);
        onClose();
      }}
      onCancel={onClose}
    >
      <p>{message}</p>
    </ConfirmDialog>
  );
}
